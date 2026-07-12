<?php

namespace App\Http\Controllers;

use App\Models\StudCompMark;
use App\Models\Student;
use App\Models\StudentClasse;
use App\Models\StudentSubject;
use App\Models\Discipline;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{

    public function setFatherMother(Request $request)
    {
        $connection = $request->input("connection");
        $stud_id = $request->input("stud_id");
        $father = $request->input("father");
        $mother = $request->input("mother");
        config(["database.default" => $connection]);
        try {
            //UPDATE student SET st1 = "father1", str2 = "mother1" WHERE stud_id = 5776

            echo "Student[$stud_id] -- Father[$father]  --  mother[$mother]<br/>";
            //DB::select("UPDATE student SET st1 = $father, str2 = $mother WHERE stud_id = $stud_id");
            DB::select("UPDATE student SET st1 = '$father', str2 = '$mother' WHERE stud_id = $stud_id");
            //DB::select("UPDATE student SET st1 = '', str2 = '' WHERE 1");
            echo "1"; //SUCCESS
        } catch (Exception $e) {
            echo "0";
            echo '<br/>ERROR: ' . $e->getMessage();
            return;
        }
    }

    public function updateSolvable(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $year = $request->input("year");

        $studList = json_decode($data, true);
        $n = count($studList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]<br/>";
        $allAffected = 1; //interpreted as true. 0-->false  
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);

        $count  = 1;
        foreach ($studList as $stud) {
            //echo"-----> Processing stud No.[$count]<br/>";
            $count++;
            $stud_id = $stud["stud_id"]; //Stud_id shouldn't be null here if it is the case ==> Fatal ERROR            
            $classe_id = $stud["classe_id"];
            $solvable = $stud["solvable1"];
            try {
                DB::select("UPDATE student_classe SET solvable1 = $solvable
                    WHERE stud_id = $stud_id AND classe_id = $classe_id
                        AND sy_id = $sy_id");
            } catch (Exception $e2) {
                echo "<br/>" . $e2->getMessage() . "<br/>";
                echo "-1"; //failed to save/update;
                $allAffected = 0;
            }
        } //END FOR
        echo "$allAffected"; //1--> All successfully saved/updated; < 0--> Failed for least one
    }

    public function updateSolvablePOST(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $year = $request->input("year");

        $studList = json_decode($data, true);
        $n = count($studList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]<br/>";
        $allAffected = 1; //interpreted as true. 0-->false  
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);

        $count  = 1;
        foreach ($studList as $stud) {
            //echo"-----> Processing stud No.[$count]<br/>";
            $count++;
            $stud_id = $stud["stud_id"]; //Stud_id shouldn't be null here if it is the case ==> Fatal ERROR            
            $classe_id = $stud["classe_id"];
            $solvable = $stud["solvable1"];
            try {
                DB::select("UPDATE student_classe SET solvable1 = $solvable
                    WHERE stud_id = $stud_id AND classe_id = $classe_id
                        AND sy_id = $sy_id");
            } catch (Exception $e2) {
                echo "<br/>" . $e2->getMessage() . "<br/>";
                echo "-1"; //failed to save/update;
                $allAffected = 0;
            }
        } //END FOR
        echo "$allAffected"; //1--> All successfully saved/updated; < 0--> Failed for least one
    }

    public function updateDismiss(Request $request)
    {
        $connection = $request->input("connection");
        $year = $request->input("year");
        $stud_id = $request->input("stud_id");
        $classe_id = $request->input("classe_id");
        $dismiss_val = $request->input("dismiss_val");
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        try {
            DB::select("UPDATE student_classe SET student_classe.isMannullalyDismissed = $dismiss_val 
                WHERE student_classe.stud_id = $stud_id AND student_classe.sy_id = $sy_id
                    AND student_classe.classe_id = $classe_id");
            echo "1";
        } catch (Exception $e) {
            //echo '<br/>Message: ' .$e->getMessage();
            echo "-1";
        }
    }

    public function addStudentToRepeatList(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $year = $request->input("year");
        $stud_id = $request->input("stud_id");
        $classe_id = $request->input("classe_id");

        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);

        $allAffected = 1;
        $sc = new StudentClasse();
        $sc->stud_id = $stud_id;
        $sc->sy_id = $sy_id; //NEXT SCHOOL YEAR
        $sc->repeating = 1;
        $sc->classe_id = $classe_id;
        try {
            $x = DB::select("SELECT*FROM student_classe WHERE student_classe.sy_id = $sy_id
                        AND student_classe.stud_id = $stud_id AND student_classe.classe_id = $sc->classe_id");
            if (count($x) == 0) {
                $sc->save();
            }
        } catch (Exception $e) {
            echo "<br/>" . $e->getMessage() . "<br/>";
            echo "-1"; //failed to save student_classe;
            $allAffected = 0;
        }
        echo "$allAffected"; //1--> All successfully saved; < 0--> Failed for least one
    }

    public function removeStudentFromClass(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $year = $request->input("year"); //Will be next year if called by basculement
        $stud_id = $request->input("stud_id");
        $classe_id = $request->input("classe_id");

        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);

        $allAffected = 1;
        try {
            $x = DB::select("DELETE FROM student_classe WHERE student_classe.sy_id = $sy_id
                        AND student_classe.stud_id = $stud_id AND student_classe.classe_id = $classe_id");
        } catch (Exception $e) {
            //echo "Error<br/>";
            echo "<br/>" . $e->getMessage() . "<br/>";
            echo "-1"; //failed to save student_classe;
            $allAffected = 0;
        }
        echo "$allAffected"; //1--> All successfully saved; < 0--> Failed for least one
    }

    public function addStudentToClass(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $year = $request->input("year"); //Will be next year if called by basculement
        $stud_id = $request->input("stud_id");
        $classe_id_new = $request->input("classe_id_new");
        $classe_id_old = $request->input("classe_id_old");
        $cas_social = $request->input("cas_social");
        $repeating = $request->input("repeating");

        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);

        $allAffected = 1;
        try {
            $x = DB::select("DELETE FROM student_classe WHERE student_classe.sy_id = $sy_id
                        AND student_classe.stud_id = $stud_id AND student_classe.classe_id = $classe_id_old");
            $sc = new StudentClasse();
            $sc->stud_id = $stud_id;
            $sc->sy_id = $sy_id;
            $sc->repeating = $repeating;
            $sc->cas_social = $cas_social;
            $sc->classe_id = $classe_id_new;
            $sc->save();
        } catch (Exception $e) {
            //echo "Error<br/>";
            echo "<br/>" . $e->getMessage() . "<br/>";
            echo "-1";
            $allAffected = 0;
        }
        echo "$allAffected"; //1--> All successfully saved; < 0--> Failed for least one
    }


    public function resetPromotionInfo(Request $request)
    {
        $connection = $request->input("connection");
        $year = $request->input("year");
        $classe_id = $request->input("classe_id");
        config(["database.default" => $connection]);
        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            DB::select("UPDATE `student_classe` SET mustRepeat = 2, 
            isMannullalyClassified = 2, isMannullalyDismissed = 2, promuEn = null, 
            codeExclusion = 0 
            WHERE classe_id = $classe_id AND sy_id = $sy_id");
            echo "1"; //SUCCESS
        } catch (Exception $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            return;
        }
    }


    public function getAllDisciplines(Request $request)
    {
        //first and second sequence of the term
        $connection = $request->input("connection");
        $year = $request->input("year");
        $term_id = $request->input("term_id");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            $marks = DB::select("SELECT student.`stud_id`, discipline.`absunjust`, discipline.`absjust`, discipline.`lateness`, 
                discipline.`blame`, discipline.`avertissement`, discipline.`nb_jour_exclusion`, discipline.`exclusion_definitive`, 
                discipline.`consigne`, discipline.`commentOnDiscipline`, student_classe.classe_id
                FROM discipline, student, student_classe                
                WHERE discipline.stud_id = student.stud_id
				AND
					student.stud_id = student_classe.stud_id
                AND
					discipline.term = $term_id 
				AND
					discipline.sy_id = $sy_id");
            return response()->json($marks, 200);
        } catch (Exception $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }

    public function getAllDisciplines2(Request $request)
    {
        //first and second sequence of the term
        $connection = $request->input("connection");
        $year = $request->input("year");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            $marks = DB::select("SELECT student.`stud_id`, discipline.`absunjust`, discipline.`absjust`, discipline.`lateness`, 
                discipline.`blame`, discipline.`avertissement`, discipline.`nb_jour_exclusion`, discipline.`exclusion_definitive`, 
                discipline.`consigne`, discipline.`commentOnDiscipline`, student_classe.classe_id, discipline.term
                FROM discipline, student, student_classe                
                WHERE discipline.stud_id = student.stud_id
				AND
					student.stud_id = student_classe.stud_id                 
				AND
					discipline.sy_id = $sy_id");
            return response()->json($marks, 200);
        } catch (Exception $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }

    public function allStudentCompMark(Request $request)
    {
        //first and second sequence of the term
        $connection = $request->input("connection");
        $year = $request->input("year");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            $marks = DB::select(
                "SELECT stud_comp_mark.`stud_comp_mark_id`, stud_comp_mark.`term_id`, stud_comp_mark.`subject_id`, 
                        stud_comp_mark.`subject_competence_id`, stud_comp_mark.`stud_id`, stud_comp_mark.`mark`, 
					    stud_comp_mark.`isEmpty`, student_classe.classe_id 
                        FROM stud_comp_mark, student_classe 
					    WHERE
					       student_classe.stud_id = stud_comp_mark.stud_id
						   AND stud_comp_mark.`sy_id` = $sy_id"
            );
            return response()->json($marks, 200);
        } catch (Exception $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }


    public function allStudentSubject(Request $request)
    {
        //first and second sequence of the term
        $connection = $request->input("connection");
        $year = $request->input("year");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            $marks = DB::select(
                "SELECT student_subject.student_subject_id, student_subject.stud_id, 
                        student_subject.subject_id, student_subject.sequence, student_subject.mark, 
                        student_subject.isEmpty, student_classe.classe_id  
                        FROM student_subject, student_classe
                            WHERE student_classe.stud_id = student_subject.stud_id
                            AND student_subject.`sy_id` = $sy_id
                            ORDER BY classe_id, sequence, subject_id, stud_id"
            );
            return response()->json($marks, 200);
        } catch (Exception $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }

    public function getDisciplineOfClasse(Request $request)
    {
        //first and second sequence of the term
        $connection = $request->input("connection");
        $year = $request->input("year");
        $term_id = $request->input("term_id");
        $classe_id = $request->input("classe_id");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            $marks = DB::select("SELECT `stud_id`, `absunjust`, `absjust`, `lateness`, 
                `blame`, `avertissement`, `nb_jour_exclusion`, `exclusion_definitive`, 
                `consigne`, `commentOnDiscipline` 
                FROM discipline
                WHERE term = $term_id AND sy_id = $sy_id
                    AND stud_id IN(SELECT student_classe.stud_id from student_classe
                    WHERE student_classe.classe_id = $classe_id)");
            return response()->json($marks, 200);
        } catch (Exception $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }

    public function saveOrUpdateABSWithPOST2(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $year = $request->input("year");
        $term_id = $request->input("term_id");

        $absList = json_decode($data, true);
        $n = count($absList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]<br/>";
        $allAffected = 1; //interpreted as true. 0-->false  
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);

        $count  = 1;
        foreach ($absList as $abs) {
            //echo"-----> Processing stud No.[$count]<br/>";
            $count++;
            $stud_id = $abs["stud_id"]; //Stud_id shouldn't be null here if it is the case ==> Fatal ERROR            

            try {
                $ref = Discipline::all()
                    ->where("sy_id", $sy_id)
                    ->where("stud_id", $stud_id)
                    ->where("term", $term_id)
                    ->first();
                if (!is_null($ref)) {
                    //echo "Ref for $stud_id found <br/>";
                    $ref->absunjust = $abs["nbAbs"];
                    $ref->nb_jour_exclusion = $abs["exclusion"];
                    $ref->lateness = $abs["lateness"];
                    $ref->consigne = $abs["consigne"];
                    $ref->avertissement = $abs["avertissement"];
                    $ref->exclusion_definitive = $abs["dismissed"];
                    $ref->commentOnDiscipline = $abs["comment"];
                    $ref->update();
                    //echo "updated";
                } else {
                    $ref = new Discipline();
                    $ref->stud_id  = $stud_id;
                    $ref->sy_id = $sy_id;
                    $ref->term = $term_id;
                    $ref->absunjust = $abs["nbAbs"];
                    $ref->nb_jour_exclusion = $abs["exclusion"];
                    $ref->lateness = $abs["lateness"];
                    $ref->consigne = $abs["consigne"];
                    $ref->avertissement = $abs["avertissement"];
                    $ref->exclusion_definitive = $abs["dismissed"];
                    $ref->commentOnDiscipline = $abs["comment"];
                    $ref->save();
                }
            } catch (Exception $e2) {
                echo "<br/>" . $e2->getMessage() . "<br/>";
                echo "-1"; //failed to save/update;
                $allAffected = 0;
            }
        } //END FOR
        echo "$allAffected"; //1--> All successfully saved/updated; < 0--> Failed for least one
    }

    public function saveOrUpdateABS2(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $year = $request->input("year");
        $term_id = $request->input("term_id");

        $absList = json_decode($data, true);
        $n = count($absList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]<br/>";
        $allAffected = 1; //interpreted as true. 0-->false  
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);

        $count  = 1;
        foreach ($absList as $abs) {
            //echo"-----> Processing stud No.[$count]<br/>";
            $count++;
            $stud_id = $abs["stud_id"]; //Stud_id shouldn't be null here if it is the case ==> Fatal ERROR            

            try {
                $ref = Discipline::all()
                    ->where("sy_id", $sy_id)
                    ->where("stud_id", $stud_id)
                    ->where("term", $term_id)
                    ->first();
                if (!is_null($ref)) {
                    //echo "Ref for $stud_id found <br/>";
                    $ref->absunjust = $abs["nbAbs"];
                    $ref->nb_jour_exclusion = $abs["exclusion"];
                    $ref->lateness = $abs["lateness"];
                    $ref->consigne = $abs["consigne"];
                    $ref->avertissement = $abs["avertissement"];
                    $ref->exclusion_definitive = $abs["dismissed"];
                    $ref->commentOnDiscipline = $abs["comment"];
                    $ref->update();
                    //echo "updated";
                } else {
                    $ref = new Discipline();
                    $ref->stud_id  = $stud_id;
                    $ref->sy_id = $sy_id;
                    $ref->term = $term_id;
                    $ref->absunjust = $abs["nbAbs"];
                    $ref->nb_jour_exclusion = $abs["exclusion"];
                    $ref->lateness = $abs["lateness"];
                    $ref->consigne = $abs["consigne"];
                    $ref->avertissement = $abs["avertissement"];
                    $ref->exclusion_definitive = $abs["dismissed"];
                    $ref->commentOnDiscipline = $abs["comment"];
                    $ref->save();
                }
            } catch (Exception $e2) {
                echo "<br/>" . $e2->getMessage() . "<br/>";
                echo "-1"; //failed to save/update;
                $allAffected = 0;
            }
        } //END FOR
        echo "$allAffected"; //1--> All successfully saved/updated; < 0--> Failed for least one
    }

    public function allStudentsOfClasseForAbs(Request $request)
    {   //THE CLASSE IS ASSUME TO BE A CLASSE OF THE CURRENT SECTION
        $connection = $request->input("connection");
        $year = $request->input("year");
        $classe_id = $request->input("classe_id");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            $students = DB::select(
                "SELECT stud_id, matricule, name, surname, bday, bplace, '' as comment,
                    sexe, 0 as repeating, 0 as `nbAbs`, 0 as `lateness`, 0 as `consigne`,
                        0 as `avertissement`, 0 as blame, 0 as dismissed, 0 as exclusion
                    FROM student WHERE stud_id 
                        IN(SELECT student_classe.stud_id from student_classe
                        WHERE student_classe.sy_id = $sy_id 
                        AND student_classe.classe_id = $classe_id)
                        Order by student.name"
            );
            return response()->json($students, 200);
        } catch (Exception $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }

    public function allStudentCompMarkOfTerm(Request $request)
    {
        //first and second sequence of the term
        $connection = $request->input("connection");
        $year = $request->input("year");
        $term_id = $request->input("term_id");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            $marks = DB::select(
                "SELECT `stud_comp_mark_id`, `term_id`, `subject_id`, 
                `subject_competence_id`, `stud_id`, `mark`, `isEmpty` 
                FROM `stud_comp_mark` WHERE `sy_id` = $sy_id AND term_id = $term_id"
            );
            return response()->json($marks, 200);
        } catch (Exception $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }

    public function allStudentCompMarkOfTerm2(Request $request)
    {
        //first and second sequence of the term
        $connection = $request->input("connection");
        $year = $request->input("year");
        $term_id = $request->input("term_id");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            $marks = DB::select(
                "SELECT stud_comp_mark.`stud_comp_mark_id`, stud_comp_mark.`term_id`, stud_comp_mark.`subject_id`, 
                        stud_comp_mark.`subject_competence_id`, stud_comp_mark.`stud_id`, stud_comp_mark.`mark`, 
					    stud_comp_mark.`isEmpty`, student_classe.classe_id 
                        FROM stud_comp_mark, student_classe 
					    WHERE
					       student_classe.stud_id = stud_comp_mark.stud_id
						   AND stud_comp_mark.`sy_id` = $sy_id 
                           AND stud_comp_mark.term_id = $term_id"
            );
            return response()->json($marks, 200);
        } catch (Exception $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }

    public function allStudentSubjectOfTerm(Request $request)
    {
        //first and second sequence of the term
        $connection = $request->input("connection");
        $year = $request->input("year");
        $sequence1 = $request->input("sequence1");
        $sequence2 = $request->input("sequence2");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            $marks = DB::select(
                "SELECT `student_subject_id`, `stud_id`, `subject_id`, 
                `sequence`, `mark`, `isEmpty`  FROM `student_subject` 
                WHERE `sy_id` = $sy_id
                AND (`sequence` = $sequence1 OR `sequence` = $sequence2)"
            );
            return response()->json($marks, 200);
        } catch (Exception $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }

    public function allStudentSubjectOfTerm2(Request $request)
    {
        //first and second sequence of the term
        $connection = $request->input("connection");
        $year = $request->input("year");
        $sequence1 = $request->input("sequence1");
        $sequence2 = $request->input("sequence2");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            $marks = DB::select(
                "SELECT student_subject.student_subject_id, student_subject.stud_id, 
                        student_subject.subject_id, student_subject.sequence, student_subject.mark, 
                        student_subject.isEmpty, student_classe.classe_id  
                        FROM student_subject, student_classe
                            WHERE student_classe.stud_id = student_subject.stud_id
                            AND student_subject.`sy_id` = $sy_id
                            AND (student_subject.`sequence` = $sequence1 
                                OR student_subject.`sequence` = $sequence2)
                            ORDER BY classe_id, sequence, subject_id, stud_id"
            );
            return response()->json($marks, 200);
        } catch (Exception $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }

    public function allStudentsOfClasse3(Request $request)
    {   //THE CLASSE IS ASSUME TO BE A CLASSE OF THE CURRENT SECTION
        $connection = $request->input("connection");
        $year = $request->input("year");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            $students = DB::select(
                "SELECT student.stud_id, student.matricule, student.name, student.surname, student.bday, 
                        student.bplace, student.sexe, student.handicape, student.position, 
                        student_classe.repeating, student_classe.solvable1, student_classe.cas_social, student_classe.position_classe,
                        student_classe.classe_id, classe.classe_name, classe.`level`, student_classe.val1, student_classe.val2, 
                        student_classe.val3, student_classe.str1, student_classe.str2, student_classe.str3,
                        student_classe.isMannullalyClassified, student_classe.isMannullalyDismissed,
                        student_classe.mustRepeat, student_classe.dismissalReason, 
                        student.st1 as father, student.str2 as mother,
                        student_classe.promuEn, student_classe.codeExclusion, student_classe.basculated, student_classe.basculated_classe_id
                            FROM student, student_classe, classe 
						        WHERE 
						            student.stud_id = student_classe.stud_id
						                AND student_classe.classe_id = classe.classe_id
						                AND student_classe.sy_id = $sy_id
                            Order by student.name, classe.`level`, classe.classe_name "
            );
            return response()->json($students, 200);
        } catch (Exception $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }


    public function allStudentsOfClasseOfSchool(Request $request)
    {   //THE CLASSE IS ASSUME TO BE A CLASSE OF THE CURRENT SECTION
        $connection = $request->input("connection");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $students = DB::select(
                "SELECT student.stud_id, student.matricule, student.name, student.surname, student.bday, 
                        student.bplace, student.sexe, student.handicape, student.position, 
                        student_classe.repeating, student_classe.solvable1, student_classe.cas_social, student_classe.position_classe,
                        student_classe.classe_id, classe.classe_name, classe.`level`, student_classe.val1, student_classe.val2, 
                        student_classe.val3, student_classe.str1, student_classe.str2, student_classe.str3,
                        student_classe.isMannullalyClassified, student_classe.isMannullalyDismissed,
                        student_classe.mustRepeat, student_classe.dismissalReason, 
                        student.st1 as father, student.str2 as mother,
                        student_classe.promuEn, student_classe.codeExclusion, student_classe.basculated, student_classe.basculated_classe_id 
                            FROM student, student_classe, classe 
						        WHERE 
						            student.stud_id = student_classe.stud_id
						                AND student_classe.classe_id = classe.classe_id 
                            Order by student.name, classe.`level`, classe.classe_name "
            );
            return response()->json($students, 200);
        } catch (Exception $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }


    public function allStudentsOfClasseOfSection(Request $request)
    {   //THE CLASSE IS ASSUME TO BE A CLASSE OF THE CURRENT SECTION
        $connection = $request->input("connection");
        $section_id = $request->input("section_id");
        $year = $request->input("year");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            $students = DB::select(
                "SELECT student.stud_id, student.matricule, student.name, student.surname, student.bday, 
                        student.bplace, student.sexe, student.handicape, student.position, 
                        student_classe.repeating, student_classe.solvable1, student_classe.cas_social, student_classe.position_classe,
                        student_classe.classe_id, classe.classe_name, classe.`level`, student_classe.val1, student_classe.val2, 
                        student_classe.val3, student_classe.str1, student_classe.str2, student_classe.str3,
                        student_classe.isMannullalyClassified, student_classe.isMannullalyDismissed,
                        student_classe.mustRepeat, student_classe.dismissalReason, 
                        student_classe.promuEn, student_classe.codeExclusion, student_classe.basculated, student_classe.basculated_classe_id 
                            FROM student, student_classe, classe, classe_year 
						        WHERE 
						            student.stud_id = student_classe.stud_id
						                AND student_classe.classe_id = classe.classe_id
                                        AND student_classe.classe_id = classe_year.classe_id
						                AND student_classe.sy_id = $sy_id
                                        AND classe_year.section_id = $section_id
                            Order by student.name, classe.`level`, classe.classe_name "
            );
            return response()->json($students, 200);
        } catch (Exception $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }

    public function updatePromotionInfoWithPOST(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $year = $request->input("year");

        $stList = json_decode($data, true);
        $n = count($stList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]<br/>";
        $allAffected = 1; //interpreted as true. 0-->false  
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);

        $count  = 1;
        foreach ($stList as $st) {
            //echo"-----> Processing stud No.[$count]<br/>";
            $count++;
            $stud_id = $st["stud_id"];
            $classe_id = $st["classe_id"];

            $isMannullalyClassified = $st["isMannullalyClassified"];
            $isMannullalyDismissed = $st["isMannullalyDismissed"];
            $mustRepeat = $st["mustRepeat"];
            $dismissalReason = $st["dismissalReason"]; //RAISON POUR laquelle l'eleve est exclu
            $promuEn = $st["promuEn"]; //id de la classe dans laquelle l'eleve sera promue.
            $codeExclusion = $st["codeExclusion"]; //CODE RAISON EXCLUSION: 1->Age; 2->Conduite; 3->Travail; 4->Ne peut trippler; 5->Abandon; 6->Insolvable
            /*
            $str1 = $st["str1"];  
            $str2 = $st["str2"];  
            $str3 = $st["str3"];  
            $val1 = $st["val1"];  
            $val2 = $st["val2"]; 
            $val3 = $st["val3"];  
            */

            try {

                DB::select("UPDATE student_classe SET isMannullalyClassified = $isMannullalyClassified, 
                        isMannullalyDismissed = $isMannullalyDismissed, mustRepeat = $mustRepeat, 
                        dismissalReason = '$dismissalReason', promuEn='$promuEn', 
                        codeExclusion=$codeExclusion 
                        WHERE sy_id = $sy_id AND classe_id = $classe_id  AND stud_id = $stud_id");
            } catch (Exception $e) {
                echo "<br/>" . $e->getMessage() . "<br/>";
                echo "-1"; //failed to save/update;
                $allAffected = 0;
            }
        } //END FOR
        echo "$allAffected"; //1--> All successfully saved/updated; < 0--> Failed for least one
    }

    public function updatePromotionInfo(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $year = $request->input("year");

        $stList = json_decode($data, true);
        $n = count($stList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]<br/>";
        $allAffected = 1; //interpreted as true. 0-->false  
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);

        $count  = 1;
        foreach ($stList as $st) {
            //echo"-----> Processing stud No.[$count]<br/>";
            $count++;
            $stud_id = $st["stud_id"];
            $classe_id = $st["classe_id"];

            $isMannullalyClassified = $st["isMannullalyClassified"];
            $isMannullalyDismissed = $st["isMannullalyDismissed"];
            $mustRepeat = $st["mustRepeat"];
            $dismissalReason = $st["dismissalReason"]; //RAISON POUR laquelle l'eleve est exclu
            $promuEn = $st["promuEn"]; //id de la classe dans laquelle l'eleve sera promue.
            $codeExclusion = $st["codeExclusion"]; //CODE RAISON EXCLUSION: 1->Age; 2->Conduite; 3->Travail; 4->Ne peut trippler; 5->Abandon; 6->Insolvable
            /*
            $str1 = $st["str1"];  
            $str2 = $st["str2"];  
            $str3 = $st["str3"];  
            $val1 = $st["val1"];  
            $val2 = $st["val2"]; 
            $val3 = $st["val3"];  
            */

            try {

                DB::select("UPDATE student_classe SET isMannullalyClassified = $isMannullalyClassified, 
                        isMannullalyDismissed = $isMannullalyDismissed, mustRepeat = $mustRepeat, 
                        dismissalReason = '$dismissalReason', promuEn='$promuEn', 
                        codeExclusion=$codeExclusion 
                        WHERE sy_id = $sy_id AND classe_id = $classe_id  AND stud_id = $stud_id");
            } catch (Exception $e) {
                echo "<br/>" . $e->getMessage() . "<br/>";
                echo "-1"; //failed to save/update;
                $allAffected = 0;
            }
        } //END FOR
        echo "$allAffected"; //1--> All successfully saved/updated; < 0--> Failed for least one
    }

    public function allStudentsForMarks(Request $request)
    {   //THE CLASSE IS ASSUME TO BE A CLASSE OF THE CURRENT SECTION
        $connection = $request->input("connection");
        $year = $request->input("year");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            $students = DB::select(
                "SELECT stud_id, matricule, name, surname, bday, bplace, 
                    sexe, handicape, position, 0 as repeating, 0 as  cas_social
                    FROM student WHERE stud_id 
                        IN(SELECT student_classe.stud_id from student_classe
                        WHERE student_classe.sy_id = $sy_id)  
                        Order by student.name"
            );
            return response()->json($students, 200);
        } catch (Exception $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }

    public function getAllCompMarksSimple(Request $request)
    {
        $connection = $request->input("connection");
        $year = $request->input("year");
        $term_id = $request->input("term_id");


        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            $marks = DB::select(
                "SELECT `stud_id`, `mark`, `isEmpty` FROM `stud_comp_mark` 
                WHERE stud_comp_mark.`sy_id` = $sy_id
                AND `term_id` = $term_id"
            );
            return response()->json($marks, 200);
        } catch (Exception $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }

    public function getAllSeqMarksSimple(Request $request)
    {
        $connection = $request->input("connection");
        $year = $request->input("year");
        $sequence2 = $request->input("sequence1");
        $sequence1 = $request->input("sequence2");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            $marks = DB::select(
                "SELECT `stud_id`, `mark`, isEmpty FROM `student_subject` WHERE `sy_id` = $sy_id
                    AND (`sequence` = $sequence1 OR `sequence` = $sequence2)"
            );
            return response()->json($marks, 200);
        } catch (Exception $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }


    public function saveCompSeqMarks2(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $year = $request->input("year");
        $subject_id = $request->input("subject_id");
        $term_id = $request->input("term_id");
        $subject_competence_id = $request->input("subject_competence_id");

        $stCompList = json_decode($data, true);
        $n = count($stCompList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]<br/>";
        $allAffected = 1; //interpreted as true. 0-->false  
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);

        $count  = 1;
        foreach ($stCompList as $comp) {
            //echo"-----> Processing stud No.[$count]<br/>";
            $count++;
            $stud_id = $comp["stud_id"]; //Stud_id shouldn't be null here if it is the case ==> Fatal ERROR
            $mark = $comp["mark"]; //if mark is not double here operation shall fail
            $isEmpty = $comp["isEmpty"];
            try {
                /*
                $tmp = StudCompMark::all()
                    ->where("sy_id", $sy_id)
                    ->where("stud_id", $stud_id)
                    ->where("subject_id", $subject_id)
                    ->where("term_id", $term_id)
                    ->where("subject_competence_id", $subject_competence_id)
                    ->first();
                */
                $tmp = DB::select("SELECT*FROM stud_comp_mark WHERE sy_id = $sy_id 
                        AND subject_id = $subject_id 
                        AND term_id = $term_id AND stud_id = $stud_id 
                        AND subject_competence_id  = $subject_competence_id");
                if (count($tmp) > 0) {
                    if ($isEmpty == 1) {
                        $mark = 0;
                    }
                    //DB::select("UPDATE stud_comp_mark SET mark = 15, isEmpty = 0 WHERE sy_id = 5 AND subject_id = 2 
                    //        AND term_id = 1 AND stud_id = 3273 AND subject_competence_id  = 168");                    
                    /*
                    echo"LET's UPDATE<br/>";
                    echo "mark = $mark<br/>";
                    echo "isEmpty = $isEmpty<br/>";
                    echo "sy_id = $sy_id<br/>";
                    echo "subject_id = $subject_id<br/>";
                    echo "term_id = $term_id<br/>";
                    echo "stud_id = $stud_id<br/>";
                    */
                    echo "subject_competence_id = $subject_competence_id<br/>";
                    DB::select("UPDATE stud_comp_mark SET mark = $mark, isEmpty = $isEmpty 
                        WHERE sy_id = $sy_id  AND subject_id = $subject_id
                            AND term_id = $term_id AND stud_id = $stud_id 
                            AND subject_competence_id  = $subject_competence_id");


                    /* 
                    echo"LET's UPDATE<br/>";                      
                     $result = json_decode(json_encode($tmp), true);
                     echo count($result)."<br/>";  
                     */

                    //echo "updated";
                } else {
                    $c = new StudCompMark();
                    if ($isEmpty == 1) {
                        $c->mark = "0";
                        $c->isEmpty = 1;
                    } else {
                        $c->mark = $mark;
                        $c->isEmpty = 0;
                    }
                    $c->term_id = $term_id;
                    $c->subject_id = $subject_id;
                    $c->stud_id = $stud_id;
                    $c->sy_id = $sy_id;
                    $c->subject_competence_id = $subject_competence_id;
                    $c->save();
                    //echo "saved";
                }
            } catch (Exception $e2) {
                echo "<br/>" . $e2->getMessage() . "<br/>";
                echo "-1"; //failed to save/update;
                $allAffected = 0;
            }
        } //END FOR
        echo "$allAffected"; //1--> All successfully saved/updated; < 0--> Failed for least one
    }

    public function saveCompSeqMarksWithPOST2(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $year = $request->input("year");
        $subject_id = $request->input("subject_id");
        $term_id = $request->input("term_id");
        $subject_competence_id = $request->input("subject_competence_id");

        $stCompList = json_decode($data, true);
        $n = count($stCompList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]<br/>";
        $allAffected = 1; //interpreted as true. 0-->false  
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);

        $count  = 1;
        foreach ($stCompList as $comp) {
            //echo"-----> Processing stud No.[$count]<br/>";
            $count++;
            $stud_id = $comp["stud_id"]; //Stud_id shouldn't be null here if it is the case ==> Fatal ERROR
            $mark = $comp["mark"]; //if mark is not double here operation shall fail
            $isEmpty = $comp["isEmpty"];
            try {
                /*
                $tmp = StudCompMark::all()
                    ->where("sy_id", $sy_id)
                    ->where("stud_id", $stud_id)
                    ->where("subject_id", $subject_id)
                    ->where("term_id", $term_id)
                    ->where("subject_competence_id", $subject_competence_id)
                    ->first();
                */
                $tmp = DB::select("SELECT*FROM stud_comp_mark WHERE sy_id = $sy_id 
                        AND subject_id = $subject_id 
                        AND term_id = $term_id AND stud_id = $stud_id 
                        AND subject_competence_id  = $subject_competence_id");
                //if (!is_null($tmp)) {
                if (count($tmp) > 0) {
                    if ($isEmpty == 1) {
                        $mark = 0;
                    }

                    //DB::select("UPDATE stud_comp_mark SET mark = 15, isEmpty = 0 WHERE sy_id = 5 AND subject_id = 2 
                    //        AND term_id = 1 AND stud_id = 3273 AND subject_competence_id  = 168");
                    /*
                    echo"LET's UPDATE<br/>";
                    echo "mark = $mark<br/>";
                    echo "isEmpty = $isEmpty<br/>";
                    echo "sy_id = $sy_id<br/>";
                    echo "subject_id = $subject_id<br/>";
                    echo "$term_id = $$term_id<br/>";
                    echo "stud_id = $stud_id<br/>";
                    echo "subject_competence_id = $subject_competence_id<br/>";
                    */
                    DB::select("UPDATE stud_comp_mark SET mark = $mark, isEmpty = $isEmpty 
                        WHERE sy_id = $sy_id  AND subject_id = $subject_id
                            AND term_id = $term_id AND stud_id = $stud_id 
                            AND subject_competence_id  = $subject_competence_id");
                    //echo "updated";
                } else {
                    $c = new StudCompMark();
                    if ($isEmpty == 1) {
                        $c->mark = "0";
                        $c->isEmpty = 1;
                    } else {
                        $c->mark = $mark;
                        $c->isEmpty = 0;
                    }
                    $c->term_id = $term_id;
                    $c->subject_id = $subject_id;
                    $c->stud_id = $stud_id;
                    $c->sy_id = $sy_id;
                    $c->subject_competence_id = $subject_competence_id;
                    $c->save();
                    //echo "saved";
                }
            } catch (Exception $e2) {
                echo "<br/>" . $e2->getMessage() . "<br/>";
                echo "-1"; //failed to save/update;
                $allAffected = 0;
            }
        } //END FOR
        echo "$allAffected"; //1--> All successfully saved/updated; < 0--> Failed for least one
    }

    public function saveManySeqMarks2(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $year = $request->input("year");
        $subject_id = $request->input("subject_id");
        $sequence = $request->input("sequence");

        $stList = json_decode($data, true);
        $n = count($stList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]<br/>";
        $allAffected = 1; //interpreted as true. 0-->false  
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);

        $count  = 1;
        foreach ($stList as $st) {
            //echo"-----> Processing stud No.[$count]<br/>";
            $count++;
            $stud_id = $st["stud_id"]; //Stud_id shouldn't be null here if it is the case ==> Fatal ERROR
            $mark = $st["mark"]; //if mark is not double here operation shall fail
            $isEmpty = $st["isEmpty"];
            try {
                /*
                $studSubTmp = StudentSubject::all()
                    ->where("sy_id", $sy_id)
                    ->where("stud_id", $stud_id)
                    ->where("subject_id", $subject_id)
                    ->where("sequence", $sequence)
                    ->first();
                */
                $studs = DB::select("SELECT*FROM student_subject 
                WHERE sy_id = $sy_id AND subject_id = $subject_id 
                AND sequence = $sequence AND stud_id = $stud_id");
                //if (!is_null($studs)) { 
                if (count($studs) > 0) {
                    if ($isEmpty == 1) {
                        $mark = 0;
                    }
                    DB::select("UPDATE student_subject SET mark = $mark, isEmpty = $isEmpty 
                    WHERE sy_id = $sy_id AND subject_id = $subject_id 
                    AND sequence = $sequence AND stud_id = $stud_id");

                    //echo "updated";
                } else {
                    $studSub = new StudentSubject();
                    if ($isEmpty == 1) {
                        $studSub->mark = "0";
                        $studSub->isEmpty = 1;
                    } else {
                        $studSub->mark = $mark;
                        $studSub->isEmpty = 0;
                    }
                    $studSub->sequence = $sequence;
                    $studSub->subject_id = $subject_id;
                    $studSub->stud_id = $stud_id;
                    $studSub->sy_id = $sy_id;
                    $studSub->save();
                    //echo "saved";
                }
            } catch (Exception $e2) {
                echo "<br/>" . $e2->getMessage() . "<br/>";
                echo "-1"; //failed to save/update;
                $allAffected = 0;
            }
        } //END FOR
        echo "$allAffected"; //1--> All successfully saved/updated; < 0--> Failed for least one
    }

    public function saveManySeqMarksWithPOST2(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $year = $request->input("year");
        $subject_id = $request->input("subject_id");
        $sequence = $request->input("sequence");

        $stList = json_decode($data, true);
        $n = count($stList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]<br/>";
        $allAffected = 1; //interpreted as true. 0-->false  
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);

        $count  = 1;
        foreach ($stList as $st) {
            //echo"-----> Processing stud No.[$count]<br/>";
            $count++;
            $stud_id = $st["stud_id"]; //Stud_id shouldn't be null here if it is the case ==> Fatal ERROR
            $mark = $st["mark"]; //if mark is not double here operation shall fail
            $isEmpty = $st["isEmpty"];
            try {
                /*
                $studSubTmp = StudentSubject::all()
                    ->where("sy_id", $sy_id)
                    ->where("stud_id", $stud_id)
                    ->where("subject_id", $subject_id)
                    ->where("sequence", $sequence)
                    ->first();
                */
                $studs = DB::select("SELECT*FROM student_subject 
                WHERE sy_id = $sy_id AND subject_id = $subject_id 
                AND sequence = $sequence AND stud_id = $stud_id");
                if (count($studs) > 0) {
                    if ($isEmpty == 1) {
                        $mark = 0;
                    }
                    DB::select("UPDATE student_subject SET mark = $mark, isEmpty = $isEmpty 
                    WHERE sy_id = $sy_id AND subject_id = $subject_id 
                    AND sequence = $sequence AND stud_id = $stud_id");

                    //echo "updated";
                } else {
                    $studSub = new StudentSubject();
                    if ($isEmpty == 1) {
                        $studSub->mark = "0";
                        $studSub->isEmpty = 1;
                    } else {
                        $studSub->mark = $mark;
                        $studSub->isEmpty = 0;
                    }
                    $studSub->sequence = $sequence;
                    $studSub->subject_id = $subject_id;
                    $studSub->stud_id = $stud_id;
                    $studSub->sy_id = $sy_id;
                    $studSub->save();
                    //echo "saved";
                }
            } catch (Exception $e2) {
                echo "<br/>" . $e2->getMessage() . "<br/>";
                echo "-1"; //failed to save/update;
                $allAffected = 0;
            }
        } //END FOR
        echo "$allAffected"; //1--> All successfully saved/updated; < 0--> Failed for least one
    }


    public function uploadSeqMarks(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $year = $request->input("year");

        $stList = json_decode($data, true);
        $n = count($stList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]<br/>";
        $allAffected = 1; //interpreted as true. 0-->false  
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);

        $count  = 1;
        foreach ($stList as $st) {
            //echo"-----> Processing stud No.[$count]<br/>";
            $count++;
            $stud_id = $st["stud_id"]; //Stud_id shouldn't be null here if it is the case ==> Fatal ERROR
            $mark = $st["mark"]; //if mark is not double here operation shall fail
            $isEmpty = 0;
            $sequence = $st["sequence"];
            $subject_id = $st["subject_id"];
            try {
                $studs = DB::select("SELECT*FROM student_subject 
                WHERE sy_id = $sy_id AND subject_id = $subject_id 
                AND sequence = $sequence AND stud_id = $stud_id");
                //if (!is_null($studs)) { 
                if (count($studs) > 0) {
                    if ($isEmpty == 1) {
                        $mark = 0;
                    }
                    DB::select("UPDATE student_subject SET mark = $mark, isEmpty = $isEmpty 
                    WHERE sy_id = $sy_id AND subject_id = $subject_id 
                    AND sequence = $sequence AND stud_id = $stud_id");

                    //echo "updated";
                } else {
                    $studSub = new StudentSubject();
                    if ($isEmpty == 1) {
                        $studSub->mark = "0";
                        $studSub->isEmpty = 1;
                    } else {
                        $studSub->mark = $mark;
                        $studSub->isEmpty = 0;
                    }
                    $studSub->sequence = $sequence;
                    $studSub->subject_id = $subject_id;
                    $studSub->stud_id = $stud_id;
                    $studSub->sy_id = $sy_id;
                    $studSub->save();
                    //echo "saved";
                }
            } catch (Exception $e2) {
                echo "<br/>" . $e2->getMessage() . "<br/>";
                echo "-1"; //failed to save/update;
                $allAffected = 0;
            }
        } //END FOR
        echo "$allAffected"; //1--> All successfully saved/updated; < 0--> Failed for least one
    }

    public function uploadSeqMarksWithPOST(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $year = $request->input("year");

        $stList = json_decode($data, true);
        $n = count($stList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]<br/>";
        $allAffected = 1; //interpreted as true. 0-->false  
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);

        $count  = 1;
        foreach ($stList as $st) {
            //echo"-----> Processing stud No.[$count]<br/>";
            $count++;
            $stud_id = $st["stud_id"]; //Stud_id shouldn't be null here if it is the case ==> Fatal ERROR
            $mark = $st["mark"]; //if mark is not double here operation shall fail
            $isEmpty = 0;
            $sequence = $st["sequence"];
            $subject_id = $st["subject_id"];
            try {
                $studs = DB::select("SELECT*FROM student_subject 
                WHERE sy_id = $sy_id AND subject_id = $subject_id 
                AND sequence = $sequence AND stud_id = $stud_id");
                //if (!is_null($studs)) { 
                if (count($studs) > 0) {
                    if ($isEmpty == 1) {
                        $mark = 0;
                    }
                    DB::select("UPDATE student_subject SET mark = $mark, isEmpty = $isEmpty 
                    WHERE sy_id = $sy_id AND subject_id = $subject_id 
                    AND sequence = $sequence AND stud_id = $stud_id");

                    //echo "updated";
                } else {
                    $studSub = new StudentSubject();
                    if ($isEmpty == 1) {
                        $studSub->mark = "0";
                        $studSub->isEmpty = 1;
                    } else {
                        $studSub->mark = $mark;
                        $studSub->isEmpty = 0;
                    }
                    $studSub->sequence = $sequence;
                    $studSub->subject_id = $subject_id;
                    $studSub->stud_id = $stud_id;
                    $studSub->sy_id = $sy_id;
                    $studSub->save();
                    //echo "saved";
                }
            } catch (Exception $e2) {
                echo "<br/>" . $e2->getMessage() . "<br/>";
                echo "-1"; //failed to save/update;
                $allAffected = 0;
            }
        } //END FOR
        echo "$allAffected"; //1--> All successfully saved/updated; < 0--> Failed for least one
    }


    public function uploadCompMarks(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $year = $request->input("year");

        $stCompList = json_decode($data, true);
        $n = count($stCompList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]<br/>";
        $allAffected = 1; //interpreted as true. 0-->false  
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);

        $count  = 1;
        foreach ($stCompList as $comp) {
            //echo"-----> Processing stud No.[$count]<br/>";
            $count++;
            $stud_id = $comp["stud_id"]; //Stud_id shouldn't be null here if it is the case ==> Fatal ERROR
            $mark = $comp["mark"]; //if mark is not double here operation shall fail
            $isEmpty = 0;
            $term_id = $comp["term_id"];
            $subject_id = $comp["subject_id"];
            $subject_competence_id = $comp["subject_competence_id"];
            try {

                $tmp = DB::select("SELECT*FROM stud_comp_mark WHERE sy_id = $sy_id 
                        AND subject_id = $subject_id 
                        AND term_id = $term_id AND stud_id = $stud_id 
                        AND subject_competence_id  = $subject_competence_id");
                if (count($tmp) > 0) {
                    if ($isEmpty == 1) {
                        $mark = 0;
                    }

                    //echo "subject_competence_id = $subject_competence_id<br/>";
                    DB::select("UPDATE stud_comp_mark SET mark = $mark, isEmpty = $isEmpty 
                        WHERE sy_id = $sy_id  AND subject_id = $subject_id
                            AND term_id = $term_id AND stud_id = $stud_id 
                            AND subject_competence_id  = $subject_competence_id");
                } else {
                    $c = new StudCompMark();
                    if ($isEmpty == 1) {
                        $c->mark = "0";
                        $c->isEmpty = 1;
                    } else {
                        $c->mark = $mark;
                        $c->isEmpty = 0;
                    }
                    $c->term_id = $term_id;
                    $c->subject_id = $subject_id;
                    $c->stud_id = $stud_id;
                    $c->sy_id = $sy_id;
                    $c->subject_competence_id = $subject_competence_id;
                    $c->save();
                    //echo "saved";
                }
            } catch (Exception $e2) {
                echo "<br/>" . $e2->getMessage() . "<br/>";
                echo "-1"; //failed to save/update;
                $allAffected = 0;
            }
        } //END FOR
        echo "$allAffected"; //1--> All successfully saved/updated; < 0--> Failed for least one
    }

    public function uploadCompMarksWithPOST(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $year = $request->input("year");

        $stCompList = json_decode($data, true);
        $n = count($stCompList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]<br/>";
        $allAffected = 1; //interpreted as true. 0-->false  
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);

        $count  = 1;
        foreach ($stCompList as $comp) {
            //echo"-----> Processing stud No.[$count]<br/>";
            $count++;
            $stud_id = $comp["stud_id"]; //Stud_id shouldn't be null here if it is the case ==> Fatal ERROR
            $mark = $comp["mark"]; //if mark is not double here operation shall fail
            $isEmpty = 0;
            $term_id = $comp["term_id"];
            $subject_id = $comp["subject_id"];
            $subject_competence_id = $comp["subject_competence_id"];
            try {

                $tmp = DB::select("SELECT*FROM stud_comp_mark WHERE sy_id = $sy_id 
                        AND subject_id = $subject_id 
                        AND term_id = $term_id AND stud_id = $stud_id 
                        AND subject_competence_id  = $subject_competence_id");
                if (count($tmp) > 0) {
                    if ($isEmpty == 1) {
                        $mark = 0;
                    }

                    echo "subject_competence_id = $subject_competence_id<br/>";
                    DB::select("UPDATE stud_comp_mark SET mark = $mark, isEmpty = $isEmpty 
                        WHERE sy_id = $sy_id  AND subject_id = $subject_id
                            AND term_id = $term_id AND stud_id = $stud_id 
                            AND subject_competence_id  = $subject_competence_id");
                } else {
                    $c = new StudCompMark();
                    if ($isEmpty == 1) {
                        $c->mark = "0";
                        $c->isEmpty = 1;
                    } else {
                        $c->mark = $mark;
                        $c->isEmpty = 0;
                    }
                    $c->term_id = $term_id;
                    $c->subject_id = $subject_id;
                    $c->stud_id = $stud_id;
                    $c->sy_id = $sy_id;
                    $c->subject_competence_id = $subject_competence_id;
                    $c->save();
                    //echo "saved";
                }
            } catch (Exception $e2) {
                echo "<br/>" . $e2->getMessage() . "<br/>";
                echo "-1"; //failed to save/update;
                $allAffected = 0;
            }
        } //END FOR
        echo "$allAffected"; //1--> All successfully saved/updated; < 0--> Failed for least one
    }


    public function getSeqMarks(Request $request)
    {
        $connection = $request->input("connection");
        $year = $request->input("year");
        $classe_id = $request->input("classe_id");
        $subject_id = $request->input("subject_id");
        $sequence = $request->input("sequence");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            $marks = DB::select(
                "SELECT `stud_id`, `mark`, isEmpty FROM `student_subject` WHERE `sy_id` = $sy_id
                    AND `subject_id` = $subject_id AND `sequence` = $sequence
                    AND stud_id IN(SELECT student_classe.stud_id from student_classe
                    WHERE student_classe.classe_id = $classe_id AND student_classe.sy_id = $sy_id)"
            );
            return response()->json($marks, 200);
        } catch (Exception $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }

    public function getSeqMarks2(Request $request)
    {
        $connection = $request->input("connection");
        $year = $request->input("year");
        $classe_id = $request->input("classe_id");
        $subject_id = $request->input("subject_id");
        $sequence1 = $request->input("seq1");
        $sequence2 = $request->input("seq2");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            $marks = DB::select(
                "SELECT `stud_id`, `mark`, isEmpty FROM `student_subject` WHERE `sy_id` = $sy_id
                    AND (`sequence` = $sequence1 OR `sequence` = $sequence2)
                    AND `subject_id` = $subject_id
                    AND stud_id IN(SELECT student_classe.stud_id from student_classe
                    WHERE student_classe.classe_id = $classe_id)"
            );
            return response()->json($marks, 200);
        } catch (Exception $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }

    public function copySeqMarks(Request $request)
    {
        $connection = $request->input("connection");
        $year = $request->input("year");
        $classe_id = $request->input("classe_id");
        $subject_id = $request->input("subject_id");
        $sequence_from = $request->input("sequence_from");
        $sequence_to = $request->input("sequence_to");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            $marks = DB::select(
                "SELECT `stud_id`, `mark`, isEmpty FROM `student_subject` WHERE `sy_id` = $sy_id
                    AND `subject_id` = $subject_id AND `sequence` = $sequence_from
                    AND stud_id IN(SELECT student_classe.stud_id from student_classe
                    WHERE student_classe.classe_id = $classe_id)"
            );

            foreach ($marks as $mark) {
                $student_id = $mark->stud_id;
                $ref1 = StudentSubject::where("stud_id", $student_id)
                    ->where("subject_id", $subject_id)
                    ->where("sy_id", $sy_id)
                    ->where("sequence", $sequence_to)
                    ->first();
                if (!is_null($ref1)) {
                    $ref1->mark = $mark->mark;
                    $ref1->isEmpty = $mark->isEmpty;
                    $ref1->update();
                    //echo $ref1->mark."<br/>";
                } else {
                    try {
                        $ref2 = new StudentSubject();
                        $ref2->stud_id = $student_id;
                        $ref2->subject_id = $subject_id;
                        $ref2->sy_id = $sy_id;
                        $ref2->sequence = $sequence_to;
                        $ref2->mark = $mark->mark;
                        $ref2->isEmpty = $mark->isEmpty;
                        $ref2->save();
                    } catch (Exception $e) {
                        echo '<br/>ERROR: ' . $e->getMessage();
                        return 0; //ERROR OCCURS
                    }
                }
            }
            return 1;
        } catch (Exception $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            return 0; //ERROR OCCURS
        }
    }


    public function copySeqMarks2(Request $request)
    {
        $connection = $request->input("connection");
        $year = $request->input("year");
        $classe_id = $request->input("classe_id");
        $subject_id_from = $request->input("subject_id_from");
        $subject_id_to = $request->input("subject_id_to");
        $sequence1 = $request->input("seq1");
        $sequence2 = $request->input("seq2");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";

        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            $marks = DB::select(
                "SELECT `stud_id`, `mark`, `sequence`, isEmpty 
                    FROM `student_subject` WHERE `sy_id` = $sy_id
                    AND (`sequence` = $sequence1 OR `sequence` = $sequence2)
                    AND `subject_id` = $subject_id_from
                    AND stud_id IN(SELECT student_classe.stud_id from student_classe
                    WHERE student_classe.classe_id = $classe_id)"
            );

            foreach ($marks as $mark) {
                $student_id = $mark->stud_id;

                $ref1 = StudentSubject::where("stud_id", $student_id)
                    ->where("subject_id", $subject_id_to)
                    ->where("sy_id", $sy_id)
                    ->where(function ($query) use ($sequence1, $sequence2) {
                        $query->where("sequence", $sequence1)
                            ->orwhere("sequence", $sequence2);
                    })
                    ->first();

                $seq = 1;
                if ($mark->sequence == $sequence1) {
                    $seq = $sequence1;
                } else {
                    $seq = $sequence2;
                }
                $ref1 = StudentSubject::where("stud_id", $student_id)
                    ->where("subject_id", $subject_id_to)
                    ->where("sy_id", $sy_id)
                    ->where("sequence", $seq)
                    ->first();

                if (!is_null($ref1)) {
                    $ref1->mark = $mark->mark;
                    $ref1->isEmpty = $mark->isEmpty;
                    $ref1->update();
                    //echo $ref1->mark."<br/>";
                } else {
                    try {
                        $ref2 = new StudentSubject();
                        $ref2->stud_id = $student_id;
                        $ref2->subject_id = $subject_id_to;
                        $ref2->sy_id = $sy_id;
                        $ref2->sequence = $seq;
                        $ref2->mark = $mark->mark;
                        $ref2->isEmpty = $mark->isEmpty;
                        $ref2->save();
                    } catch (Exception $e) {
                        echo '<br/>ERROR: ' . $e->getMessage();
                        return 0; //ERROR OCCURS
                    }
                }
            }

            return 1;
        } catch (Exception $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            return 0; //ERROR OCCURS
        }
    }

    public function copyCompMarks(Request $request)
    {
        $connection = $request->input("connection");
        $year = $request->input("year");
        $classe_id = $request->input("classe_id");
        $subject_id = $request->input("subject_id");
        $term_id = $request->input("term_id");
        $subject_competence_id_from = $request->input("subject_competence_id_from");
        $subject_competence_id_to = $request->input("subject_competence_id_to");


        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            $marks = DB::select(
                "SELECT `stud_id`, `mark`, `isEmpty` FROM `stud_comp_mark` 
                WHERE stud_comp_mark.`sy_id` = $sy_id
                AND `term_id` = $term_id
                AND `subject_id` = $subject_id
                AND `subject_competence_id` = $subject_competence_id_from
                AND stud_comp_mark.stud_id IN(SELECT student_classe.stud_id FROM student_classe
                WHERE student_classe.classe_id = $classe_id)"
            );
            //echo(count($marks)."<br/>");
            //echo "CompFrom: $subject_competence_id_from <br/>";
            //echo "CompTo: $subject_competence_id_to <br/>";
            $countMarks = 1;
            foreach ($marks as $mark) {
                $mk = $mark->mark;
                $isEmpty = $mark->isEmpty;
                //echo "$countMarks: $mk<br/>"; $countMarks++;
                $student_id = $mark->stud_id;
                $ref1 = StudCompMark::where("stud_id", $student_id)
                    ->where("term_id", $term_id)
                    ->where("subject_id", $subject_id)
                    ->where("subject_competence_id", $subject_competence_id_to)
                    ->first();
                if (!is_null($ref1)) {
                    //echo "  --> ref1[".($countMarks-1)."] exists<br/>";
                    $ref1->mark = $mk;
                    $ref1->isEmpty = $isEmpty;
                    $ref1->update();
                    //echo $ref1->mark."<br/>";
                } else {
                    try {
                        $ref2 = new StudCompMark();
                        $ref2->stud_id = $student_id;
                        $ref2->subject_id = $subject_id;
                        $ref2->subject_competence_id = $subject_competence_id_to;
                        $ref2->term_id = $term_id;
                        $ref2->mark = $mark->mark;
                        $ref2->isEmpty = $mark->isEmpty;
                        $ref2->sy_id = $sy_id;
                        $ref2->save();
                    } catch (Exception $e) {
                        echo '<br/>ERROR: ' . $e->getMessage();
                        return 0; //ERROR OCCURS
                    }
                }
            }

            return 1;
        } catch (Exception $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            return 0; //ERROR OCCURS
        }
    }

    public function copyCompMarks2(Request $request)
    {
        $connection = $request->input("connection");
        $year = $request->input("year");
        $classe_id = $request->input("classe_id");
        $subject_id_from = $request->input("subject_id_from");
        $subject_id_to = $request->input("subject_id_to");
        $term_id = $request->input("term_id");


        config(["database.default" => $connection]);
        //echo "subject_id_from: $subject_id_from -- subject_id_to: $subject_id_to -- classe_id: $classe_id \n";

        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            $competencesFrom = MySubjectHelper::fetchCompetence($sy_id, $classe_id, $subject_id_from, $term_id);
            $competencesTo = MySubjectHelper::fetchCompetence($sy_id, $classe_id, $subject_id_to, $term_id);
            $compIdsFrom = [];
            $compIdsTo = [];

            $k = 0;
            if (!is_null($competencesFrom)) {
                foreach ($competencesFrom as $comp) {
                    //echo $comp->competence_text . "<br>";
                    $compIdsFrom[$k] = $comp->subject_competence_id;
                    $k++;
                }
            }
            //echo "<br/>";
            $k = 0;
            if (!is_null($competencesTo)) {
                foreach ($competencesTo as $comp) {
                    //echo $comp->competence_text . "<br>";
                    $compIdsTo[$k] = $comp->subject_competence_id;
                    $k++;
                }
            }

            $n = count($compIdsFrom);
            $m = count($compIdsTo);
            if ($n == 0 || $m == 0) {
                echo "-1"; //Competences of one of the subjects not found
            } else {

                //REAL PROCESSING STARTS HERE
                for ($i = 0; $i < $n; $i++) {
                    try {
                        $comp_id_from = $compIdsFrom[$i];
                        $marks = DB::select(
                            "SELECT `stud_id`, `mark`, `isEmpty` FROM `stud_comp_mark` 
                            WHERE stud_comp_mark.`sy_id` = $sy_id
                            AND `term_id` = $term_id
                            AND `subject_id` = $subject_id_from  
                            AND `subject_competence_id` = $comp_id_from              
                            AND stud_comp_mark.stud_id IN(SELECT student_classe.stud_id FROM student_classe
                            WHERE student_classe.classe_id = $classe_id)"
                        );
                        if (!is_null($marks)) {
                            $comp_id_to = $compIdsTo[$i];
                            foreach ($marks as $mark) {
                                $student_id = $mark->stud_id;
                                $ref1 = StudCompMark::where("stud_id", $student_id)
                                    ->where("term_id", $term_id)
                                    ->where("subject_id", $subject_id_to)
                                    ->where("subject_competence_id", $comp_id_to)
                                    ->first();
                                if (!is_null($ref1)) {
                                    $ref1->mark = $mark->mark;
                                    $ref1->update();
                                    //echo $ref1->mark."<br/>";
                                } else {
                                    try {
                                        $ref2 = new StudCompMark();
                                        $ref2->stud_id = $student_id;
                                        $ref2->subject_id = $subject_id_to;
                                        $ref2->subject_competence_id = $comp_id_to;
                                        $ref2->term_id = $term_id;
                                        $ref2->mark = $mark->mark;
                                        $ref2->isEmpty = $mark->isEmpty;
                                        $ref2->sy_id = $sy_id;
                                        $ref2->save();
                                    } catch (Exception $e) {
                                        echo '<br/>ERROR OCCURS: ' . $e->getMessage();
                                        return 0; //ERROR OCCURS
                                    }
                                }
                            }
                        }
                    } catch (Exception $e1) {
                        //echo "". $e1->getMessage() ."<br/>";
                        //Undefined array key, this is not a problem, since we simplified the process of checking the if subjectFrom has the same number of competences like subjectTo
                    }
                } //END MAIN FOR
            }

            return 1;
        } catch (Exception $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            return 0; //ERROR OCCURS
        }
    }


    public function getCompMarks(Request $request)
    {
        $connection = $request->input("connection");
        $year = $request->input("year");
        $classe_id = $request->input("classe_id");
        $subject_id = $request->input("subject_id");
        $term_id = $request->input("term_id");
        $subject_competence_id = $request->input("subject_competence_id");


        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            $marks = DB::select(
                "SELECT `stud_id`, `mark`, `isEmpty` FROM `stud_comp_mark` 
                WHERE stud_comp_mark.`sy_id` = $sy_id
                AND `term_id` = $term_id
                AND `subject_id` = $subject_id
                AND `subject_competence_id` = $subject_competence_id
                AND stud_comp_mark.stud_id IN(SELECT student_classe.stud_id FROM student_classe
                WHERE student_classe.classe_id = $classe_id)"
            );
            return response()->json($marks, 200);
        } catch (Exception $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }

    public function getCompMarks2(Request $request)
    {
        $connection = $request->input("connection");
        $year = $request->input("year");
        $classe_id = $request->input("classe_id");
        $subject_id = $request->input("subject_id");
        $term_id = $request->input("term_id");

        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            $marks = DB::select(
                "SELECT `stud_id`, `mark`, `isEmpty` FROM `stud_comp_mark` 
                WHERE stud_comp_mark.`sy_id` = $sy_id
                AND `term_id` = $term_id
                AND `subject_id` = $subject_id
                AND stud_comp_mark.stud_id IN(SELECT student_classe.stud_id FROM student_classe
                WHERE student_classe.classe_id = $classe_id)"
            );
            return response()->json($marks, 200);
        } catch (Exception $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }


    public function saveManyStudentsWithPOST(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $year = $request->input("year");
        $override = $request->input("override");
        $classe_id = $request->input("classe_id");

        $stList = json_decode($data, true);
        //$n = count($fList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]";
        $allAffected = 1; //interpreted as true. 0-->false  
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);

        if ($override == "1") {
            //DELETE ALL STUDENTS OF THE CLASSE
            $students = DB::select("SELECT* FROM student WHERE student.stud_id 
                IN(SELECT student_classe.stud_id FROM student_classe WHERE 
                    student_classe.sy_id = $sy_id AND student_classe.classe_id = $classe_id)
                    ORDER BY student.name");
            foreach ($students as $student) {
                $id = $student->stud_id; //$student["stud_id"] NOT WORK;
                //$name = $student->name; //$students["name"]; //NOT WORK
                //echo "$k --> $stud_id   $name <br/>";
                $val = MyHelper::deleteAStudent($sy_id, $id);
            }
        }

        foreach ($stList as $st) {
            $name = $st["name"];
            if ($name == "null") {
                $name = "";
            }

            $surname = $st["surname"];
            if ($surname == "null") {
                $surname = "";
            }

            $bday = $st["bday"];
            if ($bday == "null") {
                $bday = "";
            }

            $bplace = $st["bplace"];
            if ($bplace == "null") {
                $bplace = "";
            }

            $sexe = $st["sexe"];
            if ($sexe == "null") {
                $sexe = "M";
            }

            $red = $st["repeating"];
            if ($red == "null") {
                $red = "0";
            }

            $handicape = $st["handicape"];
            if ($handicape == "null") {
                $handicape = "0";
            }

            $cas_social = $st["cas_social"];
            if ($cas_social == "null") {
                $cas_social = "0";
            }

            $matricule = $st["matricule"];
            if ($matricule == "") {
                $matricule = null; //MATRICULE CAN BE NULL
            }

            $stud = new Student();
            $stud->name = $name;
            $stud->surname = $surname;
            $stud->bday = $bday;
            $stud->bplace = $bplace;
            $stud->sexe = $sexe;
            $stud->matricule = $matricule;
            $stud->handicape = $handicape;
            try {
                //BEFORE SAVING STUDENT, LET'S MAKE SURE NO OTHER STUDENT HAS THE SAME MATRICULE
                $studTmp = Student::all()
                    ->where("matricule", $matricule)
                    ->first();
                if (!is_null($studTmp)) { //au moins un élève porte ce matricule et c'est une erreur fatale
                    $stud->matricule  = null; //to avoid duplicate
                }

                $stud->save();
                $sc = new StudentClasse();
                $sc->stud_id = $stud->stud_id;
                $sc->sy_id = $sy_id;
                $sc->repeating = $red;
                $sc->cas_social = $cas_social;
                $sc->classe_id = $classe_id;
                //echo "\$sy_id = $sy_id | \$staff_id = $staff->staff_id";
                try {
                    $sc->save();
                } catch (Exception $e3) {
                    echo "<br/>" . $e3->getMessage() . "<br/>";
                    echo "-4"; //failed to save student_classe;
                    $allAffected = 0;
                    try {
                        $sc->save();
                    } catch (Exception $ex) { //DO NOTHING
                        echo "<br/>\$ex" . $e3->getMessage() . "<br/>";
                        $allAffected = 0;
                    }
                }
            } catch (Exception $e2) {
                echo "<br/>" . $e2->getMessage() . "<br/>";
                echo "-3"; //failed to save student;
                $allAffected = 0;
            }
        } //END FOR
        echo "$allAffected"; //1--> All student successfully saved; < 0--> Failed to save at least one
    }

    public function saveAStudent(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $year = $request->input("year");
        $classe_id = $request->input("classe_id");
        $surname = $request->input("surname");
        $name = $request->input("name");
        $parent_phone = $request->input("parent_phone"); //Will be used later to process the student parent from phone 
        $bday = $request->input("bday");
        $bplace = $request->input("bplace");
        $sexe = $request->input("sexe");
        $repeating = $request->input("repeating");
        $matricule = $request->input("matricule");
        $handicape = $request->input("handicape");
        $cas_social = $request->input("cas_social");

        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        $allAffected = 1; //interpreted as true. 0-->false  

        $stud = new Student();
        $stud->name = $name;
        $stud->surname = $surname;
        $stud->bday = $bday;
        $stud->bplace = $bplace;
        $stud->sexe = $sexe;
        $stud->matricule = $matricule;
        $stud->handicape = $handicape;
        $stud->handicape = $cas_social;
        try {
            //BEFORE SAVING STUDENT, LET'S MAKE SURE NO OTHER STUDENT HAS THE SAME MATRICULE
            $studTmp = Student::all()
                ->where("matricule", $matricule)
                ->first();
            if (!is_null($studTmp)) { //au moins un élève porte ce matricule et c'est une erreur fatale
                $stud->matricule  = null; //to avoid duplicate
            }

            $stud->save();
            $sc = new StudentClasse();
            $sc->stud_id = $stud->stud_id;
            $sc->sy_id = $sy_id;
            $sc->repeating = $repeating;
            $sc->cas_social = $cas_social;
            $sc->classe_id = $classe_id;
            //We shall process student's parent later
            try {
                $sc->save();
            } catch (Exception $e3) {
                echo "<br/>" . $e3->getMessage() . "<br/>";
                echo "-4"; //failed to save student_classe;
                $allAffected = 0;
                try {
                    $stud->delete(); //WE DELETE STUDENT IN THAT CASE
                } catch (Exception $ex) { //DO NOTHING
                    echo "<br/>\$ex" . $e3->getMessage() . "<br/>";
                    $allAffected = 0;
                }
            }
        } catch (Exception $e2) {
            echo "<br/>" . $e2->getMessage() . "<br/>";
            echo "-3"; //failed to save student;
            $allAffected = 0;
        }

        echo "$allAffected"; //1--> All student successfully saved; < 0--> Failed to save at least one
    }
    public function saveManyStudents(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $year = $request->input("year");
        $override = $request->input("override");
        $classe_id = $request->input("classe_id");

        $stList = json_decode($data, true);
        //$n = count($fList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]";
        $allAffected = 1; //interpreted as true. 0-->false  
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);

        if ($override == "1") {
            //DELETE ALL STUDENTS OF THE CLASSE
            $students = DB::select("SELECT* FROM student WHERE student.stud_id 
                IN(SELECT student_classe.stud_id FROM student_classe WHERE 
                    student_classe.sy_id = $sy_id AND student_classe.classe_id = $classe_id)
                    ORDER BY student.name");
            foreach ($students as $student) {
                $id = $student->stud_id; //$student["stud_id"] NOT WORK;
                //$name = $student->name; //$students["name"]; //NOT WORK
                //echo "$k --> $stud_id   $name <br/>";
                $val = MyHelper::deleteAStudent($sy_id, $id);
            }

            /*THIS CODE DELETES INTEAD ALL STUDENTS OF SCHOOL
            $allStud = Student::all();
            if (!is_null($allStud)) {
                foreach ($allStud as $st) {
                    $id = $st->stud_id;
                    $val = MyHelper::deleteAStudent($sy_id, $id);
                }
            }*/
        }

        foreach ($stList as $st) {
            $name = $st["name"];
            if ($name == "null") {
                $name = "";
            }

            $bday = $st["bday"];
            if ($bday == "null") {
                $bday = "";
            }

            $bplace = $st["bplace"];
            if ($bplace == "null") {
                $bplace = "";
            }

            $sexe = $st["sexe"];
            if ($sexe == "null") {
                $sexe = "M";
            }

            $red = $st["repeating"];
            if ($red == "null") {
                $red = "0";
            }

            $handicape = $st["handicape"];
            if ($handicape == "null") {
                $handicape = "0";
            }

            $cas_social = $st["cas_social"];
            if ($cas_social == "null") {
                $cas_social = "0";
            }

            $matricule = $st["matricule"];
            if ($matricule == "") {
                $matricule = null; //MATRICULE CAN BE NULL
            }

            //echo "Processing ".$name."[".$matricule."]";

            $stud = new Student();
            $stud->name = $name;
            $stud->bday = $bday;
            $stud->bplace = $bplace;
            $stud->sexe = $sexe;
            $stud->matricule = $matricule;
            $stud->handicape = $handicape;
            try {
                //BEFORE SAVING STUDENT, LET'S MAKE SURE NO OTHER STUDENT HAS THE SAME MATRICULE
                $id = $stud->stud_id;
                $studTmp = Student::all()
                    ->where("matricule", $matricule)
                    ->first();
                if (!is_null($studTmp)) { //au moins un élève porte ce matricule  
                    //ON NE CREE PAS  L'ELEVE CAr il existe deja
                    //echo "Student found ";
                    $id = $studTmp->stud_id;
                } else {
                    $stud->save(); //ON CREE  L'ELEVE CAr il est nouveau
                    $id = $stud->stud_id;
                }

                $sc = new StudentClasse();
                $sc->stud_id = $id;
                $sc->sy_id = $sy_id;
                $sc->repeating = $red;
                $sc->cas_social = $cas_social;
                $sc->classe_id = $classe_id;
                //echo "\$sy_id = $sy_id | \$staff_id = $staff->staff_id";
                try {
                    $sc->save();
                } catch (Exception $e3) {
                    echo "<br/>" . $e3->getMessage() . "<br/>";
                    echo "-4"; //failed to save student_classe;//IN THAT CASE WE DELETE THE STUDENT
                    $allAffected = 0;
                    try {
                        $stud->delete();
                    } catch (Exception $ex) { //DO NOTHING
                        echo "<br/>\$ex" . $e3->getMessage() . "<br/>";
                        $allAffected = 0;
                    }
                }
            } catch (Exception $e2) {
                echo "<br/>" . $e2->getMessage() . "<br/>";
                echo "-3"; //failed to save student;
                $allAffected = 0;
            }
        } //END FOR
        echo "$allAffected"; //1--> All student successfully saved; < 0--> Failed to save at least one
    }

    public function updateManyStudents(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $year = $request->input("year");
        $classe_id = $request->input("classe_id"); //was not used because, a student can only be one classe a given school year

        $stList = json_decode($data, true);
        //$n = count($fList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]";

        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        $allAffected = 1; //interpreted as true. 0-->false  

        foreach ($stList as $st) {
            $stud_id = $st["stud_id"];
            $name = $st["name"];
            if ($name == "null") {
                $name = "";
            }

            $surname = $st["surname"];
            if ($surname == "null") {
                $surname = "";
            }

            $bday = $st["bday"];
            if ($bday == "null") {
                $bday = "";
            }

            $bplace = $st["bplace"];
            if ($bplace == "null") {
                $bplace = "";
            }

            $sexe = $st["sexe"];
            if ($sexe == "null") {
                $sexe = "M";
            }

            $red = $st["repeating"];
            if ($red == "null") {
                $red = "0";
            }

            $handicape = $st["handicape"];
            if ($handicape == "null") {
                $handicape = "0";
            }

            $cas_social = $st["cas_social"];
            if ($cas_social == "null") {
                $cas_social = "0";
            }

            $matricule = $st["matricule"];
            if ($matricule == "") {
                $matricule = null; //MATRICULE CAN BE NULL
            }

            $stud = Student::find($stud_id);
            $stud->name = $name;
            $stud->surname = $surname;
            $stud->bday = $bday;
            $stud->bplace = $bplace;
            $stud->sexe = $sexe;
            $stud->handicape = $handicape;
            try {
                $stud->update(); //if no error then we update the sc as well

                $sc = StudentClasse::where("stud_id", "=", $stud_id)
                    ->where("sy_id", "=", $sy_id)
                    ->first();
                $sc->repeating = $red;
                $sc->cas_social = $cas_social;
                try {
                    $sc->update();
                } catch (Exception $e3) {
                    echo "<br/>" . $e3->getMessage() . "<br/>";
                    echo "-4"; //failed to update student_classe;//IN THAT CASE WE DELETE THE STUDENT
                    $allAffected = 0;
                }
                $stud->refresh();
                $stud->matricule = $matricule;
                try {
                    $stud->update();
                } catch (Exception $e32) {
                    echo "<br/>" . $e32->getMessage() . "<br/>";
                    //The matricule exists already
                    $allAffected = 0; //USER SHOULD BE AWARE THAT OPERATION HAS NOT BEEN DONE COMPLETELY
                }
            } catch (Exception $e2) {
                echo "<br/>" . $e2->getMessage() . "<br/>";
                echo "-3"; //failed to update student;
                $allAffected = 0;
            }
        } //END FOR
        echo "$allAffected"; //1--> All student successfully updated; < 0--> Failed to update at least one
    }

    public function deleteManyStudents(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $year  = $request->input("year");
        $data = $request->input("data");
        $data_size = $request->input("data_size");

        $studList = json_decode($data, true);
        $n = count($studList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]";
        $allAffected = 1; //interpreted as true. 0-->false

        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        //echo ($sy_id);

        foreach ($studList as $st) {
            $stud_id = $st["stud_id"];
            $res = MyHelper::deleteAStudent($sy_id, $stud_id);
            if ($res < 0) {
                //echo "res".$res;
                $allAffected = 0;
            }
        } //END FOR
        //return response($allAffected, 200);
        echo (string) $allAffected; //1--> All groupes successfully deleted; 0--> Failed to save at least one
    }

    public function deleteManyStudentsWithPOST(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $year  = $request->input("year");
        $data = $request->input("data");
        $data_size = $request->input("data_size");

        $studList = json_decode($data, true);
        $n = count($studList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]";
        $allAffected = 1; //interpreted as true. 0-->false

        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        //echo ($sy_id);

        foreach ($studList as $st) {
            $stud_id = $st["stud_id"];
            $res = MyHelper::deleteAStudent($sy_id, $stud_id);
            if ($res < 0) {
                //echo "res".$res;
                $allAffected = 0;
            }
        } //END FOR
        //return response($allAffected, 200);
        echo (string) $allAffected; //1--> All groupes successfully deleted; 0--> Failed to save at least one
    }
    public function allStudentsOfClasse(Request $request)
    {   //THE CLASSE IS ASSUME TO BE A CLASSE OF THE CURRENT SECTION
        $connection = $request->input("connection");
        $year = $request->input("year");
        $classe_id = $request->input("classe_id");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            $students = DB::select(
                "SELECT student.stud_id, student.matricule, student.name, student.surname, student.bday, student.bplace, 
                    student.sexe, student.handicape, student.position, 0 as repeating, 0 as  cas_social
                    FROM student WHERE student.stud_id 
                        IN(SELECT student_classe.stud_id from student_classe
                        WHERE student_classe.sy_id = $sy_id 
                        AND student_classe.classe_id = $classe_id)
                        Order by student.name"
            );
            return response()->json($students, 200);
        } catch (Exception $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }

    public function allStudentsOfClasse2(Request $request)
    {   //THE CLASSE IS ASSUME TO BE A CLASSE OF THE CURRENT SECTION
        $connection = $request->input("connection");
        $year = $request->input("year");
        $classe_id = $request->input("classe_id");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            $students = DB::select(
                "SELECT stud_id, matricule, name, surname, bday, bplace, 
                    sexe, handicape, position, 0 as repeating, 0 as  cas_social, '' as mark
                    FROM student WHERE stud_id 
                        IN(SELECT student_classe.stud_id from student_classe
                        WHERE student_classe.sy_id = $sy_id 
                        AND student_classe.classe_id = $classe_id)
                        Order by student.name"
            );
            return response()->json($students, 200);
        } catch (Exception $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }

    public function allStudClassOfAClasse(Request $request)
    {   //THE CLASSE IS ASSUME TO BE A CLASSE OF THE CURRENT SECTION
        $connection = $request->input("connection");
        $year = $request->input("year");
        $classe_id = $request->input("classe_id");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            $students = DB::select(
                "SELECT `student_classe_id`, `stud_id`, `basculated`, position_classe, 
                        `repeating`, `solvable1`,`solvable2`, `cas_social`,`abandon` FROM student_classe 
                        WHERE sy_id = $sy_id AND classe_id = $classe_id"
            );
            return response()->json($students, 200);
        } catch (Exception $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }

    public function allStudClassOfYear(Request $request)
    {   //THE CLASSE IS ASSUME TO BE A CLASSE OF THE CURRENT SECTION
        $connection = $request->input("connection");
        $year = $request->input("year");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            $students = DB::select(
                "SELECT `student_classe_id`, `stud_id`, `basculated`, position_classe, 
                        `repeating`, `solvable1`,`solvable2`, `cas_social`,`abandon` FROM student_classe 
                        WHERE sy_id = $sy_id"
            );
            return response()->json($students, 200);
        } catch (Exception $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }

    public function allStudents(Request $request)
    {   //THE CLASSE IS ASSUME TO BE A CLASSE OF THE CURRENT SECTION
        $connection = $request->input("connection");
        $year = $request->input("year");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            $students = DB::select(
                "SELECT stud_id, matricule, name, surname, bday, bplace, 
                    sexe, handicape, position, 0 as repeating, 0 as  cas_social
                    FROM student WHERE stud_id 
                        IN(SELECT student_classe.stud_id from student_classe
                        WHERE student_classe.sy_id = $sy_id)"
            );
            return response()->json($students, 200);
        } catch (Exception $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Student $student)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Student $student)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student)
    {
        //
    }
}
