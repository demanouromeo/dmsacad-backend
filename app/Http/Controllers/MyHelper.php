<?php

namespace App\Http\Controllers;

use App\Models\Appreciation;
use App\Models\ClasseDecisionParam;
use App\Models\ClasseYear;
use App\Models\ClassifiedparamForclass;
use App\Models\Discipline;
use App\Models\LockSequenceClasse;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\SectionYear;
use App\Models\Staff;
use App\Models\StudCompMark;
use App\Models\Student;
use App\Models\StudentClasse;
use App\Models\StudentSubject;
use App\Models\SubjectClasse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MyHelper extends Controller
{
    public static $allowedClasseChars =
    "abcdefghijklmnopqrstuvwxyz _-0123456789àâçéèêîôùûÀÂÇÉÈÊÎÔŒÙÛABCDEFGHIJKLMNOPQRSTUVWXYZ";
    public static $allowedsSubjectsChars =
    "abcdefghijklmnopqrstuvwxyz _-0123456789àâçéèêîôùûÀÂÇÉÈÊÎÔŒÙÛABCDEFGHIJKLMNOPQRSTUVWXYZ,/&.()'œ";

    public static function fetchStudentClasse1ereTle($sy_id)
    {
        $res = DB::select("SELECT*FROM student_classe WHERE student_classe.sy_id = $sy_id 
                AND student_classe.classe_id IN(SELECT classe.classe_id FROM classe 
                    WHERE classe.level = 6 OR classe.level = 7);");
        return $res;
    }

    public static function fetchStudentClasseByLevel($level, $sy_id)
    {
        $res = DB::select("SELECT*FROM student_classe WHERE student_classe.sy_id = $sy_id 
                AND student_classe.classe_id IN(SELECT classe.classe_id FROM classe 
                    WHERE classe.level = $level);");
        return $res;
    }

    public static function  deleteAStudent($sy_id, $stud_id)
    {
        try {
            //------ REMOVE FROM absence daily
            $task1 = DB::select("DELETE FROM absence_daily 
                        WHERE absence_daily.stud_id = $stud_id 
                            AND absence_daily.sy_id = $sy_id");
            $x = DB::select("SELECT* FROM absence_daily 
                        WHERE absence_daily.stud_id = $stud_id");
            $count1 = count($x);


            //------ REMOVE FROM appreciation
            $task2 = DB::select("DELETE FROM appreciation 
                    WHERE appreciation.stud_id = $stud_id 
                    AND appreciation.sy_id = $sy_id");
            $x = DB::select("SELECT* FROM appreciation 
                    WHERE appreciation.stud_id = $stud_id");
            $count2 = count($x);


            //------ REMOVE FROM discipline
            $task3 = DB::select("DELETE FROM discipline 
                    WHERE discipline.stud_id = $stud_id 
                        AND discipline.sy_id = $sy_id");
            $x = DB::select("SELECT* FROM discipline 
                    WHERE discipline.stud_id = $stud_id ");
            $count3 = count($x);

            //------ REMOVE FROM stud_comp_mark
            $task4 = DB::select("DELETE FROM stud_comp_mark 
                    WHERE stud_comp_mark.stud_id = $stud_id 
                        AND stud_comp_mark.sy_id = $sy_id");
            $x = DB::select("SELECT* FROM stud_comp_mark 
                    WHERE stud_comp_mark.stud_id = $stud_id");
            $count4 = count($x);

            //------ REMOVE FROM student_subject
            $task5 = DB::select("DELETE FROM student_subject 
                    WHERE student_subject.stud_id = $stud_id 
                        AND student_subject.sy_id = $sy_id");
            $x = DB::select("SELECT* FROM student_subject 
                    WHERE student_subject.stud_id = $stud_id");
            $count5 = count($x);

            //------ REMOVE FROM student_classe
            $task6 = DB::select("DELETE FROM student_classe 
                    WHERE student_classe.stud_id = $stud_id 
                        AND student_classe.sy_id = $sy_id");
            $x = DB::select("SELECT* FROM student_classe WHERE student_classe.stud_id = $stud_id");
            $count6 = count($x);


            $count = $count1 + $count2 + $count3 + $count4 + $count5 + $count6;
            if ($count == 0) {
                //No refrences left ---> Delete PERMANETELY
                try {
                    $stud = Student::find($stud_id);
                    if (!is_null($stud)) {
                        $acc_id_tmp = $stud->acc_id;
                        $stud->delete();
                    } else {
                        return -2; //Very bad student not found
                    }

                    //LET'S DELETE ALL THE RELETED ACCOUNTS
                    if ($acc_id_tmp != null) { //if null then the student has no account
                        DB::select("DELETE FROM ACCOUNT WHERE acc_id = $acc_id_tmp");
                    }
                    return 1; //student deleted
                } catch (Exception $ex) {
                    echo '<br/>MyHelper.getSchoolYearID(): ERROR: ' . $ex->getMessage() . '<br/>';
                    return -2; //FAILED TO DELETE THE student
                }
            } else {
                //STUDENT DELETED IN SCHOOL YEAR AND SECTION, BUT STILL HAVING REF IN OTHERS SCHOOL YEARS
                //echo "\$count1: $count1 | \$count2: $count2 | \$count3: $count3 \$count4: $count4 \$count5: $count5 \$count6: $count6";
            }

            //CLEAR UP
            try {
                DB::select("DELETE FROM student WHERE student.stud_id NOT IN(SELECT student_classe.stud_id FROM student_classe)");
            } catch (Exception $e) {
                //throw $th;
            }
            return 1;
        } catch (Exception $e) {
            echo '<br/>MyHelper.getSchoolYearID(): ERROR: ' . $e->getMessage() . '<br/>';
            return -1; //ERROR OCCURS, --> OP FAILED
        }
    }

    public static function  deleteAStaff($sy_id, $section_id, $staff_id)
    {
        try {
            //------ REMOVE CLASSE_YEAR eventual INCONSISTENCY
            $task1 = DB::select("UPDATE classe_year set classe_year.classe_master = null where 
                (classe_year.classe_master) not in(SELECT staff_year.staff_id from staff_year);");
            $task2 = DB::select("UPDATE classe_year set classe_year.sg_id = NULL 
                where (classe_year.sg_id) not in(SELECT staff_year.staff_id from staff_year);");

            //------ REMOVE THE STAFF REFERENCES IN CLASSE_YEAR
            $task3 = DB::select("UPDATE classe_year SET classe_master = NULL 
                    where classe_year.sy_id = $sy_id
                        AND classe_year.section_id = $section_id
                        AND classe_year.classe_master = $staff_id;");
            $task4 = DB::select("UPDATE classe_year set classe_year.sg_id = NULL 
                    WHERE classe_year.sy_id = $sy_id 
                        AND classe_year.section_id = $section_id
                        AND classe_year.sg_id = $staff_id;");

            //------ DELETE SUBJECT_CLASSE_STAFF HAVING THE STAFF REFERENCES
            $task5 = DB::select("DELETE FROM subject_classe_staff
                    WHERE subject_classe_staff.subject_classe_id
	                    IN(SELECT subject_classe.subject_classe_id FROM subject_classe
	                        WHERE subject_classe.sy_id = $sy_id
	                            AND subject_classe.section_id = $section_id)
	                            AND subject_classe_staff.staff_id = $staff_id");

            //------ DELETE REFERENCES FROM STAFF_YEAR
            $task6 = DB::select("DELETE FROM staff_year 
                    WHERE staff_year.staff_id = $staff_id
                        AND staff_year.sy_id = $sy_id");

            //------ COUNT REFERENCES LEFT
            $res1 = DB::select("SELECT*FROM staff_year WHERE staff_year.staff_id = $staff_id");
            $count1 = count($res1);

            $res2 = DB::select("SELECT*FROM subject_classe_staff WHERE staff_id = $staff_id");
            $count2 = count($res2);

            $res3 = DB::select("SELECT*FROM classe_year 
                    WHERE classe_year.sg_id = $staff_id 
                        OR classe_year.classe_master = $staff_id");
            $count3 = count($res3);

            $count = $count1 + $count2 + $count3;
            if ($count == 0) {
                //No refrences left ---> Delete PERMANETELY
                try {
                    $staff = Staff::find($staff_id);
                    $acc_id_tmp = 0;
                    if (!is_null($staff)) {
                        $acc_id_tmp = $staff->acc_id;
                        $staff->delete();
                    } else {
                        return -2; //Very bad staff not found
                    }

                    //LET'S DELETE ALL THE RELETED ACCOUNTS
                    DB::select("DELETE FROM ACCOUNT WHERE acc_id = $acc_id_tmp");
                    return 1; //staff deleted
                } catch (Exception $ex) {
                    echo '<br/>MyHelper.getSchoolYearID(): ERROR: ' . $ex->getMessage() . '<br/>';
                    return -2; //FAILED TO DELETE THE STAFF
                }
            } else {
                //STAFF DELETED IN SCHOOL YEAR AND SECTION, BUT STILL HAVING REF IN OTHERS SCHOOL YEARS
                //echo "\$count1: $count1 | \$count2: $count2 | \$count3: $count3";
            }
            return 1;
        } catch (Exception $e) {
            echo '<br/>MyHelper.getSchoolYearID(): ERROR: ' . $e->getMessage() . '<br/>';
            return -1; //ERROR OCCURS, --> OP FAILED
        }
    }
    public static function  removeAStaffCourses($sy_id, $section_id, $staff_id)
    {
        try {
            $res = DB::select("DELETE FROM subject_classe_staff
                    WHERE subject_classe_staff.subject_classe_id
	                    IN(SELECT subject_classe.subject_classe_id FROM subject_classe
	                        WHERE subject_classe.sy_id = $sy_id
	                            AND subject_classe.section_id = $section_id)
	                            AND subject_classe_staff.staff_id = $staff_id");
            return 1;
        } catch (Exception $e) {
            die('<br/>MyHelper.getSchoolYearID(): ERROR: ' . $e->getMessage() . '<br/>');
            return -1;
        }
    }

    public static function  getSchoolYearID($year)
    {
        try {
            $sy = SchoolYear::where('year', $year)->first();
            $sy_id = $sy->sy_id;
            return $sy_id;
        } catch (Exception $e) {
            die('<br/>MyHelper.getSchoolYearID(): ERROR: ' . $e->getMessage() . '<br/>');
        }
    }

    public static function getAccountType($function)
    {
        $type = 5;
        switch ($function) {
            case "0":
                $type = 5;
                break;
            case "1":
                $type = 3;
                break;
            case "2":
                $type = 8;
                break;
            case "3":
                $type = 2;
                break;
            case "4":
                $type = 4;
                break;
            case "5":
                $type = 2;
                break;
            default:
                $type = 5;
        }
        return $type;
    }

    public static function getSectionID($sectionName)
    {
        try {
            $section = Section::where('section_name', '=', $sectionName)->first();
            $currentSectionId = $section->section_id;
            return $currentSectionId;
        } catch (Exception $e) {
            die('<br/>MyHelper.getSection(): ERROR: ' . $e->getMessage() . '<br/>');
        }
    }

    public static function getSectionYearID2($section_name, $sy_id): int
    {
        try {
            //$section = Section::where('section_name', '=', $section_name)->first();
            //$currentSectionId = $section->section_id;
            $section_id = MyHelper::getSectionID($section_name);
            $sectionYear = SectionYear::where('sy_id', '=', $sy_id)
                ->where('section_id', '=', $section_id)
                ->first();
            $section_year_id = $sectionYear->section_year_id;
            return $section_year_id;
        } catch (Exception $e) {
            die('<br/>MyHelper.getSectionYearID(): ERROR: ' . $e->getMessage() . '<br/>');
        }
    }

    public static function getSpecialitiesOfYearOfSection($sy_id, $section_id)
    {
        try {
            //Joined (rather than a plain whereIn) so the response also carries which filiere
            //each speciality belongs to for this specific year/section - the frontend's edit
            //form needs it to let the user reassign a speciality to a different filiere.
            $spList = DB::table('speciality')
                ->join('speciality_year', function ($join) use ($sy_id, $section_id) {
                    $join->on('speciality_year.speciality_id', '=', 'speciality.speciality_id')
                        ->where('speciality_year.sy_id', '=', $sy_id)
                        ->where('speciality_year.section_id', '=', $section_id);
                })
                ->join('filiere', 'filiere.filiere_id', '=', 'speciality_year.filiere_id')
                ->select('speciality.*', 'filiere.filiere_id as filiere_id', 'filiere.nom_filiere')
                ->orderBy('speciality.speciality_name', 'ASC')
                ->get();
            return $spList;
        } catch (Exception $e) {
            die('<br/>MyHelper.getSpecialitiesOfYearOfSection(): ERROR: ' . $e->getMessage() . '<br/>');
        }
    }

    public static function getGroupesOfYearOfSection($sy_id, $section_id)
    {
        try {
            $groupeArray = DB::select("SELECT*FROM groupe WHERE groupe.groupe_id 
                            IN(SELECT groupe_year.groupe_id from groupe_year 
	                            WHERE groupe_year.sy_id = $sy_id 
	                                AND groupe_year.section_id = $section_id)
                            ORDER BY groupe.groupe_name");
            return $groupeArray;
        } catch (Exception $e) {
            die('<br/>MyHelper.getGroupesOfYearOfSection(): ERROR: ' . $e->getMessage() . '<br/>');
        }
    }


    //----------------- ID DES FILIERES DE L'ANNEE
    public static function getFyIDsOfSection($sy_id, $section_id)
    {
        try {
            $ids = DB::table('filiere_year')
                ->select('filiere_id')
                ->where('section_id', '=', $section_id)
                ->where('sy_id', '=', $sy_id);
            return $ids;
        } catch (Exception $e) {
            die('<br/>MyHelper.getFyIDsOfSection(): ERROR: ' . $e->getMessage() . '<br/>');
        }
    }

    //ID DES SPECIALITY DE L'ANNEE
    public static function getSpYearIDsOfSection($sy_id, $section_id)
    {
        try {
            $ids = DB::table('speciality_year')
                ->select('speciality_id')
                ->where('section_id', '=', $section_id)
                ->where('sy_id', '=', $sy_id);
            return $ids;
        } catch (Exception $e) {
            die('<br/>MyHelper.getSpYearIDsOfSection(): ERROR: ' . $e->getMessage() . '<br/>');
        }
    }

    public static function getFiliereOfYearAndSection($sy_id, $section_id)
    {
        try {
            $ids = MyHelper::getFyIDsOfSection($sy_id, $section_id);
            $filieres = DB::table('filiere')
                ->whereIn('filiere_id', $ids)
                ->orderBy('nom_filiere', 'ASC')
                ->get();
            return $filieres;
        } catch (Exception $e) {
            die('<br/>MyHelper.getFiliereOfYearAndSection(): ERROR: ' . $e->getMessage() . '<br/>');
        }
    }

    public static function getSpecialitiesOfYearAndSection($sy_id, $section_id)
    {
        try {
            $ids = MyHelper::getSpYearIDsOfSection($sy_id, $section_id); //Les ids de toutes les speciality_year de la section
            $spList = DB::table('speciality')
                ->whereIn('speciality_id', $ids)
                ->orderBy('speciality_name', 'ASC')
                ->get();
            return $spList;
        } catch (Exception $e) {
            die('<br/>MyHelper.getSpecialitiesOfYearAndSection(): ERROR: ' . $e->getMessage() . '<br/>');
        }
    }

    public static function deleteDisciplineOfYear($sy_id)
    {
        $affected = 1;
        try {
            $affected = Discipline::where('sy_id', $sy_id)->delete();
        } catch (Exception $e) {
            //echo '<br/>MyHelper.deleteDisciplineOfYear(): ERROR: ' . $e->getMessage() . '<br/>';
            $affected = -1;
        }
        return $affected; //1->success; 0-> no record deleted; -1-> a PB occurs
    }

    public static function deleteDisciplineOfYearAndSection($sy_id, $section_id)
    {
        try {
            $x = DB::select(
                "
                DELETE FROM discipline WHERE discipline.sy_id = $sy_id AND discipline.stud_id  
                    IN(SELECT student_classe.stud_id FROM student_classe WHERE student_classe.classe_id 
                        IN(SELECT classe_year.classe_id FROM classe_year 
                            WHERE classe_year.section_id = $section_id)
                    )
"
            );
            return 1; //success
        } catch (Exception $e) {
            //echo '<br/>MyHelper.deleteDisciplineOfYearAndSection(): ERROR: ' . $e->getMessage() . '<br/>';
            return -1; //ECHEC
        }
    }

    public static function deleteAClasseDisciplineOfYearAndSection($sy_id, $section_id, $classe_id)
    {
        try {
            $x = DB::select(
                "
            DELETE FROM discipline WHERE discipline.sy_id = $sy_id AND discipline.stud_id  
                    IN(SELECT student_classe.stud_id FROM student_classe 
						  WHERE student_classe.classe_id = $classe_id AND student_classe.classe_id
                        IN(SELECT classe_year.classe_id FROM classe_year 
                            WHERE classe_year.section_id = $section_id)
                    )"
            );
            return 1; //success
        } catch (Exception $e) {
            echo '<br/>MyHelper.deleteClasseDisciplineOfYearAndSection(): ERROR: ' . $e->getMessage() . '<br/>';
            echo "<br/>sy_id:$sy_id<br/>section_id: $section_id<br/>classe_id:$classe_id<br/>";
            return -1; //ECHEC
        }
    }
    public static function deleteAbsenceDailyOfYearAndSection($sy_id, $section_id)
    {
        try {
            $x = DB::select(
                "
            Delete FROM absence_daily WHERE absence_daily.stud_id 
                IN(SELECT student_classe.stud_id FROM student_classe 
                    WHERE student_classe.classe_id
                        IN(SELECT classe_year.classe_id FROM classe_year 
                            WHERE classe_year.sy_id = $sy_id 
                            AND classe_year.section_id = $section_id
                        )
                )"
            );
            return 1; //success
        } catch (Exception $e) {
            //echo '<br/>MyHelper.deleteClasseDecisionParamOfYear(): ERROR: ' . $e->getMessage() . '<br/>';
            return -1; //ECHEC
        }
    }
    public static function deleteAClasseAbsenceDailyOfYearAndSection($sy_id, $section_id, $classe_id)
    {
        try {
            $x = DB::select(
                "
                        DELETE FROM absence_daily WHERE absence_daily.stud_id 
                            IN(SELECT student_classe.stud_id FROM student_classe 
                                WHERE student_classe.classe_id = $classe_id
                                AND student_classe.classe_id
                                    IN(SELECT classe_year.classe_id FROM classe_year 
                                        WHERE classe_year.sy_id = $sy_id 
                                        AND classe_year.section_id = $section_id
                                    )
                            )"
            );
            return 1; //success
        } catch (Exception $e) {
            echo '<br/>MyHelper.deleteClasseClasseAbsenceDailyOfYearAndSection(): ERROR: ' . $e->getMessage() . '<br/>';
            echo "<br/>sy_id:$sy_id<br/>section_id: $section_id<br/>classe_id:$classe_id<br/>";
            return -1; //ECHEC
        }
    }

    public static function deleteClasseDecisionParamOfYear($sy_id)
    {
        $affected = 1;
        try {
            $affected = ClasseDecisionParam::where('sy_id', $sy_id)->delete();
        } catch (Exception $e) {
            //echo '<br/>MyHelper.deleteClasseDecisionParamOfYear(): ERROR: ' . $e->getMessage() . '<br/>';
            $affected = -1;
        }
        return $affected; //1->success; 0-> no record deleted; -1-> a PB occurs
    }

    public static function deleteClasseDecisionParamOfYearSection($sy_id, $section_id)
    {
        try {
            $x = DB::select(
                "
                DELETE FROM classe_decision_param WHERE classe_decision_param.classe_id
                    IN(SELECT classe_year.classe_id FROM classe_year 
                        WHERE classe_year.sy_id = $sy_id
     	                    AND classe_year.section_id = $section_id)"
            );
            return 1; //success
        } catch (Exception $e) {
            //echo '<br/>MyHelper.deleteClasseDecisionParamOfYearSection(): ERROR: ' . $e->getMessage() . '<br/>';
            return -1; //ECHEC
        }
    }
    public static function deleteAClasseDecisionParamOfYearSection($sy_id, $section_id, $classe_id)
    {
        try {
            $x = DB::select(
                "DELETE FROM classe_decision_param 
                        WHERE classe_decision_param.classe_id =$classe_id
                        AND classe_decision_param.classe_id
                            IN(SELECT classe_year.classe_id FROM classe_year 
                                WHERE classe_year.sy_id = $sy_id
	                            AND classe_year.section_id = $section_id)"
            );
            return 1; //success
        } catch (Exception $e) {
            //echo '<br/>MyHelper.deleteClasseDecisionParamOfYearSection2(): ERROR: ' . $e->getMessage() . '<br/>';
            return -1; //ECHEC
        }
    }

    public static function deleteAppreciationOfYear($sy_id)
    {
        $affected = 1;
        try {
            $affected = Appreciation::where('sy_id', $sy_id)->delete();
        } catch (Exception $e) {
            //echo '<br/>MyHelper.deleteAppreciation(): ERROR: ' . $e->getMessage() . '<br/>';
            $affected = -1;
        }
        return $affected; //1->success; 0-> no record deleted; -1-> a PB occurs
    }

    public static function deleteAppreciationOfYearAndSection($sy_id, $section_id)
    {
        try {
            $x = DB::select(
                "
                DELETE FROM appreciation WHERE appreciation.stud_id  
                    IN(SELECT student_classe.stud_id FROM student_classe WHERE student_classe.classe_id 
                        IN(SELECT classe_year.classe_id FROM classe_year 
                            WHERE classe_year.sy_id = $sy_id
                            AND classe_year.section_id = $section_id)
                    )"
            );
            return 1; //success
        } catch (Exception $e) {
            //echo '<br/>MyHelper.deleteAppreciationOfYearAndSection(): ERROR: ' . $e->getMessage() . '<br/>';
            return -1; //ECHEC
        }
    }

    public static function deleteAClasseAppreciationOfYearAndSection($sy_id, $section_id, $classe_id)
    {
        try {
            $x = DB::select(
                "
                    DELETE FROM appreciation WHERE appreciation.stud_id  
                    IN(SELECT student_classe.stud_id FROM student_classe 
						WHERE student_classe.classe_id = $classe_id
						AND student_classe.classe_id 
                            IN(SELECT classe_year.classe_id FROM classe_year 
                                WHERE classe_year.sy_id = $sy_id
                                AND classe_year.section_id = $section_id)
                    )"
            );
            return 1; //success
        } catch (Exception $e) {
            //echo '<br/>MyHelper.deleteAppreciationOfYearAndSection(): ERROR: ' . $e->getMessage() . '<br/>';
            return -1; //ECHEC
        }
    }
    public static function deleteClasseYearOfYearAndSection($sy_id, $section_id)
    {
        $affected = 1;
        try {
            $affected = ClasseYear::where('sy_id', $sy_id)
                ->where('section_id', $section_id)
                ->delete();
        } catch (Exception $e) {
            //echo '<br/>MyHelper.deleteClasseYearOfYearAndSection(): ERROR: ' . $e->getMessage() . '<br/>';
            $affected = -1;
        }
        return $affected; //1->success; 0-> record deleted; -1-> a PB occurs
    }

    public static function deleteClassifiedparamForClassOfYear($sy_id)
    {
        $affected = 1;
        try {
            $affected = ClassifiedparamForclass::where('sy_id', $sy_id)->delete();
        } catch (Exception $e) {
            //echo '<br/>MyHelper.deleteClassifiedparamForClassOfYear(): ERROR: ' . $e->getMessage() . '<br/>';
            $affected = -1;
        }
        return $affected; //1->success; 0-> no record deleted; -1-> a PB occurs
    }

    public static function deleteClassifiedparamForClassOfYearAndSection($sy_id, $section_id)
    {
        try {
            $x = DB::select(
                "
                DELETE FROM classifiedparam_forclass WHERE classifiedparam_forclass.classe_id
                    IN(SELECT classe_year.classe_id FROM classe_year 
                        WHERE classe_year.sy_id = $sy_id
     	                    AND classe_year.section_id = $section_id)"
            );
            return 1; //success
        } catch (Exception $e) {
            //echo '<br/>MyHelper.deleteClassifiedparamForClassOfYearAndSection(): ERROR: ' . $e->getMessage() . '<br/>';
            return -1; //ECHEC
        }
    }

    public static function deleteClassifiedparamForClassOfYearAndSection2($sy_id, $section_id, $classe_id)
    {
        try {
            $x = DB::select(
                "DELETE FROM classifiedparam_forclass 
                            WHERE classifiedparam_forclass.classe_id = $classe_id
                                AND classifiedparam_forclass.classe_id
                                    IN(SELECT classe_year.classe_id FROM classe_year 
                                        WHERE classe_year.sy_id = $sy_id
                                        AND classe_year.section_id = $section_id)"
            );
            return 1; //success
        } catch (Exception $e) {
            //echo '<br/>MyHelper.deleteClassifiedparamForClassOfYearAndSection2(): ERROR: ' . $e->getMessage() . '<br/>';
            return -1; //ECHEC
        }
    }

    public static function deleteLockSequenceClasseOfYear($sy_id)
    {
        $affected = 1;
        try {
            $affected = LockSequenceClasse::where('sy_id', $sy_id)->delete();
        } catch (Exception $e) {
            //echo '<br/>MyHelper.deleteLockSequenceClasseOfYear(): ERROR: ' . $e->getMessage() . '<br/>';
            $affected = -1;
        }
        return $affected; //1->success; 0-> no record deleted; -1-> a PB occurs
    }

    public static function deleteLockSequenceClasseOfYearAndSection($sy_id, $section_id)
    {
        try {
            $x = DB::select(
                "DELETE FROM lock_sequence_classe 
                    WHERE lock_sequence_classe.classe_id
                        IN(SELECT classe_year.classe_id FROM classe_year 
                            WHERE classe_year.sy_id = $sy_id
     	                    AND classe_year.section_id = $section_id)"
            );
            return 1; //success
        } catch (Exception $e) {
            //echo '<br/>MyHelper.deleteLockSequenceClasseOfYearAndSection(): ERROR: ' . $e->getMessage() . '<br/>';
            return -1; //ECHEC
        }
    }

    public static function deleteLockSequenceClasseOfYearAndSection2($sy_id, $section_id, $classe_id)
    {
        try {
            $x = DB::select(
                "DELETE FROM lock_sequence_classe 
                        WHERE lock_sequence_classe.classe_id = $classe_id
                            AND lock_sequence_classe.classe_id
                            IN(SELECT classe_year.classe_id FROM classe_year 
                                WHERE classe_year.sy_id = $sy_id
     	                        AND classe_year.section_id = $section_id)"
            );
            return 1; //success
        } catch (Exception $e) {
            //echo '<br/>MyHelper.deleteLockSequenceClasseOfYearAndSection2(): ERROR: ' . $e->getMessage() . '<br/>';
            return -1; //ECHEC
        }
    }

    public static function deleteStudents()
    {   //CALL THIS FUNCTION ONLY WHEN ALL REF TO STUDENT ARE DELETED
        //RELATIONS: DISCIPLINE, STUD_COMP_MARK, STUDENT_CLASS, STUDENT_SUBJECT, APPRECIATION
        $affected = 1;
        try {
            $affected = Student::all()->delete();
        } catch (Exception $e) {
            //echo '<br/>MyHelper.deleteStudents(): ERROR: ' . $e->getMessage() . '<br/>';
            $affected = -1;
        }
        return $affected; //1->success; 0-> no record deleted; -1-> a PB occurs
    }

    public static function deleteStudentClasseOfYear($sy_id)
    {
        $affected = 1;
        try {
            $affected = StudentClasse::where('sy_id', $sy_id)->delete();
        } catch (Exception $e) {
            //echo '<br/>MyHelper.deleteStudentClasseOfYear(): ERROR: ' . $e->getMessage() . '<br/>';
            $affected = -1;
        }
        return $affected; //1->success; 0-> no record deleted; -1-> a PB occurs
    }

    public static function deleteStudentClasseOfYearAndSection($sy_id, $section_id)
    {
        try {
            $x = DB::select(
                "DELETE FROM student_classe WHERE student_classe.classe_id
                                IN(SELECT classe_year.classe_id FROM classe_year 
                                    WHERE classe_year.sy_id = $sy_id
     	                            AND classe_year.section_id = $section_id)"
            );
            return 1; //success
        } catch (Exception $e) {
            //echo '<br/>MyHelper.deleteStudentClasseOfYearAndSection(): ERROR: ' . $e->getMessage() . '<br/>';
            return -1; //ECHEC
        }
    }

    public static function deleteAClasseStudentClasseOfYearAndSection($sy_id, $section_id, $classe_id)
    {
        try {
            $x = DB::select(
                "DELETE FROM student_classe 
                                WHERE student_classe.classe_id = $classe_id
                                AND student_classe.classe_id
                                    IN(SELECT classe_year.classe_id FROM classe_year 
                                        WHERE classe_year.sy_id = $sy_id
     	                                AND classe_year.section_id = $section_id)"
            );
            return 1; //success
        } catch (Exception $e) {
            //echo '<br/>MyHelper.deleteAClasseStudentClasseOfYearAndSection(): ERROR: ' . $e->getMessage() . '<br/>';
            return -1; //ECHEC
        }
    }
    public static function deleteStudentSubjectOfYear($sy_id)
    {
        $affected = 1;
        try {
            $affected = StudentSubject::where('sy_id', $sy_id)->delete();
        } catch (Exception $e) {
            //echo '<br/>MyHelper.deleteStudentSubject(): ERROR: ' . $e->getMessage() . '<br/>';
            $affected = -1;
        }
        return $affected; //1->success; 0-> no record deleted; -1-> a PB occurs
    }

    public static function deleteStudentSubjectOfYearAndSection($sy_id, $section_id)
    {
        try {
            $x = DB::select(
                "DELETE FROM student_subject WHERE student_subject.stud_id
                            IN(SELECT student_classe.stud_id FROM student_classe 
                            WHERE student_classe.classe_id 
                                IN(SELECT classe_year.classe_id FROM classe_year 
		                            WHERE classe_year.sy_id = $sy_id
		                            AND classe_year.section_id = $section_id)
                                )"
            );
            return 1; //success
        } catch (Exception $e) {
            //echo '<br/>MyHelper.deleteStudentSubjectOfYearAndSection(): ERROR: ' . $e->getMessage() . '<br/>';
            return -1; //ECHEC
        }
    }

    public static function deleteAClasseSubjectCompetencesOfYearAndSection($sy_id, $section_id, $classe_id)
    {
        try {
            $x = DB::select(
                "DELETE FROM subject_competences 
                            WHERE subject_competences.sy_id = $sy_id 
                                AND subject_competences.section_id = $section_id
                                AND subject_competences.classe_id = $classe_id"
            );
            return 1; //success
        } catch (Exception $e) {
            //echo '<br/>MyHelper.deleteAClasseSubjectCompetencesOfYearAndSection(): ERROR: ' . $e->getMessage() . '<br/>';
            return -1; //ECHEC
        }
    }
    public static function deleteAClasseStudentSubjectOfYearAndSection($sy_id, $section_id, $classe_id)
    {
        try {
            $x = DB::select(
                "DELETE FROM student_subject WHERE student_subject.stud_id
                            IN(SELECT student_classe.stud_id FROM student_classe 
                            WHERE student_classe.classe_id = $classe_id
                            AND student_classe.classe_id
                                IN(SELECT classe_year.classe_id FROM classe_year 
		                            WHERE classe_year.sy_id = $sy_id
		                            AND classe_year.section_id = $section_id)
                                )"
            );
            return 1; //success
        } catch (Exception $e) {
            //echo '<br/>MyHelper.deleteAClasseStudentSubjectOfYearAndSection(): ERROR: ' . $e->getMessage() . '<br/>';
            return -1; //ECHEC
        }
    }

    public static function deleteStudCompMarkOfYear($sy_id)
    {
        $affected = 1;
        try {
            $affected = StudCompMark::where('sy_id', $sy_id)->delete();
        } catch (Exception $e) {
            //echo '<br/>MyHelper.deleteStudCompMark(): ERROR: ' . $e->getMessage() . '<br/>';
            $affected = -1;
        }
        return $affected; //1->success; 0-> no record deleted; -1-> a PB occurs
    }

    public static function deleteStudCompMarkOfYearAndSection($sy_id, $section_id)
    {
        try {
            $x = DB::select(
                "DELETE FROM stud_comp_mark WHERE stud_comp_mark.stud_id
                        IN(SELECT student_classe.stud_id FROM student_classe 
                            WHERE student_classe.classe_id 
                                IN(SELECT classe_year.classe_id FROM classe_year 
		                            WHERE classe_year.sy_id = $sy_id
		                            AND classe_year.section_id = $section_id)
                        )"
            );
            return 1; //success
        } catch (Exception $e) {
            //echo '<br/>MyHelper.deleteStudCompMarkOfYearAndSection(): ERROR: ' . $e->getMessage() . '<br/>';
            return -1; //ECHEC
        }
    }

    public static function deleteAClasseStudCompMarkOfYearAndSection($sy_id, $section_id, $classe_id)
    {
        try {
            $x = DB::select(
                "DELETE FROM stud_comp_mark WHERE stud_comp_mark.stud_id
                        IN(SELECT student_classe.stud_id FROM student_classe 
                            WHERE student_classe.classe_id = $classe_id
							AND student_classe.classe_id 
                                IN(SELECT classe_year.classe_id FROM classe_year 
		                            WHERE classe_year.sy_id = $sy_id
		                            AND classe_year.section_id = $section_id)
                        )"
            );
            return 1; //success
        } catch (Exception $e) {
            //echo '<br/>MyHelper.deleteAClasseStudCompMarkOfYearAndSection(): ERROR: ' . $e->getMessage() . '<br/>';
            return -1; //ECHEC
        }
    }

    public static function deleteStudParent()
    {   //----------- WORKING WELL
        try {
            $x = DB::select("DELETE from stud_parent WHERE stud_parent.p_id NOT IN(SELECT student.p_id FROM student)");
            //6 --> represents parents account type
            $x = DB::select("DELETE from account WHERE account.acc_id not IN(SELECT stud_parent.acc_id from stud_parent) and type = 6;");
            echo 1; //Success
        } catch (Exception $e) {
            echo -1; //Error occurs
        } //1->success; 0-> no record deleted; -1-> a PB occurs
    }

    public static function deletesSubjectClasseOfYearAndSection($sy_id, $section_id)
    {
        $affected = 1;
        try {
            $affected = SubjectClasse::where('sy_id', $sy_id)
                ->where('section_id', $section_id)
                ->delete();
        } catch (Exception $e) {
            //echo '<br/>MyHelper.deletesSubjectClasseOfYearAndSection(): ERROR: ' . $e->getMessage() . '<br/>';
            $affected = -1;
        }
        return $affected; //1->success; 0-> record deleted; -1-> a PB occurs
    }

    public static function deleteSujectsCompetences($sy_id, $section_id)
    {
        //Must be called before deleting the subject_classe 
        //------ WORKING
        try {
            $x = DB::select(
                "DELETE FROM subject_competences WHERE sy_id = $sy_id 
                AND section_id = $section_id"
            );
            return 1; //Success
        } catch (Exception $e) {
            //echo '<br/>MyHelper.deleteSujectsCompetences(): ERROR: ' . $e->getMessage() . '<br/>';
            return -1; //echec
        }
    }
    public static function deletesSubjectClasseStaffOfYearAndSection($sy_id, $section_id)
    {
        //Must be called before deleting the subject_classe 
        //------ WORKING
        try {
            $x = DB::select(
                "DELETE FROM subject_classe_staff 
                            WHERE 
                                subject_classe_staff.subject_classe_id 
                                IN(
                                    SELECT subject_classe.subject_classe_id 
                                        FROM subject_classe 
                                            WHERE sy_id = $sy_id AND section_id = $section_id
                                )"
            );
            return 1; //Success
        } catch (Exception $e) {
            //echo '<br/>MyHelper.deletesSubjectClasseStaffOfYearAndSection(): ERROR: ' . $e->getMessage() . '<br/>';
            return -1; //echec
        }
    }

    public static function deletesAClasseSubjectClasseStaffOfYearAndSection($sy_id, $section_id, $classe_id)
    {
        //Must be called before deleting the subject_classe 
        //------ WORKING
        try {
            $x = DB::select(
                "DELETE FROM subject_classe_staff 
                        WHERE subject_classe_staff.subject_classe_id   
                        IN(SELECT subject_classe.subject_classe_id 
                            FROM subject_classe 
                                WHERE sy_id = $sy_id AND section_id = $section_id 
                                AND classe_id = $classe_id
                        )"
            );
            return 1; //Success
        } catch (Exception $e) {
            //echo '<br/>MyHelper.deletesClasseSubjectClasseStaffOfYearAndSection(): ERROR: ' . $e->getMessage() . '<br/>';
            return -1; //echec
        }
    }

    public static function deleteStudentNotInAClasse()
    {
        try {
            $x = DB::select(
                "DELETE FROM student WHERE student.stud_id 
                        NOT IN(SELECT student_classe.stud_id FROM student_classe)"
            );
            return 1; //success
        } catch (Exception $e) {
            //echo '<br/>MyHelper.deleteStudentNotInAClasse(): ERROR: ' . $e->getMessage() . '<br/>';
            return -1; //ECHEC
        }
    }

    public static function deleteClasseYearOfSection($sy_id, $section_id)
    {
        try {
            $x = DB::select(
                "DELETE FROM classe_year 
                        WHERE classe_year.sy_id = $sy_id AND classe_year.section_id = $section_id"
            );
            return 1; //success
        } catch (Exception $e) {
            //echo '<br/>MyHelper.deleteClasseYearOfSection(): ERROR: ' . $e->getMessage() . '<br/>';
            return -1; //ECHEC
        }
    }

    public static function deleteAClasseClasseYearOfSection($sy_id, $section_id, $classe_id)
    {
        try {
            $x = DB::select(
                "DELETE FROM classe_year 
                    WHERE classe_year.sy_id = $sy_id 
                        AND classe_year.section_id = $section_id
                        AND classe_year.classe_id = $classe_id"
            );
            return 1; //success
        } catch (Exception $e) {
            //echo '<br/>MyHelper.deleteAClasseClasseYearOfSection(): ERROR: ' . $e->getMessage() . '<br/>';
            return -1; //ECHEC
        }
    }

    public static function deleteSubjectClasseStaffOfYearAndSection($sy_id, $section_id)
    {
        try {
            $x = DB::select(
                "DELETE FROM subject_classe_staff WHERE 
                            subject_classe_staff.subject_classe_id
                                IN(SELECT subject_classe.subject_classe_id 
                                    FROM subject_classe WHERE
                                        subject_classe.sy_id = $sy_id
                                    AND subject_classe.section_id = $section_id)"
            );
            return 1; //success
        } catch (Exception $e) {
            //echo '<br/>MyHelper.deleteSubjectClasseStaffOfYearAndSection(): ERROR: ' . $e->getMessage() . '<br/>';
            return -1; //ECHEC
        }
    }

    public static function deleteAClasseSubjectClasseStaffOfYearAndSection($sy_id, $section_id, $classe_id)
    {
        try {
            $x = DB::select(
                "SELECT* FROM subject_classe_staff 
                    WHERE subject_classe_staff.subject_classe_id
                    IN(SELECT subject_classe.subject_classe_id 
                        FROM subject_classe WHERE
                            subject_classe.sy_id = $sy_id
                            AND subject_classe.section_id = $section_id
		                    AND subject_classe.classe_id = $classe_id)"
            );
            return 1; //success
        } catch (Exception $e) {
            //echo '<br/>MyHelper.deleteAClasseSubjectClasseStaffOfYearAndSection(): ERROR: ' . $e->getMessage() . '<br/>';
            return -1; //ECHEC
        }
    }

    public static function deleteClasseNotInClasseYear()
    {
        try {
            $x = DB::select(
                "DELETE FROM classe WHERE classe.classe_id 
                            NOT IN(SELECT classe_year.classe_id FROM classe_year)
                                AND classe.classe_id 
                                NOT IN(SELECT subject_classe.classe_id FROM subject_classe)"
            );
            //SI LA REFERENCE D'UNE CLASSE N'EXISTE PAS DANS classe_year mais existe dans d'autres
            //tables comme lock_sequence_classe, alors la BD est inconsistante
            return 1; //success
        } catch (Exception $e) {
            //echo '<br/>MyHelper.deleteClasseNotInClasseYear(): ERROR: ' . $e->getMessage() . '<br/>';
            return -1; //ECHEC
        }
    }

    public static function deleteSubjectClasseOfYearAndSection($sy_id, $section_id)
    {
        try {
            $x = DB::select(
                "DELETE FROM subject_classe WHERE
                        subject_classe.sy_id = $sy_id
                        AND subject_classe.section_id = $section_id"
            );
            return 1; //success
        } catch (Exception $e) {
            //echo '<br/>MyHelper.deleteSubjectClasseOfYearAndSection(): ERROR: ' . $e->getMessage() . '<br/>';
            return -1; //ECHEC
        }
    }

    public static function deleteAClasseSubjectClasseOfYearAndSection($sy_id, $section_id, $classe_id)
    {
        try {
            $x = DB::select(
                "DELETE FROM subject_classe
 	                        WHERE subject_classe.sy_id = $sy_id
   	                            AND subject_classe.section_id = $section_id
   	                            AND subject_classe.classe_id = $classe_id"
            );
            return 1; //success
        } catch (Exception $e) {
            //echo '<br/>MyHelper.deleteAClasseSubjectClasseOfYearAndSection(): ERROR: ' . $e->getMessage() . '<br/>';
            return -1; //ECHEC
        }
    }

    public static function deleteClasses($sy_id, $section_id)
    {
        try {
            $conclusion  = 1;

            $res = MyHelper::deletesSubjectClasseStaffOfYearAndSection($sy_id, $section_id); //Works 
            $conclusion = ($res < 0  || $conclusion < 0) ? -1 : 1;
            $res = MyHelper::deleteAbsenceDailyOfYearAndSection($sy_id, $section_id); //Works
            $conclusion = ($res < 0  || $conclusion < 0) ? -1 : 1;
            $res = MyHelper::deleteDisciplineOfYearAndSection($sy_id, $section_id); //Works
            $conclusion = ($res < 0  || $conclusion < 0) ? -1 : 1;
            $res = MyHelper::deleteAppreciationOfYearAndSection($sy_id, $section_id); //Works
            $conclusion = ($res < 0  || $conclusion < 0) ? -1 : 1;
            $res = MyHelper::deleteClasseDecisionParamOfYearSection($sy_id, $section_id); //Works
            $conclusion = ($res < 0  || $conclusion < 0) ? -1 : 1;
            $res = MyHelper::deleteClassifiedparamForClassOfYearAndSection($sy_id, $section_id); //Works
            $conclusion = ($res < 0  || $conclusion < 0) ? -1 : 1;
            $res = MyHelper::deleteLockSequenceClasseOfYearAndSection($sy_id, $section_id); //Works
            $conclusion = ($res < 0  || $conclusion < 0) ? -1 : 1;
            $res = MyHelper::deleteStudentSubjectOfYearAndSection($sy_id, $section_id); //Works
            $conclusion = ($res < 0  || $conclusion < 0) ? -1 : 1;
            $res = MyHelper::deleteStudCompMarkOfYearAndSection($sy_id, $section_id); //Works
            $conclusion = ($res < 0  || $conclusion < 0) ? -1 : 1;
            $res = MyHelper::deleteSujectsCompetences($sy_id, $section_id); //Works 
            $conclusion = ($res < 0  || $conclusion < 0) ? -1 : 1;
            $res = MyHelper::deleteStudentClasseOfYearAndSection($sy_id, $section_id); //Works
            $conclusion = ($res < 0  || $conclusion < 0) ? -1 : 1;
            $res = MyHelper::deleteStudentNotInAClasse(); //Works
            $conclusion = ($res < 0  || $conclusion < 0) ? -1 : 1;
            $res = MyHelper::deleteClasseYearOfSection($sy_id, $section_id); //Works        
            $conclusion = ($res < 0  || $conclusion < 0) ? -1 : 1;
            $res = MyHelper::deleteSubjectClasseStaffOfYearAndSection($sy_id, $section_id); //Works
            $conclusion = ($res < 0  || $conclusion < 0) ? -1 : 1;
            $res = MyHelper::deleteSubjectClasseOfYearAndSection($sy_id, $section_id); //Works
            $conclusion = ($res < 0  || $conclusion < 0) ? -1 : 1;
            $res = MyHelper::deleteClasseNotInClasseYear(); //Works 
            return $conclusion; //code...
        } catch (Exception  $e) {
            echo "<br/>" . $e->getMessage() . "<br>";
        }
        return -2;
    }

    public static function deleteAClasse($sy_id, $section_id, $classe_id): int
    {
        //ATTENTION CETTE METHODE SUPPRIME LA CLASSE DANS L'ANNEE EN COURS AVEC TOUTES SES REF DE LA DITE ANNEE
        //SI CERTAIN REF (classe_id) existent dans une autre annee dans les tables comme
        //"lock_sequence_classe"; "classe_year" ... La classe ne sera plu visible dans l'année ayant pour
        //$sy_id mais existera dans d'autres années. ON NE PEUT SUPPRIMER UNE ANNEE ENTIEREMENENT QU'EN
        //TOUTES SES REF (classe_id) dans toutes les school_year (sy_id)
        $conclusion = 1;
        $res = MyHelper::deleteAClasseDisciplineOfYearAndSection($sy_id, $section_id, $classe_id); //WORKS   
        $conclusion = ($res < 0  || $conclusion < 0) ? -1 : 1;
        $res = MyHelper::deleteAClasseAbsenceDailyOfYearAndSection($sy_id, $section_id, $classe_id); //WORKS   
        $conclusion = ($res < 0  || $conclusion < 0) ? -1 : 1;
        $res = MyHelper::deleteAClasseAppreciationOfYearAndSection($sy_id, $section_id, $classe_id); //WORKS
        $conclusion = ($res < 0  || $conclusion < 0) ? -1 : 1;
        $res = MyHelper::deleteAClasseDecisionParamOfYearSection($sy_id, $section_id, $classe_id); //WORKS
        $conclusion = ($res < 0  || $conclusion < 0) ? -1 : 1;
        $res = MyHelper::deletesAClasseSubjectClasseStaffOfYearAndSection($sy_id, $section_id, $classe_id); //WORKS
        $conclusion = ($res < 0  || $conclusion < 0) ? -1 : 1;
        $res = MyHelper::deleteClassifiedparamForClassOfYearAndSection2($sy_id, $section_id, $classe_id); //WORKS
        $conclusion = ($res < 0  || $conclusion < 0) ? -1 : 1;
        $res = MyHelper::deleteLockSequenceClasseOfYearAndSection2($sy_id, $section_id, $classe_id); //WORKS
        $conclusion = ($res < 0  || $conclusion < 0) ? -1 : 1;

        $res = MyHelper::deleteAClasseStudentSubjectOfYearAndSection($sy_id, $section_id, $classe_id); //WORKS; 
        $conclusion = ($res < 0  || $conclusion < 0) ? -1 : 1;
        $res = MyHelper::deleteAClasseStudCompMarkOfYearAndSection($sy_id, $section_id, $classe_id); //Works
        $conclusion = ($res < 0  || $conclusion < 0) ? -1 : 1;
        $res = MyHelper::deleteAClasseSubjectCompetencesOfYearAndSection($sy_id, $section_id, $classe_id); //WORKS
        $conclusion = ($res < 0  || $conclusion < 0) ? -1 : 1;

        $res = MyHelper::deleteAClasseStudentClasseOfYearAndSection($sy_id, $section_id, $classe_id); //Works
        $conclusion = ($res < 0  || $conclusion < 0) ? -1 : 1;
        $res = MyHelper::deleteStudentNotInAClasse(); //Works
        $conclusion = ($res < 0  || $conclusion < 0) ? -1 : 1;
        $res = MyHelper::deleteAClasseClasseYearOfSection($sy_id, $section_id, $classe_id); //Works
        $conclusion = ($res < 0  || $conclusion < 0) ? -1 : 1;
        $res = MyHelper::deleteAClasseSubjectClasseStaffOfYearAndSection($sy_id, $section_id, $classe_id); //Works
        $conclusion = ($res < 0  || $conclusion < 0) ? -1 : 1;
        $res = MyHelper::deleteAClasseSubjectClasseOfYearAndSection($sy_id, $section_id, $classe_id); //Works
        $conclusion = ($res < 0  || $conclusion < 0) ? -1 : 1;
        $res = MyHelper::deleteClasseNotInClasseYear(); //Works 
        $conclusion = ($res < 0  || $conclusion < 0) ? -1 : 1;
        return $conclusion; //1-->success; (-1)--> Error occurs classe not deleted and some ref not deleted
    }
    public static function validateStr($str)
    {
        for ($i = 0; $i < strlen($str); $i++) {
            $res = str_contains(MyHelper::$allowedClasseChars, $str[$i]);
            //echo "$str[$i]  [$res]<br/>";
            if (!$res) {
                return false;
            }
        }
        return true;
    }

    public static function validateText($str, $allowdChars)
    {
        for ($i = 0; $i < strlen($str); $i++) {
            $res = str_contains($allowdChars, $str[$i]);
            //echo "$str[$i]  [$res]<br/>";
            if (!$res) {
                return false;
            }
        }
        return true;
    }

    public static function findRole($type)
    {
        /*
            1-superAdministrtateur | 
            2-Top management (proviseur directeur ..) | 
            3-SG | 
            4-bursar | 
            5-Teacher(Simple enseignant) | 
            6-Parent | 
            7-Student | 
            8-Censeur
        */
        switch ($type) {
            case 1:
                return "ADMIN";
            case 2:
                return "TOP_MANAGEMENT";
            case 3:
                return "SG";
            case 4:
                return "BURSAR";
            case 5:
                return "TEACHER";
            case 6:
                return "PARENT";
            case 7:
                return "STUDENT";
            case 8:
                return "CENSEUR";
            default:
                return "Unknown";
        }
    }

    // Uses the file cache store explicitly (not the "database" store configured by default),
    // since the DB default connection is switched per-school on every request and can't be
    // relied on to hold a stable, always-present `cache` table.
    public static function blacklistToken($jti, $ttlSeconds)
    {
        if (!$jti || $ttlSeconds <= 0) {
            return;
        }
        Cache::store('file')->put('jwt_blacklist:' . $jti, true, $ttlSeconds);
    }

    public static function isTokenBlacklisted($jti)
    {
        if (!$jti) {
            return false;
        }
        return Cache::store('file')->has('jwt_blacklist:' . $jti);
    }
}
