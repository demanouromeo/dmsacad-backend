<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\SubjectClasse;

class MySubjectHelper extends Controller
{
    
    public static function subjectClasseList($sy_id, $classe_id){     
        //CHECK IF A SUBJECT IS TAUGHT IN A CLASSE WITHIN A GIVEN SCHOOL YEAR
        $res = SubjectClasse::where('sy_id', $sy_id)
                ->where('classe_id', $classe_id)
                ->get();
         
        if (count($res) > 0) {
            return $res;
        } else {
            return null;
        }
    }

    public static function checkCompetenceText($sy_id, $classe_id, $subject_id, $term, $text){     
        //CHECK IF A SUBJECT IS TAUGHT IN A CLASSE WITHIN A GIVEN SCHOOL YEAR
        $res = DB::select("SELECT * FROM subject_competences WHERE sy_id = $sy_id                         
            AND classe_id = $classe_id
            AND `subject_id` = $subject_id 
            AND `term_id` = $term
            AND competence_text = ?", [$text]);
         
        if (count($res) > 0) {
            return 1; //true;
        } else {
            return 0; //false
        }
    }
    public static function checkSujectClass($sy_id, $classe_id, $subject_id)
    {
        //CHECK IF A SUBJECT IS TAUGHT IN A CLASSE WITHIN A GIVEN SCHOOL YEAR
        $res = DB::select("SELECT * FROM `subject_classe` 
        WHERE `sy_id` = $sy_id AND `subject_id` = $subject_id 
        and classe_id = $classe_id;");
        if (count($res) > 0) {
            return 1; //true;
        } else {
            return 0; //false
        }
    }
    public static function getSubjectsOfYearOfSection($sy_id, $section_id)
    { //echo"<br/>sy_id". $sy_id ."<br>subject_id". $section_id;
        try {
            $groupeArray = DB::select("SELECT subject_id, subject_title FROM subject 
	                    WHERE subject.subject_id 
                        IN(SELECT subject_year.subject_id FROM subject_year 
			                WHERE subject_year.sy_id  = $sy_id 
			                AND subject_year.section_id = $section_id)   	
                        ORDER BY subject.subject_title");
            return $groupeArray;
        } catch (Exception $e) {
            die('<br/>MyHelper.getSubjectsOfYearOfSection(): ERROR: ' . $e->getMessage() . '<br/>');
        }
    }

    public static function deleteAStudSubOfYearAndSection($sy_id, $section_id, $subject_id)
    {
        try {
            $x = DB::select(
                "DELETE FROM student_subject WHERE student_subject.subject_id = $subject_id 
                            AND student_subject.subject_id IN(SELECT subject_year.subject_id 
                            FROM subject_year WHERE subject_year.sy_id = $sy_id
                            AND subject_year.section_id = $section_id)"
            );
            return 1; //success
        } catch (Exception $e) {
            echo '<br/>MySubjectHelper.deleteAStudSubOfYearAndSection(): ERROR: ' . $e->getMessage() . '<br/>';
            //echo "<br/>sy_id:$sy_id<br/>section_id: $section_id<br/>classe_id:$classe_id<br/>";
            return -1; //ECHEC
        }
    }

    public static function deleteAStudSubOfYearAndSection2($sy_id, $section_id, $subject_id, $classe_id)
    {   //NO NEED TO CHECH THE SECTION SINCE THE CLASSE IS ALREADY A CLASSE OF THAT SECTION
        $section_id  = $section_id + 0;
        try {
            $x = DB::select(
                "DELETE FROM student_subject WHERE subject_id = $subject_id
                            AND sy_id = $sy_id AND stud_id IN(SELECT student_classe.stud_id 
                            FROM student_classe WHERE student_classe.classe_id = $classe_id)"
            );
            return 1; //success
        } catch (Exception $e) {
            echo '<br/>MySubjectHelper.deleteAStudSubOfYearAndSection2(): ERROR: ' . $e->getMessage() . '<br/>';
            //echo "<br/>sy_id:$sy_id<br/>section_id: $section_id<br/>classe_id:$classe_id<br/>";
            return -1; //ECHEC
        }
    }

    public static function deleteAStudCompOfYearAndSection($sy_id, $section_id, $subject_id)
    {
        try {
            $x = DB::select(
                "DELETE FROM stud_comp_mark WHERE stud_comp_mark.subject_id = $subject_id 
                            AND stud_comp_mark.subject_id IN(SELECT subject_year.subject_id 
                            FROM subject_year WHERE subject_year.sy_id = $sy_id 
                            AND subject_year.section_id = $section_id)"
            );
            return 1; //success
        } catch (Exception $e) {
            echo '<br/>MySubjectHelper.deleteAStudCompOfYearAndSection(): ERROR: ' . $e->getMessage() . '<br/>';
            //echo "<br/>sy_id:$sy_id<br/>section_id: $section_id<br/>classe_id:$classe_id<br/>";
            return -1; //ECHEC
        }
    }

    public static function deleteAStudCompOfYearAndSection2($sy_id, $section_id, $subject_id, $classe_id)
    {   //NO NEED TO USE section_id SINCE THE CLASSE IS ALREADY A CLASSE OF THE SECTION
        $section_id = $section_id + 0;
        try {
            //echo "<br/>Deleting the competence OF subject_id: $subject_id | classe_id: $classe_id | sy_id: $sy_id";
            $x = DB::select(
                "DELETE FROM stud_comp_mark WHERE subject_id = $subject_id
                        AND sy_id = $sy_id AND stud_id IN(SELECT student_classe.stud_id 
                        FROM student_classe WHERE student_classe.classe_id = $classe_id)"
            );
            //echo "<br/>Competence deleted";
            return 1; //success
        } catch (Exception $e) {
            echo '<br/>MySubjectHelper.deleteAStudCompOfYearAndSection2(): ERROR: ' . $e->getMessage() . '<br/>';
            //echo "<br/>sy_id:$sy_id<br/>section_id: $section_id<br/>classe_id:$classe_id<br/>";
            return -1; //ECHEC
        }
    }

    public static function deleteASubjectCompOfYearAndSection($sy_id, $section_id, $subject_id)
    {
        try {
            $x = DB::select(
                "DELETE FROM subject_competences 
                            WHERE subject_competences.sy_id = $sy_id 
                            AND subject_competences.section_id = $section_id
                            AND subject_competences.subject_id = $subject_id"
            );
            return 1; //success
        } catch (Exception $e) {
            echo '<br/>MySubjectHelper.deleteASubjectCompOfYearAndSection(): ERROR: ' . $e->getMessage() . '<br/>';
            //echo "<br/>sy_id:$sy_id<br/>section_id: $section_id<br/>classe_id:$classe_id<br/>";
            return -1; //ECHEC
        }
    }

    public static function deleteASubjectCompOfYearAndSection2($sy_id, $section_id, $subject_id, $classe_id)
    {
        try {
            $x = DB::select(
                "DELETE FROM subject_competences 
                        WHERE subject_competences.sy_id = $sy_id 
                            AND subject_competences.section_id = $section_id
                            AND subject_competences.subject_id = $subject_id
                            AND subject_competences.classe_id = $classe_id"
            );
            return 1; //success
        } catch (Exception $e) {
            echo '<br/>MySubjectHelper.deleteASubjectCompOfYearAndSection2(): ERROR: ' . $e->getMessage() . '<br/>';
            //echo "<br/>sy_id:$sy_id<br/>section_id: $section_id<br/>classe_id:$classe_id<br/>";
            return -1; //ECHEC
        }
    }

    public static function deleteASubjectClasseStaffOfYearAndSection($sy_id, $section_id, $subject_id)
    {
        try {
            $x = DB::select(
                "DELETE FROM subject_classe_staff 
                        WHERE subject_classe_staff.subject_classe_id 
                            IN(SELECT subject_classe.subject_classe_id from subject_classe 
                                WHERE subject_classe.sy_id = $sy_id 
	                            AND subject_classe.section_id = $section_id 
	                            AND subject_classe.subject_id = $subject_id)"
            );
            return 1; //success
        } catch (Exception $e) {
            echo '<br/>MySubjectHelper.deleteASubjectClasseStaffOfYearAndSection(): ERROR: ' . $e->getMessage() . '<br/>';
            //echo "<br/>sy_id:$sy_id<br/>section_id: $section_id<br/>classe_id:$classe_id<br/>";
            return -1; //ECHEC
        }
    }

    public static function deleteASubjectClasseStaffOfYearAndSection2($sy_id, $section_id, $subject_id, $classe_id)
    {
        try {
            $x = DB::select(
                "DELETE FROM subject_classe_staff 
                WHERE subject_classe_staff.subject_classe_id 
                    IN(SELECT subject_classe.subject_classe_id from subject_classe 
                        WHERE subject_classe.sy_id = $sy_id 
	                        AND subject_classe.section_id = $section_id
	                        AND subject_classe.subject_id = $subject_id
	                        AND subject_classe.classe_id = $classe_id)"
            );
            return 1; //success
        } catch (Exception $e) {
            echo '<br/>MySubjectHelper.deleteASubjectClasseStaffOfYearAndSection2(): ERROR: ' . $e->getMessage() . '<br/>';
            //echo "<br/>sy_id:$sy_id<br/>section_id: $section_id<br/>classe_id:$classe_id<br/>";
            return -1; //ECHEC
        }
    }

    public static function deleteASubjectClasseOfYearAndSection($sy_id, $section_id, $subject_id)
    {
        try {
            $x = DB::select(
                "DELETE FROM subject_classe WHERE subject_classe.sy_id = $sy_id
                            AND subject_classe.section_id = $section_id 
                            AND subject_classe.subject_id = $subject_id"
            );
            return 1; //success
        } catch (Exception $e) {
            echo '<br/>MySubjectHelper.deleteASubjectClasseOfYearAndSection(): ERROR: ' . $e->getMessage() . '<br/>';
            //echo "<br/>sy_id:$sy_id<br/>section_id: $section_id<br/>classe_id:$classe_id<br/>";
            return -1; //ECHEC
        }
    }

    public static function deleteASubjectClasseOfYearAndSection2($sy_id, $section_id, $subject_id, $classe_id)
    {
        try {
            $x = DB::select(
                "DELETE FROM subject_classe WHERE subject_classe.sy_id = $sy_id
                            AND subject_classe.section_id = $section_id 
                            AND subject_classe.subject_id = $subject_id
                            AND subject_classe.classe_id = $classe_id"
            );
            return 1; //success
        } catch (Exception $e) {
            echo '<br/>MySubjectHelper.deleteASubjectClasseOfYearAndSection2(): ERROR: ' . $e->getMessage() . '<br/>';
            //echo "<br/>sy_id:$sy_id<br/>section_id: $section_id<br/>classe_id:$classe_id<br/>";
            return -1; //ECHEC
        }
    }

    public static function deleteAabsDailyAndSection($sy_id, $section_id, $subject_id)
    {
        try {
            $x = DB::select(
                "DELETE FROM absence_daily WHERE absence_daily.subject_id = $subject_id
                        AND absence_daily.subject_id IN(SELECT subject_year.subject_id 
                            FROM subject_year WHERE subject_year.sy_id = $sy_id
                            AND subject_year.section_id = $section_id)"
            );
            return 1; //success
        } catch (Exception $e) {
            echo '<br/>MySubjectHelper.deleteAabsDailyAndSection(): ERROR: ' . $e->getMessage() . '<br/>';
            //echo "<br/>sy_id:$sy_id<br/>section_id: $section_id<br/>classe_id:$classe_id<br/>";
            return -1; //ECHEC
        }
    }
    public static function deleteAabsDailyAndSection2($sy_id, $section_id, $subject_id, $classe_id)
    {
        try {
            $x = DB::select(
                "DELETE FROM absence_daily WHERE absence_daily.subject_id = $subject_id
                        AND absence_daily.subject_id IN(SELECT subject_classe.subject_id 
                        FROM subject_classe WHERE subject_classe.classe_id = $classe_id
                        AND subject_classe.sy_id = $sy_id
                        AND subject_classe.section_id = $section_id)"
            );
            return 1; //success
        } catch (Exception $e) {
            echo '<br/>MySubjectHelper.deleteAabsDailyAndSection2(): ERROR: ' . $e->getMessage() . '<br/>';
            //echo "<br/>sy_id:$sy_id<br/>section_id: $section_id<br/>classe_id:$classe_id<br/>";
            return -1; //ECHEC
        }
    }
    public static function deleteASubjectYearOfYearAndSection($sy_id, $section_id, $subject_id)
    {
        try {
            $x = DB::select(
                "DELETE FROM subject_year WHERE subject_year.sy_id = $sy_id
                            AND subject_year.section_id = $section_id 
                            AND subject_year.subject_id = $subject_id"
            );
            return 1; //success
        } catch (Exception $e) {
            echo '<br/>MySubjectHelper.deleteASubjectYearOfYearAndSection(): ERROR: ' . $e->getMessage() . '<br/>';
            //echo "<br/>sy_id:$sy_id<br/>section_id: $section_id<br/>classe_id:$classe_id<br/>";
            return -1; //ECHEC
        }
    }

    public static function deleteASubjetNotInSubejectYear()
    {
        try {
            $x = DB::select(
                "DELETE FROM subject WHERE subject.subject_id
                    NOT IN(SELECT subject_year.subject_id FROM subject_year)"
            );
            //SI LA REFERENCE D'UNE MATIERE N'EXISTE PAS DANS subject_year mais existe dans d'autres
            //tables comme absence_daily, subject_classe, alors la BD est inconsistante
            //CETTE METHOD SUPPRIMERA LA CLASSE DEFINITIVEMENT SI ELLE N'A AUCUNE REF. 
            //DANS LE CAS CONTRAIRE, ELLE VA SUPPRIMER UNIQUEMENT DANS L'ANNEE EN COURS
            return 1; //success
        } catch (Exception $e) {
            //echo '<br/>MyHelper.deleteASubjetNotInSubejectYear(): ERROR: ' . $e->getMessage() . '<br/>';
            return -1; //ECHEC
        }
    }

    public static function deleteASubject($sy_id, $section_id, $subject_id)
    {
        $conclusion = 1;
        $res = MySubjectHelper::deleteAStudSubOfYearAndSection($sy_id, $section_id, $subject_id); //WORKS
        $conclusion = ($res < 0  || $conclusion < 0) ? -1 : 1;
        $res = MySubjectHelper::deleteAStudCompOfYearAndSection($sy_id, $section_id, $subject_id); //WORKS
        $conclusion = ($res < 0  || $conclusion < 0) ? -1 : 1;
        $res = MySubjectHelper::deleteASubjectCompOfYearAndSection($sy_id, $section_id, $subject_id); //WORKS
        $conclusion = ($res < 0  || $conclusion < 0) ? -1 : 1;
        $res = MySubjectHelper::deleteASubjectClasseStaffOfYearAndSection($sy_id, $section_id, $subject_id); //WORKS
        $conclusion = ($res < 0  || $conclusion < 0) ? -1 : 1;
        $res = MySubjectHelper::deleteASubjectClasseOfYearAndSection($sy_id, $section_id, $subject_id); //WORKS
        $conclusion = ($res < 0  || $conclusion < 0) ? -1 : 1;
        $res = MySubjectHelper::deleteAabsDailyAndSection($sy_id, $section_id, $subject_id); //WORKS
        $conclusion = ($res < 0  || $conclusion < 0) ? -1 : 1;
        $res = MySubjectHelper::deleteASubjectYearOfYearAndSection($sy_id, $section_id, $subject_id); //WORKS
        $conclusion = ($res < 0  || $conclusion < 0) ? -1 : 1;
        MySubjectHelper::deleteASubjetNotInSubejectYear();

        return $conclusion;
    }

    public static function deleteASubjectOfAClasse($sy_id, $section_id, $subject_id, $classe_id)
    {
        $conclusion = 1;
        $res = MySubjectHelper::deleteAStudSubOfYearAndSection2($sy_id, $section_id, $subject_id, $classe_id); //WORKS
        $conclusion = ($res < 0  || $conclusion < 0) ? -1 : 1;
        $res = MySubjectHelper::deleteAStudCompOfYearAndSection2($sy_id, $section_id, $subject_id, $classe_id); //WORKS
        $conclusion = ($res < 0  || $conclusion < 0) ? -1 : 1;
        $res = MySubjectHelper::deleteASubjectCompOfYearAndSection2($sy_id, $section_id, $subject_id, $classe_id); //WORKS
        $conclusion = ($res < 0  || $conclusion < 0) ? -1 : 1;
        $res = MySubjectHelper::deleteASubjectClasseStaffOfYearAndSection2($sy_id, $section_id, $subject_id, $classe_id); //WORKS
        $conclusion = ($res < 0  || $conclusion < 0) ? -1 : 1;
        $res = MySubjectHelper::deleteASubjectClasseOfYearAndSection2($sy_id, $section_id, $subject_id, $classe_id); //WORKS
        $conclusion = ($res < 0  || $conclusion < 0) ? -1 : 1;
        $res = MySubjectHelper::deleteAabsDailyAndSection2($sy_id, $section_id, $subject_id, $classe_id); //WORKS
        $conclusion = ($res < 0  || $conclusion < 0) ? -1 : 1;
        MySubjectHelper::deleteASubjetNotInSubejectYear();

        return $conclusion;
    }

    public static function deleteSubjects($sy_id, $section_id): int
    {
        //echo "sy_id: $sy_id   section_id: $section_id";
        try {
            $k = 1; //Assuming all deleted
            $subjects = MySubjectHelper::getSubjectsOfYearOfSection($sy_id, $section_id);
            //echo count($subjects);      
            foreach ($subjects as $sub) {
                //echo $sub->subject_id." ".$sub->subject_title."<br/>";
                $subject_id = $sub->subject_id;
                $res = MySubjectHelper::deleteASubject($sy_id, $section_id, $subject_id);
                if ($res < 0) {
                    $k = -1;
                }
            }
            return $k; //1-> success; -1 -> Echec; -2 -> Exception occurs
        } catch (Exception $e) {
            echo "<br>" . $e->getMessage() . "<br/>";
            return -2;
        }
    }
   
}
