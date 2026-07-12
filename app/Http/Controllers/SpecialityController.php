<?php

namespace App\Http\Controllers;

use App\Models\Filiere;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\SectionYear;
use App\Models\Speciality;
use App\Models\SpecialityYear;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SpecialityController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    //PB its joining well but selecting only One
    public function allSpecialites2(Request $request)
    {
        $connection = $request->input("connection");
        $year = $request->input("year");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $sp2List = DB::table('speciality')
                ->join('speciality_year', 'speciality.speciality_id', '=', 'speciality_year.speciality_year_id')
                ->join('filiere', 'filiere.filiere_id', '=', 'speciality_year.filiere_id')
                ->select('speciality.speciality_id', 'speciality.speciality_name', 'speciality.description', 'filiere.filiere_id', 'nom_filiere')
                ->get();

            $count = $sp2List->count();
            echo "$count \n";
            return response()->json($sp2List, 200);
        } catch (Exception $e) {
            //echo '<br/>ERROR: ' .$e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }

    public function allSpecialites(Request $request)
    {
        $connection = $request->input("connection");
        $year = $request->input("year");
        $section_name = $request->input("section");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $section_id = MyHelper::getSectionID($section_name);

            $specialities = MyHelper::getSpecialitiesOfYearOfSection($sy_id, $section_id);
            return response()->json($specialities, 200);
        } catch (Exception $e) {
            //echo '<br/>ERROR: ' .$e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }

    public function saveSpeciality(Request $request)
    {
        $connection = $request->input("connection");
        $year = $request->input("year");
        $speciality_name = $request->input("speciality_name");
        $nom_filiere = $request->input("nom_filiere");
        $desc = $request->input("desc");
        $section_name = $request->input("section");
        config(["database.default" => $connection]);
        //echo "Connection: $connection <br/>Year: $year <br/>sp_name: $speciality_name"
        //    . "<br/>Filiere: $nom_filiere <br/>Description: $desc<br/>Section: $section_name <br/>";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $section_id = MyHelper::getSectionID($section_name);
            //echo "sy_id: $sy_id  --  section_id: $section_id <br/>";
            //ID de la filiere dont on connait le nom
            $filiere = Filiere::all()
                ->where('nom_filiere', '=', $nom_filiere)
                ->first();
            $filiere_id = $filiere->filiere_id;

            $sp = new Speciality();
            $spYear = new SpecialityYear();

            $sp->speciality_name = $speciality_name;
            $sp->description = $desc;
            try {
                $sp->save();

                $spYear->speciality_id = $sp->speciality_id; //ID OF spYear CAN ONLY BE OBTAINED AFTER SAVING the sp (Speciality)
                $spYear->filiere_id = $filiere_id;
                $spYear->sy_id = $sy_id;
                $spYear->section_id = $section_id;
                $spYear->save();
                echo "1"; //Operation is successfull*/
            } catch (Exception $ex) {
                //If exception then sp or spyear failed to save. We delete them to avoid inconsitency
                $sp->delete();
                try {
                    $spYear->delete();
                } catch (Exception $exx) {
                }
                echo '<br/>Message: ' . $ex->getMessage() . '<br>';
                echo "-2"; //Operation failed
            }
        } catch (Exception $e) {
            //echo '<br/>Message: ' . $e->getMessage();
            echo "-1"; //La specialiteé existe déja
        }
    }

    public function updateManySpecialities(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $data = $request->input("data");
        //$data = $request->input("year");
        $data_size = $request->input("data_size");

        $spList = json_decode($data, true);
        //$n = count($fList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]";
        $allAffected = 1; //interpreted as true. 0-->false
        config(["database.default" => $connection]);
        foreach ($spList as $sp) {
            // code
            $speciality_id = $sp["speciality_id"];
            $speciality_name = $sp["speciality_name"];
            $description = $sp["description"];
            //echo "id: " . $sp["speciality_id"] . " -- speciality_name: " . $sp["speciality_name"]
            //    . "  description: " . $sp['description'];
            $affected = DB::table('speciality')
                ->where('speciality_id', $speciality_id)
                ->update(['speciality_name' => $speciality_name, 'description' => $description]);
            if ($affected != 1) {
                $allAffected = 0;
            }
        }
        echo "$allAffected"; //1--> All filere successfully modified; 0--> Failed to save at least one
    }
    
    public function deleteManySpecialities(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $year  = $request->input("year");
        $data = $request->input("data");
        $data_size = $request->input("data_size");

        $spList = json_decode($data, true);
        $n = count($spList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]";
        //$allAffected = 1; //interpreted as true. 0-->false
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        //echo "sy_id: $sy_id</br>";
        foreach ($spList as $sp) {
            $speciality_id = $sp["speciality_id"];
            $spRef = Speciality::find($speciality_id);
            $allAffected = 1;
            try {
                $val1 = DB::select("UPDATE classe_year SET classe_year.speciality_id = NULL 
                        where classe_year.speciality_id = $speciality_id
                            AND classe_year.sy_id = $sy_id"); //HERE WE ASSUME THAT THE HAS ONLY ONE SPECIALITY FOR A SCHOOL YEAR
                $res = SpecialityYear::where("speciality_id", '=', $speciality_id)
                    ->where('sy_id', '=', $sy_id)
                    ->first();
                //echo "speciality_id [$speciality_id] --> speciality_year_id[$res->speciality_year_id] <br/>";
                $tmp = 0;
                if (!is_null($res)) { //VERY IMPORTANT TO CHECH IF NULL
                    $tmp = $res->delete();
                }
                //echo "rows affected AFTTER deleting sp_years [$tmp]<br/>";

                if ($tmp == 1) { //The speciality year has been deleted successfully
                    //On peut eventuellement supprimer la speciality si elle n'est pas dans un autre schoolyear
                    $sp_yList = SpecialityYear::where('speciality_id', '=', $speciality_id)->get();
                    $count = $sp_yList->count();
                    if ($count == 0) {
                        //La speciality n'apparait dans aucune autre annee
                        if (!is_null($spRef)) {
                            $spRef->delete();
                        }
                    }
                } else {
                    //La speciality ne sera pas supprimé
                    $allAffected = 0;
                    //echo "spId: $speciality_id will not be affected Since res = $res<br/> ";
                }
            } catch (Exception $ex) {
                $allAffected = 0;
                //echo "ERROR " . $ex->getMessage();
            }
        } //END FOR
        //return response($allAffected, 200);
        echo (string) $allAffected; //1--> All speciality successfully deleted; 0--> Failed to save at least one
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
    public function show(Speciality $speciality)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Speciality $speciality)
    {
        //$this->getSchoolYearID("346");
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Speciality $speciality)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Speciality $speciality)
    {
        //
    }
}
