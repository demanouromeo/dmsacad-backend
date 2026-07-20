<?php

namespace App\Http\Controllers;

use App\Models\Thparam;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ThParamController extends Controller
{

    public function saveThParam(Request $request)
    {   //THE CLASSE IS ASSUME TO BE A CLASSE OF THE CURRENT SECTION
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'lb' => 'required|numeric|min:0|max:20',
                'ub' => 'required|numeric|min:0|max:20',
                'lb_default' => 'required|numeric|min:0|max:20',
                'ub_default' => 'required|numeric|min:0|max:20',
                'seuil_abs' => 'required|numeric|min:0',
                'val1' => 'required|numeric|min:1|max:3',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

        $connection = $request->input("connection");
        $year = $request->input("year");
        $lb = $request->input("lb");
        $ub = $request->input("ub");
        $lb_default = $request->input("lb_default");
        $ub_default = $request->input("ub_default");
        $seuil_abs = $request->input("seuil_abs");
        $val1 = $request->input("val1");

        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        //$param = DB::select("SELECT* FROM thparam WHERE sy_id = $sy_id");
        $ref = Thparam::where("sy_id", "=", $sy_id)->first();
        try {
            //if(count($param)>0){
            if (!is_null($ref)) {
                //update
                $ref->lb = $lb;
                $ref->ub = $ub;
                $ref->lb_default = $lb_default;
                $ref->ub_default = $ub_default;
                $ref->seuil_abs = $seuil_abs;
                $ref->val1 = $val1;
                //$ref->seuil_abs_default = $seuil_abs_default;
                $ref->update();
            } else {
                $ref2 = new Thparam();
                $ref2->sy_id = $sy_id;
                $ref2->lb = $lb;
                $ref2->ub = $ub;
                $ref2->lb_default = $lb_default;
                $ref2->ub_default = $ub_default;
                $ref2->seuil_abs = $seuil_abs;
                $ref2->seuil_abs_default = $seuil_abs;
                $ref2->val1 = $val1;
                $ref2->save();
            }
            return response()->json([
                'status' => true,
                'message' => 'Thparam saved successfully.',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error occured when saving thparam: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function thParamOfYear(Request $request)
    {   //THE CLASSE IS ASSUME TO BE A CLASSE OF THE CURRENT SECTION
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

            $students = DB::select(
                "SELECT  `th_id`, `lb`, `ub`, `lb_default`, `ub_default`, `seuil_abs`, 
                `seuil_abs_default`, `sy_id`, `val1` FROM thparam WHERE sy_id = $sy_id"
            );
            return response()->json($students, 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage(),
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
    public function show(Thparam $thparam)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Thparam $thparam)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Thparam $thparam)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Thparam $thparam)
    {
        //
    }
}
