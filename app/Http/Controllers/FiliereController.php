<?php

namespace App\Http\Controllers;

use App\Models\Filiere;
use App\Models\FiliereYear;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\SectionYear;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FiliereController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function deleteManyFiliere(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $year  = $request->input("year");
        $data = $request->input("data");
        $data_size = $request->input("data_size");

        $fList = json_decode($data, true);
        $n = count($fList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]";
        //$allAffected = 1; //interpreted as true. 0-->false
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        //echo "sy_id: $sy_id</br>";
        foreach ($fList as $fil) {
            $filiere_id = $fil["filiere_id"];
            $filiereRef = Filiere::find($filiere_id);
            $allAffected = 1;
            try {
                //Set speciality_id of all classe_year that are in speciality in that filière to null
                $val1 = DB::select("UPDATE classe_year SET classe_year.speciality_id = NULL 
                        where classe_year.speciality_id 
                        IN(SELECT speciality.speciality_id FROM speciality 
                        WHERE speciality.speciality_id IN (SELECT speciality_year.speciality_id FROM speciality_year 
                        WHERE speciality_year.filiere_id = $filiere_id 
                         AND speciality_year.sy_id = $sy_id))");
                $val2 = DB::select("DELETE FROM speciality_year 
                                WHERE speciality_year.sy_id = $sy_id 
                                    AND speciality_year.filiere_id = $filiere_id"); //ASSUMING A FILIERE IS IN ONLY ON SECTION IN A SCHOOL YEAR
                
                //Supprimons toutes les specialités de cette filiere
                $val3 = DB::select("DELETE FROM speciality WHERE speciality.speciality_id NOT 
                            IN(SELECT speciality_year.speciality_id FROM speciality_year)");


                $res = FiliereYear::where("filiere_id", '=', $filiere_id)
                    ->where('sy_id', '=', $sy_id)
                    ->first();
                //echo "filiere_id [$filiere_id] --> filiere_year_id[$res->filiere_year_id] <br/>";
                $tmp = 0;
                if (!is_null($res)) { //VERY IMPORTANT TO CHECH IF NULL
                    $tmp = $res->delete();
                }
                //echo "rows affected AFTTER deleting fyear [$tmp]<br/>";

                if ($tmp == 1) { //The filiere year has been deleted successfully
                    //On peut eventuellement supprimer la filiere si elle n'est pas dans un autre schoolyear
                    $fyList = FiliereYear::where('filiere_id', '=', $filiere_id)->get();
                    $count = $fyList->count();
                    if ($count == 0) {
                        //La filiere n'apparait dans aucune autre annee
                        if (!is_null($filiereRef)) {
                            $filiereRef->delete();
                        }
                    }
                } else {
                    //La filiere ne sera pas supprimé
                    $allAffected = 0;
                    //echo "filireId: $filiere_id will not be affected Since res = $res<br/> ";
                }
            } catch (Exception $ex) {
                $allAffected = 0;
                //echo "ERROR " . $ex->getMessage();
            }
        } //END FOR
        //return response($allAffected, 200);
        echo (string) $allAffected; //1--> All filere successfully deleted; 0--> Failed to save at least one
    }
    public function updateManyFiliere(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");

        $fList = json_decode($data, true);
        //$n = count($fList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]";
        $allAffected = 1; //interpreted as true. 0-->false
        config(["database.default" => $connection]);
        foreach ($fList as $fil) {
            // code
            $filiere_id = $fil["filiere_id"];
            $nom_filiere = $fil["nom_filiere"];
            //echo"id: ". $fil["filiere_id"] ." -- nom_filiere: ". $fil["nom_filiere"]."  ";
            $affected = DB::table('filiere')
                ->where('filiere_id', $filiere_id)
                ->update(['nom_filiere' => $nom_filiere]);
            //echo "[$filiere_id]-->$affected   ";
            if ($affected != 1) {
                $allAffected = 0;
            }
        }
        echo "$allAffected"; //1--> All filere successfully modified; 0--> Failed to save at least one
    }

    public function updateFiliere(Request $request)
    {
        $connection = $request->input("connection");
        $nom_filiere_old = $request->input("nom_filiere_old");
        $nom_filiere_new = $request->input("nom_filiere_new");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- nom_filiere_old: $nom_filiere_old -- nom_filiere_new: $nom_filiere_new \n";
        try {
            $filiere = Filiere::where('nom_filiere', '=', $nom_filiere_old)->first();
            if (is_null($filiere)) {
                echo "-1"; //Operation failed
            } else {
                $filiere->nom_filiere = $nom_filiere_new;
                $filiere->Update();
                echo "1"; //Operation successfull
            }
        } catch (Exception $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            //return response()->json([], 300); //ERROR OCCURS
            echo "-2"; //Le nom de la filiere existe deja
        }
    }

    public function allFilieres(Request $request)
    {
        $connection = $request->input("connection");
        $year = $request->input("year");
        $sectionName = $request->input("section");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        $sy_id = MyHelper::getSchoolYearID($year);
        //$section_year_id = MyHelper::getSectionYearID($sectionName, $sy_id);
        $section_id = MyHelper::getSectionID($sectionName);
        $filieres = MyHelper::getFiliereOfYearAndSection($sy_id, $section_id);
        return response()->json($filieres, 200);
    }
    public function saveFiliere(Request $request)
    {
        $connection = $request->input("connection");
        $year = $request->input("year");
        $nom_filiere = $request->input("nom_filiere");
        $sectionName = $request->input("section");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Nom_Filiere: $nom_filiere -- Section: $section";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $section_id = MyHelper::getSectionID($sectionName);
            $myOption = new Filiere();
            $myOption->nom_filiere = $nom_filiere;
            $myOption->save();
            $optionYear = new FiliereYear();
            $optionYear->filiere_id = $myOption->filiere_id;
            $optionYear->section_id = $section_id;
            $optionYear->sy_id = $sy_id;
            try {
                $optionYear->save();
            } catch (Exception $ex) {
                //Failed to save the corresponding speciality year;
                try {
                    $myOption->delete(); //to avoid inconsistancy
                } catch (Exception $exx) {
                }
                echo "-2";
            }
            echo "1"; //Operation is successfull
        } catch (Exception $e) {
            //echo '<br/>Message: ' .$e->getMessage();
            echo "-1"; //La filiere existe déja
        }
    }
    public function index() {}

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
    public function show(Filiere $filiere)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Filiere $filiere)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Filiere $filiere)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Filiere $filiere)
    {
        //
    }
}
