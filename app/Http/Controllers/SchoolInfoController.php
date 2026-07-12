<?php

namespace App\Http\Controllers;

use App\Models\BasicSchoolConfig;
use App\Models\SchoolYear;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SchoolInfoController extends Controller
{

    public function allSchools()
    {   //echo "Helo";
        $schools = array(
            //"CES_DE_DABAYE", 
            //"CES_DE_KILWO",
            "CES_DE_LDIRI",
            "CES_DE_MOUROUM",
            "CES_DE_SEDEK",
            "CES_DE_YOLDEO",
            "CES_DE_ZIMADO",
            "CETIC_DE_BOGO",
            "CETIC_DE_DARGALA",
            "CETIC_DE_DOUBANE",
            "CETIC_DE_GADOUA",
            //"CETIC_DE_GODOLA",
            "CETIC_DE_MAKARY",
            "COLLEGE_DE_LA_FRATERNITE",
            "COLBIPPOLFOSH",
            "ENIEG_DE_GUIDER",
            "ENIEG_BILINGUE_DE_MAROUA",
            "GBHS_MINAWAO",
            "GBTHS_MEWOULOU",
            "LB_BOGO",
            //"LB_GUISSA",
            //"LB_KARTOUA",
            "LB_KOZA",
            "LB_MAKALINGAI",
            "LB_ZAMAI",
            //"LT_BIDZAR",
            "LT_DOUALARE",
            "LT_GAZAWA",
            "LT_KOZA",
            "LT_LOGONE_BIRNI",
            "LT_MERI",
            "LT_MINDIF",
            "LT_MORA",
            "LYCEE_DE_BALAZA_ALCALI",
            "LYCEE_CLASSIQUE_DE_MAROUA",
            //"LYCEE_DE_BIDZAR",
            "LYCEE_DE_DOGBA",
            "LYCEE_DE_DOMO",
            "LYCEE_DE_DOUALARE",
            "LYCEE_DE_GABOUA",
            "LYCEE_DE_GODOLA",
            "LYCEE_DE_GUIDER",
            "LYCEE_DE_HARDE_MAROUA",
            "LYCEE_DE_HOULA",
            "LYCEE_DE_KAHEO",
            "LYCEE_DE_KALLIAO",
            "LYCEE_DE_KOTRABA",
            "LYCEE_DE_LOGONE_BIRNI",
            "LYCEE_DE_MAKABAYE",
            "LYCEE_DE_MAROUA_SALAK",
            "LYCEE_DE_MASSAKAL",
            "LYCEE_DE_MOGOM",
            //"LYCEE_DE_MEME",
            "LYCEE_DE_MERI",
            //"LYCEE_DE_MESKINE",
            "LYCEE_DE_MOKIO",
            "LYCEE_DE_PITOA",
            "LYCEE_DE_WAZA",
            "TEST",
            "TEST_PLAY"
        );

        //echo "<br/>".count($schools )."<br/>";
        //response()->json($schools, 200);
        return response()->json($schools, 200);
    }

    public function getSchoolYears(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }
        $connection = $request->input('connection');
        config(["database.default" => $connection]);
        $schoolYears = DB::select("SELECT sy_id, year, is_current FROM school_year ORDER BY year DESC");
        return response()->json($schoolYears, 200);
    }

    public function getClassificationParam(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }
        //first and second sequence of the term
        $connection = $request->input("connection");
        $year = $request->input("year");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            $params = DB::select("SELECT `nb_matieres_rate`, `total_coef_rate`,`classified`, 
                `class_specific`, `term_specific` FROM `classifiedparam` WHERE $sy_id");
            return response()->json($params, 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve classification parameters: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function allSchoolConfig(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }
        $connection = $request->input("connection");
        $year = $request->input("year"); //Will be used to find sy_id         
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year\n";
        try {
            //$config = BasicSchoolConfig::where('sy_id', '=', $sy_id)->get();
            $config = BasicSchoolConfig::all();
            //$config = BasicSchoolConfig::where('sy_id', '=', $sy_id)->first();
            return response()->json($config, 200);
        } catch (\Throwable $e) {
            return response()->json([], 500); //ERROR OCCURS; 500 = Internal Server Error
        }
    }

    public function getSchoolYearID(Request $request)
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
        $year = $request->input('year');
        $connection = $request->input('connection');
        config(["database.default" => $connection]);
        return MyHelper::getSchoolYearID($year);
    }

    public function saveSchoolInfo(Request $request)
    {
        try {
            $request->validate([
                'sy' => 'required',
                'connection' => 'required',
                'logo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                'schoolName' => 'required', //schoolName FR
                'schoolNameEN' => 'required',
                'delRegionFR' => 'required',
                'delRegionEN' => 'required',
                'delDeptFR' => 'required',
                'delDeptEN' => 'required',
                'phone' => 'required',
                'email' => 'required|email',
                'pobox' => 'required',
                'type' => 'required|integer|min:1|max:20',
                'signDate' => 'required',
                'signPlace' => 'required',
                'immt' => 'required',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }


        $sy = $request->input('sy');
        $sy_id = MyHelper::getSchoolYearID($sy);
        $connection = $request->input('connection');
        config(["database.default" => $connection]);
        $imageName = 'logo' . time() . '.' . $request->logo->extension();
        try {
            $request->logo->move(public_path("images/$connection/logo"), $imageName);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'status' => false,
                'message' => 'Failed to upload logo: ' . $th->getMessage(),
            ], 500);
        }

        $schoolConfig = BasicSchoolConfig::where('sy_id', '=', $sy_id)->first();
        //echo "School Config: " . json_encode($schoolConfig) . "<br/>";
        if (is_null($schoolConfig)) {
            return response()->json([
                'status' => false,
                'message' => 'School configuration year not found for the provided year: ' . $sy,
            ], 404);
        } else {
            echo "CONFIG: $request->schoolNameEN\n";
            $schoolConfig->sy_id = $sy_id;
            $schoolConfig->name_fr = $request->schoolName;
            $schoolConfig->name_en = $request->schoolNameEN;
            $schoolConfig->del_regionale_fr = $request->delRegionFR;
            $schoolConfig->del_regionale_en = $request->delRegionEN;
            $schoolConfig->del_dept_fr = $request->delDeptFR;
            $schoolConfig->del_dept_en = $request->delDeptEN;
            $schoolConfig->phone1 = $request->phone;
            $schoolConfig->email = $request->email;
            $schoolConfig->pobox = $request->pobox;
            $schoolConfig->type = $request->type;
            $schoolConfig->date_signature = $request->signDate;
            $schoolConfig->lieu_signature = $request->signPlace;
            $schoolConfig->school_matricule = $request->immt;
            $schoolConfig->logo_path = "images/$connection/" . $imageName;

            try {
                $query = $schoolConfig->update();
                if ($query) {
                    return response()->json([
                        'status' => true,
                        'message' => 'School configuration successfully saved',
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Failed to save config. No Exception thrown, but save returned false.',
                    ], 409); // 409 Conflict. Maybe the record already exists or some other conflict occurred.
                }
            } catch (\Throwable $th) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to save config: ' . $th->getMessage(),
                ], 500);
            }
        }
    }

    public function updateSchoolInfo(Request $request)
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
        $connection = $request->input('connection');
        config(["database.default" => $connection]);
        $schoolConfig = new BasicSchoolConfig();
        $year = SchoolYear::where('year', $request->year)->first();
        $sy_id = $year->sy_id;
        $name_fr = $request->schoolName;
        $name_en = $request->schoolNameEN;
        $del_regionale_fr = $request->delRegionFR;
        $del_regionale_en = $request->delRegionEN;
        $del_dept_fr = $request->delDeptFR;
        $del_dept_en = $request->delDeptEN;
        $phone1 = $request->phone;
        $email = $request->email;
        $pobox = $request->pobox;
        $type = $request->type;
        $date_signature = $request->signDate;
        $lieu_signature = $request->signPlace;
        $school_matricule = $request->immt;
        //$img64 = $request->image;


        $schoolConfig->sy_id = $sy_id;
        $schoolConfig->name_fr = $name_fr;
        $schoolConfig->name_en = $name_en;
        $schoolConfig->del_regionale_fr = $del_regionale_fr;
        $schoolConfig->del_regionale_en = $del_regionale_en;
        $schoolConfig->del_dept_fr = $del_dept_fr;
        $schoolConfig->del_dept_en = $del_dept_en;
        $schoolConfig->phone1 = $phone1;
        $schoolConfig->email = $email;
        $schoolConfig->pobox = $pobox;
        $schoolConfig->type = $type;
        $schoolConfig->date_signature = $date_signature;
        $schoolConfig->lieu_signature = $lieu_signature;
        $schoolConfig->school_matricule = $school_matricule;

        $query = 1;
        try {
            $query = $schoolConfig->save();
        } catch (\Throwable $e) {
            //echo '<br/>ERROR: ' . $e->getMessage() . "<br/> we shall try to update instead<br/>";
            //Exists already let's try update
            //echo "lets try to update<br/>";
            $conf = BasicSchoolConfig::where('sy_id', "=", "$sy_id")->first();
            if (!is_null($conf)) {
                //echo "\$conf is not null<br/>";
                $conf->sy_id = $sy_id;
                $conf->name_fr = $name_fr;
                $conf->name_en = $name_en;
                $conf->del_regionale_fr = $del_regionale_fr;
                $conf->del_regionale_en = $del_regionale_en;
                $conf->del_dept_fr = $del_dept_fr;
                $conf->del_dept_en = $del_dept_en;
                //echo "[$phone1]<br/>";
                if (empty($phone1) || $phone1 == "null") {
                    $conf->phone1 = 0;
                } else {
                    $conf->phone1 = $phone1;
                }
                $conf->email = $email;
                $conf->pobox = $pobox;
                $conf->type = $type;
                $conf->date_signature = $date_signature;
                $conf->lieu_signature = $lieu_signature;
                $conf->school_matricule = $school_matricule;
                $conf->update();
            } else {
                $query = -2; //Operation failed
                //echo '<br/>ERROR: ' . $e->getMessage(); 
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to update config: ' . $e->getMessage(),
                ], 500);
            }
        } //End try-catch

        //echo "$query";
        return response()->json([
            'status' => true,
            'message' => 'School configuration successfully updated',
        ], 200);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'connection' => 'required',
            'year' => 'required',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $connection = $request->input("connection");
        $year = $request->input("year");
        $connection = $request->input('connection');
        config(["database.default" => $connection]);
        //$name = $request->input('name');

        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $conf = BasicSchoolConfig::where('sy_id', "=", "$sy_id")->first();
            //$image = $request->file("image");
            //$imageName = "logo ".time() . '.' . $request->image->extension(); 
            //$imageName = "logo1.". $request->image->extension();
            $imageName = "logo1.png";
            $request->image->move(public_path("images/$connection/logo"), $imageName);
            //$product->image_path = "images/$connection/" . $imageName;         
            //echo "1"; //Product saved
            return response()->json([
                'status' => true,
                'message' => 'Logo uploaded successfully',
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to upload logo: ' . $e->getMessage(),
            ], 500);
        }
    }
}
