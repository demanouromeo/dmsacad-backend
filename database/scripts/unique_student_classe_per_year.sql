-- ============================================================================================
-- One classe per student per school year: audit, cleanup and unique constraint on student_classe.
--
-- Run it on EACH school database (one per school connection), in this order, reading the result
-- of every step before going on. Steps 1-2 only read. Step 3 backs up, step 4 deletes, step 5 adds
-- the constraint (it fails with error 1062 while duplicates remain, so it can never half-apply).
--
-- Rule: a student (stud_id = one row per matricule) has AT MOST ONE student_classe row per sy_id.
-- The same student in different school years is normal and untouched.
-- Marks (student_subject / stud_comp_mark) and discipline are keyed by stud_id, not by classe, so
-- removing an extra enrolment never deletes marks.
-- ============================================================================================

-- ---------------------------------------------------------------------------------------------
-- STEP 1 (read only) - how many students are enrolled twice or more in the same school year?
-- ---------------------------------------------------------------------------------------------
SELECT COUNT(*) AS students_with_duplicates
FROM (
    SELECT stud_id, sy_id
    FROM student_classe
    GROUP BY stud_id, sy_id
    HAVING COUNT(*) > 1
) d;

-- ---------------------------------------------------------------------------------------------
-- STEP 2 (read only) - the detail: who, which year, which classes. Review before deleting.
-- ---------------------------------------------------------------------------------------------
SELECT st.matricule,
       st.name,
       st.surname,
       sy.year,
       sc.student_classe_id,
       c.classe_name,
       sc.repeating,
       sc.created_at,
       -- 1 = the row STEP 4 keeps (the most recent enrolment), 0 = the rows it removes
       (sc.student_classe_id = (
            SELECT MAX(x.student_classe_id)
            FROM student_classe x
            WHERE x.stud_id = sc.stud_id AND x.sy_id = sc.sy_id
       )) AS kept
FROM student_classe sc
JOIN (
    SELECT stud_id, sy_id
    FROM student_classe
    GROUP BY stud_id, sy_id
    HAVING COUNT(*) > 1
) d ON d.stud_id = sc.stud_id AND d.sy_id = sc.sy_id
JOIN student st     ON st.stud_id = sc.stud_id
JOIN school_year sy ON sy.sy_id = sc.sy_id
JOIN classe c       ON c.classe_id = sc.classe_id
ORDER BY sy.year, st.name, st.surname, sc.student_classe_id;

-- ---------------------------------------------------------------------------------------------
-- STEP 3 - back up every row STEP 4 is about to delete (skip if STEP 1 returned 0).
-- Restore later with: INSERT INTO student_classe SELECT * FROM student_classe_dup_backup;
-- ---------------------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS student_classe_dup_backup LIKE student_classe;

INSERT INTO student_classe_dup_backup
SELECT sc.*
FROM student_classe sc
WHERE sc.student_classe_id NOT IN (
        SELECT keep_id FROM (
            SELECT MAX(student_classe_id) AS keep_id
            FROM student_classe
            GROUP BY stud_id, sy_id
        ) k
    )
  AND sc.student_classe_id NOT IN (SELECT student_classe_id FROM student_classe_dup_backup);

-- ---------------------------------------------------------------------------------------------
-- STEP 4 - keep ONE enrolment per (student, school year): the most recent one (highest
-- student_classe_id); delete the older duplicates. If the right classe is another one, fix it in
-- the app afterwards (change of classe) - or edit the keep rule here before running.
-- ---------------------------------------------------------------------------------------------
DELETE sc
FROM student_classe sc
JOIN (
    SELECT stud_id, sy_id, MAX(student_classe_id) AS keep_id
    FROM student_classe
    GROUP BY stud_id, sy_id
    HAVING COUNT(*) > 1
) k ON k.stud_id = sc.stud_id AND k.sy_id = sc.sy_id
WHERE sc.student_classe_id <> k.keep_id;

-- Must now return 0.
SELECT COUNT(*) AS remaining_duplicates
FROM (
    SELECT stud_id, sy_id
    FROM student_classe
    GROUP BY stud_id, sy_id
    HAVING COUNT(*) > 1
) d;

-- ---------------------------------------------------------------------------------------------
-- STEP 5 - the database itself now refuses a second enrolment for the same student and year.
-- Safe to re-run: only creates the index if it does not exist yet. (The backend's
-- StudentClasse model already blocks it too; this protects against any other writer.)
-- ---------------------------------------------------------------------------------------------
SET @idx_exists := (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'student_classe'
      AND index_name = 'uq_student_classe_stud_sy'
);
SET @ddl := IF(@idx_exists = 0,
    'ALTER TABLE student_classe ADD UNIQUE INDEX uq_student_classe_stud_sy (stud_id, sy_id)',
    'SELECT ''uq_student_classe_stud_sy already exists'' AS info');
PREPARE stmt FROM @ddl;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- To undo STEP 5 only:  ALTER TABLE student_classe DROP INDEX uq_student_classe_stud_sy;
