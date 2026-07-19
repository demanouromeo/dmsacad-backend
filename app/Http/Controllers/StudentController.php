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
        try {
            $request->validate([
                'connection' => 'required|string',
                'stud_id' => 'required|integer|min:1',
                'father' => 'required|string',
                'mother' => 'required|string',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

        $connection = $request->input("connection");
        $stud_id = $request->input("stud_id");
        $father = $request->input("father");
        $mother = $request->input("mother");
        config(["database.default" => $connection]);

        try {
            //UPDATE student SET st1 = "father1", str2 = "mother1" WHERE stud_id = 5776
            //"Student[$stud_id] -- Father[$father]  --  mother[$mother]<br/>";
            //DB::select("UPDATE student SET st1 = $father, str2 = $mother WHERE stud_id = $stud_id");
            DB::select("UPDATE student SET st1 = '$father', str2 = '$mother' WHERE stud_id = $stud_id");
            //DB::select("UPDATE student SET st1 = '', str2 = '' WHERE 1");
            return response()->json([
                'status' => true,
                'message' => 'Father and mother information updated successfully.',
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update student father and mother information: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateSolvable(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'data' => 'required|string',
                'data_size' => 'nullable|integer|min:0',
                'year' => 'required|string',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $year = $request->input("year");

        $studList = json_decode($data, true);
        $n = count($studList);
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);

        $msg = "";
        $count  = 1;
        $allAffected = 1; //interpreted as true. 0-->false  
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
            } catch (\Throwable $e2) {
                $msg .= "<br/>" . $e2->getMessage() . "<br/>";
                $allAffected = 0;
            }
        } //END FOR
        // "$allAffected"; //1--> All successfully saved/updated; < 0--> Failed for least one
        return response()->json([
            'status' => $allAffected === 1,
            'message' => $allAffected === 1 ? 'All solvable records updated successfully.' : 'Failed to update some solvable records.',
            'error_details' => $msg
        ], $allAffected === 1 ? 200 : 500);
    }


    public function updateDismiss(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'stud_id' => 'required|integer|min:1',
                'classe_id' => 'required|integer|min:1',
                'dismiss_val' => 'required|integer|min:0|max:1', //is boolean value 0 or 1
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

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
            return response()->json([
                'status' => true,
                'message' => 'Dismissal status updated successfully.',
            ], 200);
        } catch (\Throwable $e) {
            //echo '<br/>Message: ' .$e->getMessage();
            return response()->json([
                'status' => false,
                'message' => 'Failed to update dismissal status: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function addStudentToRepeatList(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'stud_id' => 'required|integer|min:1',
                'classe_id' => 'required|integer|min:1',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

        $connection = $request->input("connection");
        $year = $request->input("year");
        $stud_id = $request->input("stud_id");
        $classe_id = $request->input("classe_id");

        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);


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
            return response()->json([
                'status' => true,
                'message' => 'Student added to repeat list successfully.',
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to add student to repeat list: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function removeStudentFromClass(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'stud_id' => 'required|integer|min:1',
                'classe_id' => 'required|integer|min:1',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

        $connection = $request->input("connection");
        $year = $request->input("year"); //Will be next year if called by basculement
        $stud_id = $request->input("stud_id");
        $classe_id = $request->input("classe_id");

        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);

        try {
            $x = DB::select("DELETE FROM student_classe WHERE student_classe.sy_id = $sy_id
                        AND student_classe.stud_id = $stud_id AND student_classe.classe_id = $classe_id");
            return response()->json([
                'status' => true,
                'message' => 'Student removed from class successfully.',
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to remove student from class: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function addStudentToClass(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'stud_id' => 'required|integer|min:1',
                'classe_id_new' => 'required|integer|min:1',
                'classe_id_old' => 'required|integer|min:1',
                'cas_social' => 'required|integer|min:0|max:1',
                'repeating' => 'required|integer|min:0|max:1', //repeating is boolean. value 0 or 1
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

        $connection = $request->input("connection");
        $year = $request->input("year"); //Will be next year if called by basculement
        $stud_id = $request->input("stud_id");
        $classe_id_new = $request->input("classe_id_new");
        $classe_id_old = $request->input("classe_id_old");
        $cas_social = $request->input("cas_social");
        $repeating = $request->input("repeating");

        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);


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
            return response()->json([
                'status' => true,
                'message' => 'Student added to new class successfully.',
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to add student to new class: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function resetPromotionInfo(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'classe_id' => 'required|integer|min:1',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

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
            return response()->json([
                'status' => true,
                'message' => 'Promotion information reset successfully for the class.',
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to reset promotion information: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function getAllDisciplines(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'term_id' => 'required|integer|min:1|max:3',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

        $connection = $request->input("connection");
        $year = $request->input("year");
        $term_id = $request->input("term_id");
        config(["database.default" => $connection]);

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
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getAllDisciplines2(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

        $connection = $request->input("connection");
        $year = $request->input("year");
        config(["database.default" => $connection]);

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
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error occurred: ' . $e->getMessage(),
            ], 500); //ERROR OCCURS
        }
    }

    public function allStudentCompMark(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

        $connection = $request->input("connection");
        $year = $request->input("year");
        config(["database.default" => $connection]);

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
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error occurred: ' . $e->getMessage(),
            ], 500); //ERROR OCCURS
        }
    }


    public function allStudentSubject(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

        $connection = $request->input("connection");
        $year = $request->input("year");
        config(["database.default" => $connection]);
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
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getDisciplineOfClasse(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'term_id' => 'required|integer|min:1|max:3',
                'classe_id' => 'required|integer|min:1',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }
        $connection = $request->input("connection");
        $year = $request->input("year");
        $term_id = $request->input("term_id");
        $classe_id = $request->input("classe_id");

        config(["database.default" => $connection]);

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
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error occurred: ' . $e->getMessage(),
            ], 500); //ERROR OCCURS
        }
    }

    public function saveOrUpdateABS(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'data' => 'required|string',
                'data_size' => 'nullable|integer|min:0',
                'year' => 'required|string',
                'term_id' => 'required|integer|min:1|max:3',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $year = $request->input("year");
        $term_id = $request->input("term_id");

        $absList = json_decode($data, true);
        $n = count($absList);
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);

        $msg = "";
        $count  = 1;
        $allAffected = 1; //interpreted as true. 0-->false  
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
                    //"Ref for $stud_id found <br/>";
                    $ref->absunjust = $abs["nbAbs"];
                    $ref->nb_jour_exclusion = $abs["exclusion"];
                    $ref->lateness = $abs["lateness"];
                    $ref->consigne = $abs["consigne"];
                    $ref->avertissement = $abs["avertissement"];
                    $ref->exclusion_definitive = $abs["dismissed"];
                    $ref->commentOnDiscipline = $abs["comment"];
                    $ref->update();
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
            } catch (\Throwable $e2) {
                $msg .= "<br/>" . $e2->getMessage() . "<br/>";
                $allAffected = 0;
            }
        } //END FOR
        //"$allAffected"; //1--> All successfully saved/updated; < 0--> Failed for least one
        return response()->json([
            'status' => $allAffected === 1,
            'message' => $allAffected === 1 ? 'All ABS saved/updated successfully.' : 'Failed to save/update some ABS.',
            'error_details' => $msg
        ], $allAffected === 1 ? 200 : 500);
    }

    public function allStudentsOfClasseForAbs(Request $request)
    {   //THE CLASSE IS ASSUME TO BE A CLASSE OF THE CURRENT SECTION
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'classe_id' => 'required|integer|min:1',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

        $connection = $request->input("connection");
        $year = $request->input("year");
        $classe_id = $request->input("classe_id");
        config(["database.default" => $connection]);

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
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error occurred: ' . $e->getMessage(),
            ], 500); //ERROR OCCURS
        }
    }

    public function allStudentCompMarkOfTerm(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'term_id' => 'required|integer|min:1|max:3',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

        $connection = $request->input("connection");
        $year = $request->input("year");
        $term_id = $request->input("term_id");
        config(["database.default" => $connection]);

        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $marks = DB::select(
                "SELECT `stud_comp_mark_id`, `term_id`, `subject_id`, 
                `subject_competence_id`, `stud_id`, `mark`, `isEmpty` 
                FROM `stud_comp_mark` WHERE `sy_id` = $sy_id AND term_id = $term_id"
            );
            return response()->json($marks, 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error occurred: ' . $e->getMessage(),
            ], 500); //ERROR OCCURS
        }
    }

    public function allStudentCompMarkOfTerm2(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'term_id' => 'required|integer|min:1|max:3',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

        $connection = $request->input("connection");
        $year = $request->input("year");
        $term_id = $request->input("term_id");
        config(["database.default" => $connection]);

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
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error occurred: ' . $e->getMessage(),
            ], 500); //ERROR OCCURS
        }
    }

    public function allStudentSubjectOfTerm(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'sequence1' => 'required|integer|min:1|max:6',
                'sequence2' => 'required|integer|min:1|max:6',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

        $connection = $request->input("connection");
        $year = $request->input("year");
        $sequence1 = $request->input("sequence1");
        $sequence2 = $request->input("sequence2");
        config(["database.default" => $connection]);

        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $marks = DB::select(
                "SELECT `student_subject_id`, `stud_id`, `subject_id`, 
                `sequence`, `mark`, `isEmpty`  FROM `student_subject` 
                WHERE `sy_id` = $sy_id
                AND (`sequence` = $sequence1 OR `sequence` = $sequence2)"
            );
            return response()->json($marks, 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error occurred: ' . $e->getMessage(),
            ], 500); //ERROR OCCURS
        }
    }

    public function allStudentSubjectOfTerm2(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'sequence1' => 'required|integer|min:1|max:6',
                'sequence2' => 'required|integer|min:1|max:6',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

        $connection = $request->input("connection");
        $year = $request->input("year");
        $sequence1 = $request->input("sequence1");
        $sequence2 = $request->input("sequence2");
        config(["database.default" => $connection]);

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
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error occurred: ' . $e->getMessage(),
            ], 500); //ERROR OCCURS
        }
    }

    public function allStudentsOfClasse3(Request $request)
    {   //THE CLASSE IS ASSUME TO BE A CLASSE OF THE CURRENT SECTION
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

        $connection = $request->input("connection");
        $year = $request->input("year");
        config(["database.default" => $connection]);

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
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error occurred: ' . $e->getMessage(),
            ], 500); //ERROR OCCURS
        }
    }


    public function allStudentsOfClasseOfSchool(Request $request)
    {   //THE CLASSE IS ASSUME TO BE A CLASSE OF THE CURRENT SECTION
        try {
            $request->validate([
                'connection' => 'required|string',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

        $connection = $request->input("connection");
        config(["database.default" => $connection]);


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
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error occurred: ' . $e->getMessage(),
            ], 500); //ERROR OCCURS
        }
    }


    public function allStudentsOfClasseOfSection(Request $request)
    {   //THE CLASSE IS ASSUME TO BE A CLASSE OF THE CURRENT SECTION
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'section_id' => 'required|integer|min:1',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

        $connection = $request->input("connection");
        $section_id = $request->input("section_id");
        $year = $request->input("year");
        config(["database.default" => $connection]);

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
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error occurred: ' . $e->getMessage(),
            ], 500); //ERROR OCCURS
        }
    }

    public function updatePromotionInfo(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'data' => 'required|string',
                'data_size' => 'nullable|integer|min:0',
                'year' => 'required|string',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $year = $request->input("year");

        $stList = json_decode($data, true);
        $n = count($stList);
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);

        $msg = "";
        $count  = 1;
        $allAffected = 1; //interpreted as true. 0-->false  
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
            } catch (\Throwable $e) {
                $msg .= "<br/>" . $e->getMessage();
                $allAffected = 0;
            }
        } //END FOR
        return response()->json([
            'status' => $allAffected === 1,
            'message' => $allAffected === 1 ? 'All Promotion Information updated successfully.' : 'Failed to update some Promotion Information.',
            'error_details' => $msg
        ], $allAffected === 1 ? 200 : 500);
    }


    public function allStudentsForMarks(Request $request)
    {   //THE CLASSE IS ASSUME TO BE A CLASSE OF THE CURRENT SECTION
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

        $connection = $request->input("connection");
        $year = $request->input("year");
        config(["database.default" => $connection]);

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
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error occurred: ' . $e->getMessage(),
            ], 500); //ERROR OCCURS
        }
    }

    public function getAllCompMarksSimple(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'term_id' => 'required|integer|min:1|max:3',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

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
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error occurred: ' . $e->getMessage(),
            ], 500); //ERROR OCCURS
        }
    }

    public function getAllSeqMarksSimple(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'sequence1' => 'required|integer|min:1|max:6',
                'sequence2' => 'required|integer|min:1|max:6',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

        $connection = $request->input("connection");
        $year = $request->input("year");
        $sequence1 = $request->input("sequence1");
        $sequence2 = $request->input("sequence2");
        config(["database.default" => $connection]);

        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            $marks = DB::select(
                "SELECT `stud_id`, `mark`, isEmpty FROM `student_subject` WHERE `sy_id` = $sy_id
                    AND (`sequence` = $sequence1 OR `sequence` = $sequence2)"
            );
            return response()->json($marks, 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error occurred: ' . $e->getMessage(),
            ], 500); //ERROR OCCURS
        }
    }

    public function saveCompMarks(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'data' => 'required|string',
                'data_size' => 'nullable|integer|min:0',
                'year' => 'required|string',
                'subject_id' => 'required|integer|min:1',
                'term_id' => 'required|integer|min:1|max:3',
                'subject_competence_id' => 'required|integer|min:1',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $year = $request->input("year");
        $subject_id = $request->input("subject_id");
        $term_id = $request->input("term_id");
        $subject_competence_id = $request->input("subject_competence_id");

        $stCompList = json_decode($data, true);
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);

        $msg = "";
        $count  = 1;
        $allAffected = 1; //interpreted as true. 0-->false 
        foreach ($stCompList as $comp) {
            $count++;
            $stud_id = $comp["stud_id"]; //Stud_id shouldn't be null here if it is the case ==> Fatal ERROR
            $mark = $comp["mark"]; //if mark is not double here operation shall fail
            $isEmpty = $comp["isEmpty"];
            try {

                $tmp = DB::select("SELECT*FROM stud_comp_mark WHERE sy_id = $sy_id 
                        AND subject_id = $subject_id 
                        AND term_id = $term_id AND stud_id = $stud_id 
                        AND subject_competence_id  = $subject_competence_id");

                if (count($tmp) > 0) {
                    if ($isEmpty == 1) {
                        $mark = 0;
                    }

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
            } catch (\Throwable $e2) {
                $msg .= "<br/>" . $e2->getMessage() . "<br/>";
                $allAffected = 0;
            }
        } //END FOR

        //"$allAffected"; //1--> All successfully saved/updated; < 0--> Failed for least one
        return response()->json([
            'status' => $allAffected === 1,
            'message' => $allAffected === 1 ? 'All compMarks saved/updated successfully.' : 'Failed to save/update some compMarks.',
            'error_details' => $msg
        ], $allAffected === 1 ? 200 : 500);
    }

    public function saveSeqMarks(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'data' => 'required|string',
                'data_size' => 'nullable|integer|min:0',
                'year' => 'required|string',
                'subject_id' => 'required|integer|min:1',
                'sequence' => 'required|integer|min:1|max:6',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $year = $request->input("year");
        $subject_id = $request->input("subject_id");
        $sequence = $request->input("sequence");

        $stList = json_decode($data, true);
        $n = count($stList);
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);

        $msg = "";
        $count  = 1;
        $allAffected = 1; //interpreted as true. 0-->false  
        foreach ($stList as $st) {
            //echo"-----> Processing stud No.[$count]<br/>";
            $count++;
            $stud_id = $st["stud_id"]; //Stud_id shouldn't be null here if it is the case ==> Fatal ERROR
            $mark = $st["mark"]; //if mark is not double here operation shall fail
            $isEmpty = $st["isEmpty"];
            try {

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
                }
            } catch (\Throwable $e2) {
                $msg .= "<br/>" . $e2->getMessage() . "<br/>";
                $allAffected = 0;
            }
        } //END FOR
        //"$allAffected"; //1--> All successfully saved/updated; < 0--> Failed for least one
        return response()->json([
            'status' => $allAffected === 1,
            'message' => $allAffected === 1 ? 'All seqMarks saved/updated successfully.' : 'Failed to save/update some seqMarks.',
            'error_details' => $msg
        ], $allAffected === 1 ? 200 : 500);
    }


    public function uploadSeqMarks(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'data' => 'required|string',
                'data_size' => 'nullable|integer|min:1',
                'year' => 'required|string',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $year = $request->input("year");

        $stList = json_decode($data, true);
        $n = count($stList);
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);

        $msg = "";
        $count  = 1;
        $allAffected = 1; //interpreted as true. 0-->false 
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
            } catch (\Throwable $e2) {
                $msg .= "<br/>failed to save/update " . $e2->getMessage() . "<br/>";
                $allAffected = 0;
            }
        } //END FOR
        //"$allAffected"; //1--> All successfully saved/updated; < 0--> Failed for least one
        return response()->json([
            'status' => $allAffected === 1,
            'message' => $allAffected === 1 ? 'All seqMarks successfully uploaded.' : 'Failed to uploaded some seqMarks. Details: ' . $msg,
        ], $allAffected === 1 ? 200 : 500);
    }


    public function uploadCompMarks(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'data' => 'required|string',
                'data_size' => 'nullable|integer|min:1',
                'year' => 'required|string',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $year = $request->input("year");

        $stCompList = json_decode($data, true);
        $n = count($stCompList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]<br/>";        
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);

        $count  = 1;
        $msg = "";
        $allAffected = 1; //interpreted as true. 0-->false  
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
            } catch (\Throwable $e2) {
                $msg .= "<br/>" . $e2->getMessage() . "<br/>";
                $allAffected = 0;
            }
        } //END FOR
        return response()->json([
            'status' => $allAffected === 1,
            'message' => $allAffected === 1 ? 'All compMarks successfully uploaded.' : 'Failed to uploaded some compMarks. Details: ' . $msg,
        ], $allAffected === 1 ? 200 : 500);
    }


    public function getSeqMarks(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'classe_id' => 'required|integer|min:1',
                'subject_id' => 'required|integer|min:1',
                'sequence' => 'required|integer|min:1|max:6',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }
        $connection = $request->input("connection");
        $year = $request->input("year");
        $classe_id = $request->input("classe_id");
        $subject_id = $request->input("subject_id");
        $sequence = $request->input("sequence");
        config(["database.default" => $connection]);

        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            $marks = DB::select(
                "SELECT `stud_id`, `mark`, isEmpty FROM `student_subject` WHERE `sy_id` = $sy_id
                    AND `subject_id` = $subject_id AND `sequence` = $sequence
                    AND stud_id IN(SELECT student_classe.stud_id from student_classe
                    WHERE student_classe.classe_id = $classe_id AND student_classe.sy_id = $sy_id)"
            );
            return response()->json($marks, 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getSeqMarks2(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'classe_id' => 'required|integer|min:1',
                'subject_id' => 'required|integer|min:1',
                'seq1' => 'required|integer|min:1|max:6',
                'seq2' => 'required|integer|min:1|max:6',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }
        $connection = $request->input("connection");
        $year = $request->input("year");
        $classe_id = $request->input("classe_id");
        $subject_id = $request->input("subject_id");
        $sequence1 = $request->input("seq1");
        $sequence2 = $request->input("seq2");
        config(["database.default" => $connection]);

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
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error occurred: ' . $e->getMessage(),
            ], 500); //ERROR OCCURS
        }
    }

    public function copySeqMarks(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'classe_id' => 'required|integer|min:1',
                'subject_id' => 'required|integer|min:1',
                'sequence_from' => 'required|integer|min:1|max:6',
                'sequence_to' => 'required|integer|min:1|max:6',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }
        $connection = $request->input("connection");
        $year = $request->input("year");
        $classe_id = $request->input("classe_id");
        $subject_id = $request->input("subject_id");
        $sequence_from = $request->input("sequence_from");
        $sequence_to = $request->input("sequence_to");
        config(["database.default" => $connection]);

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
                    } catch (\Throwable $e) {
                        echo '<br/>ERROR: ' . $e->getMessage();
                        return 0; //ERROR OCCURS
                    }
                }
            }
            return response()->json([
                'status' => true,
                'message' => 'Marks copied successfully',
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error occurred: ' . $e->getMessage(),
            ], 500); //ERROR OCCURS
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
                    } catch (\Throwable $e) {
                        echo '<br/>ERROR: ' . $e->getMessage();
                        return 0; //ERROR OCCURS
                    }
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Marks copied successfully',
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error occurred: ' . $e->getMessage(),
            ], 500); //ERROR OCCURS
        }
    }

    public function copyCompMarks(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'classe_id' => 'required|integer|min:1',
                'subject_id' => 'required|integer|min:1',
                'term_id' => 'required|integer|min:1|max:3',
                'subject_competence_id_from' => 'required|integer|min:1',
                'subject_competence_id_to' => 'required|integer|min:1',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

        $connection = $request->input("connection");
        $year = $request->input("year");
        $classe_id = $request->input("classe_id");
        $subject_id = $request->input("subject_id");
        $term_id = $request->input("term_id");
        $subject_competence_id_from = $request->input("subject_competence_id_from");
        $subject_competence_id_to = $request->input("subject_competence_id_to");

        config(["database.default" => $connection]);

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
                    } catch (\Throwable $e) {
                        return response()->json([
                            'status' => false,
                            'message' => 'Error occurred while copying mark: ' . $e->getMessage(),
                        ], 500); //ERROR OCCURS
                    }
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Marks copied successfully',
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error occurred: ' . $e->getMessage(),
            ], 500); //ERROR OCCURS
        }
    }

    public function copyCompMarks2(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'classe_id' => 'required|integer|min:1',
                'subject_id_from' => 'required|integer|min:1',
                'subject_id_to' => 'required|integer|min:1',
                'term_id' => 'required|integer|min:1|max:3',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

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
                return response()->json([
                    'status' => false,
                    'message' => 'Competences of one of the subjects not found',
                ], 404);
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
                                    } catch (\Throwable $e) {
                                        return response()->json([
                                            'status' => false,
                                            'message' => 'Error occurred while copying mark: ' . $e->getMessage(),
                                        ], 500); //ERROR OCCURS
                                    }
                                }
                            }
                        }
                    } catch (\Throwable $e1) {
                        return response()->json([
                            'status' => false,
                            'message' => 'Error occurred while fetching marks: ' . $e1->getMessage(),
                        ], 500);
                        //Undefined array key, this is not a problem, since we simplified the process of checking the if subjectFrom has the same number of competences like subjectTo
                    }
                } //END MAIN FOR
            }

            return 1;
        } catch (\Throwable $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            return 0; //ERROR OCCURS
        }
    }


    public function getCompMarks(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'classe_id' => 'required|integer|min:1',
                'subject_id' => 'required|integer|min:1',
                'term_id' => 'required|integer|min:1|max:3',
                'subject_competence_id' => 'required|integer|min:1',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

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
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error occurred: ' . $e->getMessage(),
            ], 500); //ERROR OCCURS
        }
    }

    public function getCompMarks2(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'classe_id' => 'required|integer|min:1',
                'subject_id' => 'required|integer|min:1',
                'term_id' => 'required|integer|min:1|max:3',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }
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
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error occurred: ' . $e->getMessage(),
            ], 500); //ERROR OCCURS
        }
    }

    public function saveStudents(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'data' => 'required|string',
                'data_size' => 'nullable|integer|min:1',
                'year' => 'required|string',
                'override' => 'required|boolean',
                'classe_id' => 'required|integer|min:1',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $year = $request->input("year");
        $override = $request->input("override");
        $classe_id = $request->input("classe_id");

        $stList = json_decode($data, true);
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
                MyHelper::deleteAStudent($sy_id, $id);
            }
        }

        $msg = "";
        $allAffected = 1; //interpreted as true. 0-->false
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
                try {
                    $sc->save();
                } catch (\Throwable $e3) {
                    $msg .= "<br/>" . $e3->getMessage() . "<br/>";
                    $allAffected = 0;
                    try {
                        $sc->save();
                    } catch (\Throwable $ex) { //DO NOTHING
                        $msg .= "<br/>\$ex" . $ex->getMessage() . "<br/>";
                        $allAffected = 0;
                    }
                }
            } catch (\Throwable $e2) {
                $msg .= "<br/>" . $e2->getMessage() . "<br/>";
                $allAffected = 0;
            }
        } //END FOR
        return response()->json([
            'status' => $allAffected === 1,
            'message' => $allAffected === 1 ? 'All students successfully saved.' : 'Failed to save some students. Details: ' . $msg,
        ], $allAffected === 1 ? 200 : 500);
    }

    public function saveAStudent(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'classe_id' => 'required|integer|min:1',
                'surname' => 'nullable|string|max:100|min:2|regex:/^[a-zA-ZÀ-ÿ\s\-]+$/',
                'name' => 'required|string|max:100|min:2|regex:/^[a-zA-ZÀ-ÿ\s\-]+$/',
                'parent_phone' => 'nullable|string',
                'bday' => 'nullable|String',
                'bplace' => 'nullable|string',
                'sexe' => 'required|string|in:M,F,m,f',
                'repeating' => 'nullable|boolean',
                'matricule' => 'nullable|string',
                'handicape' => 'nullable|boolean',
                'cas_social' => 'nullable|boolean',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

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


        $stud = new Student();
        $stud->name = $name;
        $stud->surname = $surname;
        $stud->bday = $bday;
        $stud->bplace = $bplace;
        $stud->sexe = $sexe;
        $stud->matricule = $matricule;
        $stud->handicape = $handicape;

        $msg = "";
        $allAffected = 1; //interpreted as true. 0-->false
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
            } catch (\Throwable $e3) {
                $msg .= "<br/>Failed to save student_classe " . $e3->getMessage() . "<br/>";
                $allAffected = 0;
                try {
                    $stud->delete(); //WE DELETE STUDENT IN THAT CASE
                } catch (\Throwable $ex) { //DO NOTHING
                    $msg .= "<br/>\$ex" . $ex->getMessage() . "<br/>";
                    $allAffected = 0;
                }
            }
        } catch (\Throwable $e2) {
            $msg .= "<br/>Failed to save student" . $e2->getMessage() . "<br/>";
            $allAffected = 0;
        }
        return response()->json([
            'status' => $allAffected === 1,
            'message' => $allAffected === 1 ? 'Student successfully saved.' : 'Failed to save student. Details: ' . $msg,
        ], $allAffected === 1 ? 200 : 500);
    }

    // student.photo is a mediumblob - the raw bytes are stored directly in the row rather than a
    // filesystem path (unlike basic_school_config.logo_path), so the photo travels inside the same
    // per-school DB backup/restore boundary as the rest of the student's data. The frontend always
    // re-encodes the edited photo as JPEG (canvas.toBlob) before uploading and keeps it under 500KB
    // client-side already; the max:500 rule here is defense-in-depth against a non-browser caller.
    public function uploadStudentPhoto(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'stud_id' => 'required|integer|min:1',
                'photo' => 'required|image|mimes:jpeg,png,jpg|max:500',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

        $connection = $request->input('connection');
        $stud_id = $request->input('stud_id');
        config(["database.default" => $connection]);

        $student = Student::find($stud_id);
        if (is_null($student)) {
            return response()->json([
                'status' => false,
                'message' => 'Student not found.',
            ], 404);
        }

        try {
            $student->photo = file_get_contents($request->file('photo')->getRealPath());
            $student->save();
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to save photo: ' . $th->getMessage(),
            ], 500);
        }

        return response()->json([
            'status' => true,
            'message' => 'Photo successfully saved.',
        ], 200);
    }

    // Streams the raw blob back out through the API (mirrors SchoolInfoController::schoolLogo) -
    // there's no static-file path for a DB-stored blob, so this is the only way to retrieve it.
    // Content-Type is hardcoded to image/jpeg since uploadStudentPhoto only ever receives what the
    // frontend's canvas editor already re-encoded as JPEG.
    public function studentPhoto(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'stud_id' => 'required|integer|min:1',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

        $connection = $request->input('connection');
        $stud_id = $request->input('stud_id');
        config(["database.default" => $connection]);

        $student = Student::find($stud_id);
        if (is_null($student) || is_null($student->photo)) {
            return response()->json([
                'status' => false,
                'message' => 'Photo not found.',
            ], 404);
        }

        return response($student->photo, 200)->header('Content-Type', 'image/jpeg');
    }


    public function updateStudents(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'data' => 'required|string',
                'data_size' => 'nullable|integer|min:1',
                'year' => 'required|string',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $year = $request->input("year");

        $stList = json_decode($data, true);
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);

        $msg = "";
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
                } catch (\Throwable $e3) {
                    $msg .= "<br/>Failed to update student_classe" . $e3->getMessage() . "<br/>";
                    //IN THAT CASE WE DELETE THE STUDENT                     
                    $allAffected = 0;
                }
                $stud->refresh();
                $stud->matricule = $matricule;
                try {
                    $stud->update();
                } catch (\Throwable $e32) {
                    $msg .= "<br/>The matricule exists already" . $e32->getMessage() . "<br/>";
                    $allAffected = 0; //USER SHOULD BE AWARE THAT OPERATION HAS NOT BEEN DONE COMPLETELY
                }
            } catch (\Throwable $e2) {
                $msg .= "<br/>Failed to update student" . $e2->getMessage() . "<br/>";
                $allAffected = 0;
            }
        } //END FOR
        return response()->json([
            'status' => $allAffected === 1,
            'message' => $allAffected === 1 ? 'All students successfully updated.' : 'Failed to update some students. Details: ' . $msg,
        ], $allAffected === 1 ? 200 : 500);
    }


    public function deleteStudents(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'data' => 'required|string',
                'data_size' => 'nullable|integer|min:0',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }
        $connection = $request->input("connection");
        $year  = $request->input("year");
        $data = $request->input("data");
        $data_size = $request->input("data_size");

        $studList = json_decode($data, true);
        $n = count($studList);
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);

        $msg = "";
        $allAffected = 1; //interpreted as true. 0-->false
        foreach ($studList as $st) {
            $stud_id = $st["stud_id"];
            $res = MyHelper::deleteAStudent($sy_id, $stud_id);
            if ($res < 0) {
                $msg .= "<br/>Failed to delete student with ID [$stud_id]<br/>";
                $allAffected = 0;
            }
        } //END FOR
        return response()->json([
            'status' => $allAffected === 1,
            'message' => $allAffected === 1 ? 'All students successfully deleted.' : 'Failed to delete some students. Details: ' . $msg,
        ], $allAffected === 1 ? 200 : 500);
    }


    public function allStudentsOfClasse(Request $request)
    {   //THE CLASSE IS ASSUME TO BE A CLASSE OF THE CURRENT SECTION
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'classe_id' => 'required|integer|min:1',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }
        $connection = $request->input("connection");
        $year = $request->input("year");
        $classe_id = $request->input("classe_id");
        config(["database.default" => $connection]);

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
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function allStudentsOfClasse2(Request $request)
    {   //THE CLASSE IS ASSUME TO BE A CLASSE OF THE CURRENT SECTION
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'classe_id' => 'required|integer|min:1',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

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
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error occurred: ' . $e->getMessage(),
            ], 500); //ERROR OCCURS
        }
    }

    public function allStudClassOfAClasse(Request $request)
    {   //THE CLASSE IS ASSUME TO BE A CLASSE OF THE CURRENT SECTION
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'classe_id' => 'required|integer|min:1',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }
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
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error occurred: ' . $e->getMessage(),
            ], 500); //ERROR OCCURS
        }
    }

    public function allStudClassOfYear(Request $request)
    {   //THE CLASSE IS ASSUME TO BE A CLASSE OF THE CURRENT SECTION
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

        $connection = $request->input("connection");
        $year = $request->input("year");
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);

        try {
            $students = DB::select(
                "SELECT `student_classe_id`, `stud_id`, `basculated`, position_classe, 
                        `repeating`, `solvable1`,`solvable2`, `cas_social`,`abandon` FROM student_classe 
                        WHERE sy_id = $sy_id"
            );
            return response()->json($students, 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error occurred: ' . $e->getMessage(),
            ], 500); //ERROR OCCURS
        }
    }

    public function allStudents(Request $request)
    {   //THE CLASSE IS ASSUME TO BE A CLASSE OF THE CURRENT SECTION
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

        $connection = $request->input("connection");
        $year = $request->input("year");
        config(["database.default" => $connection]);

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
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error occurred: ' . $e->getMessage(),
            ], 500); //ERROR OCCURS
        }
    }

    // Lightweight per-student rows (sexe/repeating/classe_id/classe_name/level only) for every
    // student enrolled in the given section+year - backs the "Effectifs par classe" report, which
    // needs sexe/repeating tallies grouped by classe/cycle across a whole section rather than a
    // single classe. Takes `section` as the literal francophone/anglophone string (same convention
    // as allClasse1/getAPCLevels), not a section_id, unlike allStudentsOfClasseOfSection.
    public function allStudentsSummaryOfSection(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'section' => 'required|string',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

        $connection = $request->input("connection");
        $year = $request->input("year");
        $section = $request->input("section");
        config(["database.default" => $connection]);

        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $section_id = MyHelper::getSectionID($section);

            $students = DB::select(
                "SELECT student.sexe, student_classe.repeating,
                        student_classe.classe_id, classe.classe_name, classe.`level`
                            FROM student, student_classe, classe, classe_year
                            WHERE
                                student.stud_id = student_classe.stud_id
                                    AND student_classe.classe_id = classe.classe_id
                                    AND student_classe.classe_id = classe_year.classe_id
                                    AND student_classe.sy_id = $sy_id
                                    AND classe_year.section_id = $section_id
                        ORDER BY classe.`level`, classe.classe_name"
            );
            return response()->json($students, 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error occurred: ' . $e->getMessage(),
            ], 500); //ERROR OCCURS
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
