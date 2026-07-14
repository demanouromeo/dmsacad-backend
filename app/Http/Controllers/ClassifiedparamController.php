<?php

namespace App\Http\Controllers;

use App\Models\Classifiedparam;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClassifiedparamController extends Controller
{

    public function saveClassifiedParamOfYear(Request $request)
    {   //THE CLASSE IS ASSUME TO BE A CLASSE OF THE CURRENT SECTION
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'classified' => 'required|integer|min:0|max:1',
                'nbMatieresRate' => 'required|integer',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }
        $connection = $request->input("connection");
        $year = $request->input("year");
        $classified = $request->input("classified");
        $nbMatieresRate = $request->input("nbMatieresRate");

        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        $ref = Classifiedparam::where("sy_id", "=", $sy_id)->first();
        try {
            //if(count($param)>0){
            if (!is_null($ref)) {
                //update
                $ref->classified = $classified;
                $ref->nb_matieres_rate = $nbMatieresRate;
                $ref->update();
            } else {
                $ref2 = new Classifiedparam();
                $ref2->sy_id = $sy_id;
                $ref2->classified = $classified;
                $ref2->nb_matieres_rate = $nbMatieresRate;
                $ref2->save();
            }
            return response()->json([
                'status' => true,
                'message' => 'Classified parameters saved successfully.',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to save classified parameters: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function classifiedParamOfYear(Request $request)
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

            $param = DB::select(
                "SELECT id, sy_id, nb_matieres_rate, total_coef_rate, classified, 
                class_specific, term_specific FROM classifiedparam WHERE sy_id =  $sy_id"
            );
            return response()->json($param, 200);
        } catch (Exception $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }
}
