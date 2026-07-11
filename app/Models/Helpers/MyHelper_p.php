<?php

use App\Http\Controllers\Controller;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\SectionYear;
use Illuminate\Support\Facades\DB;

class MyHelper{
    public static function  getSchoolYearID($year)
    {
        $sy = SchoolYear::where('year', $year)->first();
        $sy_id = $sy->sy_id;
        return $sy_id;
    }

    public static function getSectionYearID($section_name, $sy_id): int
    {
        $section = Section::where('section_name', '=', $section_name)->first();
        $currentSectionId = $section->section_id;

        $sectionYear = SectionYear::where('sy_id', '=', $sy_id)
            ->where('section_id', '=', $currentSectionId)
            ->first();
        $section_year_id = $sectionYear->section_year_id;
        return $section_year_id;
    }

    public static function getFiliereYearIDs($section_year_id)
    {
        //id des filiere_year de l'annee de la section en question
        $ids_fy = DB::table('filiere_year')
            ->select('filiere_year_id')
            ->where('section_year_id', '=', $section_year_id);
            //echo response()->json($ids_fy, 200);
        return $ids_fy;
    }

    public static function findSpYearBySpID($ids_fy, $sp_id): Object|null{
        $spYear = DB::table('speciality_year') 
            ->where('speciality_id', '=', $sp_id)
            //->whereIn('filiere_year_id ', $ids_fy)
            ->first();
        return $spYear;
    }
}
