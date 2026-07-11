<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\LockSequence;
use App\Models\Patient;

class TestController extends Controller
{
    
    public $schools = array(
            //"CES_DE_DABAYE", 
            //"CES_DE_KILWO",
            "CES_DE_LDIRI",
            "CES_DE_MOUROUM",
            "CES_DE_SEDEK",
            "CES_DE_YOLDEO",
            "CES_DE_ZIMADO",
            "CETIC_DE_BOGO",
            "CETIC_DE_DARGALA",
            "CETIC_DE_DOUBANE",
            "CETIC_DE_GADOUA", 
            //"CETIC_DE_GODOLA",
            "CETIC_DE_MAKARY",
            "COLLEGE_DE_LA_FRATERNITE",
            "COLBIPPOLFOSH",
            "ENIEG_DE_GUIDER",
            "ENIEG_BILINGUE_DE_MAROUA", 
            "GBHS_MINAWAO",
            "GBTHS_MEWOULOU", 
            "LB_BOGO",
            //"LB_GUISSA",
            //"LB_KARTOUA",
            "LB_KOZA",
            "LB_MAKALINGAI",
            "LB_ZAMAI",
            //"LT_BIDZAR",
            "LT_DOUALARE",
            "LT_GAZAWA",
            "LT_KOZA",
            "LT_LOGONE_BIRNI",
            "LT_MERI",
            "LT_MINDIF",
            "LT_MORA",
            "LYCEE_DE_BALAZA_ALCALI",
            "LYCEE_CLASSIQUE_DE_MAROUA", 
            //"LYCEE_DE_BIDZAR",
            "LYCEE_DE_DOGBA",
            "LYCEE_DE_DOMO",
            "LYCEE_DE_DOUALARE",
            "LYCEE_DE_GABOUA",
            "LYCEE_DE_GODOLA",
            "LYCEE_DE_GUIDER",
            "LYCEE_DE_HARDE_MAROUA",
            "LYCEE_DE_HOULA",
            "LYCEE_DE_KAHEO",
            "LYCEE_DE_KALLIAO",
            "LYCEE_DE_KOTRABA",
            "LYCEE_DE_LOGONE_BIRNI",
            "LYCEE_DE_MAKABAYE",
            "LYCEE_DE_MAROUA_SALAK",
            "LYCEE_DE_MASSAKAL",
            "LYCEE_DE_MOGOM",
            //"LYCEE_DE_MEME",
            "LYCEE_DE_MERI",
            //"LYCEE_DE_MESKINE",
            "LYCEE_DE_MOKIO",
            "LYCEE_DE_PITOA",
            "LYCEE_DE_WAZA", 
            "TEST",
            "TEST_PLAY"
    );
     
        
    public function getData(Request $request){
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $year = $request->input("year");
        $override = $request->input("override");
        $classe_id = $request->input("classe_id");

        $stList = json_decode($data, true);
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);

        $n = count($stList);
        echo "Sent[$data_size]  <--> Received[$n]<br/><br/>";
        $k = 1;
        foreach ($stList as $st) {
            $name = $st["name"]; 
            $id = $st["stud_id"];
            echo "[$k]  <--> name: $name   stud_id: $id<br/>";
            $k++;
        }
    }
    
    public function test(Request $request)
    {
        $connection = $request->input("connection");
        //$year = $request->input("year");
        $sy_id = $request->input("sy_id");
        $classe_id = $request->input("classe_id");
        $section_id = $request->input("section_id"); 
        config(["database.default" => $connection]);
        /*
        echo "Conn: $connection -- Year: $year <br/>";
        try {
            $x = DB::select("DELETE from stud_parent WHERE stud_parent.p_id NOT IN(SELECT student.p_id FROM student)");
            //6 --> represents parents account type
            $x = DB::select("DELETE from account WHERE account.acc_id not IN(SELECT stud_parent.acc_id from stud_parent) and type = 6;");
            echo 1; //Success
        } catch (Exception $e) {
            echo -1;//Error occurs
        }        
        //echo "X length: ".count( $x )."";
        //foreach ($x as $parent) {
        //    echo "$parent->p_id <br>";
        //}
        */
        /*   
         
        
        
      
        $res = MyHelper::deleteSubjectClasseOfYearAndSection(5, 1); //Works
        $conclusion = $conclusion * $res;
        $res = MyHelper::deleteClasseNotInClasseYear(); //Works 
        echo $conclusion;
        */

        /*$bool = MyHelper::validateStr("Hello");
        echo "BOOL: $bool";
        */         
        //$res = MyHelper::deleteAClasse(  "$sy_id", $section_id, $classe_id);      
        //$res = MyHelper::getGroupesOfYearOfSection(5, 1);
        //$res = MySubjectHelper::getSubjectsOfYearOfSection(5, 1);
        //return response()->json( $res, 200);
        //$res = MySubjectHelper::deleteASubject(5,1,3);
        $res = MySubjectHelper::deleteSubjects(5, 1);
        $students = DB::select("SELECT* FROM student WHERE student.stud_id 
                IN(SELECT student_classe.stud_id FROM student_classe WHERE 
                    student_classe.sy_id = 5 AND student_classe.classe_id = 1)
                    ORDER BY student.name");
        $k = 1;
        foreach ($students as $student) {
            $stud_id = $student->stud_id; //$student["stud_id"];
            $name = $student->name;//$students["name"];
            echo "$k --> $stud_id   $name <br/>";
            $k++;
        }

        echo "$res";
    }
    
    public function lockTerms()
    {        
        for ($k = 0; $k < count($this->schools); $k++) {
            $sy_id = 5;
            $connection = $this->schools[$k];
            echo ("processing '$connection' ..... ");
            config(["database.default" => $connection]);

            //----- SEQ 1;
            try {
                $lockRef = LockSequence::where('sy_id', $sy_id)->first();
                if (!is_null($lockRef)) {
                    $lockRef->is_blocked = 1;
                    $lockRef->update();
                } else {
                    $new = new LockSequence();
                    $new->sy_id = $sy_id;
                    $new->is_blocked = 1;
                    $new->seq = 1;
                    $new->save();
                }
            } catch (Exception $th) {
            }

            //----- SEQ 2;
            try {
                $lockRef = LockSequence::where('sy_id', $sy_id)
                    ->where('seq', 2)
                    ->first();
                if (!is_null($lockRef)) {
                    $lockRef->is_blocked = 1;
                    $lockRef->update();
                } else {
                    $new = new LockSequence();
                    $new->sy_id = $sy_id;
                    $new->is_blocked = 1;
                    $new->seq = 2;
                    $new->save();
                }
            } catch (Exception $th) {
            }

            //----- SEQ 3;
            try {
                $lockRef = LockSequence::where('sy_id', $sy_id)
                    ->where('seq', 3)
                    ->first();
                if (!is_null($lockRef)) {
                    $lockRef->is_blocked = 1;
                    $lockRef->update();
                } else {
                    $new = new LockSequence();
                    $new->sy_id = $sy_id;
                    $new->is_blocked = 1;
                    $new->seq = 3;
                    $new->save();
                }
            } catch (Exception $th) {
            }
            echo " Done. <br/>";
        } //END MAIN FOR
    }
    
    public function addCenseurToClasses()
    {
        for ($k = 0; $k < count($this->schools); $k++) {
            $sy_id = 5;
            $connection = $this->schools[$k];
            echo ("processing '$connection' ..... ");
            config(["database.default" => $connection]);

            //----- SEQ 1;
            try {
                DB::select("ALTER TABLE `classe_year` ADD `vp_id` INT NULL COMMENT 'identifiant du censeur de la classe'");
                DB::select("ALTER TABLE `classe_year` ADD FOREIGN KEY (`vp_id`) REFERENCES `staff` (`staff_id`)");
            } catch (Exception $e) {
                echo "<br/> ERROR WHEN PROCESSING [$connection]<br/>";
                echo "" . $e->getMessage() . "<br/><br/>";
            }

             
 
            echo " Done. <br/>";
        } //END MAIN FOR
    }
    
    public function updateStudentClasseStructure()
    {
        for ($k = 0; $k < count($this->schools); $k++) {
            $sy_id = 5;
            $connection = $this->schools[$k];
            echo ("processing '$connection' ..... ");
            config(["database.default" => $connection]);

            //----- SEQ 1;
            try {
                /*
                DB::select("ALTER TABLE `student_classe` DROP `isMannullalyClassified`;");
                DB::select("ALTER TABLE `student_classe` DROP `isMannullalyDismissed`;");
                DB::select("ALTER TABLE `student_classe` DROP `mustRepeat`;");
                DB::select("ALTER TABLE `student_classe` DROP `codeExclusion`;");

                DB::select("ALTER TABLE student_classe ADD isMannullalyClassified tinyint(1) NOT NULL DEFAULT '2' COMMENT '0=NC; 1=C; 2=AUTO' AFTER position_classe;");
                DB::select("ALTER TABLE student_classe ADD isMannullalyDismissed tinyint(1) NOT NULL DEFAULT '2' COMMENT '0=NOT exclu; 1=exclu; 2=AUTO' AFTER isMannullalyClassified;");
                DB::select("ALTER TABLE student_classe ADD mustRepeat tinyint(1) NOT NULL DEFAULT '2' COMMENT '0=Not repeating; 1=repeating; 2=AUTO' AFTER isMannullalyDismissed;");
                DB::select("ALTER TABLE student_classe ADD codeExclusion tinyint(1) NOT NULL DEFAULT '0' COMMENT 'CODE RAISON EXCLUSION: 0=VIDE eleve pas exclu; 1=Age; 2=Conduite; 3=Travail; 4=Ne peut trippler; 5=Abandon; 6=Insolvable' AFTER promuEn;"); 

                
                DB::select("ALTER TABLE student_classe ADD dismissalReason VARCHAR(100) NOT NULL DEFAULT '' COMMENT 'Pour expliquer pourquoi l\'eleve est renvoyé' AFTER mustRepeat ");
                DB::select("ALTER TABLE student_classe ADD promuEn INT NULL COMMENT 'Store the id of the classe student is promoted to/promu en' AFTER dismissalReason;");
                               */
                DB::select("ALTER TABLE `student_classe` ADD `basculated_classe_id` TINYINT NOT NULL DEFAULT '0' 
                                COMMENT 'Ce champ stoque l\'ID de la classe dans laquelle l\'élève est basculé' AFTER `basculated`;");
            } catch (Exception $e) {
                echo "<br/> ERROR WHEN PROCESSING [$connection]<br/>";
                echo "" . $e->getMessage() . "<br/><br/>";
            }
 
            echo " Done. <br/>";
        } //END MAIN FOR
    }

    public function updateClasseYearStructure()
    {
        for ($k = 0; $k < count($this->schools); $k++) {
            $sy_id = 5;
            $connection = $this->schools[$k];
            echo ("processing '$connection' ..... ");
            config(["database.default" => $connection]);

            try {
                
                DB::select("ALTER TABLE `classe_year` ADD `avgDismissalTh` FLOAT NOT NULL DEFAULT '7.5' COMMENT 'l\'élève exclu si moy. < avgDismissalTh' AFTER `vp_id`;");
                DB::select("ALTER TABLE `classe_year` ADD `repeatUB` FLOAT NOT NULL DEFAULT '9' COMMENT 'l\'élève redouble si moy. dans[avgDismissalTh, repeatUB[' AFTER `avgDismissalTh`;");
                DB::select("ALTER TABLE `classe_year` ADD `passMark` FLOAT NOT NULL DEFAULT '10' COMMENT 'l\'élève admis exceptionnelement si moy. dans[repeatUB, passMark[' AFTER `repeatUB`;");
                
                //DB::select("ALTER TABLE `classe_year` ADD `totalAbsTh` INT NOT NULL DEFAULT '40' COMMENT 'exclu si total abs. > totalAbsTh' AFTER `passMark`;");
                //DB::select("ALTER TABLE `classe_year` ADD `totalExclusionTh` INT NOT NULL DEFAULT '8' COMMENT 'exclu si on totalise plus de \'totalExclusionTh(8)\' jours d\'exclusion' AFTER `totalAbsTh`;");
                
                //DB::select("ALTER TABLE `classe_year` DROP `avgDismissalTh`");
                //DB::select("ALTER TABLE `classe_year` DROP `repeatUB`");
                //DB::select("ALTER TABLE `classe_year` DROP `passMark`");
            } catch (Exception $e) {
                echo "<br/> ERROR WHEN PROCESSING [$connection]<br/>";
                echo "" . $e->getMessage() . "<br/><br/>";
            }

            echo " Done. <br/>";
        } //END MAIN FOR
    }
    
    public function add2627()
    {
        for ($k = 0; $k < count($this->schools); $k++) {            
            $connection = $this->schools[$k];
            echo ("processing '$connection' ..... ");
            config(["database.default" => $connection]);

            try {
                DB::select("INSERT INTO `school_year` (`sy_id`, `year`, `description`, `is_current`) VALUES ('7', '2026/2027', '', '0')");
                 
            } catch (Exception $e) {
                echo "<br/> ERROR WHEN PROCESSING [$connection]<br/>";
                echo "" . $e->getMessage() . "<br/><br/>";
            }

            echo "Done. <br/>";
        } //END MAIN FOR
    }
    
    
    public static function findOptions($sy_id)
    {
        $res = DB::select("SELECT*FROM filiere_year where filiere_year.sy_id = $sy_id");
        return $res;
    }

    public static function findSpecialities($sy_id)
    {
        $res = DB::select("SELECT*FROM speciality_year where speciality_year.sy_id = $sy_id");
        return $res;
    }

    public static function findSections($sy_id)
    {
        $res = DB::select("SELECT*FROM section_year where section_year.sy_id = $sy_id");
        return $res;
    }

    public static function findClasses($sy_id)
    {
        $res = DB::select("SELECT*FROM classe_year where classe_year.sy_id = $sy_id");
        return $res;
    }
    public static function findStaffs($sy_id)
    {
        $res = DB::select("SELECT*FROM staff_year where staff_year.sy_id = $sy_id");
        return $res;
    }  
    
    public static function findGroups($sy_id)
    {
        $res = DB::select("SELECT*FROM groupe_year where groupe_year.sy_id = $sy_id");
        return $res;
    }

    public static function findSubjects($sy_id)
    {
        $res = DB::select("SELECT*FROM subject_year where subject_year.sy_id = $sy_id");
        return $res;
    }

    public static function findSubjectClasse($sy_id)
    {
        $res = DB::select("SELECT*FROM subject_classe where subject_classe.sy_id = $sy_id");
        return $res;
    }

    public static function findSubjectCompetences($sy_id)
    {
        $res = DB::select("SELECT*FROM subject_competences where subject_competences.sy_id = $sy_id");
        return $res;
    }
    
    public static function findAPC($sy_id)
    {
        $res = DB::select("SELECT*FROM apc_level where apc_level.sy_id = $sy_id");
        return $res;
    }
    
    public static function findClassifiedParam($sy_id)
    {
        $res = DB::select("SELECT*FROM classifiedparam where classifiedparam.sy_id = $sy_id");
        return $res;
    }
    
    public static function findSubjectClasseStaff($sy_id)
    {
        $res = DB::select("SELECT*FROM subject_classe_staff WHERE subject_classe_staff.subject_classe_id 
                    IN(SELECT subject_classe.subject_classe_id FROM subject_classe
                        WHERE subject_classe.sy_id = $sy_id)");
        return $res;
    }

    public function findStaffId($scStaffOld, $subject_classe_id_old)
    {
        foreach ($scStaffOld as $scS) {
            if ($scS->subject_classe_id == $subject_classe_id_old) {
                return $scS->staff_id;
            }
        }
        return -1; //No staff taking that course
    }

    public function findMatch($subjectClasseNew, $subject_id, $classe_id){
        foreach ($subjectClasseNew as $sc) {
            if($sc->subject_id == $subject_id && $sc->classe_id == $classe_id){
                return $sc->subject_classe_id;
            }
        }
    }

    public function prepareNewYear()
    {
        for ($k = 0; $k < count($this->schools); $k++) {
            $connection = $this->schools[$k];
            echo ("processing '$connection' ..... ");
            config(["database.default" => $connection]);
            $sy_id = 6;
            $sy_next_id = 7;
            try {
                $optionsOld = $this->findOptions($sy_id);
                $spOld = $this->findSpecialities($sy_id);
                $sectionsOld = $this->findSections($sy_id);
                $classesOld = $this->findClasses($sy_id);
                $staffOld = $this->findStaffs($sy_id);
                $groupsOld = $this->findGroups($sy_id);
                $subjectsOld = $this->findSubjects($sy_id);
                $subjectClasseOld = $this->findSubjectClasse($sy_id);
                $subjectClasseNew = $this->findSubjectClasse($sy_next_id);
                $subjectCompetencesOld = $this->findSubjectCompetences($sy_id);
                $apcOld = $this->findAPC($sy_id);
                $classifiedParamOld = $this->findClassifiedParam($sy_id);
                $scStaffOld = $this->findSubjectClasseStaff($sy_id);
                
                
                echo count($optionsOld);
                foreach ($optionsOld as $opt) {
                    DB::select("INSERT INTO filiere_year(sy_id, filiere_id, section_id) 
                    VALUES($sy_next_id, $opt->filiere_id, $opt->section_id)");
                }                
                echo count($spOld);
                foreach ($spOld as $sp) {
                    DB::select("INSERT INTO speciality_year(speciality_id, sy_id, filiere_id, section_id) 
                    VALUES($sp->speciality_id, $sy_next_id, $sp->filiere_id, $sp->section_id)");
                }
                
                echo count($sectionsOld);
                foreach ($sectionsOld as $section) {
                    DB::select("INSERT INTO section_year(sy_id, section_id) 
                    VALUES($sy_next_id, $section->section_id)");
                }
                echo count($classesOld);
                foreach ($classesOld as $cl) {
                    DB::select("INSERT INTO classe_year(sy_id, classe_id, section_id, speciality_id, classe_master, sg_id, vp_id, avgDismissalTh, repeatUB, passMark, totalAbsTh, totalExclusionTh) 
                    VALUES($sy_next_id, $cl->classe_id, ".(is_null($cl->section_id)?'NULL':$cl->section_id).", 
                    ".(is_null($cl->speciality_id)?'NULL':$cl->speciality_id).", ".(is_null($cl->classe_master)?'NULL':$cl->classe_master).", 
                    ".(is_null($cl->sg_id)?'NULL':$cl->sg_id).", ".(is_null($cl->vp_id)?'NULL':$cl->vp_id).", 
                    $cl->avgDismissalTh, $cl->repeatUB, $cl->passMark, $cl->totalAbsTh, $cl->totalExclusionTh)");
                }
                
                echo count($staffOld);
                foreach ($staffOld as $st) {
                    DB::select("INSERT INTO staff_year(staff_id, sy_id) 
                    VALUES($st->staff_id, $sy_next_id)");
                }
                
                echo count($groupsOld);
                foreach ($groupsOld as $grp) {
                    DB::select("INSERT INTO groupe_year(groupe_id, section_id, sy_id) 
                    VALUES($grp->groupe_id, ".(is_null($grp->section_id)?'NULL':$grp->section_id).", $sy_next_id)");
                }
                
                echo count($subjectsOld);
                foreach ($subjectsOld as $sub) {
                    DB::select("INSERT INTO subject_year(subject_id, section_id, sy_id) 
                    VALUES($sub->subject_id, ".(is_null($sub->section_id)?'NULL':$sub->section_id).", $sy_next_id)");
                }
                
                echo count($subjectClasseOld);
                foreach ($subjectClasseOld as $sc) {
                    DB::select("INSERT INTO subject_classe(subject_id, section_id, sy_id, coef, classe_id, groupe_id) 
                    VALUES($sc->subject_id, ".(is_null($sc->section_id)?'NULL':$sc->section_id).", $sy_next_id, $sc->coef, $sc->classe_id, $sc->groupe_id)");
                }
                
                echo count($subjectCompetencesOld);
                foreach ($subjectCompetencesOld as $sc) {
                    DB::select("INSERT INTO subject_competences(term_id, section_id, sy_id, classe_id, subject_id, competence_text) 
                    VALUES($sc->term_id, ".(is_null($sc->section_id)?'NULL':$sc->section_id).", $sy_next_id, $sc->classe_id, $sc->subject_id, ?)", [$sc->competence_text]);
                }
                
                echo count($apcOld);
                foreach ($apcOld as $apc) {
                    DB::select("INSERT INTO apc_level(`level`, activated, section_id, sy_id) 
                    VALUES($apc->level, $apc->activated, ".(is_null($apc->section_id)?'NULL':$apc->section_id).", $sy_next_id)");
                    
                }
                
                echo count($classifiedParamOld);
                foreach ($classifiedParamOld as $param) {
                    DB::select("INSERT INTO classifiedparam(sy_id, nb_matieres_rate, total_coef_rate, classified, class_specific, term_specific) 
                    VALUES($sy_next_id, $param->nb_matieres_rate, $param->total_coef_rate, $param->classified, $param->class_specific, $param->term_specific)");
                }
                
                foreach ($subjectClasseOld as $scOld) {
                    $classe_id = $scOld->classe_id;
                    $subject_id = $scOld->subject_id;
                    $subject_classe_id_old = $scOld->subject_classe_id;
                    $staff_id = $this->findStaffId($scStaffOld, $subject_classe_id_old);
                    $subject_classe_id_new = $this->findMatch($subjectClasseNew, $subject_id, $classe_id);
                    if ($staff_id > 0 && $subject_classe_id_new > 0) {
                        DB::select("INSERT INTO subject_classe_staff(subject_classe_id, staff_id) 
                    VALUES($subject_classe_id_new, $staff_id)");
                    }
                }
                
                echo "<br/>";
            } catch (Exception  $e) {//(is_null($cl->sg_id)?'NULL':$cl->sg_id)
                echo " ERROR<br/>";
                //if($this->schools[$k] == "mysql"){
                //    echo "[" . $e->getMessage() . "<br/><br/>";
                //}
                echo "[" . $e->getMessage() . "<br/><br/>";
            }
        }
    }
    
    public function alterStaff()
    {
        for ($k = 0; $k < count($this->schools); $k++) {
            $connection = $this->schools[$k];
            echo ("processing '$connection' ..... ");
            config(["database.default" => $connection]);             
            try {
                //DB::select("ALTER TABLE `staff` ADD `grade` VARCHAR(100) NULL DEFAULT ' ' COMMENT 'PLEG, PLET..' AFTER `acc_id`;");
                //DB::select("ALTER TABLE `staff` ADD `region` VARCHAR(100) NULL DEFAULT ' ' COMMENT 'region d\'origine' AFTER `grade`;");
                //DB::select("ALTER TABLE `staff` ADD `department` VARCHAR(100) NULL DEFAULT ' ' COMMENT 'departement d\'origine' AFTER `region`;");
                //DB::select("ALTER TABLE `staff` ADD `arrodissement` VARCHAR(100) NULL DEFAULT ' ' COMMENT 'Arrondissement d\'origine' AFTER department");
                //DB::select("ALTER TABLE `staff` ADD `numeroRecrutement` VARCHAR(200) NULL DEFAULT ' ' COMMENT 'numero de recrutement' AFTER `arrodissement`;");
                //DB::select("ALTER TABLE `staff` ADD `provenantDe` VARCHAR(100) NULL DEFAULT ' ' COMMENT 'L\'enseignant provient de' AFTER `numeroRecrutement`;");
                //DB::select("ALTER TABLE `staff` ADD `dateReprise` VARCHAR(100) NULL DEFAULT ' ' COMMENT 'Date de reprise de service' AFTER `provenantDe`;");
                //DB::select("ALTER TABLE `staff` ADD `diplome` VARCHAR(150) NULL DEFAULT ' ' AFTER `dateReprise`;");
                //DB::select("ALTER TABLE `staff` ADD `matiereEnseignee` VARCHAR(200) NULL DEFAULT ' ' COMMENT 'Matiere effectivement enseignee' AFTER `diplome`;");
                //DB::select("ALTER TABLE `staff` ADD `dateEntree` VARCHAR(100) NULL DEFAULT ' ' COMMENT 'Date entrée à la fonction publique' AFTER `matiereEnseignee`;");
                //DB::select("ALTER TABLE `staff` ADD `date1erePrise` VARCHAR(100) NULL DEFAULT ' ' COMMENT 'Date de premiere prise de service' AFTER `dateEntree`;");
                DB::select("ALTER TABLE `staff` ADD `specilitee` VARCHAR(100) NULL DEFAULT ' ' COMMENT 'Specialitée de l\'enseignant' AFTER `date1erePrise`;");
                
                //DB::select("");
                //DB::select(""); 
                echo "Done with $connection";
               } catch (Exception  $e) {  
                echo " ERROR<br/>"; 
                echo "[" . $e->getMessage() . "<br/><br/>";
            }
            echo "<br/>";
        }
    }
    
    public function deleteStudClasse(Request $request)
    {
        $connection = $request->input("connection"); 
        $next_year = $request->input("next_year");
        $stud_id = $request->input("stud_id");
        $old_classe_id = $request->input("old_classe_id");
        config(["database.default" => $connection]);
        try { 
            $sy_next_id = MyHelper::getSchoolYearID($next_year);
             
            DB::select("DELETE FROM student_classe 
                WHERE student_classe.stud_id = $stud_id 
                    AND student_classe.sy_id = $sy_next_id  
                    AND student_classe.classe_id = $old_classe_id");
            echo "1"; //SUCCESS
        } catch (Exception $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            return;
        }
    }
    
    public function deleteManyStudClasse(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $next_year = $request->input("next_year"); 

        $stList = json_decode($data, true);
        $n = count($stList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]<br/>";
        $allAffected = 1; //interpreted as true. 0-->false  
        config(["database.default" => $connection]); 
        $sy_next_id = MyHelper::getSchoolYearID($next_year);

        //$count  = 1;
        foreach ($stList as $stud) {
            //echo"-----> Processing stud No.[$count]<br/>";
            $old_classe_id = $stud['old_classe_id'];
            $stud_id = $stud['stud_id'];
            try {
                DB::select("DELETE FROM student_classe 
                WHERE student_classe.stud_id = $stud_id 
                    AND student_classe.sy_id = $sy_next_id  
                    AND student_classe.classe_id = $old_classe_id");
            } catch (Exception $e) {
                echo '<br/>ERROR: ' . $e->getMessage();
                $allAffected = 0;
            }
        } //END FOR
        echo "$allAffected"; //1--> successfully  | 0--> Failed 
    }

    public function deleteManyStudClassePOST(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $next_year = $request->input("next_year"); 

        $stList = json_decode($data, true);
        $n = count($stList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]<br/>";
        $allAffected = 1; //interpreted as true. 0-->false  
        config(["database.default" => $connection]); 
        $sy_next_id = MyHelper::getSchoolYearID($next_year);

        //$count  = 1;
        foreach ($stList as $stud) {
            //echo"-----> Processing stud No.[$count]<br/>";
            $old_classe_id = $stud['old_classe_id'];
            $stud_id = $stud['stud_id'];
            try {
                DB::select("DELETE FROM student_classe 
                WHERE student_classe.stud_id = $stud_id 
                    AND student_classe.sy_id = $sy_next_id  
                    AND student_classe.classe_id = $old_classe_id");
            } catch (Exception $e) {
                echo '<br/>ERROR: ' . $e->getMessage();
                $allAffected = 0;
            }
        } //END FOR
        echo "$allAffected"; //1--> successfully  | 0--> Failed 
    }
    
    
    public function allPatients(Request $request)
    {
        $connection = $request->input("connection"); 
        config(["database.default" => $connection]); 
        $patients = Patient::all();
        return response()->json($patients, 200);
    }
    

    public function savePatient(Request $request)
    {
        $connection = $request->input("connection");
        $p_name = $request->input("p_name");
        $pwd = $request->input("pwd");
        $region = $request->input("region");
        $gender = $request->input("gender");
        config(["database.default" => $connection]);
        try {
            $patient = new Patient();
            $patient->p_name = $p_name;
            $patient->pwd = $pwd;
            $patient->region = $region;
            $patient->gender = $gender;
            try {
                $patient->save();
            } catch (Exception $ex) {
                echo "-1"; //Failed to save patient
                echo '<br/>Message: ' . $ex->getMessage();
                return;
            }
            echo "1"; //Operation is successfull
        } catch (Exception $e) {
            echo "-2"; //Error occurs
            echo '<br/>Message: ' . $e->getMessage();
        }
    }

    

}
