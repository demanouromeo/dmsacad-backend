<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\TeacherTimetableMail;

class TimetableController extends Controller
{
    private function validationError(\Throwable $th)
    {
        return response()->json([
            'status' => false,
            'message' => 'Validation failed: ' . $th->getMessage(),
        ], 422);
    }

    //--------------------------------------------------------------------------------------------
    // TT CONFIG (start time / break durations / period duration) - one row per school, find-or-create
    // by sy_id, same pattern as ClassifiedparamController::saveClassifiedParamOfYear.
    //--------------------------------------------------------------------------------------------

    public function getTtConfig(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
            ]);
        } catch (\Throwable $th) {
            return $this->validationError($th);
        }
        $connection = $request->input('connection');
        $year = $request->input('year');
        config(["database.default" => $connection]);
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $config = DB::select(
                "SELECT tt_config_id, start_time, duration_break1, duration_break2, period_duration,
                        number_of_period_before_break1_start, number_of_period_before_break2_start, sy_id
                 FROM tt_config WHERE sy_id = $sy_id"
            );
            return response()->json($config, 200);
        } catch (Exception $e) {
            return response()->json([], 500);
        }
    }

    public function saveTtConfig(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'start_time' => 'required|string|regex:/^\d{2}h\d{2}$/',
                'duration_break1' => 'required|integer|min:0',
                'duration_break2' => 'required|integer|min:0',
                'period_duration' => 'required|integer|min:30|max:60',
                'number_of_period_before_break1_start' => 'required|integer|min:1',
                'number_of_period_before_break2_start' => 'required|integer|min:1',
            ]);
        } catch (\Throwable $th) {
            return $this->validationError($th);
        }
        $connection = $request->input('connection');
        $year = $request->input('year');
        config(["database.default" => $connection]);
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $existing = DB::select("SELECT tt_config_id FROM tt_config WHERE sy_id = $sy_id");
            $startTime = $request->input('start_time');
            $break1 = $request->input('duration_break1');
            $break2 = $request->input('duration_break2');
            $periodDuration = $request->input('period_duration');
            $beforeBreak1 = $request->input('number_of_period_before_break1_start');
            $beforeBreak2 = $request->input('number_of_period_before_break2_start');

            if (count($existing) > 0) {
                DB::update(
                    "UPDATE tt_config SET start_time = ?, duration_break1 = ?, duration_break2 = ?,
                        period_duration = ?, number_of_period_before_break1_start = ?,
                        number_of_period_before_break2_start = ? WHERE sy_id = ?",
                    [$startTime, $break1, $break2, $periodDuration, $beforeBreak1, $beforeBreak2, $sy_id]
                );
            } else {
                DB::insert(
                    "INSERT INTO tt_config (start_time, duration_break1, duration_break2, period_duration,
                        number_of_period_before_break1_start, number_of_period_before_break2_start, sy_id)
                     VALUES (?, ?, ?, ?, ?, ?, ?)",
                    [$startTime, $break1, $break2, $periodDuration, $beforeBreak1, $beforeBreak2, $sy_id]
                );
            }
            return response()->json([
                'status' => true,
                'message' => 'Time table configuration saved successfully.',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to save time table configuration: ' . $e->getMessage(),
            ], 500);
        }
    }

    //--------------------------------------------------------------------------------------------
    // JOURS (school days). NOTE: `label` and `num` each carry a genuine single-column UNIQUE index
    // (confirmed via SHOW CREATE TABLE - not a composite with sy_id), so in practice this table can
    // only ever hold one coherent set of days school-wide, not one set per school year despite the
    // sy_id column/FK. Treated here as the school's single day configuration (sy_id just records which
    // year last touched it) - upsert by `num`, never a plain insert-per-year.
    //--------------------------------------------------------------------------------------------

    public function getJours(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
            ]);
        } catch (\Throwable $th) {
            return $this->validationError($th);
        }
        $connection = $request->input('connection');
        config(["database.default" => $connection]);
        try {
            $jours = DB::select("SELECT jour_id, label, num, number_of_periods, sy_id FROM jours ORDER BY num ASC");
            return response()->json($jours, 200);
        } catch (Exception $e) {
            return response()->json([], 500);
        }
    }

    public function saveJour(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'label' => 'required|string|max:20',
                'num' => 'required|integer|min:1|max:7',
                'number_of_periods' => 'required|integer|min:1|max:12',
            ]);
        } catch (\Throwable $th) {
            return $this->validationError($th);
        }
        $connection = $request->input('connection');
        $year = $request->input('year');
        config(["database.default" => $connection]);
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $label = $request->input('label');
            $num = $request->input('num');
            $numberOfPeriods = $request->input('number_of_periods');

            $existing = DB::select("SELECT jour_id FROM jours WHERE num = ?", [$num]);
            if (count($existing) > 0) {
                DB::update(
                    "UPDATE jours SET label = ?, number_of_periods = ?, sy_id = ? WHERE num = ?",
                    [$label, $numberOfPeriods, $sy_id, $num]
                );
            } else {
                DB::insert(
                    "INSERT INTO jours (label, num, number_of_periods, sy_id) VALUES (?, ?, ?, ?)",
                    [$label, $num, $numberOfPeriods, $sy_id]
                );
            }
            return response()->json([
                'status' => true,
                'message' => 'Day saved successfully.',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to save day: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function deleteJour(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'jour_id' => 'required|integer|min:1',
            ]);
        } catch (\Throwable $th) {
            return $this->validationError($th);
        }
        $connection = $request->input('connection');
        $jour_id = $request->input('jour_id');
        config(["database.default" => $connection]);
        try {
            DB::delete("DELETE FROM classe_period WHERE jour_id = ?", [$jour_id]);
            DB::delete("DELETE FROM jours WHERE jour_id = ?", [$jour_id]);
            return response()->json([
                'status' => true,
                'message' => 'Day deleted successfully.',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete day: ' . $e->getMessage(),
            ], 500);
        }
    }

    //--------------------------------------------------------------------------------------------
    // TEACHER WEEKLY LOAD (staff_year.max_periods_per_week)
    //--------------------------------------------------------------------------------------------

    public function getStaffMaxPeriods(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
            ]);
        } catch (\Throwable $th) {
            return $this->validationError($th);
        }
        $connection = $request->input('connection');
        $year = $request->input('year');
        config(["database.default" => $connection]);
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $rows = DB::select(
                "SELECT staff.staff_id, staff.name, staff.surname,
                        COALESCE(staff_year.max_periods_per_week, 18) AS max_periods_per_week
                 FROM staff
                 LEFT JOIN staff_year ON staff_year.staff_id = staff.staff_id AND staff_year.sy_id = $sy_id
                 ORDER BY staff.name ASC, staff.surname ASC"
            );
            return response()->json($rows, 200);
        } catch (Exception $e) {
            return response()->json([], 500);
        }
    }

    public function updateStaffMaxPeriods(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'staff_id' => 'required|integer|min:1',
                'max_periods_per_week' => 'required|integer|min:1|max:60',
            ]);
        } catch (\Throwable $th) {
            return $this->validationError($th);
        }
        $connection = $request->input('connection');
        $year = $request->input('year');
        config(["database.default" => $connection]);
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $staff_id = $request->input('staff_id');
            $maxPeriods = $request->input('max_periods_per_week');

            $existing = DB::select(
                "SELECT staff_year_id FROM staff_year WHERE staff_id = ? AND sy_id = ?",
                [$staff_id, $sy_id]
            );
            if (count($existing) > 0) {
                DB::update(
                    "UPDATE staff_year SET max_periods_per_week = ? WHERE staff_id = ? AND sy_id = ?",
                    [$maxPeriods, $staff_id, $sy_id]
                );
            } else {
                DB::insert(
                    "INSERT INTO staff_year (staff_id, sy_id, max_periods_per_week) VALUES (?, ?, ?)",
                    [$staff_id, $sy_id, $maxPeriods]
                );
            }
            return response()->json([
                'status' => true,
                'message' => 'Teacher weekly load saved successfully.',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to save teacher weekly load: ' . $e->getMessage(),
            ], 500);
        }
    }

    //--------------------------------------------------------------------------------------------
    // SUBJECTS OF A CLASSE - timetable settings (weight / numnber_of_period_per_week / commoncourse)
    // plus the assigned teacher, joined in so this same endpoint also feeds the grid cell editor's
    // subject/teacher dropdowns.
    //--------------------------------------------------------------------------------------------

    public function getClasseSubjectSettings(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'classe_id' => 'required|integer|min:1',
            ]);
        } catch (\Throwable $th) {
            return $this->validationError($th);
        }
        $connection = $request->input('connection');
        $year = $request->input('year');
        $classe_id = $request->input('classe_id');
        config(["database.default" => $connection]);
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $rows = DB::select(
                "SELECT subject_classe.subject_classe_id, subject_classe.subject_id, subject.subject_title,
                        subject_classe.weight, subject_classe.numnber_of_period_per_week, subject_classe.commoncourse,
                        teacher.staff_id AS staff_id, teacher.name AS staff_name, teacher.surname AS staff_surname
                 FROM subject_classe
                 JOIN subject ON subject.subject_id = subject_classe.subject_id
                 LEFT JOIN (
                     SELECT subject_classe_id, MIN(staff_id) AS staff_id
                     FROM subject_classe_staff
                     GROUP BY subject_classe_id
                 ) assigned ON assigned.subject_classe_id = subject_classe.subject_classe_id
                 LEFT JOIN staff teacher ON teacher.staff_id = assigned.staff_id
                 WHERE subject_classe.sy_id = $sy_id AND subject_classe.classe_id = $classe_id
                 ORDER BY subject.subject_title ASC"
            );
            return response()->json($rows, 200);
        } catch (Exception $e) {
            return response()->json([], 500);
        }
    }

    public function updateClasseSubjectSetting(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'subject_classe_id' => 'required|integer|min:1',
                'weight' => 'required|integer|min:1|max:5',
                'numnber_of_period_per_week' => 'required|integer|min:0|max:20',
                'commoncourse' => 'required|integer|min:0|max:1',
            ]);
        } catch (\Throwable $th) {
            return $this->validationError($th);
        }
        $connection = $request->input('connection');
        config(["database.default" => $connection]);
        try {
            DB::update(
                "UPDATE subject_classe SET weight = ?, numnber_of_period_per_week = ?, commoncourse = ?
                 WHERE subject_classe_id = ?",
                [
                    $request->input('weight'),
                    $request->input('numnber_of_period_per_week'),
                    $request->input('commoncourse'),
                    $request->input('subject_classe_id'),
                ]
            );
            return response()->json([
                'status' => true,
                'message' => 'Subject time table setting saved successfully.',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to save subject time table setting: ' . $e->getMessage(),
            ], 500);
        }
    }

    //--------------------------------------------------------------------------------------------
    // ASSIGN / CHANGE THE TEACHER OF A SUBJECT IN A CLASSE (bulk across every already-placed period)
    // Backs TimetableGridView's "Change or Assign teacher" button in the period-edit dialog: instead
    // of editing one classe_period cell at a time, this reassigns subject_classe_staff for the whole
    // (classe, subject) pair AND every classe_period row already carrying that subject in that classe,
    // so the timetable doesn't end up out of sync with the "official" teacher of that subject.
    //--------------------------------------------------------------------------------------------

    // For every classe_period row of (sy_id, classe_id, subject_id), checks whether $staff_id can be
    // placed there without a real conflict elsewhere - same commoncourse-aware rule updateCell() already
    // enforces for a single cell (a teacher may legitimately appear twice at the same day/period only
    // when it's a genuine combined session: same subject_id, and subject_classe.commoncourse=1 on BOTH
    // classes involved). Returns null if the subject isn't assigned to this classe for this year.
    private function buildTeacherAssignmentPlan($sy_id, $classe_id, $subject_id, $staff_id)
    {
        $scRows = DB::select(
            "SELECT subject_classe_id, commoncourse FROM subject_classe WHERE sy_id = ? AND classe_id = ? AND subject_id = ?",
            [$sy_id, $classe_id, $subject_id]
        );
        if (count($scRows) === 0) {
            return null;
        }
        $subjectClasseId = $scRows[0]->subject_classe_id;
        $selfCommon = (int)$scRows[0]->commoncourse === 1;

        $periods = DB::select(
            "SELECT classe_period_id, jour_id, period_number
             FROM classe_period
             WHERE sy_id = ? AND classe_id = ? AND subject_id = ?
             ORDER BY jour_id ASC, period_number ASC",
            [$sy_id, $classe_id, $subject_id]
        );

        $labelOf = [];
        foreach (DB::select("SELECT jour_id, label FROM jours") as $j) {
            $labelOf[$j->jour_id] = $j->label;
        }

        $plan = [];
        foreach ($periods as $p) {
            $others = DB::select(
                "SELECT classe_period.classe_period_id, classe_period.classe_id, classe.classe_name,
                        classe_period.subject_id, subject.subject_title
                 FROM classe_period
                 JOIN classe ON classe.classe_id = classe_period.classe_id
                 JOIN subject ON subject.subject_id = classe_period.subject_id
                 WHERE classe_period.sy_id = ? AND classe_period.jour_id = ? AND classe_period.period_number = ?
                   AND classe_period.staff_id = ? AND classe_period.classe_id != ?",
                [$sy_id, $p->jour_id, $p->period_number, $staff_id, $classe_id]
            );

            $collision = null;
            foreach ($others as $other) {
                $isValidCombined = false;
                if ($selfCommon && (int)$other->subject_id === (int)$subject_id) {
                    $otherSc = DB::select(
                        "SELECT commoncourse FROM subject_classe WHERE sy_id = ? AND classe_id = ? AND subject_id = ?",
                        [$sy_id, $other->classe_id, $subject_id]
                    );
                    $isValidCombined = count($otherSc) > 0 && (int)$otherSc[0]->commoncourse === 1;
                }
                if (!$isValidCombined) {
                    $collision = [
                        'other_classe_period_id' => (int)$other->classe_period_id,
                        'other_classe_id' => (int)$other->classe_id,
                        'other_classe_name' => $other->classe_name,
                        'other_subject_id' => (int)$other->subject_id,
                        'other_subject_title' => $other->subject_title,
                    ];
                    break; // one blocking collision is enough to flag this period
                }
            }

            $plan[] = [
                'classe_period_id' => (int)$p->classe_period_id,
                'jour_id' => (int)$p->jour_id,
                'jour_label' => $labelOf[$p->jour_id] ?? '',
                'period_number' => (int)$p->period_number,
                'collision' => $collision,
            ];
        }

        return ['subject_classe_id' => $subjectClasseId, 'periods' => $plan];
    }

    public function previewAssignTeacher(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'classe_id' => 'required|integer|min:1',
                'subject_id' => 'required|integer|min:1',
                'staff_id' => 'required|integer|min:1',
            ]);
        } catch (\Throwable $th) {
            return $this->validationError($th);
        }
        $connection = $request->input('connection');
        $year = $request->input('year');
        $classe_id = $request->input('classe_id');
        $subject_id = $request->input('subject_id');
        $staff_id = $request->input('staff_id');
        config(["database.default" => $connection]);

        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            $plan = $this->buildTeacherAssignmentPlan($sy_id, $classe_id, $subject_id, $staff_id);
            if (is_null($plan)) {
                return response()->json([
                    'status' => false,
                    'message' => 'That subject is not assigned to this class for this year.',
                ], 422);
            }

            $currentRows = DB::select(
                "SELECT staff.staff_id, staff.name, staff.surname
                 FROM subject_classe_staff
                 JOIN staff ON staff.staff_id = subject_classe_staff.staff_id
                 WHERE subject_classe_staff.subject_classe_id = ?
                 LIMIT 1",
                [$plan['subject_classe_id']]
            );

            $collisions = [];
            $freeCount = 0;
            foreach ($plan['periods'] as $p) {
                if ($p['collision']) {
                    $collisions[] = [
                        'jour_id' => $p['jour_id'],
                        'jour_label' => $p['jour_label'],
                        'period_number' => $p['period_number'],
                        'other_classe_id' => $p['collision']['other_classe_id'],
                        'other_classe_name' => $p['collision']['other_classe_name'],
                        'other_subject_id' => $p['collision']['other_subject_id'],
                        'other_subject_title' => $p['collision']['other_subject_title'],
                    ];
                } else {
                    $freeCount++;
                }
            }

            return response()->json([
                'status' => true,
                'current_staff_id' => count($currentRows) > 0 ? (int)$currentRows[0]->staff_id : null,
                'current_staff_name' => count($currentRows) > 0
                    ? trim($currentRows[0]->name . ' ' . $currentRows[0]->surname) : null,
                'total_periods' => count($plan['periods']),
                'free_periods' => $freeCount,
                'collisions' => $collisions,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to preview teacher assignment: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function assignTeacherToSubjectClasse(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'classe_id' => 'required|integer|min:1',
                'subject_id' => 'required|integer|min:1',
                'staff_id' => 'required|integer|min:1',
                'overrides' => 'nullable|array',
                'overrides.*.jour_id' => 'required_with:overrides|integer|min:1',
                'overrides.*.period_number' => 'required_with:overrides|integer|min:1',
            ]);
        } catch (\Throwable $th) {
            return $this->validationError($th);
        }
        $connection = $request->input('connection');
        $year = $request->input('year');
        $classe_id = $request->input('classe_id');
        $subject_id = $request->input('subject_id');
        $staff_id = $request->input('staff_id');
        $overrideKeys = [];
        foreach (($request->input('overrides') ?? []) as $o) {
            $overrideKeys[$o['jour_id'] . '-' . $o['period_number']] = true;
        }
        config(["database.default" => $connection]);

        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            // Recomputed fresh here (never trusting the client's earlier preview snapshot) so a stale
            // collision list from a preview the admin sat on for a while can't apply overrides against
            // periods that have since changed underneath it.
            $plan = $this->buildTeacherAssignmentPlan($sy_id, $classe_id, $subject_id, $staff_id);
            if (is_null($plan)) {
                return response()->json([
                    'status' => false,
                    'message' => 'That subject is not assigned to this class for this year.',
                ], 422);
            }

            DB::beginTransaction();

            DB::delete("DELETE FROM subject_classe_staff WHERE subject_classe_id = ?", [$plan['subject_classe_id']]);
            DB::insert(
                "INSERT INTO subject_classe_staff (subject_classe_id, staff_id) VALUES (?, ?)",
                [$plan['subject_classe_id'], $staff_id]
            );

            $assignedCount = 0;
            $freedCount = 0;
            $skippedCount = 0;

            foreach ($plan['periods'] as $p) {
                if (!$p['collision']) {
                    DB::update(
                        "UPDATE classe_period SET staff_id = ? WHERE classe_period_id = ?",
                        [$staff_id, $p['classe_period_id']]
                    );
                    $assignedCount++;
                    continue;
                }

                $key = $p['jour_id'] . '-' . $p['period_number'];
                if (isset($overrideKeys[$key])) {
                    // Admin approved freeing the selected teacher from the other classe's conflicting
                    // period so they can be used here instead.
                    DB::update(
                        "UPDATE classe_period SET staff_id = NULL WHERE classe_period_id = ?",
                        [$p['collision']['other_classe_period_id']]
                    );
                    DB::update(
                        "UPDATE classe_period SET staff_id = ? WHERE classe_period_id = ?",
                        [$staff_id, $p['classe_period_id']]
                    );
                    $assignedCount++;
                    $freedCount++;
                } else {
                    // Declined: the subject's official teacher just changed, so leaving the old
                    // teacher's name on this period would misrepresent who now teaches it - clear it
                    // instead, same as a manual "no teacher" edit via updateCell().
                    DB::update(
                        "UPDATE classe_period SET staff_id = NULL WHERE classe_period_id = ?",
                        [$p['classe_period_id']]
                    );
                    $skippedCount++;
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Teacher assignment updated successfully.',
                'assigned_count' => $assignedCount,
                'freed_count' => $freedCount,
                'skipped_count' => $skippedCount,
            ], 200);
        } catch (Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            return response()->json([
                'status' => false,
                'message' => 'Failed to assign teacher: ' . $e->getMessage(),
            ], 500);
        }
    }

    //--------------------------------------------------------------------------------------------
    // UNASSIGNED-TEACHER REPORT - backs TimetableHub's "More options" floating menu: every
    // (classe, subject) pair of the WHOLE SCHOOL (both sections - see below) that has at least one
    // already-placed classe_period with no teacher, whatever the reason (subject never had a teacher
    // assigned at all, or the generate() algorithm/a manual edit couldn't place the assigned teacher
    // there without a conflict) - so an admin can spot every gap at a glance, without flipping
    // through every class one by one. Returns one row PER MISSING PERIOD (not pre-aggregated) so the
    // frontend can list which day/period each gap falls on, not just a count - grouping into one
    // (classe, subject) line with a formatted period list is done client-side.
    //--------------------------------------------------------------------------------------------

    public function getUnassignedTeacherSubjects(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
            ]);
        } catch (\Throwable $th) {
            return $this->validationError($th);
        }
        $connection = $request->input('connection');
        $year = $request->input('year');
        config(["database.default" => $connection]);
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            // Deliberately NOT scoped to useAuth().section, same precedent as EffectifsManager's
            // "Effectifs par classe" report - a school-wide gap report shouldn't silently come back
            // empty just because a different section happens to be selected in the top banner at the
            // moment the admin opens this menu.
            $rows = DB::select(
                "SELECT classe.classe_id, classe.classe_name, classe.level, section.section_name,
                        subject.subject_id, subject.subject_title,
                        classe_period.jour_id, jours.label AS jour_label, classe_period.period_number
                 FROM classe_period
                 JOIN classe ON classe.classe_id = classe_period.classe_id
                 JOIN classe_year ON classe_year.classe_id = classe.classe_id AND classe_year.sy_id = classe_period.sy_id
                 JOIN section ON section.section_id = classe_year.section_id
                 JOIN subject ON subject.subject_id = classe_period.subject_id
                 JOIN jours ON jours.jour_id = classe_period.jour_id
                 WHERE classe_period.sy_id = ? AND classe_period.staff_id IS NULL
                 ORDER BY section.section_name ASC, classe.level ASC, classe.classe_name ASC, subject.subject_title ASC,
                          jours.num ASC, classe_period.period_number ASC",
                [$sy_id]
            );
            return response()->json($rows, 200);
        } catch (Exception $e) {
            return response()->json([], 500);
        }
    }

    //--------------------------------------------------------------------------------------------
    // GENERATED GRID (read one classe) + MANUAL CELL EDIT
    //--------------------------------------------------------------------------------------------

    public function getClasseCells(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'classe_id' => 'required|integer|min:1',
            ]);
        } catch (\Throwable $th) {
            return $this->validationError($th);
        }
        $connection = $request->input('connection');
        $year = $request->input('year');
        $classe_id = $request->input('classe_id');
        config(["database.default" => $connection]);
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $rows = DB::select(
                "SELECT classe_period.jour_id, classe_period.period_number, classe_period.subject_id,
                        subject.subject_title, classe_period.staff_id, staff.name AS staff_name, staff.surname AS staff_surname
                 FROM classe_period
                 JOIN subject ON subject.subject_id = classe_period.subject_id
                 LEFT JOIN staff ON staff.staff_id = classe_period.staff_id
                 WHERE classe_period.sy_id = $sy_id AND classe_period.classe_id = $classe_id"
            );
            return response()->json($rows, 200);
        } catch (Exception $e) {
            return response()->json([], 500);
        }
    }

    public function updateCell(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'classe_id' => 'required|integer|min:1',
                'jour_id' => 'required|integer|min:1',
                'period_number' => 'required|integer|min:1',
                'subject_id' => 'nullable|integer|min:1',
                'staff_id' => 'nullable|integer|min:1',
            ]);
        } catch (\Throwable $th) {
            return $this->validationError($th);
        }
        $connection = $request->input('connection');
        $year = $request->input('year');
        $classe_id = $request->input('classe_id');
        $jour_id = $request->input('jour_id');
        $period_number = $request->input('period_number');
        $subject_id = $request->input('subject_id');
        $staff_id = $request->input('staff_id');
        config(["database.default" => $connection]);

        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            if (is_null($subject_id)) {
                DB::delete(
                    "DELETE FROM classe_period WHERE sy_id = ? AND classe_id = ? AND jour_id = ? AND period_number = ?",
                    [$sy_id, $classe_id, $jour_id, $period_number]
                );
                return response()->json(['status' => true, 'message' => 'Period cleared successfully.'], 200);
            }

            $assignment = DB::select(
                "SELECT subject_classe_id, commoncourse FROM subject_classe WHERE sy_id = ? AND classe_id = ? AND subject_id = ?",
                [$sy_id, $classe_id, $subject_id]
            );
            if (count($assignment) === 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'That subject is not assigned to this class for this year.',
                ], 422);
            }

            if (!is_null($staff_id)) {
                // A teacher may legitimately appear at the same day/period in more than one class for
                // the SAME subject only when it's a genuine combined/joint session - i.e.
                // subject_classe.commoncourse=1 on BOTH this class's and the other class's assignment
                // row for that subject. A different subject at the same slot is always a conflict, and
                // so is the same subject when commoncourse isn't set on every classe_id involved.
                $otherRows = DB::select(
                    "SELECT classe_period.classe_id, classe.classe_name, classe_period.subject_id, subject.subject_title
                     FROM classe_period
                     JOIN classe ON classe.classe_id = classe_period.classe_id
                     JOIN subject ON subject.subject_id = classe_period.subject_id
                     WHERE classe_period.sy_id = ? AND classe_period.jour_id = ? AND classe_period.period_number = ?
                       AND classe_period.staff_id = ?
                       AND NOT (classe_period.classe_id = ? AND classe_period.jour_id = ? AND classe_period.period_number = ?)",
                    [$sy_id, $jour_id, $period_number, $staff_id, $classe_id, $jour_id, $period_number]
                );

                if (count($otherRows) > 0) {
                    $selfIsCommon = (int)$assignment[0]->commoncourse === 1;
                    foreach ($otherRows as $other) {
                        $isValidCombined = false;
                        if ($selfIsCommon && (int)$other->subject_id === (int)$subject_id) {
                            $otherAssignment = DB::select(
                                "SELECT commoncourse FROM subject_classe WHERE sy_id = ? AND classe_id = ? AND subject_id = ?",
                                [$sy_id, $other->classe_id, $subject_id]
                            );
                            $isValidCombined = count($otherAssignment) > 0 && (int)$otherAssignment[0]->commoncourse === 1;
                        }
                        if (!$isValidCombined) {
                            return response()->json([
                                'status' => false,
                                'message' => 'This teacher is already teaching "' . $other->subject_title . '" in ' . $other->classe_name . ' at this day/period.',
                            ], 409);
                        }
                    }
                }
            }

            $existing = DB::select(
                "SELECT classe_period_id FROM classe_period WHERE sy_id = ? AND classe_id = ? AND jour_id = ? AND period_number = ?",
                [$sy_id, $classe_id, $jour_id, $period_number]
            );
            if (count($existing) > 0) {
                DB::update(
                    "UPDATE classe_period SET subject_id = ?, staff_id = ? WHERE classe_period_id = ?",
                    [$subject_id, $staff_id, $existing[0]->classe_period_id]
                );
            } else {
                DB::insert(
                    "INSERT INTO classe_period (jour_id, period_number, sy_id, subject_id, staff_id, classe_id)
                     VALUES (?, ?, ?, ?, ?, ?)",
                    [$jour_id, $period_number, $sy_id, $subject_id, $staff_id, $classe_id]
                );
            }
            return response()->json(['status' => true, 'message' => 'Period saved successfully.'], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to save period: ' . $e->getMessage(),
            ], 500);
        }
    }

    //--------------------------------------------------------------------------------------------
    // MY TIMETABLE - a staff member's own cells across every class they teach (unlike getClasseCells,
    // not scoped by classe_id). staff_id is deliberately read from auth_payload->user_id (the JWT
    // claim AccountController::connect already sets to the staff table's id for staff-type accounts,
    // same one RoleMiddleware/AccountController's own self-service endpoints trust) rather than a
    // client-supplied param, since this is a genuine "give me only my own data" endpoint - a
    // deliberate departure from this app's usual convention of returning a broader list and letting
    // the frontend filter client-side (see SG/CENSEUR classe scoping elsewhere in this codebase).
    //--------------------------------------------------------------------------------------------

    public function getMyCells(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
            ]);
        } catch (\Throwable $th) {
            return $this->validationError($th);
        }
        $connection = $request->input('connection');
        $year = $request->input('year');
        config(["database.default" => $connection]);
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $staff_id = (int) $request->attributes->get('auth_payload')->user_id;
            $rows = DB::select(
                "SELECT classe_period.jour_id, classe_period.period_number, classe_period.subject_id,
                        subject.subject_title, classe_period.classe_id, classe.classe_name
                 FROM classe_period
                 JOIN subject ON subject.subject_id = classe_period.subject_id
                 JOIN classe ON classe.classe_id = classe_period.classe_id
                 WHERE classe_period.sy_id = ? AND classe_period.staff_id = ?",
                [$sy_id, $staff_id]
            );
            return response()->json($rows, 200);
        } catch (Exception $e) {
            return response()->json([], 500);
        }
    }

    //--------------------------------------------------------------------------------------------
    // MY STAFF INFO - the same auth-scoped staff member's own HR record + weekly load, backing the
    // official "individual time table" PDF/Excel export (My Timetable) alongside getMyCells above -
    // same staff_id-from-auth_payload scoping, same reason.
    //--------------------------------------------------------------------------------------------

    public function getMyStaffInfo(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
            ]);
        } catch (\Throwable $th) {
            return $this->validationError($th);
        }
        $connection = $request->input('connection');
        $year = $request->input('year');
        config(["database.default" => $connection]);
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $staff_id = (int) $request->attributes->get('auth_payload')->user_id;
            $rows = DB::select(
                "SELECT staff.staff_id, staff.name, staff.surname, staff.function, staff.status,
                        staff.grade, staff.diplome, staff.specilitee, staff.matiereEnseignee, staff.longivity,
                        COALESCE(staff_year.max_periods_per_week, 18) AS max_periods_per_week
                 FROM staff
                 LEFT JOIN staff_year ON staff_year.staff_id = staff.staff_id AND staff_year.sy_id = $sy_id
                 WHERE staff.staff_id = ?",
                [$staff_id]
            );
            return response()->json($rows[0] ?? null, 200);
        } catch (Exception $e) {
            return response()->json(null, 500);
        }
    }

    //--------------------------------------------------------------------------------------------
    // ALL STAFF TIMETABLES (ADMIN) - bulk equivalents of getMyCells/getMyStaffInfo above, backing
    // the Time table hub's "print/export every staff member's individual time table at once"
    // feature. Not staff_id-scoped (ADMIN has no staff_id of its own to scope against, same reason
    // getMyCells/getMyStaffInfo stay outside the ADMIN role group) - two requests return every
    // staff member's data for the whole school in one shot, instead of looping the single-staff
    // endpoints above once per staff member (2xN requests).
    //--------------------------------------------------------------------------------------------

    public function getAllStaffCells(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
            ]);
        } catch (\Throwable $th) {
            return $this->validationError($th);
        }
        $connection = $request->input('connection');
        $year = $request->input('year');
        config(["database.default" => $connection]);
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $rows = DB::select(
                "SELECT classe_period.staff_id, classe_period.jour_id, classe_period.period_number,
                        classe_period.subject_id, subject.subject_title, classe_period.classe_id, classe.classe_name
                 FROM classe_period
                 JOIN subject ON subject.subject_id = classe_period.subject_id
                 JOIN classe ON classe.classe_id = classe_period.classe_id
                 WHERE classe_period.sy_id = ? AND classe_period.staff_id IS NOT NULL",
                [$sy_id]
            );
            return response()->json($rows, 200);
        } catch (Exception $e) {
            return response()->json([], 500);
        }
    }

    public function getAllStaffInfo(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
            ]);
        } catch (\Throwable $th) {
            return $this->validationError($th);
        }
        $connection = $request->input('connection');
        $year = $request->input('year');
        config(["database.default" => $connection]);
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            // LEFT JOIN + COALESCE (not allStaffs1's inner join on staff_year) so a staff member
            // with no staff_year row for this year still gets an individual timetable page
            // (defaulting max_periods_per_week to 18), same convention as getMyStaffInfo/
            // getStaffMaxPeriods above.
            $rows = DB::select(
                "SELECT staff.staff_id, staff.name, staff.surname, staff.function, staff.status,
                        staff.grade, staff.diplome, staff.specilitee, staff.matiereEnseignee, staff.longivity,
                        COALESCE(staff_year.max_periods_per_week, 18) AS max_periods_per_week
                 FROM staff
                 LEFT JOIN staff_year ON staff_year.staff_id = staff.staff_id AND staff_year.sy_id = $sy_id
                 ORDER BY staff.name ASC, staff.surname ASC"
            );
            return response()->json($rows, 200);
        } catch (Exception $e) {
            return response()->json([], 500);
        }
    }

    //--------------------------------------------------------------------------------------------
    // GENERATE - builds the whole school's timetable for one year from scratch.
    //--------------------------------------------------------------------------------------------

    public function generate(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
            ]);
        } catch (\Throwable $th) {
            return $this->validationError($th);
        }
        $connection = $request->input('connection');
        $year = $request->input('year');
        config(["database.default" => $connection]);

        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            $jours = DB::select("SELECT jour_id, num, number_of_periods FROM jours ORDER BY num ASC");
            if (count($jours) === 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'No school days are configured yet. Configure them in Time table settings first.',
                ], 422);
            }

            $allSlots = [];
            foreach ($jours as $jour) {
                for ($p = 1; $p <= (int)$jour->number_of_periods; $p++) {
                    $allSlots[] = ['jour_id' => (int)$jour->jour_id, 'period_number' => $p];
                }
            }

            $scRows = DB::select(
                "SELECT sc.subject_classe_id, sc.classe_id, sc.subject_id, sc.weight,
                        sc.numnber_of_period_per_week AS periods_per_week, sc.commoncourse, sc.section_id,
                        c.level, c.classe_name, s.subject_title
                 FROM subject_classe sc
                 JOIN classe c ON c.classe_id = sc.classe_id
                 JOIN subject s ON s.subject_id = sc.subject_id
                 WHERE sc.sy_id = $sy_id"
            );

            $teacherRows = DB::select(
                "SELECT scs.subject_classe_id, MIN(scs.staff_id) AS staff_id
                 FROM subject_classe_staff scs
                 JOIN subject_classe sc2 ON sc2.subject_classe_id = scs.subject_classe_id
                 WHERE sc2.sy_id = $sy_id
                 GROUP BY scs.subject_classe_id"
            );
            $teacherOf = [];
            foreach ($teacherRows as $t) {
                $teacherOf[$t->subject_classe_id] = (int)$t->staff_id;
            }

            // "classeId-subjectId" => commoncourse flag, so both phases below can tell whether a
            // teacher legitimately appearing twice at the same day/period (once per class) is a real
            // combined/joint session or a scheduling conflict - see the same rule enforced in updateCell().
            $commonOf = [];
            foreach ($scRows as $row) {
                $commonOf[$row->classe_id . '-' . $row->subject_id] = (int)$row->commoncourse;
            }

            $maxPeriodsRows = DB::select("SELECT staff_id, max_periods_per_week FROM staff_year WHERE sy_id = $sy_id");
            $maxPeriodsOf = [];
            foreach ($maxPeriodsRows as $m) {
                $maxPeriodsOf[(int)$m->staff_id] = (int)$m->max_periods_per_week;
            }

            $usedSlot = [];       // [classe_id][ "jour-period" ] = true
            $teacherBusy = [];    // [staff_id][ "jour-period" ] = ['subject_id' => id, 'classes' => [classe_id => true, ...]]
            $teacherLoad = [];    // [staff_id] = count
            $subjectDayCount = []; // [classe_id][subject_id][jour_id] = count

            $slotKey = function ($jour_id, $period_number) {
                return $jour_id . '-' . $period_number;
            };

            // Can $row (a classe_id/subject_id pair) join teacher $teacherId at slot $key, given
            // whatever is already placed there for that teacher? Free if nothing's there yet; if
            // something is, only a same-subject combined session where EVERY class already at that
            // slot (and this one) has commoncourse=1 for that subject is allowed - anything else,
            // including a same-subject class with commoncourse=0 on either side, is a conflict.
            $canPlaceWithTeacher = function ($teacherId, $key, $row) use (&$teacherBusy, $commonOf) {
                if (!isset($teacherBusy[$teacherId][$key])) {
                    return true;
                }
                $busyEntry = $teacherBusy[$teacherId][$key];
                if ((int)$busyEntry['subject_id'] !== (int)$row->subject_id) {
                    return false;
                }
                if (($commonOf[$row->classe_id . '-' . $row->subject_id] ?? 0) !== 1) {
                    return false;
                }
                foreach (array_keys($busyEntry['classes']) as $existingClasseId) {
                    if (($commonOf[$existingClasseId . '-' . $row->subject_id] ?? 0) !== 1) {
                        return false;
                    }
                }
                return true;
            };

            $maxOf = function ($staff_id) use ($maxPeriodsOf) {
                return $maxPeriodsOf[$staff_id] ?? 18;
            };

            $unassignedWarnings = []; // key = classe_id-subject_id
            $noCapacityWarnings = []; // key = classe_id-subject_id

            $recordUnassigned = function ($row) use (&$unassignedWarnings) {
                $key = $row->classe_id . '-' . $row->subject_id;
                if (!isset($unassignedWarnings[$key])) {
                    $unassignedWarnings[$key] = [
                        'classe_name' => $row->classe_name,
                        'subject_title' => $row->subject_title,
                        'count' => 0,
                    ];
                }
                $unassignedWarnings[$key]['count']++;
            };

            $recordNoCapacity = function ($row) use (&$noCapacityWarnings) {
                $key = $row->classe_id . '-' . $row->subject_id;
                if (!isset($noCapacityWarnings[$key])) {
                    $noCapacityWarnings[$key] = [
                        'classe_name' => $row->classe_name,
                        'subject_title' => $row->subject_title,
                        'count' => 0,
                    ];
                }
                $noCapacityWarnings[$key]['count']++;
            };

            $insertRows = []; // rows to bulk insert at the end: [jour_id, period_number, sy_id, subject_id, staff_id, classe_id]

            $place = function ($row, $jour_id, $period_number, $staffId) use (&$insertRows, &$usedSlot, &$subjectDayCount, $sy_id, $slotKey) {
                $insertRows[] = [$jour_id, $period_number, $sy_id, $row->subject_id, $staffId, $row->classe_id];
                $usedSlot[$row->classe_id][$slotKey($jour_id, $period_number)] = true;
                $subjectDayCount[$row->classe_id][$row->subject_id][$jour_id] =
                    ($subjectDayCount[$row->classe_id][$row->subject_id][$jour_id] ?? 0) + 1;
            };

            // ---------- PHASE A: combined ("commoncourse") groups ----------
            $groups = [];
            foreach ($scRows as $row) {
                if ((int)$row->commoncourse === 1) {
                    $groupKey = $row->level . '_' . $row->subject_id . '_' . $row->section_id;
                    $groups[$groupKey][] = $row;
                }
            }

            $leftover = []; // subject_classe_id => remaining periods_per_week after phase A
            foreach ($scRows as $row) {
                $leftover[$row->subject_classe_id] = (int)$row->periods_per_week;
            }

            foreach ($groups as $groupRows) {
                $sessionCount = min(array_map(fn($r) => (int)$r->periods_per_week, $groupRows));
                if ($sessionCount <= 0) {
                    continue;
                }
                $memberClasseIds = array_unique(array_map(fn($r) => $r->classe_id, $groupRows));
                $distinctTeacherIds = array_values(array_unique(array_filter(
                    array_map(fn($r) => $teacherOf[$r->subject_classe_id] ?? null, $groupRows)
                )));

                $placedSessions = 0;
                foreach ($allSlots as $slot) {
                    if ($placedSessions >= $sessionCount) {
                        break;
                    }
                    $key = $slotKey($slot['jour_id'], $slot['period_number']);

                    $freeForAll = true;
                    foreach ($memberClasseIds as $cid) {
                        if (isset($usedSlot[$cid][$key])) {
                            $freeForAll = false;
                            break;
                        }
                    }
                    if (!$freeForAll) {
                        continue;
                    }

                    $teachersFree = true;
                    foreach ($distinctTeacherIds as $tid) {
                        if (isset($teacherBusy[$tid][$key])) {
                            $teachersFree = false;
                            break;
                        }
                    }

                    // A teacher already at/over their weekly cap can still join a combined session -
                    // the cap is informational (feeds "heures supplementaires" in the staff hours
                    // report) rather than a hard placement gate, so a genuinely collision-free slot
                    // is used instead of being hidden behind "Sans enseignant" (see the same
                    // relaxation in PHASE B below).
                    $useTeachers = $teachersFree;

                    foreach ($groupRows as $r) {
                        $staffForRow = null;
                        if ($useTeachers && isset($teacherOf[$r->subject_classe_id])) {
                            $staffForRow = $teacherOf[$r->subject_classe_id];
                        }
                        if (is_null($staffForRow)) {
                            $recordUnassigned($r);
                        }
                        $place($r, $slot['jour_id'], $slot['period_number'], $staffForRow);
                        $leftover[$r->subject_classe_id]--;
                    }
                    if ($useTeachers) {
                        foreach ($distinctTeacherIds as $tid) {
                            if (!isset($teacherBusy[$tid][$key])) {
                                $teacherBusy[$tid][$key] = ['subject_id' => $groupRows[0]->subject_id, 'classes' => []];
                            }
                            foreach ($memberClasseIds as $cid) {
                                $teacherBusy[$tid][$key]['classes'][$cid] = true;
                            }
                            $teacherLoad[$tid] = ($teacherLoad[$tid] ?? 0) + 1;
                        }
                    }
                    $placedSessions++;
                }
            }

            // ---------- PHASE B: independent placement, weight DESC ----------
            usort($scRows, fn($a, $b) => $b->weight <=> $a->weight);

            foreach ($scRows as $row) {
                $remaining = $leftover[$row->subject_classe_id] ?? 0;
                $teacherId = $teacherOf[$row->subject_classe_id] ?? null;

                for ($i = 0; $i < $remaining; $i++) {
                    $free = [];
                    foreach ($allSlots as $slot) {
                        $key = $slotKey($slot['jour_id'], $slot['period_number']);
                        if (!isset($usedSlot[$row->classe_id][$key])) {
                            $free[] = $slot;
                        }
                    }
                    if (count($free) === 0) {
                        $recordNoCapacity($row);
                        continue;
                    }

                    usort($free, function ($a, $b) use ($row, $subjectDayCount) {
                        $aRepeat = $subjectDayCount[$row->classe_id][$row->subject_id][$a['jour_id']] ?? 0;
                        $bRepeat = $subjectDayCount[$row->classe_id][$row->subject_id][$b['jour_id']] ?? 0;
                        if ($aRepeat !== $bRepeat) {
                            return $aRepeat <=> $bRepeat;
                        }
                        return $a['period_number'] <=> $b['period_number'];
                    });

                    $chosen = null;
                    $chosenStaff = null;
                    if (is_null($teacherId)) {
                        $chosen = $free[0];
                    } else {
                        // Pass 1: prefer a slot that keeps the teacher within their configured
                        // weekly cap (staff_year.max_periods_per_week).
                        foreach ($free as $slot) {
                            $key = $slotKey($slot['jour_id'], $slot['period_number']);
                            $withinCap = ($teacherLoad[$teacherId] ?? 0) < $maxOf($teacherId);
                            if ($canPlaceWithTeacher($teacherId, $key, $row) && $withinCap) {
                                $chosen = $slot;
                                $chosenStaff = $teacherId;
                                break;
                            }
                        }
                        // Pass 2: no slot kept them within cap - this teacher has been assigned too
                        // many courses across their classes. Place them anyway in any genuinely
                        // collision-free slot (producing "heures supplementaires", surfaced in the
                        // staff hours report) rather than hiding a real assignment behind "Sans
                        // enseignant". Only a genuine day/period conflict (canPlaceWithTeacher false
                        // everywhere) still falls through to unassigned below.
                        if (is_null($chosen)) {
                            foreach ($free as $slot) {
                                $key = $slotKey($slot['jour_id'], $slot['period_number']);
                                if ($canPlaceWithTeacher($teacherId, $key, $row)) {
                                    $chosen = $slot;
                                    $chosenStaff = $teacherId;
                                    break;
                                }
                            }
                        }
                        if (is_null($chosen)) {
                            $chosen = $free[0];
                            $recordUnassigned($row);
                        }
                    }

                    $place($row, $chosen['jour_id'], $chosen['period_number'], $chosenStaff);
                    if (!is_null($chosenStaff)) {
                        $key = $slotKey($chosen['jour_id'], $chosen['period_number']);
                        if (!isset($teacherBusy[$chosenStaff][$key])) {
                            $teacherBusy[$chosenStaff][$key] = ['subject_id' => $row->subject_id, 'classes' => []];
                        }
                        $teacherBusy[$chosenStaff][$key]['classes'][$row->classe_id] = true;
                        $teacherLoad[$chosenStaff] = ($teacherLoad[$chosenStaff] ?? 0) + 1;
                    }
                }
            }

            DB::beginTransaction();
            DB::delete("DELETE FROM classe_period WHERE sy_id = ?", [$sy_id]);
            foreach ($insertRows as $r) {
                DB::insert(
                    "INSERT INTO classe_period (jour_id, period_number, sy_id, subject_id, staff_id, classe_id)
                     VALUES (?, ?, ?, ?, ?, ?)",
                    $r
                );
            }
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Time table generated successfully for every class.',
                'warnings' => [
                    'unassignedTeacher' => array_values($unassignedWarnings),
                    'noCapacity' => array_values($noCapacityWarnings),
                ],
            ], 200);
        } catch (Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            return response()->json([
                'status' => false,
                'message' => 'Failed to generate time table: ' . $e->getMessage(),
            ], 500);
        }
    }

    //--------------------------------------------------------------------------------------------
    // SEND EACH TEACHER THEIR INDIVIDUAL TIME TABLE BY EMAIL - reads the already-generated
    // classe_period rows (never generates anything itself), groups them per teacher, and emails
    // each one their own weekly grid. A teacher with no email on their account, or whose send fails
    // (bad SMTP config, invalid address, ...), is skipped and reported back rather than aborting the
    // whole batch - one bad address must not block everyone else's email.
    //--------------------------------------------------------------------------------------------

    // Mirrors src/utils/timetableTime.ts's computeDayTimeline() (period start/end only, breaks just
    // shift the running clock) so the emailed grid's times match what the admin sees on screen.
    private function computePeriodTimes($ttConfig, int $maxPeriods): array
    {
        $times = [];
        if (is_null($ttConfig) || $maxPeriods <= 0) {
            return $times;
        }
        if (!preg_match('/^(\d{2})h(\d{2})$/', $ttConfig->start_time, $m)) {
            return $times;
        }
        $t = ((int)$m[1]) * 60 + (int)$m[2];
        $format = function (int $minutes) {
            $h = intdiv($minutes, 60) % 24;
            $mi = $minutes % 60;
            return sprintf('%02dH%02d', $h, $mi);
        };
        for ($p = 1; $p <= $maxPeriods; $p++) {
            $start = $t;
            $t += (int)$ttConfig->period_duration;
            $times[$p] = ['start' => $format($start), 'end' => $format($t)];
            if ($p === (int)$ttConfig->number_of_period_before_break1_start) {
                $t += (int)$ttConfig->duration_break1;
            } elseif ($p - (int)$ttConfig->number_of_period_before_break1_start === (int)$ttConfig->number_of_period_before_break2_start) {
                $t += (int)$ttConfig->duration_break2;
            }
        }
        return $times;
    }

    public function sendTeacherEmails(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
            ]);
        } catch (\Throwable $th) {
            return $this->validationError($th);
        }
        $connection = $request->input('connection');
        $year = $request->input('year');
        config(["database.default" => $connection]);

        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            $cellRows = DB::select(
                "SELECT classe_period.jour_id, classe_period.period_number, classe_period.staff_id,
                        subject.subject_title, classe.classe_name
                 FROM classe_period
                 JOIN subject ON subject.subject_id = classe_period.subject_id
                 JOIN classe ON classe.classe_id = classe_period.classe_id
                 WHERE classe_period.sy_id = ? AND classe_period.staff_id IS NOT NULL",
                [$sy_id]
            );

            if (count($cellRows) === 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'No time table has been generated yet for this year.',
                ], 422);
            }

            $jours = DB::select("SELECT jour_id, label, num, number_of_periods FROM jours ORDER BY num ASC");
            $maxPeriods = 0;
            foreach ($jours as $j) {
                $maxPeriods = max($maxPeriods, (int)$j->number_of_periods);
            }

            $configRows = DB::select(
                "SELECT start_time, duration_break1, duration_break2, period_duration,
                        number_of_period_before_break1_start, number_of_period_before_break2_start
                 FROM tt_config WHERE sy_id = ?",
                [$sy_id]
            );
            $timeOf = $this->computePeriodTimes(count($configRows) > 0 ? $configRows[0] : null, $maxPeriods);

            $byStaff = []; // [staff_id][ "jour_id-period_number" ] = ['subject_title'=>, 'classe_name'=>]
            foreach ($cellRows as $row) {
                $byStaff[$row->staff_id][$row->jour_id . '-' . $row->period_number] = [
                    'subject_title' => $row->subject_title,
                    'classe_name' => $row->classe_name,
                ];
            }

            $staffIds = array_keys($byStaff);
            $placeholders = implode(',', array_fill(0, count($staffIds), '?'));
            $teachers = DB::select(
                "SELECT staff.staff_id, staff.name, staff.surname, account.email
                 FROM staff
                 JOIN account ON account.acc_id = staff.acc_id
                 WHERE staff.staff_id IN ($placeholders)",
                $staffIds
            );

            $days = [];
            foreach ($jours as $j) {
                $days[] = [
                    'jour_id' => (int)$j->jour_id,
                    'label' => $j->label,
                    'number_of_periods' => (int)$j->number_of_periods,
                ];
            }
            $periods = [];
            for ($p = 1; $p <= $maxPeriods; $p++) {
                $periods[] = [
                    'number' => $p,
                    'start' => $timeOf[$p]['start'] ?? null,
                    'end' => $timeOf[$p]['end'] ?? null,
                ];
            }

            $sentCount = 0;
            $noEmail = [];
            $sendFailed = [];

            foreach ($teachers as $teacher) {
                $teacherName = trim($teacher->name . ' ' . $teacher->surname);
                if (empty($teacher->email)) {
                    $noEmail[] = ['staff_name' => $teacherName];
                    continue;
                }

                $grid = [];
                foreach ($periods as $period) {
                    foreach ($days as $day) {
                        $key = $day['jour_id'] . '-' . $period['number'];
                        if (isset($byStaff[$teacher->staff_id][$key])) {
                            $grid[$period['number']][$day['jour_id']] = $byStaff[$teacher->staff_id][$key];
                        }
                    }
                }

                try {
                    Mail::to($teacher->email)->send(
                        new TeacherTimetableMail($teacherName, $year, $days, $periods, $grid)
                    );
                    $sentCount++;
                } catch (\Throwable $e) {
                    $sendFailed[] = ['staff_name' => $teacherName];
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Time table emails sent.',
                'sentCount' => $sentCount,
                'warnings' => [
                    'noEmail' => $noEmail,
                    'sendFailed' => $sendFailed,
                ],
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to send time table emails: ' . $e->getMessage(),
            ], 500);
        }
    }
}
