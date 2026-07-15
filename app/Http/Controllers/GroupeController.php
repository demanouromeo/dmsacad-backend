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

    public function deleteAGroupes(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'groupe_id' => 'required|integer|min:1',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

        $connection = $request->input("connection");
        $year  = $request->input("year");
        $groupe_id = $request->input("groupe_id");
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);

        $grpRef = Groupe::find($groupe_id);
        if (is_null($grpRef)) {
            return response()->json([
                'status' => true,
                'message' => "Groupe with ID $groupe_id does not exist. But just consider it as deleted.",
            ], 200);
        } else {

            try {
                $res = GroupeYear::where("groupe_id", '=', $groupe_id)
                    ->where('sy_id', '=', $sy_id)
                    ->first();

                $tmp = 0;
                if (!is_null($res)) { //VERY IMPORTANT TO CHECH IF NULL
                    $tmp = $res->delete(); //To delete the groupe_year found
                }
                DB::select("DELETE FROM `groupe` WHERE groupe.`groupe_id` not IN(select groupe_year.groupe_id from groupe_year)");
            } catch (\Throwable $th) {
                return response()->json([
                    'status' => false,
                    'message' => "Groupe with ID $groupe_id could not be deleted becausean exception: " . $th->getMessage(),
                ], 500);
            }
        }
        return response()->json([
            'status' => true,
            'message' => "Groupe with ID $groupe_id has been deleted successfully.",
        ], 200);
    }
    public function deleteManyGroupes(Request $request)
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

        $groupList = json_decode($data, true);
        $n = count($groupList);
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);

        $errMsg = "";
        $allAffected = 1; //interpreted as true. 0-->false
        foreach ($groupList as $group) {
            $groupe_id = $group["groupe_id"];
            $grpRef = Groupe::find($groupe_id);
            if (is_null($grpRef)) {
                $errMsg .= "Groupe with ID $groupe_id does not exist. We simply consider it as deleted. ";
                continue; //Skip to the next groupe
            }
            try {
                $res = GroupeYear::where("groupe_id", '=', $groupe_id)
                    ->where('sy_id', '=', $sy_id)
                    ->first();
                $tmp = 0;
                if (!is_null($res)) { //VERY IMPORTANT TO CHECH IF NULL
                    $tmp = $res->delete(); //To delete the groupe_year found
                }


                if ($tmp == 1) { //The groupe_year has been deleted successfully
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
                    $errMsg .= "Groupe with ID $groupe_id could not be deleted. because we failed to delete the corresponding groupe_year. ";
                }
            } catch (Exception $ex) {
                $allAffected = 0;
                $errMsg .= "Groupe with ID $groupe_id could not be deleted due to an exception: " . $ex->getMessage() . " ";
            }
        } //END FOR

        //echo (string) $allAffected; //1--> All groupes successfully deleted; 0--> Failed to save at least one
        if ($allAffected == 1) {
            return response()->json([
                'status' => true,
                'message' => 'All groupes successfully deleted.',
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'ERROR:' . $errMsg,
            ], 500);
        }
    }

    public function updateManyGroupes(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
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
        $data = $request->input("data");
        $data_size = $request->input("data_size");

        $groupList = json_decode($data, true);
        config(["database.default" => $connection]);

        $msg = "";
        $allAffected = 1; //interpreted as true. 0-->false
        foreach ($groupList as $grp) {
            // code
            $groupe_id = $grp["groupe_id"];
            $groupe_name = $grp["groupe_name"];
            $ref = Groupe::find($groupe_id);
            if (is_null($ref)) {
                $allAffected = 0;
                $msg .= "\nGroupe with ID $groupe_id does not exist. ";
                continue; //Skip to the next groupe
            }
            try {
                DB::table('groupe')
                    ->where('groupe_id', $groupe_id)
                    ->update(['groupe_name' => $groupe_name]);
            } catch (\Throwable $th) {
                $allAffected = 0;
                $msg .= "\nGroupe with ID $groupe_id could not be updated due to an exception: " . $th->getMessage() . " ";
            }
        }
        if ($allAffected == 1) {
            return response()->json([
                'status' => true,
                'message' => 'All groupes successfully updated.',
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update at least one groupe.' . $msg,
            ], 500);
        }
    }


    public function saveGroupe(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'groupe_name' => 'required|string',
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
        $groupe_name = $request->input("groupe_name");
        $section_name = $request->input("section");

        config(["database.default" => $connection]);

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
                if (is_null($groupeTmp)) {
                    $grp->save();
                    $groupe_id = $grp->groupe_id; //ID OF gropueYear CAN ONLY BE OBTAINED AFTER SAVING the grp (Groupe)
                } else {
                    $groupe_id = $groupeTmp->groupe_id;
                }

                $grpYear->groupe_id = $groupe_id;
                $grpYear->sy_id = $sy_id;
                $grpYear->section_id = $section_id;
                $grpYear->save();
                return response()->json([
                    'status' => true,
                    'message' => "Groupe '$groupe_name' has been saved successfully.",
                ], 200);
            } catch (Exception $ex) {
                //A groupe with may exist already
                //If exception then grp or grpYear failed to save. We delete them to avoid inconsitency
                $grp->delete();
                try {
                    $grpYear->delete();
                } catch (Exception $exx) {
                }
                //echo '<br/>Message: ' . $ex->getMessage() . '<br>';
                return response()->json([
                    'status' => false,
                    'message' => "A groupe with name '$groupe_name' already exists in the current section or in another section" . $ex->getMessage(),
                ], 400);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Error while saving groupe: " . $e->getMessage(),
            ], 500);
        }
    }


    public function allGroupes(Request $request)
    {
        //-------------------- THIS METHOD FETCHES ALL GROUPES OF THE SECTIONS IN The current schoolyear
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
        $section_name = $request->input("section");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $section_id = MyHelper::getSectionID($section_name);

            $groupes = MyHelper::getGroupesOfYearOfSection($sy_id, $section_id);
            return response()->json($groupes, 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error fetching groupes: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function groupesOfYearAndSection(Request $request)
    {   //REPETITION CETTE METHODE EXISTE DEJA AU NOM DE allGroupes
        //-------------------- THIS METHOD FETCHES ALL GROUPES OF THE SECTIONS IN The current schoolyear
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
            return response()->json([
                'status' => false,
                'message' => 'Error fetching groupes: ' . $e->getMessage(),
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
