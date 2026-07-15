<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Staff;
use App\Models\StaffYear;
use App\Models\SubjectClasse;
use App\Models\SubjectClasseStaff;
use \Throwable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StaffController extends Controller
{

    public function modifyStaff(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'staff_id' => 'required|integer|min:1',
                'grade' => 'nullable|string',
                'region' => 'nullable|string',
                'department' => 'nullable|string',
                'arrodissement' => 'nullable|string',
                'numeroRecrutement' => 'nullable|string',
                'provenantDe' => 'nullable|string',
                'dateReprise' => 'nullable|string',
                'diplome' => 'nullable|string',
                'specilitee' => 'nullable|string',
                'matiereEnseignee' => 'nullable|string',
                'dateEntree' => 'nullable|string',
                'date1erePrise' => 'nullable|string',
                'matricule' => 'nullable|string',
                'dob' => 'nullable|string',
                'pob' => 'nullable|string',
                'posting_decision' => 'nullable|string',

            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }
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
            return response()->json([
                'status' => true,
                'message' => 'Staff updated successfully',
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Staff update failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function subjectTaughtByaStaff2(Request $request)
    {   //FIND ALL THE SUBJECTS ASSIGNED TO STAFF giving for each the classe and subject title
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
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while retrieving subjects taught by the staff: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function AllAttributionsOfSection(Request $request)
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
        } catch (\Throwable $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }

    public function removeALLCourses(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'section' => 'required|string',
                'staff_id' => 'required|integer|min:1',
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
        $staff_id = $request->input("staff_id");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        $sy_id = MyHelper::getSchoolYearID($year);
        $section_id = MyHelper::getSectionID($section);

        try {
            $result = MyHelper::removeAStaffCourses($sy_id, $section_id, $staff_id);
        } catch (\Throwable $e) {
            //echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([
                'status' => false,
                'message' => 'Failed to remove courses for staff [' . $staff_id . ']: \n[' . $e->getMessage() . ']',
            ], 500);
        }
        return response()->json([
            'status' => true,
            'message' => 'Successfully removed all courses from staff [' . $staff_id . ']',
        ], 200);
    }

    public function removeACourse(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'section' => 'required|string',
                'subject_id' => 'required|integer|min:1',
                'classe_id' => 'required|integer|min:1',
                'staff_id' => 'required|integer|min:1',
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
        $subject_id = $request->input("subject_id");
        $classe_id = $request->input("classe_id");
        $staff_id = $request->input("staff_id");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";

        $errMsg = "";
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
                    $id =  $st->id;
                }

                try {
                    $ref = SubjectClasseStaff::find($id)->delete();
                } catch (\Throwable $e) {
                    //"<br/>" . $e->getMessage() . "<br/>"; //Fatal error, since id has to exist according to above DB::select()
                    $result = -1;
                    $errMsg = $e->getMessage();
                }
            } else {
            }
        } catch (\Throwable $e) {
            //echo '<br/>ERROR: ' . $e->getMessage();
            $result = -2; //ERROR OCCURS
            $errMsg = $e->getMessage();
        }
        if ($result == 1) {
            return response()->json([
                'status' => true,
                'message' => 'Course removed successfully',
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Failed to remove course [' . $subject_id . '] from staff [' . $staff_id . ']: \n[' . $errMsg . ']',
            ], 500);
        }
    }

    public function assignACourse(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'section' => 'required|string',
                'subject_id' => 'required|integer|min:1',
                'classe_id' => 'required|integer|min:1',
                'staff_id' => 'required|integer|min:1',
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
        $subject_id = $request->input("subject_id");
        $classe_id = $request->input("classe_id");
        $staff_id = $request->input("staff_id");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";

        $errMsg = "";
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
                    } catch (\Throwable $e) {
                        $errMsg = $e->getMessage();
                        $result = -1;
                    }
                } else {
                    $errMsg = "Subject $subject_id is not taught in class $classe_id for year $year and section $section\nSo it can't be assigned to staff $staff_id";
                    $result = 0;
                }
            }
        } catch (\Throwable $e) {
            $errMsg = $e->getMessage();
            $result = -2; //ERROR OCCURS
        }

        if ($result == 1) {
            return response()->json([
                'status' => true,
                'message' => 'Course assigned successfully',
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Failed to assign course: \n[' . $errMsg . ']',
            ], 500);
        }
    }

    public function batchAssignCourses(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'section' => 'required|string',
                'data' => 'required|json',
                'data_size' => 'nullable|integer|min:0',
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
        $section = $request->input("section");

        $attribList = json_decode($data, true);
        //$n = count($attribList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]";

        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        $section_id = MyHelper::getSectionID($section);

        $msg = "";
        $k = 1; //1--> Success. 0--> Some classes not assignes 
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
                        } catch (\Throwable $e) {
                            //"<br/>" . $e->getMessage() . "<br/>";
                            $k = -1;
                            $msg = $e->getMessage();
                        }
                    } else {
                        $k = 0;
                        $msg = "Subject ['" . $subject_id . "'] is not taught in class ['" . $classe_id . "'] for year ['" . $year . "'] and section ['" . $section . "']\nSo it can't be assigned to staff ['" . $staff_id . "']";
                    }
                }
            } catch (\Throwable $e) {
                // '<br/>ERROR: ' . $e->getMessage();
                $msg = $e->getMessage();
                $k = -2; //ERROR OCCURS
            }
        } //END FOR

        if ($k == 1) { //success
            return response()->json([
                'status' => true,
                'message' => 'All courses assigned successfully',
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => "Failed assignment of at least one course: \n" . $msg,
            ], 400);
        } //K=1--> All attributions Applied; K=0, -1 ou -2. --> Failed to save at least one
    }


    public function batchRemoveCourses(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'section' => 'required|string',
                'data' => 'required|json',
                'data_size' => 'nullable|integer|min:0',
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
        $section = $request->input("section");

        $attribList = json_decode($data, true);
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        $section_id = MyHelper::getSectionID($section);

        $msg = "";
        $k = 1; //1--> Success. 0--> Some classes not assignes  
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
                    } catch (\Throwable $e) {
                        //"<br/>" . $e->getMessage() . "<br/>"; //Fatal error, since id has to exist according to above DB::select()
                        $k = -1;
                        $msg = $e->getMessage();
                    }
                } else {
                }
            } catch (\Throwable $e) {
                //'<br/>ERROR: ' . $e->getMessage();
                $k = -2; //ERROR OCCURS
                $msg = $e->getMessage();
            }
        } //END FOR

        if ($k == 1) { //success
            return response()->json([
                'status' => true,
                'message' => 'All courses removed successfully',
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => "Failed to remove at least one course: \n" . $msg,
            ], 500);
        } //K=1--> All attributions Applied; K=0, -1 ou -2. --> Failed to save at least one
    }


    public function deleteManyStaffs(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'data' => 'required|json',
                'section' => 'required|string',
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
        $section = $request->input("section");
        $data_size = $request->input("data_size");

        $staffList = json_decode($data, true);
        $n = count($staffList);
        $allAffected = 1; //interpreted as true. 0-->false

        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        $section_id = MyHelper::getSectionID($section);
        foreach ($staffList as $st) {
            $staff_id = $st["staff_id"];
            try {
                $res = MyHelper::deleteAStaff($sy_id, $section_id, $staff_id);
            } catch (\Throwable $th) {
                $allAffected = 0;
            }
            /*
            if ($res < 0) {
                $allAffected = 0;
            }*/
        } //END FOR       
        //echo (string) $allAffected; //1--> All groupes successfully deleted; 0--> Failed to save at least one
        if ($allAffected == 1) {
            return response()->json([
                'status' => true,
                'message' => 'All staff deleted successfully',
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete at least one staff',
            ], 500);
        }
    }



    public function updateManyStaffs(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'data' => 'required|json',
                'data_size' => 'nullable|integer|min:0',
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

        $errMsg = "";
        $allAffected = 1; //interpreted as true. 0-->false  
        foreach ($stList as $st) {
            $staff_id = $st["staff_id"];
            $name = $st["name"];
            $surname = $st["surname"];
            if ($name == "null") {
                $name = "";
            }
            if ($surname == "null") {
                $surname = "";
            }
            $sexe = $st["sexe"];
            $phone1 = $st["phone1"];
            $function = $st["function"];
            $civility = $st["civility"];

            $login = $st["login"];
            $pwd = $st["pwd"];
            $acc_id = $st["acc_id"];
            try {

                $st2 = Staff::find($staff_id);
                $st2->name = $name;
                $st2->surname = $surname;
                $st2->sexe = $sexe;
                $st2->civility = $civility;
                $st2->function = $function;
                $query = $st2->update(); //Mettre a jour les champs except phone1;
                try {
                    $st2->phone1 = $phone1;
                    $tmp1 = DB::table('staff')
                        ->where('staff_id', $staff_id)
                        ->update(['phone1' => $phone1]);
                } catch (\Throwable $e) {
                    //Field Phone is unique so can't be suplicated
                    $allAffected = 0;
                    //echo "<br/>".$e->getMessage()."<br/>";   
                    //echo "allAffected: $allAffected FOR $st2->phone1 as \Throwable ouccurs<br/>";  
                    $errMsg = "Phone number $phone1 is already used by another staff. Please use another phone number for $name";
                }

                //LETS UPDATE THE RELATED ACCOUNT
                $acc = Account::find($acc_id);
                $acc->pwd = $pwd;
                $acc->login = $login;
                try {
                    $tmp2 = $acc->update();
                } catch (\Throwable $e) {
                    //Login is unique so can't be duplicated
                    //echo "<br/>".$e->getMessage()."<br/>";
                    $allAffected = 0;
                    $errMsg = "Login $login is already used by another staff. Please use another login for $name";
                }
            } catch (\Throwable $ex) {
                //echo $ex->getMessage();
                $allAffected = 0;
                $errMsg = $ex->getMessage();
            }
        }
        //echo "$allAffected"; //1--> All classes successfully modified; 0--> Failed to save at least one
        if ($allAffected == 1) {
            return response()->json([
                'status' => true,
                'message' => 'All staff updated successfully',
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update at least one staff: \n[' . $errMsg . ']',
            ], 500);
        }
    }

    public function saveManyStaffs(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'data' => 'required|json',
                'section' => 'required|string',
                'data_size' => 'nullable|integer|min:0',
                'override' => 'nullable|string',

            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }
        $connection = $request->input("connection");
        $data = $request->input("data");
        $section = $request->input("section");
        $data_size = $request->input("data_size");
        $year = $request->input("year");
        $override = $request->input("override");

        $stList = json_decode($data, true);

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

        $errMsg = "";
        $allAffected = 1; //interpreted as true. 0-->false 
        foreach ($stList as $st) {
            $name = $st["name"];
            $surname = $st["surname"];
            if ($name == "null") {
                $name = "";
            }
            if ($surname == "null") {
                $surname = "";
            }
            $phone1 = $st["phone1"];
            $function = $st["function"];
            $civility = $st["civility"];
            $sexe = $st["sexe"]; //M or F
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
                $acc->login = $login . "" . rand(1000, 9999);
            }


            try {
                //Save account
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
                $staff->sexe = $sexe;
                try {
                    $staff->save();
                    $syear = new StaffYear();
                    $syear->staff_id = $staff->staff_id;
                    $syear->sy_id = $sy_id;
                    //echo "\$sy_id = $sy_id | \$staff_id = $staff->staff_id";
                    try {
                        $syear->save();
                    } catch (\Throwable $e3) {
                        // echo "<br/>" . $e3->getMessage() . "<br/>";
                        // echo "-4"; //failed to save Staff_year;
                        $errMsg = $e3->getMessage();
                        $allAffected = 0;
                        try {
                            $staff->delete();
                        } catch (\Throwable $ex) { //DO NOTHING
                            //echo "<br/>\$ex" . $e3->getMessage() . "<br/>";
                            $errMsg = $e3->getMessage();
                            $allAffected = 0;
                        }
                    }
                } catch (\Throwable $e2) {
                    // echo "<br/>" . $e2->getMessage() . "<br/>";
                    // echo "-3"; //failed to save Staff;
                    $errMsg = $e2->getMessage();
                    $allAffected = 0;
                    try {
                        $acc->delete();
                    } catch (\Throwable $exx) { //DO NOTHING
                        $errMsg = $exx->getMessage();
                        $allAffected = 0;
                    }
                }
            } catch (\Throwable $e1) {
                // echo "<br/>" . $e1->getMessage() . "<br/>";
                // echo "-2"; //failed to save account
                $errMsg = $e1->getMessage();
                $allAffected = 0;
            }
        }

        self::arrangeSGSimple();
        //echo "$allAffected"; //1--> All classes successfully modified; 0--> Failed to save at least one
        if ($allAffected == 1) {
            return response()->json([
                'status' => true,
                'message' => 'All staff saved successfully',
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Failed to save at least one staff: \n[' . $errMsg . ']',
            ], 500);
        }
    }



    public function saveStaff(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'name' => 'required|string|max:80|min:2',
                'surname' => 'nullable|string|max:80|min:2',
                'phone1' => 'nullable|string|max:20|min:2',
                'login' => 'required|string|max:25|min:4',
                'pwd' => 'required|string|max:25|min:4',
                'sexe' => 'required|string|max:1|min:1',
                'function' => 'required|integer|max:10|min:0',
                'civility' => 'nullable|string|max:5|min:2',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }
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
                            return response()->json([
                                'status' => true,
                                'message' => 'Staff saved successfully',
                            ], 200);
                        } catch (\Throwable $e3) {
                            try {
                                $staff->delete();
                            } catch (\Throwable $ex) { //DO NOTHING
                                //"<br/>\$ex" . $e3->getMessage() . "<br/>";
                            }
                            return response()->json([
                                'status' => false,
                                'message' => 'Failed to save staff year: Consequently, staff was not saved: ' . $e3->getMessage(),
                            ], 500);
                        }
                    } catch (\Throwable $e2) {
                        try {
                            $acc->delete();
                        } catch (\Throwable $exx) { //DO NOTHING
                        }
                        return response()->json([
                            'status' => false,
                            'message' => 'Failed to save staff: ' . $e2->getMessage(),
                        ], 500);
                    }
                } catch (\Throwable $e1) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Failed to save staff account: ' . $e1->getMessage(),
                    ], 500);
                }
            }
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while saving staff: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function allStaffs1(Request $request)
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
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while retrieving staff: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function allStaffs2(Request $request)
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
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            $staff = DB::select(
                "SELECT staff.staff_id, staff.name, staff.surname FROM `staff`
                WHERE staff_id IN(SELECT staff_year.staff_id FROM staff_year 
		                WHERE staff_year.sy_id = $sy_id)
                    ORDER BY staff.name;"
            );
            return response()->json($staff, 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while retrieving staff: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function teachFromAcc(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'acc_id' => 'required|integer|min:1',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }
        $connection = $request->input("connection");
        $acc_id = $request->input("acc_id");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {

            $staff = DB::select(
                "SELECT*FROM staff WHERE staff.acc_id = $acc_id"
            );
            return response()->json($staff, 200);
        } catch (\Throwable $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }

    public function allStaffsOfaSC(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'section' => 'required|string',
                'subject_id' => 'required|integer|min:1',
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
        $section = $request->input("section");
        $subject_id = $request->input("subject_id");
        $classe_id = $request->input("classe_id");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";

        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $section_id = MyHelper::getSectionID($section);
            $staff = DB::select(
                "SELECT staff.staff_id, staff.name, staff.surname, staff.civility FROM staff WHERE staff.staff_id
                    IN (SELECT subject_classe_staff.staff_id FROM subject_classe_staff
                        WHERE subject_classe_staff.subject_classe_id 
                        IN(SELECT subject_classe.subject_classe_id FROM subject_classe 
                            WHERE subject_classe.classe_id = $classe_id
                                AND subject_classe.subject_id = $subject_id
                                AND subject_classe.sy_id = $sy_id
		                        AND subject_classe.section_id = $section_id))  "
            );
            return response()->json($staff, 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while retrieving staff for the subject and class: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function subjectTaughtByaStaff(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'section' => 'required|string',
                'staff_id' => 'required|integer|min:1',
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
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while retrieving subjects taught by the staff: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function allClassMastersOfYear(Request $request)
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
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            $staff = DB::select(
                "SELECT staff.staff_id, staff.name, staff.surname FROM `staff`
                WHERE staff.function = 0                
                    AND staff_id IN(SELECT staff_year.staff_id FROM staff_year 
		                WHERE staff_year.sy_id = $sy_id)
                    ORDER BY staff.name;" // 0==>Enseignant; 2==>Censeur; 1==>Sg; 6==>Chef of work
            );
            return response()->json($staff, 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching class masters: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function allSgOfYear(Request $request)
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
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            $staff = DB::select(
                "SELECT staff.staff_id, staff.name, staff.surname FROM `staff`
                WHERE staff.function = 1                
                    AND staff_id IN(SELECT staff_year.staff_id FROM staff_year 
		                WHERE staff_year.sy_id = $sy_id)
                    ORDER BY staff.name;" // 0==>Enseignant; 2==>Censeur; 1==>Sg; 6==>Chef of work
            );
            return response()->json($staff, 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching SG staff: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function allTeachingStaffOfYear(Request $request)
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
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            $staff = DB::select(
                "SELECT staff.staff_id, staff.name, staff.surname, staff.civility FROM `staff`
                WHERE (staff.function = 0 OR staff.function = 2 
                    OR staff.function = 1 OR staff.function = 6)
                    AND staff_id IN(SELECT staff_year.staff_id FROM staff_year 
		                WHERE staff_year.sy_id = $sy_id)
                    ORDER BY staff.name;" // 0==>Enseignant; 2==>Censeur; 1==>Sg; 6==>Chef of work
            );
            return response()->json($staff, 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching teaching staff: ' . $e->getMessage(),
            ], 500);
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


        for ($k = 0; $k < count($schools); $k++) {
            $connection = $schools[$k];
            echo ("\n$connection ");
            try {
                config(["database.default" => $connection]);
                $staffs = DB::select("SELECT *FROM staff");
                if (!is_null($staffs)) {
                    echo "<br/>";
                    foreach ($staffs as $st) {
                        if ($st->function == "1") {
                            echo $st->name . "<br/>";
                            $acc_id = $st->acc_id;
                            $accounts = DB::select("SELECT*FROM account WHERE account.acc_id = $acc_id");
                            if (!is_null($accounts)) {
                                foreach ($accounts as $acc) {
                                    $id = $acc->acc_id;
                                    $ref = Account::find($id);
                                    $ref->type = 3;
                                    try {
                                        $ref->update();
                                    } catch (\Throwable $e) {
                                        echo "<br/>" . $e;
                                    }
                                }
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                echo "<br/>" . $e->getMessage() . "<br/>";
            }
        }
    }


    public function arrangeSGSimple()
    {
        $staffs = DB::select("SELECT *FROM staff");
        if (!is_null($staffs)) {
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
                        } catch (\Throwable $e) {
                            echo "<br/>" . $e;
                        }
                    }
                }
            }
        }
    }
}
