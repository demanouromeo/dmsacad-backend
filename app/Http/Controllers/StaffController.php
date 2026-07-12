<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Staff;
use App\Models\StaffYear;
use App\Models\SubjectClasse;
use App\Models\SubjectClasseStaff;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StaffController extends Controller
{
    
    public function modifyStaff(Request $request)
    {
        $connection = $request->input("connection");
        $staff_id = $request->input("staff_id");
        $grade = $request->input("grade");
        $region = $request->input("region");
        $department = $request->input("department");
        $arrodissement = $request->input("arrodissement");
        $numeroRecrutement = $request->input("numeroRecrutement");
        $provenantDe = $request->input("provenantDe");
        $dateReprise = $request->input("dateReprise");
        $diplome = $request->input("diplome");
        $specilitee = $request->input("specilitee");
        $matiereEnseignee = $request->input("matiereEnseignee");
        $dateEntree = $request->input("dateEntree");
        $date1erePrise = $request->input("date1erePrise");

        $matricule = $request->input("matricule");
        $dob = $request->input("dob");
        $pob = $request->input("pob");
        $posting_decision = $request->input("posting_decision");

        config(["database.default" => $connection]);
        try {

            
            DB::select("UPDATE staff SET grade = '$grade', region = '$region', department = '$department',
            arrodissement = '$arrodissement', numeroRecrutement = '$numeroRecrutement', provenantDe = '$provenantDe',
            dateReprise = '$dateReprise', diplome = '$diplome', specilitee = '$specilitee', matiereEnseignee = '$matiereEnseignee', 
            dateEntree = '$dateEntree', date1erePrise = '$date1erePrise',
            matricule = '$matricule', dob = '$dob', pob = '$pob',
            posting_decision = '$posting_decision' 
            WHERE staff_id  = $staff_id");
            
            
            //DB::select("UPDATE staff SET grade = '$grade' WHERE staff_id = $staff_id");
            echo "1"; //SUCCESS
        } catch (Exception $e) {
            echo "0";
            echo '<br/>ERROR: ' . $e->getMessage();
            return;
        }
    }
    
    public function subjectTaughtByaStaff2(Request $request)
    {   //FIND ALL THE SUBJECTS ASSIGNED TO STAFF giving for each the classe and subject title
        $connection = $request->input("connection");
        $year = $request->input("year");
        $section = $request->input("section"); 
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";

        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $section_id = MyHelper::getSectionID($section);
            $staff = DB::select(
                "SELECT classe.classe_id, subject.subject_id,classe.classe_name, subject.subject_title FROM classe, subject, subject_classe, staff, subject_classe_staff
                    WHERE classe.classe_id = subject_classe.classe_id
                        AND subject.subject_id = subject_classe.subject_id
                        AND subject_classe_staff.subject_classe_id = subject_classe.subject_classe_id
                        AND staff.staff_id = subject_classe_staff.staff_id
                        AND subject_classe.sy_id = $sy_id
                        AND subject_classe.section_id = $section_id"
            );
            return response()->json($staff, 200);
        } catch (Exception $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }
   
    public function AllAttributionsOfSection(Request $request)
    {
        $connection = $request->input("connection");
        $year = $request->input("year");
        $section = $request->input("section");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";

        $sy_id = MyHelper::getSchoolYearID($year);
        $section_id = MyHelper::getSectionID($section);

        try {           

            $staff = DB::select(
                "SELECT classe.`level`, classe.classe_name,  subject.subject_title,  staff.name, subject.subject_id, classe.classe_id, staff.staff_id, subject_classe.subject_classe_id, subject_classe_staff.id 
                FROM classe, subject, subject_classe, staff, subject_classe_staff
                    WHERE classe.classe_id = subject_classe.classe_id
                        AND subject.subject_id = subject_classe.subject_id
                        AND subject_classe_staff.subject_classe_id = subject_classe.subject_classe_id
                        AND staff.staff_id = subject_classe_staff.staff_id
                        AND subject_classe.sy_id = $sy_id
                        AND subject_classe.section_id = $section_id 
                        ORDER BY classe.`level`, classe.classe_name, subject.subject_title"
            );
            return response()->json($staff, 200);
        } catch (Exception $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }

    public function removeALLCourses(Request $request)
    {
        $connection = $request->input("connection");
        $year = $request->input("year");
        $section = $request->input("section");
        $staff_id = $request->input("staff_id");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        $sy_id = MyHelper::getSchoolYearID($year);
        $section_id = MyHelper::getSectionID($section);
        $result = 1;
        try {
            $result = MyHelper::removeAStaffCourses($sy_id, $section_id, $staff_id);
        } catch (Exception $e) {
            //echo '<br/>ERROR: ' . $e->getMessage();
            $result = -2; //ERROR OCCURS
        }
        echo $result;
    }

    public function removeACourse(Request $request)
    {
        $connection = $request->input("connection");
        $year = $request->input("year");
        $section = $request->input("section");
        $subject_id = $request->input("subject_id");
        $classe_id = $request->input("classe_id");
        $staff_id = $request->input("staff_id");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";

        $result = 1;
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $section_id = MyHelper::getSectionID($section);

            //VERIFIONS SI LA MATIERE A DEJA ETE ATTRIBUE  A CETTE ENSEIGNANT DANS L'ANNEE ET LA SECTION EN COURS
            $res1 = DB::select(
                "SELECT subject_classe.subject_id, subject_classe_staff.subject_classe_id,
                        subject_classe_staff.id 
                    FROM  subject_classe, staff, subject_classe_staff
                     WHERE 
                         subject_classe_staff.subject_classe_id = subject_classe.subject_classe_id 
                     AND subject_classe.sy_id = $sy_id
                     AND subject_classe.section_id = $section_id
                     AND subject_classe.classe_id = $classe_id
                     AND subject_classe.subject_id = $subject_id
                     AND subject_classe_staff.staff_id = $staff_id"
            );
            if (count($res1) > 0) { //DELETE
                //THE SUBJECT HAS BEEN  ASSIGNED TO THAT TEACHER IN THAT CLASSE
                $id = -1;
                foreach ($res1 as $st) {
                    //echo $cl->classe_name;
                    $id =  $st->id;
                }
                //echo "<br/>id=$id";
                try {
                    $ref = SubjectClasseStaff::find($id)->delete();
                } catch (Exception $e) {
                    //echo "<br/>" . $e->getMessage() . "<br/>"; //Fatal error, since id has to exist according to above DB::select()
                    $result = -1;
                }
            } else {
            }
        } catch (Exception $e) {
            //echo '<br/>ERROR: ' . $e->getMessage();
            $result = -2; //ERROR OCCURS
        }
        echo $result;
    }

    public function assignACourse(Request $request)
    {
        $connection = $request->input("connection");
        $year = $request->input("year");
        $section = $request->input("section");
        $subject_id = $request->input("subject_id");
        $classe_id = $request->input("classe_id");
        $staff_id = $request->input("staff_id");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";

        $result = 1;
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $section_id = MyHelper::getSectionID($section);

            //VERIFIONS SI LA MATIERE A DEJA ETE ATTRIBUE  A CETTE ENSEIGNANT DANS L'ANNEE ET LA SECTION EN COURS
            $res1 = DB::select(
                "SELECT subject_classe.subject_id, subject_classe_staff.subject_classe_id FROM  subject_classe, staff, subject_classe_staff
                    WHERE 
                        subject_classe_staff.subject_classe_id = subject_classe.subject_classe_id 
                    AND subject_classe.sy_id = $sy_id
                    AND subject_classe.section_id = $section_id
                    AND subject_classe.classe_id = $classe_id
                    AND subject_classe.subject_id = $subject_id
                    AND subject_classe_staff.staff_id = $staff_id"
            );
            if (count($res1) > 0) {
                //DO NOTHING THE SUBJECT IS ALREADY ASSIGNED TO THAT TEACHER
            } else {
                //IT IS NO YET ASSIGNED, LET'S DO IT NOW
                $sc = SubjectClasse::where("subject_id", $subject_id)
                    ->where("classe_id", $classe_id)
                    ->where("sy_id", $sy_id)
                    ->where("subject_id", $subject_id)
                    ->where("section_id", $section_id)
                    ->first();
                $scStaff = new SubjectClasseStaff();
                if (!is_null($sc)) {
                    $scStaff->subject_classe_id = $sc->subject_classe_id;
                    $scStaff->staff_id = $staff_id;
                    try {
                        $scStaff->save();
                    } catch (Exception $e) {
                        echo "<br/>" . $e->getMessage() . "<br/>";
                        $result = -1;
                    }
                } else {
                    $result = 0;
                }
            }
        } catch (Exception $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            $result = -2; //ERROR OCCURS
        }
        echo $result;
    }
    
    public function batchAssignCourses(Request $request)
    {
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $year = $request->input("year");
        $section = $request->input("section");

        $attribList = json_decode($data, true);
        //$n = count($attribList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]";
        $k = 1; //1--> Success. 0--> Some classes not assignes  
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        $section_id = MyHelper::getSectionID($section);

        $msg = "";
        foreach ($attribList as $attribution) {
            $staff_id = $attribution["staff_id"];
            $subject_id = $attribution["subject_id"];
            $classe_id = $attribution["classe_id"];

            try { 

                //VERIFIONS SI LA MATIERE A DEJA ETE ATTRIBUE  A CETTE ENSEIGNANT DANS L'ANNEE ET LA SECTION EN COURS
                $res1 = DB::select(
                    "SELECT subject_classe.subject_id, subject_classe_staff.subject_classe_id FROM  subject_classe, staff, subject_classe_staff
                    WHERE 
                        subject_classe_staff.subject_classe_id = subject_classe.subject_classe_id 
                    AND subject_classe.sy_id = $sy_id
                    AND subject_classe.section_id = $section_id
                    AND subject_classe.classe_id = $classe_id
                    AND subject_classe.subject_id = $subject_id
                    AND subject_classe_staff.staff_id = $staff_id"
                );
                if (count($res1) > 0) {
                    //DO NOTHING THE SUBJECT IS ALREADY ASSIGNED TO THAT TEACHER
                } else {
                    //IT IS NO YET ASSIGNED, LET'S DO IT NOW
                    $sc = SubjectClasse::where("subject_id", $subject_id)
                        ->where("classe_id", $classe_id)
                        ->where("sy_id", $sy_id)
                        ->where("subject_id", $subject_id)
                        ->where("section_id", $section_id)
                        ->first();
                    $scStaff = new SubjectClasseStaff();
                    if (!is_null($sc)) {
                        $scStaff->subject_classe_id = $sc->subject_classe_id;
                        $scStaff->staff_id = $staff_id;
                        try {
                            $scStaff->save();
                        } catch (Exception $e) {
                            echo "<br/>" . $e->getMessage() . "<br/>";
                            $k = -1;
                        }
                    } else {
                        $k = 0;
                    }
                }
            } catch (Exception $e) {
                echo '<br/>ERROR: ' . $e->getMessage();
                $k = -2; //ERROR OCCURS
            }
        } //END FOR

        if ($k == 1) {//success
            echo $k;
        } else {
            echo "$k|$msg";
        } //K=1--> All attributions Applied; K=0, -1 ou -2. --> Failed to save at least one
    }
    
    
    public function batchRemoveCourses(Request $request)
    {
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $year = $request->input("year");
        $section = $request->input("section");

        $attribList = json_decode($data, true);
        //$n = count($attribList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]";
        $k = 1; //1--> Success. 0--> Some classes not assignes  
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        $section_id = MyHelper::getSectionID($section);

        $msg = "";
        foreach ($attribList as $attribution) {
            $staff_id = $attribution["staff_id"];
            $subject_id = $attribution["subject_id"];
            $classe_id = $attribution["classe_id"];

            try { 

                //VERIFIONS SI LA MATIERE A DEJA ETE ATTRIBUE  A CETTE ENSEIGNANT DANS L'ANNEE ET LA SECTION EN COURS
                $res1 = DB::select(
                    "SELECT subject_classe.subject_id, subject_classe_staff.subject_classe_id,
                        subject_classe_staff.id 
                    FROM  subject_classe, staff, subject_classe_staff
                     WHERE 
                         subject_classe_staff.subject_classe_id = subject_classe.subject_classe_id 
                     AND subject_classe.sy_id = $sy_id
                     AND subject_classe.section_id = $section_id
                     AND subject_classe.classe_id = $classe_id
                     AND subject_classe.subject_id = $subject_id
                     AND subject_classe_staff.staff_id = $staff_id"
                );
                if (count($res1) > 0) { //DELETE
                    //THE SUBJECT HAS BEEN  ASSIGNED TO THAT TEACHER IN THAT CLASSE
                    $id = -1;
                    foreach ($res1 as $st) {
                        //echo $cl->classe_name;
                        $id =  $st->id;
                    }
                    //echo "<br/>id=$id";
                    try {
                        $ref = SubjectClasseStaff::find($id)->delete();
                    } catch (Exception $e) {
                        //echo "<br/>" . $e->getMessage() . "<br/>"; //Fatal error, since id has to exist according to above DB::select()
                        $k = -1;
                    }
                } else {
                }
            } catch (Exception $e) {
                //echo '<br/>ERROR: ' . $e->getMessage();
                $k = -2; //ERROR OCCURS
            }
        } //END FOR

        if ($k == 1) { //success
            echo $k;
        } else {
            echo "$k|$msg";
        } //K=1--> All attributions Applied; K=0, -1 ou -2. --> Failed to save at least one
    }
    
    
    public function deleteManyStaffs(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $year  = $request->input("year");
        $data = $request->input("data");
        $section = $request->input("section");
        $data_size = $request->input("data_size");

        $staffList = json_decode($data, true);
        $n = count($staffList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]";
        $allAffected = 1; //interpreted as true. 0-->false

        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        $section_id = MyHelper::getSectionID($section);
        foreach ($staffList as $st) {
            $staff_id = $st["staff_id"];
            $res = MyHelper::deleteAStaff($sy_id, $section_id, $staff_id);
            if ($res < 0) {
                $allAffected = 0;
            }
            /*
            //echo "$staff_id<br/>";
            $stRef = Staff::where("staff_id", "=", $staff_id)->first();
            //echo "".($stRef->staff_id);
            $stClasses = SubjectClasseStaff::where("staff_id", '=', $staff_id)
                ->get();
            $stYears = StaffYear::where("staff_id", '=', $staff_id)
                ->where("sy_id", '=', $sy_id) //MAKE SURE IT IS STAFF OF THE YEAR
                ->get();
            try {
                if (!is_null($stClasses)) { //A teacher may not be assign any course, in that case it is normal to have this stClasse null
                    foreach ($stClasses as $stClass) {
                        $res1 = $stClass->delete();
                        if (!$res1) {
                            $allAffected = 0;
                        }
                    }
                }

                if (!is_null($stYears)) {
                    foreach ($stYears as $stYear) {
                        $res2 = $stYear->delete();
                        if (!$res2) {
                            $allAffected = 0;
                        }
                    }
                } else { //DB IS INCONSITENT IN THIS CASE; SINCE A STAFF HAS TO BE IN A SCHOOL YEAR
                    //do nothng yet
                }

                //LETS VERIFY IF st EXISTS IN ANY OTHER SCHOOL_YEAR BEFORE DELETING
                $x = StaffYear::where("staff_id", '=', $staff_id)
                    ->first();
                if (is_null($x)) { //WE CAN DELETE stRef SINCE HE IS NOT APPEARING IN ANY OTHER SCHOOL YEAR
                    $res2 = $stRef->delete();
                    if (!$res2) {
                        $allAffected = 0;
                    }
                }
            } catch (Exception $ex) {
                $allAffected = 0;
                echo "ERROR " . $ex->getMessage();
            }
            $val1 = DB::select("DELETE FROM subject_classe_staff WHERE 
            subject_classe_staff.staff_id 
            NOT IN(SELECT staff_year.staff_id FROM staff_year)");
            $val2 = DB::select("DELETE FROM staff WHERE staff.staff_id NOT IN (SELECT staff_year.staff_id from staff_year)");
            */
        } //END FOR
        //return response($allAffected, 200);
        echo (string) $allAffected; //1--> All groupes successfully deleted; 0--> Failed to save at least one
    }

    public function deleteManyStaffsWithPOST(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $year  = $request->input("year");
        $data = $request->input("data");
        $section = $request->input("section");
        $data_size = $request->input("data_size");

        $staffList = json_decode($data, true);
        $n = count($staffList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]";
        $allAffected = 1; //interpreted as true. 0-->false

        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        $section_id = MyHelper::getSectionID($section);
        foreach ($staffList as $st) {
            $staff_id = $st["staff_id"];
            $res = MyHelper::deleteAStaff($sy_id, $section_id, $staff_id);
            if ($res < 0) {
                $allAffected = 0;
            }
            /*
            //echo "$staff_id<br/>";
            $stRef = Staff::where("staff_id", "=", $staff_id)->first();
            //echo "".($stRef->staff_id);
            $stClasses = SubjectClasseStaff::where("staff_id", '=', $staff_id)
                ->get();
            $stYears = StaffYear::where("staff_id", '=', $staff_id)
                ->where("sy_id", '=', $sy_id) //MAKE SURE IT IS STAFF OF THE YEAR
                ->get();
            try {
                if (!is_null($stClasses)) { //A teacher may not be assign any course, in that case it is normal to have this stClasse null
                    foreach ($stClasses as $stClass) {
                        $res1 = $stClass->delete();
                        if (!$res1) {
                            $allAffected = 0;
                        }
                    }
                }

                if (!is_null($stYears)) {
                    foreach ($stYears as $stYear) {
                        $res2 = $stYear->delete();
                        if (!$res2) {
                            $allAffected = 0;
                        }
                    }
                } else { //DB IS INCONSITENT IN THIS CASE; SINCE A STAFF HAS TO BE IN A SCHOOL YEAR
                    //do nothng yet
                }

                //LETS VERIFY IF st EXISTS IN ANY OTHER SCHOOL_YEAR BEFORE DELETING
                $x = StaffYear::where("staff_id", '=', $staff_id)
                    ->first();
                if (is_null($x)) { //WE CAN DELETE stRef SINCE HE IS NOT APPEARING IN ANY OTHER SCHOOL YEAR
                    $res2 = $stRef->delete();
                    if (!$res2) {
                        $allAffected = 0;
                    }
                }
            } catch (Exception $ex) {
                $allAffected = 0;
                echo "ERROR " . $ex->getMessage();
            }
            $val1 = DB::select("DELETE FROM subject_classe_staff WHERE 
            subject_classe_staff.staff_id 
            NOT IN(SELECT staff_year.staff_id FROM staff_year)");
            $val2 = DB::select("DELETE FROM staff WHERE staff.staff_id NOT IN (SELECT staff_year.staff_id from staff_year)");
            */
        } //END FOR
        //return response($allAffected, 200);
        echo (string) $allAffected; //1--> All groupes successfully deleted; 0--> Failed to save at least one
    }

    public function updateManyStaffsPOST(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $year = $request->input("year");

        $stList = json_decode($data, true);
        //$n = count($fList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]";
        $allAffected = 1; //interpreted as true. 0-->false  
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        foreach ($stList as $st) {
            $staff_id = $st["staff_id"];
            $name = $st["name"];
            $phone1 = $st["phone1"];
            $function = $st["function"];
            $civility = $st["civility"];

            $login = $st["login"];
            $pwd = $st["pwd"];
            $acc_id = $st["acc_id"];
            try {

                $st2 = Staff::find($staff_id);
                $st2->name = $name;
                $st2->civility = $civility;
                $st2->function = $function;
                $query = $st2->update(); //Mettre a jour les champs except phone1;
                try {
                    $st2->phone1 = $phone1;
                    $tmp1 = DB::table('staff')
                        ->where('staff_id', $staff_id)
                        ->update(['phone1' => $phone1]);
                    //echo "allAffected: $allAffected FOR $st2->phone1<br/>";
                } catch (Exception $e) {
                    //Field Phone is unique so can't be suplicated
                    $allAffected = 0;
                    //echo "<br/>".$e->getMessage()."<br/>";   
                    //echo "allAffected: $allAffected FOR $st2->phone1 as exception ouccurs<br/>";                  
                }

                //LETS UPDATE THE RELATED ACCOUNT
                $acc = Account::find($acc_id);
                //$k = is_null($acc);
                //$id = $acc->acc_id;
                //echo "is acc null: ".$k."  [$id]-$login+$pwd <br/>";
                $acc->pwd = $pwd;
                $acc->login = $login;
                try {
                    $tmp2 = $acc->update();
                } catch (Exception $e) {
                    //Login is unique so can't be suplicated
                    //echo "<br/>".$e->getMessage()."<br/>";
                    $allAffected = 0;
                }
            } catch (Exception $ex) {
                //echo $ex->getMessage();
                $allAffected = 0;
            }
        }
        echo "$allAffected"; //1--> All classes successfully modified; 0--> Failed to save at least one
        self::arrangeSGSimple();
    }
    
    public function updateManyStaffs(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $year = $request->input("year");

        $stList = json_decode($data, true);
        //$n = count($fList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]";
        $allAffected = 1; //interpreted as true. 0-->false  
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        foreach ($stList as $st) {
            $staff_id = $st["staff_id"];
            $name = $st["name"];
            $phone1 = $st["phone1"];
            $function = $st["function"];
            $civility = $st["civility"];

            $login = $st["login"];
            $pwd = $st["pwd"];
            $acc_id = $st["acc_id"];
            try {

                $st2 = Staff::find($staff_id);
                $st2->name = $name;
                $st2->civility = $civility;
                $st2->function = $function;
                $query = $st2->update(); //Mettre a jour les champs except phone1;
                try {
                    $st2->phone1 = $phone1;
                    $tmp1 = DB::table('staff')
                        ->where('staff_id', $staff_id)
                        ->update(['phone1' => $phone1]);
                    //echo "allAffected: $allAffected FOR $st2->phone1<br/>";
                } catch (Exception $e) {
                    //Field Phone is unique so can't be suplicated
                    $allAffected = 0;
                    //echo "<br/>".$e->getMessage()."<br/>";   
                    //echo "allAffected: $allAffected FOR $st2->phone1 as exception ouccurs<br/>";                  
                }

                //LETS UPDATE THE RELATED ACCOUNT
                $acc = Account::find($acc_id);
                //$k = is_null($acc);
                //$id = $acc->acc_id;
                //echo "is acc null: ".$k."  [$id]-$login+$pwd <br/>";
                $acc->pwd = $pwd;
                $acc->login = $login;
                try {
                    $tmp2 = $acc->update();
                } catch (Exception $e) {
                    //Login is unique so can't be suplicated
                    //echo "<br/>".$e->getMessage()."<br/>";
                    $allAffected = 0;
                }
            } catch (Exception $ex) {
                //echo $ex->getMessage();
                $allAffected = 0;
            }
        }
        echo "$allAffected"; //1--> All classes successfully modified; 0--> Failed to save at least one
    }

    public function saveManyStaffs(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $data = $request->input("data");
        $section = $request->input("section");
        $data_size = $request->input("data_size");
        $year = $request->input("year");
        $override = $request->input("override");

        $stList = json_decode($data, true);
        //$n = count($fList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]";
        $allAffected = 1; //interpreted as true. 0-->false  
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        $section_id = MyHelper::getSectionID($section);

        if ($override == "1") {
            //DELETE ALL STAFF
            $allStaffs = Staff::all();
            if (!is_null($allStaffs)) {
                foreach ($allStaffs as $st) {
                    MyHelper::deleteAStaff($sy_id, $section_id, $st->staff_id);
                }
            }
        }

        foreach ($stList as $st) {
            $name = $st["name"];
            if($name == "null") {
                $name = "";
            }
            $phone1 = $st["phone1"];
            $function = $st["function"];
            $civility = $st["civility"];
            $login = $st["login"];
            $pwd = $st["pwd"];
            $acc = new Account();
            $acc->login = $login;
            $acc->pwd = $pwd;
            $type = MyHelper::getAccountType($function);
            $acc->type = $type;

            //Garanty the unicity of phone;
            $x = Staff::all()
                ->where("phone1", $phone1)
                ->first();
            if (!is_null($x)) {
                $phone1 = null;
            }

            //GARANTY THE UNICITY OF ACC LOGIN
            $accTmp = Account::all()
                ->where('login', '=', $login)
                ->first();
            if (!is_null($accTmp)) {
                //Account exists already
                //LET'S CHANGE THE LOGIN TO EXPECT HAVING IT SAVED without violating the integrity
                //$acc->login = $login."".$type."".$name;
                $acc->login = $login."".rand(1000, 9999);
            }


            try {
                //Save account
                $acc->save();
                $staff = new Staff();
                $staff->name = $name;
                if (empty($phone1) || $phone1 == "0") {
                    //WE ARE NOT ASSIGNING PHONE1 IN THIS CASE
                } else {
                    $staff->phone1 = $phone1;
                }
                $staff->acc_id = $acc->acc_id;
                $staff->function = $function;
                $staff->civility = $civility;
                try {
                    $staff->save();
                    $syear = new StaffYear();
                    $syear->staff_id = $staff->staff_id;
                    $syear->sy_id = $sy_id;
                    //echo "\$sy_id = $sy_id | \$staff_id = $staff->staff_id";
                    try {
                        $syear->save();
                    } catch (Exception $e3) {
                        echo "<br/>" . $e3->getMessage() . "<br/>";
                        echo "-4"; //failed to save Staff_year;
                        $allAffected = 0;
                        try { 
                            $staff->delete();                            
                        } catch (Exception $ex) { //DO NOTHING
                            echo "<br/>\$ex" . $e3->getMessage() . "<br/>";
                            $allAffected = 0;
                        }
                    }
                } catch (Exception $e2) {
                    echo "<br/>" . $e2->getMessage() . "<br/>";
                    echo "-3"; //failed to save Staff;
                    $allAffected = 0;
                    try {
                        $acc->delete();
                    } catch (Exception $exx) { //DO NOTHING
                        //$allAffected = 0;
                    }
                }
            } catch (Exception $e1) {
                echo "<br/>" . $e1->getMessage() . "<br/>";
                echo "-2"; //failed to save account
                $allAffected = 0;
            }
        }
        echo "$allAffected"; //1--> All classes successfully modified; 0--> Failed to save at least one
        self::arrangeSGSimple();
    }

    public function saveManyStaffsWithPOST(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $data = $request->input("data");
        $section = $request->input("section");
        $data_size = $request->input("data_size");
        $year = $request->input("year");
        $override = $request->input("override");

        $stList = json_decode($data, true);
        //$n = count($fList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]";
        $allAffected = 1; //interpreted as true. 0-->false  
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        $section_id = MyHelper::getSectionID($section);

        if ($override == "1") {
            //DELETE ALL STAFF
            $allStaffs = Staff::all();
            if (!is_null($allStaffs)) {
                foreach ($allStaffs as $st) {
                    MyHelper::deleteAStaff($sy_id, $section_id, $st->staff_id);
                }
            }
        }

        foreach ($stList as $st) {
            $name = $st["name"];
            if($name == "null") {
                $name = "";
            }
            $phone1 = $st["phone1"];
            $function = $st["function"];
            $civility = $st["civility"];
            $login = $st["login"];
            $pwd = $st["pwd"];
            $acc = new Account();
            $acc->login = $login;
            $acc->pwd = $pwd;
            $type = MyHelper::getAccountType($function);
            $acc->type = $type;

            //Garanty the unicity of phone;
            $x = Staff::all()
                ->where("phone1", $phone1)
                ->first();
            if (!is_null($x)) {
                $phone1 = null;
            }

            //GARANTY THE UNICITY OF ACC LOGIN
            $accTmp = Account::all()
                ->where('login', '=', $login)
                ->first();
            if (!is_null($accTmp)) {
                //Account exists already
                //LET'S CHANGE THE LOGIN TO EXPECT HAVING IT SAVED without violating the integrity
                //$acc->login = $login."".$type."".$name;
                $acc->login = $login."".rand(1000, 9999);
            }


            try {
                //Save account
                $acc->save();
                $staff = new Staff();
                $staff->name = $name;
                if (empty($phone1) || $phone1 == "0") {
                    //WE ARE NOT ASSIGNING PHONE1 IN THIS CASE
                } else {
                    $staff->phone1 = $phone1;
                }
                $staff->acc_id = $acc->acc_id;
                $staff->function = $function;
                $staff->civility = $civility;
                try {
                    $staff->save();
                    $syear = new StaffYear();
                    $syear->staff_id = $staff->staff_id;
                    $syear->sy_id = $sy_id;
                    //echo "\$sy_id = $sy_id | \$staff_id = $staff->staff_id";
                    try {
                        $syear->save();
                    } catch (Exception $e3) {
                        echo "<br/>" . $e3->getMessage() . "<br/>";
                        echo "-4"; //failed to save Staff_year;
                        $allAffected = 0;
                        try { 
                            $staff->delete();                            
                        } catch (Exception $ex) { //DO NOTHING
                            echo "<br/>\$ex" . $e3->getMessage() . "<br/>";
                            $allAffected = 0;
                        }
                    }
                } catch (Exception $e2) {
                    echo "<br/>" . $e2->getMessage() . "<br/>";
                    echo "-3"; //failed to save Staff;
                    $allAffected = 0;
                    try {
                        $acc->delete();
                    } catch (Exception $exx) { //DO NOTHING
                        //$allAffected = 0;
                    }
                }
            } catch (Exception $e1) {
                echo "<br/>" . $e1->getMessage() . "<br/>";
                echo "-2"; //failed to save account
                $allAffected = 0;
            }
        }
        echo "$allAffected"; //1--> All classes successfully modified; 0--> Failed to save at least one
        self::arrangeSGSimple();
    }

    public function saveStaff(Request $request)
    {
        $connection = $request->input("connection");
        $year = $request->input("year");
        $name = $request->input("name");
        $surname = $request->input("surname");
        $phone1 = $request->input("phone1");
        $login = $request->input("login");
        $pwd = $request->input("pwd");
        $sexe = $request->input(key: "sexe");
        $function = $request->input("function"); //Code de la function '0' -> Enseignant ....
        $civility = $request->input("civility"); //Mr , Mme, M ..
        config(["database.default" => $connection]);

        //if(empty($phone1)){
        //   echo"\$phone is empty";
        //    $phone1 = null;
        //}
        //echo "Connection: $connection <br/>Year: $year <br/>name: $name"
        //    . "<br/>surname: $surname <br/>phone1: $phone1<br/>login: $login <br/>"
        //    . "pwd: $pwd <br/>function: $function<br/>civility: $civility";

        try {
            $sy_id = MyHelper::getSchoolYearID($year);


            $accTmp = Account::all()
                ->where('login', '=', $login)
                ->first();
            if (!is_null($accTmp)) {
                echo "-1 Account exists already"; //Account exists already
            } else {
                //Save account
                $acc = new Account();
                $acc->login = $login;
                $acc->pwd = $pwd;
                //Let's find type from function code
                $type = MyHelper::getAccountType($function);
                $acc->type = $type;
                try {
                    $acc->save();
                    $staff = new Staff();
                    $staff->name = $name;
                    $staff->surname = $surname;
                    if (empty($phone1) || $phone1 == "0") {
                        //WE ARE NOT ASSIGNING PHONE1 IN THIS CASE
                    } else {
                        $staff->phone1 = $phone1;
                    }
                    $staff->acc_id = $acc->acc_id;
                    $staff->function = $function;
                    $staff->civility = $civility;
                    $staff->sexe = $staff->$sexe;
                    try {
                        $staff->save();
                        $syear = new StaffYear();
                        $syear->staff_id = $staff->staff_id;
                        $syear->sy_id = $sy_id;
                        //echo "\$sy_id = $sy_id | \$staff_id = $staff->staff_id";
                        try {
                            $syear->save();
                            echo 1;
                        } catch (Exception $e3) {
                            echo "<br/>" . $e3->getMessage() . "<br/>";
                            echo "-4"; //failed to save Staff_year;
                            try {
                                //$acc->delete();
                                $staff->delete();
                            } catch (Exception $ex) { //DO NOTHING
                                echo "<br/>\$ex" . $e3->getMessage() . "<br/>";
                            }
                        }
                    } catch (Exception $e2) {
                        echo "<br/>" . $e2->getMessage() . "<br/>";
                        echo "-3"; //failed to save Staff;
                        try {
                            $acc->delete();
                        } catch (Exception $exx) { //DO NOTHING
                        }
                    }
                } catch (Exception $e1) {
                    echo "<br/>" . $e1->getMessage() . "<br/>";
                    echo "-2"; //failed to save account
                }
            }
        } catch (Exception $e) {
            echo '<br/>Message: ' . $e->getMessage();
            echo "-5"; //Error occurs
        }
    }
    
    public function allStaffs1(Request $request)
    {
        $connection = $request->input("connection");
        $year = $request->input("year");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            $staff = DB::select(
                "SELECT staff.staff_id, staff.name, staff.surname, staff.phone1, staff.function, 
                            staff.sexe, staff.civility, account.acc_id, account.login, account.pwd, 
                            account.type, account.email, staff.dob, staff.pob, staff.matricule, staff.posting_decision,
                            staff.grade, staff.region, staff.department, staff.arrodissement, staff.numeroRecrutement,
                            staff.provenantDe, staff.dateReprise, staff.diplome, staff.specilitee, staff.matiereEnseignee,
                            staff.dateEntree, staff.date1erePrise, staff.matricule
                            FROM 
                                `staff`, account, staff_year 
                                WHERE 
                                    staff.acc_id = account.acc_id 
                                    AND staff.staff_id=staff_year.staff_id 
                                    AND staff_year.sy_id = $sy_id
                                    ORDER BY staff.name, staff.function, staff.civility;"
            );
            return response()->json($staff, 200);
        } catch (Exception $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }
    
    public function allStaffs2(Request $request)
    {
        //TEACHER STAFF ARE ARE THOSE HAVING FUNCTIONS IN(0[ENS.], 1[SG], 2[CENSEUR], 6[CHIEF OF WORK])
        $connection = $request->input("connection");
        $year = $request->input("year");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            $staff = DB::select(
                "SELECT staff.staff_id, staff.name FROM `staff`
                WHERE staff_id IN(SELECT staff_year.staff_id FROM staff_year 
		                WHERE staff_year.sy_id = $sy_id)
                    ORDER BY staff.name;" 
            );
            return response()->json($staff, 200);
        } catch (Exception $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }

    public function teachFromAcc(Request $request)
    {
        $connection = $request->input("connection");
        $acc_id = $request->input("acc_id");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try { 

            $staff = DB::select(
                "SELECT*FROM staff WHERE staff.acc_id = $acc_id"
            );
            return response()->json($staff, 200);
        } catch (Exception $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }

    public function allStaffsOfaSC(Request $request)
    {   //FIND ALL THE STAFF TEACHING A COURSE IN A CLASSE
        $connection = $request->input("connection");
        $year = $request->input("year");
        $section = $request->input("section");
        $subject_id = $request->input("subject_id");
        $classe_id = $request->input("classe_id");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";

        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $section_id = MyHelper::getSectionID($section);
            $staff = DB::select(
                "SELECT staff.staff_id, staff.name FROM staff WHERE staff.staff_id 
                    IN (SELECT subject_classe_staff.staff_id FROM subject_classe_staff
                        WHERE subject_classe_staff.subject_classe_id 
                        IN(SELECT subject_classe.subject_classe_id FROM subject_classe 
                            WHERE subject_classe.classe_id = $classe_id
                                AND subject_classe.subject_id = $subject_id
                                AND subject_classe.sy_id = $sy_id
		                        AND subject_classe.section_id = $section_id))  "
            );
            return response()->json($staff, 200);
        } catch (Exception $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }

    public function subjectTaughtByaStaff(Request $request)
    {   //FIND ALL THE SUBJECTS ASSIGNED TO STAFF giving for each the classe and subject title
        $connection = $request->input("connection");
        $year = $request->input("year");
        $section = $request->input("section");
        $staff_id = $request->input("staff_id");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";

        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $section_id = MyHelper::getSectionID($section);
            $staff = DB::select(
                "SELECT classe.classe_id, subject.subject_id,classe.classe_name, subject.subject_title FROM classe, subject, subject_classe, staff, subject_classe_staff
                    WHERE classe.classe_id = subject_classe.classe_id
                        AND subject.subject_id = subject_classe.subject_id
                        AND subject_classe_staff.subject_classe_id = subject_classe.subject_classe_id
                        AND staff.staff_id = subject_classe_staff.staff_id
                        AND subject_classe.sy_id = $sy_id
                        AND subject_classe.section_id = $section_id
                        AND staff.staff_id = $staff_id"
            );
            return response()->json($staff, 200);
        } catch (Exception $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }

    public function allClassMastersOfYear(Request $request)
    {
        //ClassMasters STAFF ARE ARE THOSE HAVING FUNCTIONS = 0 ; (0[ENS.], 1[SG], 2[CENSEUR], 6[CHIEF OF WORK])
        $connection = $request->input("connection");
        $year = $request->input("year");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            $staff = DB::select(
                "SELECT staff.staff_id, staff.name FROM `staff`
                WHERE staff.function = 0                
                    AND staff_id IN(SELECT staff_year.staff_id FROM staff_year 
		                WHERE staff_year.sy_id = $sy_id)
                    ORDER BY staff.name;" // 0==>Enseignant; 2==>Censeur; 1==>Sg; 6==>Chef of work
            );
            return response()->json($staff, 200);
        } catch (Exception $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }

    public function allSgOfYear(Request $request)
    {
        //ClassMasters STAFF ARE ARE THOSE HAVING FUNCTIONS = 0 ; (0[ENS.], 1[SG], 2[CENSEUR], 6[CHIEF OF WORK])
        $connection = $request->input("connection");
        $year = $request->input("year");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            $staff = DB::select(
                "SELECT staff.staff_id, staff.name FROM `staff`
                WHERE staff.function = 1                
                    AND staff_id IN(SELECT staff_year.staff_id FROM staff_year 
		                WHERE staff_year.sy_id = $sy_id)
                    ORDER BY staff.name;" // 0==>Enseignant; 2==>Censeur; 1==>Sg; 6==>Chef of work
            );
            return response()->json($staff, 200);
        } catch (Exception $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }

    public function allTeachingStaffOfYear(Request $request)
    {
        //TEACHER STAFF ARE ARE THOSE HAVING FUNCTIONS IN(0[ENS.], 1[SG], 2[CENSEUR], 6[CHIEF OF WORK])
        $connection = $request->input("connection");
        $year = $request->input("year");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            $staff = DB::select(
                "SELECT staff.staff_id, staff.name FROM `staff`
                WHERE (staff.function = 0 OR staff.function = 2 
                    OR staff.function = 1 OR staff.function = 6)
                    AND staff_id IN(SELECT staff_year.staff_id FROM staff_year 
		                WHERE staff_year.sy_id = $sy_id)
                    ORDER BY staff.name;" // 0==>Enseignant; 2==>Censeur; 1==>Sg; 6==>Chef of work
            );
            return response()->json($staff, 200);
        } catch (Exception $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }

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
    public function show(Staff $staff)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Staff $staff)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Staff $staff)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Staff $staff)
    {
        //
    }
    
    public function arrangeSG()
    {
        $schools = array(  
            "CES_DE_LDIRI",
            "CES_DE_ZIMADO",
            "CETIC_DE_BOGO",
            "CETIC_DE_DARGALA",
            "CETIC_DE_GADOUA",  
            "CETIC_DE_MAKARY",
            "ENIEG_DE_GUIDER",
            "ENIEG_BILINGUE_DE_MAROUA",
            "GBTHS_MEWOULOU", 
            "GHS_MINAWAO",          
            "LB_BOGO",  
            "LB_MAKALINGAI",
            "LB_ZAMAI", 
            "LT_GAZAWA",
            "LT_KOZA",
            "LT_LOGONE_BIRNI",
            "LT_MERI",
            "LT_MINDIF",
            "LT_MORA",
            "LYCEE_CLASSIQUE_DE_MAROUA", 
            "LYCEE_DE_BALAZA_ALCALI",  
            "LYCEE_DE_DOUALARE",
            "LYCEE_DE_GABOUA",
            "LYCEE_DE_GODOLA",
            "LYCEE_DE_GUIDER",
            "LYCEE_DE_HARDE_MAROUA",
            "LYCEE_DE_HOULA",
            "LYCEE_DE_KALLIAO",
            "LYCEE_DE_KOTRABA",
            "LYCEE_DE_LOGONE_BIRNI",
            "LYCEE_DE_MAKABAYE",
            "LYCEE_DE_MASSAKAL",
            "LYCEE_DE_MAROUA_SALAK",            
            "LYCEE_DE_MOGOM",
            "LYCEE_DE_MEME",
            "LYCEE_DE_MERI",
            "LYCEE_DE_MESKINE",
            "LYCEE_DE_MOKIO",
            "LYCEE_DE_PITOA",
            "LYCEE_DE_WAZA", 
            "TEST"
        );
        
        /*
        $schools = array(
            "mysql",
        );*/

        for ($k = 0; $k < count($schools); $k++) {
            $connection = $schools[$k];
            echo ("$connection <br/>");
            config(["database.default" => $connection]);
            $staffs = DB::select("SELECT *FROM staff");
            if (!is_null($staffs)) {
                echo "<br/>";
                foreach ($staffs as $st) {
                    if ($st->function == "1") {
                        echo $st->name . "<br/>";
                        $acc_id = $st->acc_id;
                        $accounts = DB::select("SELECT*FROM account WHERE account.acc_id = $acc_id");
                        if(!is_null($accounts)) {
                            foreach ($accounts as $acc) {
                                $id = $acc->acc_id;
                                $ref = Account::find($id );
                                $ref->type = 3;
                                try {
                                  $ref->update();
                                } catch (Exception $e) { 
                                    echo "<br/>".$e;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    
    
    public function arrangeSGSimple()
    {
        $staffs = DB::select("SELECT *FROM staff");
        if (!is_null($staffs)) {
            echo "<br/>";
            foreach ($staffs as $st) {
                //SG == 1 (acc: 3); Enseignant == 0 (acc: 5); Censeur == 2 (acc: 8) 
                $type = 5;
                if ($st->function == "0") {
                    $type = 5; //Enseignant
                } else if ($st->function == "1") {
                    $type = 3; //SG
                } else if ($st->function == "2") {
                    $type = 8; //CENSEUR
                } else if ($st->function == "3") {
                    $type = 2; //PROVISEUR
                } else if ($st->function == "4") {
                    $type = 4; //BURSA
                } else if ($st->function == "5") {
                    $type = 2; //DIRECTOR
                } else if ($st->function == "6") {
                    $type = 8; //CHIEF OF WORK/CENSEUR
                }

                $acc_id = $st->acc_id;
                $accounts = DB::select("SELECT*FROM account WHERE account.acc_id = $acc_id");
                if (!is_null($accounts)) {
                    foreach ($accounts as $acc) {
                        $id = $acc->acc_id;
                        $ref = Account::find($id);
                        $ref->type = $type;
                        try {
                            $ref->update();
                        } catch (Exception $e) {
                            echo "<br/>" . $e;
                        }
                    }
                }
            }
        }
    }
    
    
    
    
}
