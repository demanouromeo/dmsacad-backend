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
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'data' => 'required|json',
                'data_size' => 'integer',
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

        $fList = json_decode($data, true);
        $n = count($fList);

        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        $allAffected = 1;
        foreach ($fList as $fil) {
            $filiere_id = $fil["filiere_id"];
            $filiereRef = Filiere::find($filiere_id);
            if (is_null($filiereRef)) {
                //echo "Filiere with id [$filiere_id] not found. Skipping deletion.\n"; 
                continue; //Skip to the next filiere. WE COUNT AS IF IT HAS BEEN DELETED SUCCESSFULLY. BECAUSE IT IS NOT IN THE DATABASE ANYMORE
            }
            //echo "deleting... filiere_id: $filiere_id --> filiere_name: " . $filiereRef->nom_filiere . "\n";
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
                    $forceDelete = DB::select("DELETE FROM filiere WHERE filiere.filiere_id not IN(SELECT filiere_year.filiere_id FROM filiere_year)");
                    //Ceci relève un problème de consistance dans la base de données. La filiere n'est pas reliée a un filiere_year. Elle ete inserer directement dans BD pour des test. Mais l'administrateur a du oublie de le supprimer.
                }
            } catch (Exception $ex) {
                //echec de suppression d'au moins une filiere
                $allAffected = 0;
                //echo "ERROR " . $ex->getMessage();
            }
        } //END FOR
        //return response($allAffected, 200);
        //echo (string) $allAffected; //1--> All filere successfully deleted; 0--> Failed to save at least one
        if ($allAffected == 1) {
            return response()->json([
                'status' => true,
                'message' => 'All filieres successfully deleted.',
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete at least one filiere.',
            ], 500);
        }
    }


    public function updateManyFiliere(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'data' => 'required|json',
                'data_size' => 'integer',
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
        if ($allAffected == 1) {
            return response()->json([
                'status' => true,
                'message' => 'All filieres successfully updated.',
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update at least one filiere.',
            ], 500);
        }
    }

    public function updateFiliere(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'nom_filiere_old' => 'required|string',
                'nom_filiere_new' => 'required|string',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }
        $connection = $request->input("connection");
        $nom_filiere_old = $request->input("nom_filiere_old");
        $nom_filiere_new = $request->input("nom_filiere_new");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- nom_filiere_old: $nom_filiere_old -- nom_filiere_new: $nom_filiere_new \n";
        try {
            $filiere = Filiere::where('nom_filiere', '=', $nom_filiere_old)->first();
            if (is_null($filiere)) {
                //echo "-1"; //NOT FOUND
                return response()->json([
                    'status' => false,
                    'message' => 'Operation failed: Filiere with name [' . $nom_filiere_old . '] not found.',
                ], 404); //404 = Not Found
            } else {
                $filiere->nom_filiere = $nom_filiere_new;
                $filiere->Update();
                //echo "1"; //Operation successfull
                return response()->json([
                    'status' => true,
                    'message' => 'Operation successful: Filiere name updated from [' . $nom_filiere_old . '] to [' . $nom_filiere_new . '].',
                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Error occurred: ' . $th->getMessage(),
            ], 500); //500 = Internal Server Error
        }
    }

    public function allFilieres(Request $request)
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
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'nom_filiere' => 'required|string',
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
                return response()->json([
                    'status' => false,
                    'message' => 'Operation failed: unable to save the corresponding filiere_year. ' . $ex->getMessage(),
                ], 500);
            }
            return response()->json([
                'status' => true,
                'message' => 'Operation successful',
            ], 200);
        } catch (Exception $e) {
            //echo '<br/>Message: ' .$e->getMessage();
            //echo "-1"; //La filiere existe déja
            return response()->json([
                'status' => false,
                'message' => 'Operation failed: A filiere with the same name [' . $nom_filiere . '] already exists. ' . $e->getMessage(),
            ], 500);
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
