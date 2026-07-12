<?php

namespace App\Http\Controllers;

use App\Models\Groupe;
use App\Models\GroupeYear;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GroupeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function deleteManyGroupes(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $year  = $request->input("year");
        $data = $request->input("data");
        $data_size = $request->input("data_size");

        $groupList = json_decode($data, true);
        $n = count($groupList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]";
        //$allAffected = 1; //interpreted as true. 0-->false
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        //echo "sy_id: $sy_id</br>";
        foreach ($groupList as $group) {
            $groupe_id = $group["groupe_id"];
            $grpRef = Groupe::find($groupe_id);
            $allAffected = 1;
            try {
                $res = GroupeYear::where("groupe_id", '=', $groupe_id)
                    ->where('sy_id', '=', $sy_id)
                    ->first();
                $tmp = 0;
                if (!is_null($res)) { //VERY IMPORTANT TO CHECH IF NULL
                    $tmp = $res->delete(); //To delete the groupe_year found
                }
                //echo "rows affected AFTTER deleting group_year [$tmp]<br/>";

                if ($tmp == 1) { //The speciality year has been deleted successfully
                    //On peut eventuellement supprimer le groupe s'il n'est pas dans un autre schoolyear
                    $grpList = GroupeYear::where("groupe_id", '=', $groupe_id)
                        ->get();
                    $count = $grpList->count();
                    if ($count == 0) {
                        //Le groupe n'apparait dans aucune autre annee
                        if (!is_null($grpRef)) {
                            $grpRef->delete();
                        }
                    }
                } else {
                    //Le groupe ne sera pas supprimé
                    $allAffected = 0;
                    //echo "spId: $groupe_id will not be affected Since res = $res<br/> ";
                }
            } catch (Exception $ex) {
                $allAffected = 0;
                //echo "ERROR " . $ex->getMessage();
            }
        } //END FOR
        //return response($allAffected, 200);
        echo (string) $allAffected; //1--> All groupes successfully deleted; 0--> Failed to save at least one
    }

    public function updateManyGroupes(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");

        $groupList = json_decode($data, true);
        //$n = count($fList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]";
        $allAffected = 1; //interpreted as true. 0-->false
        config(["database.default" => $connection]);
        foreach ($groupList as $grp) {
            // code
            $groupe_id = $grp["groupe_id"];
            $groupe_name = $grp["groupe_name"];
            $affected = DB::table('groupe')
                ->where('groupe_id', $groupe_id)
                ->update(['groupe_name' => $groupe_name]);
            if ($affected != 1) {
                $allAffected = 0;
            }
        }
        echo "$allAffected"; //1--> All groupes successfully modified; 0--> Failed to save at least one
    }
    
    
    public function saveGroupe(Request $request)
    {
        $connection = $request->input("connection");
        $year = $request->input("year");
        $groupe_name = $request->input("groupe_name");
        $section_name = $request->input("section");
        config(["database.default" => $connection]);
        //echo "Connection: $connection <br/>Year: $year <br/>sp_name: $speciality_name"
        //    . "<br/>Filiere: $nom_filiere <br/>Description: $desc<br/>Section: $section_name <br/>";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $section_id = MyHelper::getSectionID($section_name);

            $grp = new Groupe();
            $grpYear = new GroupeYear();

            $grp->groupe_name = $groupe_name;
            $groupe_id = 1;
            try {
                $groupeTmp = Groupe::where("groupe_name", "=", $groupe_name)
                    ->first();
                if (is_null($groupeTmp)){
                    $grp->save();
                    $groupe_id = $grp->groupe_id; //ID OF gropueYear CAN ONLY BE OBTAINED AFTER SAVING the grp (Groupe)
                }else{
                    $groupe_id = $groupeTmp->groupe_id;
                }
                
                $grpYear->groupe_id = $groupe_id; 
                $grpYear->sy_id = $sy_id;
                $grpYear->section_id = $section_id;
                $grpYear->save();
                echo "1"; //Operation is successfull*/
            } catch (Exception $ex) {
                //A groupe with may exist already
                //If exception then grp or grpYear failed to save. We delete them to avoid inconsitency
                $grp->delete();
                try {
                    $grpYear->delete();
                } catch (Exception $exx) {
                }
                //echo '<br/>Message: ' . $ex->getMessage() . '<br>';
                echo "-2"; //Operation failed OR groupe exists already
            }
        } catch (Exception $e) {
            //echo '<br/>Message: ' . $e->getMessage();
            echo "-1"; //Le groupe existe déja
        }
    }
    

    public function allGroupes(Request $request)
    {
        //-------------------- THIS METHOD FETCHES ALL GROUPES OF THE SECTIONS IN The current schoolyear
        $connection = $request->input("connection");
        $year = $request->input("year");
        $section_name = $request->input("section");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $section_id = MyHelper::getSectionID($section_name);

            $groupes = MyHelper::getGroupesOfYearOfSection($sy_id, $section_id);
            return response()->json($groupes, 200);
        } catch (Exception $e) {
            //echo '<br/>ERROR: ' .$e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }

    public function groupesOfYearAndSection(Request $request)
    {   //REPETITION CETTE METHODE EXISTE DEJA AU NOM DE allGroupes
        //-------------------- THIS METHOD FETCHES ALL GROUPES OF THE SECTIONS IN The current schoolyear
        $connection = $request->input("connection");
        $year = $request->input("year");
        $section_name = $request->input("section");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $section_id = MyHelper::getSectionID($section_name);

            $subjects = DB::select("SELECT groupe.groupe_id, groupe.groupe_name FROM groupe
                    WHERE groupe.groupe_id IN (SELECT groupe_year.groupe_id 
                        FROM groupe_year WHERE groupe_year.sy_id = $sy_id
                        AND groupe_year.section_id = $section_id)");
            return response()->json($subjects, 200);
        } catch (Exception $e) {
            //echo '<br/>ERROR: ' .$e->getMessage();
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
    public function show(Groupe $groupe)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Groupe $groupe)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Groupe $groupe)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Groupe $groupe)
    {
        //
    }
}
