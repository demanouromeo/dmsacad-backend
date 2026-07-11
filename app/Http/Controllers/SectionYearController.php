<?php

namespace App\Http\Controllers;

use App\Models\SectionYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SectionYearController extends Controller
{
    
    public function getSections(Request $request)
    {
        $connection = $request->input("connection");
        $year = $request->input("year"); 
        config(["database.default" => $connection]);
        try {
            $sy_id = MyHelper::getSchoolYearID($year); 

            $res = DB::select(
                "SELECT section_year.section_year_id, section_year.section_id, section.section_name 
                            FROM section_year, section 
                            WHERE
                            section_year.section_id = section.section_id
                            AND 
                            sy_id = $sy_id;"
            );
            if (count($res) > 0) {
                return response()->json($res, 200);
            } else {
                return [];
            }
        } catch (Exception  $e) {
            //echo '<br/>ERROR: ' . $e->getMessage();
            return [];
        }
    }
    
    /**
     * Display a listing of the resource.
     */
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
    public function show(SectionYear $sectionYear)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SectionYear $sectionYear)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SectionYear $sectionYear)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SectionYear $sectionYear)
    {
        //
    }
}
