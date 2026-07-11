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
            echo 1;
        } catch (Exception $e) {
            echo 0; //Failed
            //echo $e->getMessage();
        }
    }
   
    public function classifiedParamOfYear(Request $request)
    {   
        $connection = $request->input("connection");
        $year = $request->input("year");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            $param = DB::select(
                "SELECT * FROM classifiedparam WHERE sy_id =  $sy_id"
            );
            return response()->json($param, 200);
        } catch (Exception $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 300); //ERROR OCCURS
        }
    }
}
