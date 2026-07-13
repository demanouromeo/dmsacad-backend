<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\ClasseYear;
use App\Models\Speciality;
use App\Models\StudentClasse;
use FFI\Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClasseController extends Controller
{
    public function updateApcLevel(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $year = $request->input("year");
        $level = $request->input("level");
        $section = $request->input("section");
        $activated = $request->input("activated");

        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        $section_id = MyHelper::getSectionID($section);

        try {
            DB::select("INSERT INTO `apc_level` (`sy_id`, `section_id`, `level`, `activated`) 
           VALUES ('$sy_id', '$section_id', '$level', '$activated');");
        } catch (Exception $e) {
        }

        $allAffected = 1;
        try {
            $x = DB::select("UPDATE apc_level SET activated = $activated 
            WHERE sy_id = $sy_id AND section_id =$section_id AND level = $level;");
        } catch (Exception $e) {
            //echo "Error<br/>";
            echo "<br/>" . $e->getMessage() . "<br/>";
            echo "-1";
            $allAffected = 0;
        }
        echo "$allAffected"; //1--> All successfully saved; < 0--> Failed for least one
    }

    public function cancelAllBasculement(Request $request)
    {
        $connection = $request->input("connection");
        $year = $request->input("year");
        $next_year = $request->input("next_year");
        config(["database.default" => $connection]);
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $sy_next_id = MyHelper::getSchoolYearID($next_year);

            DB::select("DELETE FROM student_classe WHERE student_classe.sy_id = $sy_next_id;");

            DB::select("UPDATE student_classe SET student_classe.basculated = 0, 
                student_classe.basculated_classe_id = 0
                    WHERE student_classe.sy_id = $sy_id");
            echo "1"; //SUCCESS
        } catch (Exception $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            return;
        }
    }

    public function processRedoublants(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $next_year = $request->input("next_year");
        $year = $request->input("year");

        $stList = json_decode($data, true);
        $n = count($stList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]<br/>";
        $allAffected = 1; //interpreted as true. 0-->false  
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        $sy_next_id = MyHelper::getSchoolYearID($next_year);

        $count  = 1;
        foreach ($stList as $stud) {
            //echo"-----> Processing stud No.[$count]<br/>";
            $count++;
            $sc = new StudentClasse();
            $sc->stud_id = $stud['stud_id'];
            $sc->sy_id = $sy_next_id; //NEXT SCHOOL YEAR
            $sc->repeating = 1;
            $sc->cas_social = $stud['cas_social'];
            $sc->classe_id = $stud['classe_id'];
            try {
                //$old_classe_id = $stud['classe_id'];
                $stud_id = $stud['stud_id'];
                $x = DB::select("SELECT*FROM student_classe WHERE student_classe.sy_id = $sy_next_id
                        AND student_classe.stud_id = $stud_id AND student_classe.classe_id = $sc->classe_id");
                if (count($x) == 0) {
                    $sc->save();
                }
            } catch (Exception $e) {
                echo "<br/>" . $e->getMessage() . "<br/>";
                echo "-1"; //failed to save student_classe;
                $allAffected = 0;
            }
        } //END FOR
        echo "$allAffected"; //1--> All successfully saved; < 0--> Failed for least one
    }

    public function processRedoublantsWithPOST(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $next_year = $request->input("next_year");
        $year = $request->input("year");

        $stList = json_decode($data, true);
        $n = count($stList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]<br/>";
        $allAffected = 1; //interpreted as true. 0-->false  
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        $sy_next_id = MyHelper::getSchoolYearID($next_year);

        $count  = 1;
        foreach ($stList as $stud) {
            //echo"-----> Processing stud No.[$count]<br/>";
            $count++;
            $sc = new StudentClasse();
            $sc->stud_id = $stud['stud_id'];
            $sc->sy_id = $sy_next_id; //NEXT SCHOOL YEAR
            $sc->repeating = 1;
            $sc->cas_social = $stud['cas_social'];
            $sc->classe_id = $stud['classe_id'];
            try {
                //$old_classe_id = $stud['classe_id'];
                $stud_id = $stud['stud_id'];
                $x = DB::select("SELECT*FROM student_classe WHERE student_classe.sy_id = $sy_next_id
                        AND student_classe.stud_id = $stud_id AND student_classe.classe_id = $sc->classe_id");
                if (count($x) == 0) {
                    $sc->save();
                }
            } catch (Exception $e) {
                echo "<br/>" . $e->getMessage() . "<br/>";
                echo "-1"; //failed to save student_classe;
                $allAffected = 0;
            }
        } //END FOR
        echo "$allAffected"; //1--> All successfully saved; < 0--> Failed for least one
    }


    public function clearExclusWithPOST(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $next_year = $request->input("next_year");

        $stList = json_decode($data, true);
        $n = count($stList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]<br/>";        
        config(["database.default" => $connection]);
        $sy_next_id = MyHelper::getSchoolYearID($next_year);

        $allAffected = 1; //interpreted as true. 0-->false  
        foreach ($stList as $stud) {
            //echo"-----> Processing stud No.[$count]<br/>"; 
            $stud_id = $stud['stud_id'];
            $classe_id = $stud['classe_id'];
            try {
                $x = DB::select("DELETE FROM student_classe WHERE student_classe.sy_id = $sy_next_id
                        AND student_classe.stud_id = $stud_id AND student_classe.classe_id = $classe_id");
            } catch (Exception $e) {
                echo "<br/>" . $e->getMessage() . "<br/>";
                echo "-1"; //failed to save student_classe;
                $allAffected = 0;
            }
        } //END FOR
        echo "$allAffected"; //1--> All successfully saved; < 0--> Failed for least one
    }

    public function clearExclus(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $next_year = $request->input("next_year");

        $stList = json_decode($data, true);
        $n = count($stList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]<br/>";        
        config(["database.default" => $connection]);
        $sy_next_id = MyHelper::getSchoolYearID($next_year);

        $allAffected = 1; //interpreted as true. 0-->false  
        foreach ($stList as $stud) {
            //echo"-----> Processing stud No.[$count]<br/>"; 
            $stud_id = $stud['stud_id'];
            $classe_id = $stud['classe_id'];
            try {
                $x = DB::select("DELETE FROM student_classe WHERE student_classe.sy_id = $sy_next_id
                        AND student_classe.stud_id = $stud_id AND student_classe.classe_id = $classe_id");
            } catch (Exception $e) {
                echo "<br/>" . $e->getMessage() . "<br/>";
                echo "-1"; //failed to save student_classe;
                $allAffected = 0;
            }
        } //END FOR
        echo "$allAffected"; //1--> All successfully saved; < 0--> Failed for least one
    }


    public function resetBasculement(Request $request)
    {
        $connection = $request->input("connection");
        $year = $request->input("year");
        $next_year = $request->input("next_year");
        $classe_id = $request->input("classe_id");
        config(["database.default" => $connection]);
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $sy_next_id = MyHelper::getSchoolYearID($next_year);

            DB::select("DELETE FROM student_classe WHERE student_classe.sy_id = $sy_next_id 
                AND student_classe.stud_id IN(SELECT student_classe.stud_id FROM student_classe 
                    WHERE student_classe.classe_id = $classe_id 
                    AND student_classe.sy_id = $sy_id)");

            DB::select("UPDATE student_classe SET student_classe.basculated = 0, student_classe.basculated_classe_id = 0 
                         WHERE student_classe.classe_id = $classe_id AND student_classe.sy_id = $sy_id");
            echo "1"; //SUCCESS
        } catch (Exception $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            return;
        }
    }

    public function applyBasculementWithPOST(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $next_year = $request->input("next_year"); //echo"[$next_year]<br/>";
        $year = $request->input("year");
        $new_classe_id = $request->input("new_classe_id");

        $stList = json_decode($data, true);
        $n = count($stList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]<br/>";
        $allAffected = 1; //interpreted as true. 0-->false  
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        //echo "$year: id=$sy_id";
        //echo "$next_year";
        $sy_next_id = MyHelper::getSchoolYearID($next_year);

        //echo "$year: id2=$sy_next_id";

        $count  = 1;
        foreach ($stList as $stud) {
            //echo"-----> Processing stud No.[$count]<br/>";
            $count++;
            $sc = new StudentClasse();
            $sc->stud_id = $stud['stud_id'];
            $sc->sy_id = $sy_next_id; //NEXT SCHOOL YEAR
            $sc->repeating = 0; //DANS CE CAS L'eleve est promu
            $sc->cas_social = $stud['cas_social'];
            $sc->classe_id = $new_classe_id;

            try {
                $sc->save();
                $old_classe_id = $stud['classe_id'];
                $stud_id = $stud['stud_id'];
                DB::select("UPDATE student_classe SET student_classe.basculated = 1, student_classe.basculated_classe_id = $new_classe_id 
                         WHERE student_classe.classe_id = $old_classe_id AND student_classe.sy_id = $sy_id
                         AND student_classe.stud_id = $stud_id");
                DB::select("DELETE FROM student_classe 
                    WHERE student_classe.stud_id = $stud_id 
                        AND student_classe.sy_id = $sy_next_id 
                        AND student_classe.classe_id = $old_classe_id");
            } catch (Exception $e) {
                echo "<br/>" . $e->getMessage() . "<br/>";
                echo "-1"; //failed to save student_classe;
                $allAffected = 0;
            }
        } //END FOR
        echo "$allAffected"; //1--> All successfully saved; < 0--> Failed for least one
    }

    public function applyBasculement(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $next_year = $request->input("next_year"); //echo"[$next_year]<br/>";
        $year = $request->input("year");
        $new_classe_id = $request->input("new_classe_id");

        $stList = json_decode($data, true);
        $n = count($stList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]<br/>";
        $allAffected = 1; //interpreted as true. 0-->false  
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        //echo "$year: id=$sy_id";
        //echo "$next_year";
        $sy_next_id = MyHelper::getSchoolYearID($next_year);

        //echo "$year: id2=$sy_next_id";

        $count  = 1;
        foreach ($stList as $stud) {
            //echo"-----> Processing stud No.[$count]<br/>";
            $count++;
            $sc = new StudentClasse();
            $sc->stud_id = $stud['stud_id'];
            $sc->sy_id = $sy_next_id; //NEXT SCHOOL YEAR
            $sc->repeating = 0; //DANS CE CAS L'eleve est promu
            $sc->cas_social = $stud['cas_social'];
            $sc->classe_id = $new_classe_id;

            try {
                $sc->save();
                $old_classe_id = $stud['classe_id'];
                $stud_id = $stud['stud_id'];
                DB::select("UPDATE student_classe SET student_classe.basculated = 1, student_classe.basculated_classe_id = $new_classe_id 
                         WHERE student_classe.classe_id = $old_classe_id AND student_classe.sy_id = $sy_id
                         AND student_classe.stud_id = $stud_id");
                DB::select("DELETE FROM student_classe 
                    WHERE student_classe.stud_id = $stud_id 
                        AND student_classe.sy_id = $sy_next_id 
                        AND student_classe.classe_id = $old_classe_id");
            } catch (Exception $e) {
                echo "<br/>" . $e->getMessage() . "<br/>";
                echo "-1"; //failed to save student_classe;
                $allAffected = 0;
            }
        } //END FOR
        echo "$allAffected"; //1--> All successfully saved; < 0--> Failed for least one
    }

    public function removeBasculement(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $next_year = $request->input("next_year");
        $year = $request->input("year");
        $new_classe_id = $request->input("new_classe_id");

        $stList = json_decode($data, true);
        $n = count($stList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]<br/>";
        $allAffected = 1; //interpreted as true. 0-->false  
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        $sy_next_id = MyHelper::getSchoolYearID($next_year);

        $count  = 1;
        foreach ($stList as $stud) {
            //echo"-----> Processing stud No.[$count]<br/>";
            $count++;
            try {
                $old_classe_id = $stud['classe_id'];
                $stud_id = $stud['stud_id'];
                /*
                DB::select("UPDATE student_classe SET student_classe.basculated = 0, 
                            student_classe.basculated_classe_id = 0 WHERE student_classe.sy_id = $sy_id 
                            AND student_classe.stud_id IN(SELECT student_classe.stud_id FROM student_classe 
                                WHERE student_classe.classe_id = $new_classe_id 
                                    AND student_classe.sy_id = $sy_next_id)");
                */
                DB::select("UPDATE student_classe SET student_classe.basculated = 0, 
                            student_classe.basculated_classe_id = 0 WHERE student_classe.sy_id = $sy_id 
                            AND student_classe.stud_id = $stud_id");
                DB::select("DELETE FROM student_classe 
                            WHERE student_classe.classe_id = $new_classe_id 
                                    AND student_classe.sy_id = $sy_next_id
                                    AND student_classe.stud_id = $stud_id");
            } catch (Exception $e) {
                echo "<br/>" . $e->getMessage() . "<br/>";
                echo "-1"; //failed to save student_classe;
                $allAffected = 0;
            }
        } //END FOR
        echo "$allAffected"; //1--> All successfully saved; < 0--> Failed for least one
    }

    public function removeBasculementWithPOST(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $next_year = $request->input("next_year");
        $year = $request->input("year");
        $new_classe_id = $request->input("new_classe_id");

        $stList = json_decode($data, true);
        $n = count($stList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]<br/>";
        $allAffected = 1; //interpreted as true. 0-->false  
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        $sy_next_id = MyHelper::getSchoolYearID($next_year);

        $count  = 1;
        foreach ($stList as $stud) {
            //echo"-----> Processing stud No.[$count]<br/>";
            $count++;
            try {
                $old_classe_id = $stud['classe_id'];
                $stud_id = $stud['stud_id'];
                /*
                DB::select("UPDATE student_classe SET student_classe.basculated = 0, 
                            student_classe.basculated_classe_id = 0 WHERE student_classe.sy_id = $sy_id 
                            AND student_classe.stud_id IN(SELECT student_classe.stud_id FROM student_classe 
                                WHERE student_classe.classe_id = $new_classe_id 
                                    AND student_classe.sy_id = $sy_next_id)");
                                    */
                DB::select("UPDATE student_classe SET student_classe.basculated = 0, 
                            student_classe.basculated_classe_id = 0 WHERE student_classe.sy_id = $sy_id 
                            AND student_classe.stud_id = $stud_id");
                DB::select("DELETE FROM student_classe 
                            WHERE student_classe.classe_id = $new_classe_id 
                                    AND student_classe.sy_id = $sy_next_id
                                    AND student_classe.stud_id = $stud_id");
            } catch (Exception $e) {
                echo "<br/>" . $e->getMessage() . "<br/>";
                echo "-1"; //failed to save student_classe;
                $allAffected = 0;
            }
        } //END FOR
        echo "$allAffected"; //1--> All successfully saved; < 0--> Failed for least one
    }


    public function saveChanges(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $next_year = $request->input("next_year");
        $year = $request->input("year");

        $stList = json_decode($data, true);
        $n = count($stList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]<br/>";
        $allAffected = 1; //interpreted as true. 0-->false  
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        $sy_next_id = MyHelper::getSchoolYearID($next_year);

        $count  = 1;
        foreach ($stList as $stud) {
            //echo"-----> Processing stud No.[$count]<br/>";
            $count++;
            $sc = new StudentClasse();
            $sc->stud_id = $stud['stud_id'];
            $sc->sy_id = $sy_next_id; //NEXT SCHOOL YEAR
            $sc->repeating = 0;
            $sc->cas_social = $stud['cas_social'];
            $sc->classe_id = $stud['promuEn'];
            try {
                //$old_classe_id = $stud['classe_id'];
                $stud_id = $stud['stud_id'];
                DB::select("DELETE FROM student_classe WHERE student_classe.sy_id = $sy_next_id
                        AND student_classe.stud_id = $stud_id");
                $sc->save();
            } catch (Exception $e) {
                echo "<br/>" . $e->getMessage() . "<br/>";
                echo "-1"; //failed to save student_classe;
                $allAffected = 0;
            }
        } //END FOR
        echo "$allAffected"; //1--> All successfully saved; < 0--> Failed for least one
    }

    public function saveChangesWithPOST(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $next_year = $request->input("next_year");
        $year = $request->input("year");

        $stList = json_decode($data, true);
        $n = count($stList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]<br/>";
        $allAffected = 1; //interpreted as true. 0-->false  
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        $sy_next_id = MyHelper::getSchoolYearID($next_year);

        $count  = 1;
        foreach ($stList as $stud) {
            //echo"-----> Processing stud No.[$count]<br/>";
            $count++;
            $sc = new StudentClasse();
            $sc->stud_id = $stud['stud_id'];
            $sc->sy_id = $sy_next_id; //NEXT SCHOOL YEAR
            $sc->repeating = 0;
            $sc->cas_social = $stud['cas_social'];
            $sc->classe_id = $stud['promuEn'];
            try {
                //$old_classe_id = $stud['classe_id'];
                $stud_id = $stud['stud_id'];
                DB::select("DELETE FROM student_classe WHERE student_classe.sy_id = $sy_next_id
                        AND student_classe.stud_id = $stud_id");
                $sc->save();
            } catch (Exception $e) {
                echo "<br/>" . $e->getMessage() . "<br/>";
                echo "-1"; //failed to save student_classe;
                $allAffected = 0;
            }
        } //END FOR
        echo "$allAffected"; //1--> All successfully saved; < 0--> Failed for least one
    }

    public function basculerSpecial(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $year = $request->input("year");
        $next_year = $request->input("next_year");

        $allAffected = 1; //interpreted as true. 0-->false  
        config(["database.default" => $connection]);
        $sy_next_id = MyHelper::getSchoolYearID($next_year);
        $sy_id = MyHelper::getSchoolYearID($year);

        //$stList = MyHelper::fetchStudentClasseByLevel(6, $sy_id);
        $stList = MyHelper::fetchStudentClasse1ereTle($sy_id);
        $count = 0;
        foreach ($stList as $stud) {
            $count++;
            //echo"-----> Processing stud No.[$count]<br/>";
            $sc = new StudentClasse();
            $sc->stud_id = $stud->stud_id;
            $sc->sy_id = $sy_next_id; //NEXT SCHOOL YEAR
            $sc->repeating = 0;
            $sc->cas_social = $stud->cas_social;
            $sc->classe_id = $stud->classe_id;


            $old_classe_id = $stud->classe_id;
            $stud_id = $stud->stud_id;
            $obj = DB::select(
                "SELECT*FROM student_classe WHERE student_classe.sy_id = $sy_next_id 
                    AND student_classe.classe_id = $old_classe_id 
                    AND student_classe.stud_id = $stud_id"
            );
            /*
                $obj = StudentClasse::all()
                ->where("sy_id", $sy_id)
                ->where("classe_id",$old_classe_id )
                ->where("stud_id",$stud_id)
                ->first();*/
            try {

                if (count($obj) == 0) {
                    DB::select("UPDATE student_classe SET student_classe.basculated = 1, 
                        student_classe.basculated_classe_id = $old_classe_id /*Les eleve 1ere et tle sont basculés sur place*/
                        WHERE student_classe.classe_id = $old_classe_id AND student_classe.sy_id = $sy_id
                        AND student_classe.stud_id = $stud_id");
                    $sc->save();
                }
            } catch (Exception $e) {
                //echo "<br/>" . $e->getMessage() . "<br/>";
                //echo "-1"; //failed to save student_classe;
                //$allAffected = 0;
            }
        } //END FOR
        echo "$allAffected"; //1--> All successfully saved; < 0--> Failed for least one
    }

    public function updateClassSettings(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'classe_id' => 'required|integer|min:1',
                'totalAbsTh' => 'required|integer|min:1|max:300',
                'totalExclusionTh' => 'required|integer|min:0|max:300', // Assuming 300 is the maximum number of school days in a year
                'avgDismissalTh' => 'required|numeric|min:0|max:20',
                'repeatUb' => 'required|numeric',
                'passMark' => 'required|numeric|min:0|max:20',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }
        $connection = $request->input("connection");
        $year = $request->input("year");
        $classe_id = $request->input("classe_id");
        $totalAbsTh = $request->input("totalAbsTh");
        $totalExclusionTh = $request->input("totalExclusionTh");
        $avgDismissalTh = $request->input("avgDismissalTh");
        $repeatUb = $request->input("repeatUb");
        $passMark = $request->input("passMark");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        $sy_id = MyHelper::getSchoolYearID($year);
        try {
            DB::select("update `classe_year` set totalAbsTh = $totalAbsTh, 
                    totalExclusionTh = $totalExclusionTh, avgDismissalTh = $avgDismissalTh,
                    repeatUb = $repeatUb, passMark = $passMark
                WHERE classe_id =$classe_id AND sy_id = $sy_id");
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while updating class settings: ' . $e->getMessage(),
            ], 500); //ERROR OCCURS
        }
        return response()->json([
            'status' => true,
            'message' => 'Class settings updated successfully',
        ], 200);
    }

    public function assignVpAClass(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'classe_id' => 'required|integer|min:1',
                'vp_id' => 'required|integer|min:1',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }
        $connection = $request->input("connection");
        $year = $request->input("year");
        $classe_id = $request->input("classe_id");
        $vp_id = $request->input("vp_id");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";

        $sy_id = MyHelper::getSchoolYearID($year);
        $result = "1";
        try {
            DB::select("update `classe_year` set vp_id = $vp_id WHERE classe_id =$classe_id AND sy_id = $sy_id");
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while assigning VP[' . $vp_id . '] to the class[' . $classe_id . ']: ' . $e->getMessage(),
            ], 500); //ERROR OCCURS
        }
        return response()->json([
            'status' => true,
            'message' => 'VP[' . $vp_id . '] assigned to the class[' . $classe_id . '] successfully',
        ], 200);
    }

    public function assignClassesToAVp(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'vp_id' => 'required|integer|min:1',
                'data' => 'required|json',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }
        $connection = $request->input("connection");
        $year = $request->input("year");
        $vp_id = $request->input("vp_id");
        config(["database.default" => $connection]);

        $sy_id = MyHelper::getSchoolYearID($year);

        $data = $request->input("data");
        $data_size = $request->input("data_size");

        $classList = json_decode($data, true);
        $allAffected = 1; //interpreted as true. 0-->false
        foreach ($classList as $classe) {
            $classe_id = $classe['classe_id'];
            try {
                DB::select("update `classe_year` set vp_id = $vp_id WHERE classe_id =$classe_id AND sy_id = $sy_id");
            } catch (Exception $e) {
                //'message' => 'An error occurred while assigning VP[' . $vp_id . '] to the class[' . $classe_id . ']: ' . $e->getMessage(),
                $allAffected = 0; //failed to assign VP to at least one class
            }
        }

        if ($allAffected == 1) {
            return response()->json([
                'status' => true,
                'message' => 'VP[' . $vp_id . '] assigned to all classes successfully',
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while assigning VP[' . $vp_id . '] to some classes (at least one class assignment failed)',
            ], 500);
        }
    }

    public function removeALLVpClasses(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'vp_id' => 'required|integer|min:1',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }
        $connection = $request->input("connection");
        $year = $request->input("year");
        $vp_id = $request->input("vp_id");
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);

        try {
            DB::select("update `classe_year` set vp_id = NULL WHERE vp_id =$vp_id AND sy_id = $sy_id");
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while removing classes from VP[' . $vp_id . '] : ' . $e->getMessage(),
            ], 500); //ERROR OCCURS
        }
        return response()->json([
            'status' => true,
            'message' => 'All classes removed from VP[' . $vp_id . ']  successfully',
        ], 200);
    }

    public function removeAClassFromAVp(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'vp_id' => 'required|integer|min:1',
                'class_id' => 'required|integer|min:1',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }
        $connection = $request->input("connection");
        $year = $request->input("year");
        $vp_id = $request->input("vp_id");
        $class_id = $request->input("class_id");
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);

        try {
            DB::select("update classe_year set vp_id = NULL WHERE vp_id =$vp_id AND classe_id = $class_id AND sy_id = $sy_id");
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while removing class[' . $class_id . '] from VP[' . $vp_id . '] : ' . $e->getMessage(),
            ], 500); //ERROR OCCURS
        }
        return response()->json([
            'status' => true,
            'message' => 'Class[' . $class_id . '] removed from VP[' . $vp_id . ']  successfully',
        ], 200);
    }


    public function allVpClasses(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'section' => 'required|string',
                'vp_id' => 'required|integer|min:1',
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
        $vp_id = $request->input("vp_id");
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        $section_id = MyHelper::getSectionID($sectionName);

        $classes = DB::select(
            "SELECT classe.classe_id, classe.classe_name, classe.level FROM classe, classe_year 
                WHERE classe.classe_id = classe_year.classe_id
                AND classe_year.sy_id = $sy_id AND classe_year.vp_id = $vp_id 
                AND classe_year.section_id = $section_id"
        );
        return response()->json($classes, 200);
    }


    public function getAllClassesOfSubject(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'section' => 'required|string',
                'subject_id' => 'required|integer|min:1',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }
        $connection = $request->input("connection");
        $year = $request->input("year");
        $section = $request->input("section");
        config(["database.default" => $connection]);
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $section_id = MyHelper::getSectionID($section);

            $classes = DB::select(
                "SELECT subject.subject_title, classe.classe_name, classe.level, 
                    subject_classe.subject_id, subject_classe.classe_id 
                    FROM subject_classe, classe, subject 
                        WHERE  section_id = $section_id AND subject_classe.sy_id = $sy_id  
                        AND subject_classe.classe_id = classe.classe_id 
                        AND subject_classe.subject_id = subject.subject_id
                        ORDER BY subject.subject_title, classe.level, classe.classe_name"
            );
            if (count($classes) > 0) {
                return response()->json($classes, 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'No classes found for this year and section',
                ], 404);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching classes for the year and section: ' . $e->getMessage(),
            ], 500); //ERROR OCCURS
        }
    }

    public function allClassesOfSubject(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'section' => 'required|string',
                'subject_id' => 'required|integer|min:1',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }
        $connection = $request->input("connection");
        $year = $request->input("year");
        $subject_id = $request->input("subject_id");
        $section = $request->input("section");
        config(["database.default" => $connection]);
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $section_id = MyHelper::getSectionID($section);


            $classes = DB::select(
                "SELECT subject_classe.classe_id, classe.classe_name, classe.level, subject_classe.subject_id 
                         FROM subject_classe, classe WHERE subject_id = $subject_id
                         AND section_id = $section_id AND subject_classe.sy_id = $sy_id  
                         AND subject_classe.classe_id = classe.classe_id 
                         ORDER BY classe.level, classe.classe_name"
            );
            if (count($classes) > 0) {
                return response()->json($classes, 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'No classes found for the given subject[' . $subject_id . '] in the specified year and section.',
                ], 404);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching classes for the subject[' . $subject_id . ']: ' . $e->getMessage(),
            ], 500); //ERROR OCCURS
        }
    }

    public function allClasse1OfCM(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'section' => 'required|string',
                'staff_id' => 'required|integer|min:1',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }
        $connection = $request->input("connection");
        $year = $request->input("year");
        $section = $request->input("section");
        $staff_id = $request->input("staff_id");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $section_id = MyHelper::getSectionID($section);


            $classes = DB::select(
                "SELECT  
	                        classe.classe_id, classe.classe_name, classe.level,
	                        speciality.speciality_id, speciality.speciality_name,
	                        classe_year.classe_master AS classe_master_id, 
	                        staff.name AS classe_master_name, 
	                        classe_year.sg_id, 'sg' AS sg_name,	 
	                        0 AS nb_girls, 0 AS nb_boys, 0 As nb_repeating, 0 As nb_solvable, 0 As num
                            FROM 
	                            classe
			                    LEFT OUTER JOIN classe_year
			                        ON classe.classe_id = classe_year.classe_id
			                    LEFT OUTER JOIN speciality
			                        ON speciality.speciality_id = classe_year.speciality_id
			                    LEFT OUTER JOIN staff
			                        ON staff.staff_id = classe_year.classe_master
                            WHERE 
	                            classe_year.sy_id = $sy_id
	                            AND
	                            classe_year.section_id = $section_id
                                AND 
                                classe_year.classe_master = $staff_id
                                ORDER BY classe.level, classe.classe_name ASC;"
            );
            if (count($classes) > 0) {
                return response()->json($classes, 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'No classes found for the class master with ID [' . $staff_id . '] in the given year and section.',
                ], 404);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred when fetching classes for the class master with ID [' . $staff_id . ']: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getAPCLevels(Request $request)
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
        $section = $request->input("section");
        config(["database.default" => $connection]);
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $section_id = MyHelper::getSectionID($section);

            $apcLevels = DB::select(
                "SELECT `apc_level_id`, `sy_id`, `level`, `activated`, `section_id` 
                FROM apc_level WHERE `sy_id` = $sy_id
                AND `section_id` = $section_id;"
            );
            if (count($apcLevels) > 0) {
                return response()->json($apcLevels, 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'No APC levels found for the given year and section.',
                ], 404);
            }
        } catch (Exception  $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching APC levels: ' . $e->getMessage(),
            ], 500);
        }
    }

    //Get all the classes of the section
    public function getAllClassesOfSection(Request $request)
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
        $section = $request->input("section");
        config(["database.default" => $connection]);
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $section_id = MyHelper::getSectionID($section);

            $classes = DB::select(
                "SELECT classe.classe_id, classe.classe_name FROM classe
                            WHERE classe.classe_id IN(SELECT classe_year.classe_id
                            FROM classe_year WHERE classe_year.sy_id = $sy_id
                            AND classe_year.section_id = $section_id)
                            ORDER BY classe.level, classe.classe_name"
            );
            if (count($classes) > 0) {
                return response()->json($classes, 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'No classes found for the given section.',
                ], 404);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching classes of section [' . $section . '] ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getClassesOfSameLevel(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'section' => 'required|string',
                'classe_id' => 'required|integer|min:1',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }
        $connection = $request->input("connection");
        $year = $request->input("year");
        $section = $request->input("section");
        $classe_id = $request->input("classe_id");
        config(["database.default" => $connection]);
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $section_id = MyHelper::getSectionID($section);

            $classe = Classe::find($classe_id);
            if (!is_null($classe)) {
                $level = $classe->level;
                $classes = DB::select(
                    "SELECT classe.classe_name FROM classe, classe_year
                            WHERE classe.classe_id = classe_year.classe_id
                                AND classe.level = $level
                                AND classe_year.sy_id = $sy_id
                                AND classe_year.section_id = $section_id
                                AND classe.classe_id <> $classe_id"
                );

                if (count($classes) > 0) {
                    $res = array();
                    $k = 0;
                    foreach ($classes as $cl) {
                        //echo $cl->classe_name;
                        $res[$k] =  $cl->classe_name;
                        $k++;
                    }
                    return response()->json($res, 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'No classes of the same level found for classe_id [' . $classe_id . ']',
                    ], 404);
                }
            }
            return response()->json([
                'status' => false,
                'message' => 'Classe not found for classe_id [' . $classe_id . ']',
            ], 404); //CLASSES OF SAME LEVEL NOT FOUND
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching classes of the same level as classe_id [' . $classe_id . '] : ' . $e->getMessage(),
            ], 500);
        }
    }

    //Return classe ids along side with their nb boys nb girls, nb repeating for that school year and section
    public function getForClasseSize(Request $request)
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
        $section = $request->input("section");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $section_id = MyHelper::getSectionID($section);

            $classes = DB::select(
                "SELECT  
                             classe.classe_id, classe.classe_name, classe.level,
                             student.stud_id, student.name, student.sexe, 
                             student_classe.stud_id, student_classe.repeating,
                             student_classe.solvable1
                             FROM 
                                 classe
                                 LEFT OUTER JOIN student_classe
                                     ON classe.classe_id = student_classe.classe_id
                                 LEFT OUTER JOIN student
                                     ON student.stud_id = student_classe.stud_id 
                             WHERE 
                                 student_classe.sy_id = $sy_id
                                 AND
                                 student_classe.classe_id IN(
                                 SELECT classe_year.classe_id FROM classe_year 
											WHERE classe_year.section_id = $section_id)
                                 ORDER BY classe.level, classe.classe_name ASC;"
            );
            if (count($classes) > 0) {
                return response()->json($classes, 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'No classes found for the specified year and section.',
                ], 404);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error when getForClasseSize: ' . $e->getMessage(),
            ], 500);
        }
    }

    ///Find classes WHERE A SUJECT IS TAUGHT
    public function getClassesOfASuject(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'section' => 'required|string',
                'subject_id' => 'required|integer|min:1',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }
        $connection = $request->input("connection");
        $year = $request->input("year");
        $section = $request->input("section");
        $subject_id = $request->input("subject_id");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $section_id = MyHelper::getSectionID($section);

            $classes = DB::select(
                "SELECT classe_id, classe_name, LEVEL, '' as msg FROM classe  WHERE classe.classe_id 
                            IN(SELECT subject_classe.classe_id FROM subject_classe
                                WHERE subject_classe.sy_id = $sy_id AND 
                                    subject_classe.section_id = $section_id
                                    AND subject_classe.subject_id = $subject_id)
                                    ORDER BY level, classe.classe_name"
            );
            if (count($classes) > 0) {
                return response()->json($classes, 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'No classes found where subject [' . $subject_id . '] is taught',
                ], 404); //NOT FOUND
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error occurred when fetching classes where subject [' . $subject_id . '] is taught: ' . $e->getMessage(),
            ], 500); //ERROR OCCURS

        }
    }

    public function deleteClassesOfSectionAndYear(Request $request)
    {
        $connection = $request->input("connection");
        $year = $request->input("year");
        $section = $request->input("section");
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        $section_id = MyHelper::getSectionID($section);
        $res = MyHelper::deleteClasses($sy_id, $section_id);
        //echo $res; //1--> success; negative int --> Failed
        echo 1;
    }

    public function deleteManyClasses(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $year  = $request->input("year");
        $data = $request->input("data");
        $section = $request->input("section");
        $data_size = $request->input("data_size");

        $clList = json_decode($data, true);
        $n = count($clList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]";
        //$allAffected = 1; //interpreted as true. 0-->false

        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        $section_id = MyHelper::getSectionID($section);
        $allAffected = 1;
        foreach ($clList as $cl) {
            $classe_id = $cl["classe_id"];
            //$clRef = Classe::find($classe_id);            
            try {
                $res = MyHelper::deleteAClasse("$sy_id", $section_id, $classe_id);
                if ($res < 0) {
                    $allAffected = 0;
                    echo "\$res$res<br/>";
                }
            } catch (Exception $ex) {
                $allAffected = 0;
                echo "ERROR " . $ex->getMessage() . '<br/>';
            }
        } //END FOR
        //return response($allAffected, 200);
        echo  $allAffected; //1--> All speciality successfully deleted; 0--> Failed to save at least one
    }

    public function deleteManyClassesWithPOST(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $year  = $request->input("year");
        $data = $request->input("data");
        $section = $request->input("section");
        $data_size = $request->input("data_size");

        $clList = json_decode($data, true);
        $n = count($clList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]";
        //$allAffected = 1; //interpreted as true. 0-->false

        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        $section_id = MyHelper::getSectionID($section);
        $allAffected = 1;
        foreach ($clList as $cl) {
            $classe_id = $cl["classe_id"];
            //$clRef = Classe::find($classe_id);            
            try {
                $res = MyHelper::deleteAClasse("$sy_id", $section_id, $classe_id);
                if ($res < 0) {
                    $allAffected = 0;
                    echo "\$res$res<br/>";
                }
            } catch (Exception $ex) {
                $allAffected = 0;
                echo "ERROR " . $ex->getMessage() . '<br/>';
            }
        } //END FOR
        //return response($allAffected, 200);
        echo  $allAffected; //1--> All speciality successfully deleted; 0--> Failed to save at least one
    }


    public function updateManyClasses(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'data' => 'required|json',
                'data_size' => 'nullable|integer|min:1',
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
        $year = $request->input("year");

        $allAffected = 1; //interpreted as true. 0-->false
        $allAffected2 = 1;
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);

        $clList = json_decode($data, true);
        foreach ($clList as $cl) {
            $classe_id = $cl["classe_id"];
            $classe_name = $cl["classe_name"];
            $level = $cl["level"];
            $speciality_id = $cl["speciality_id"];
            $classe_master = $cl["classe_master_id"];
            $sg_id = $cl["sg_id"];

            //------- UPDATE THE CLASSE YEAR
            try {
                $cl = Classe::where('classe_id', $classe_id)->first();
                $cl->level = $level;
                $cl->classe_name = $classe_name;
                $allAffected = $cl->update();

                $cly = ClasseYear::where('sy_id', $sy_id)
                    ->where('classe_id', $classe_id)
                    ->first();
                $cly->speciality_id = $speciality_id;
                $cly->sg_id = $sg_id;
                $cly->classe_master = $classe_master;
                $allAffected2 = $cly->update();
            } catch (Exception $ex) {
                //echo $ex->getMessage();
                $allAffected = 0;
            }
            if ($allAffected != 1 || $allAffected2 != 1) {
                $allAffected = 0;
            }
        } //END FOR

        //echo "$allAffected"; //1--> All classes successfully modified; 0--> Failed to save at least one
        if ($allAffected == 1) {
            return response()->json([
                'status' => true,
                'message' => 'All classes updated successfully',
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update at least one class',
            ], 500);
        }
    }


    public function allClasse1(Request $request)
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
        $section = $request->input("section");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $section_id = MyHelper::getSectionID($section);

            $classes = DB::select(
                "SELECT  
	                        classe.classe_id, classe.classe_name, classe.level,
	                        speciality.speciality_id, speciality.speciality_name,
	                        classe_year.classe_master AS classe_master_id, 
	                        staff.name AS classe_master_name, 
	                        classe_year.sg_id, classe_year.vp_id, 'sg' AS sg_name,	 
	                        0 AS nb_girls, 0 AS nb_boys, 0 As nb_repeating, 0 As nb_solvable, 0 As num,
                            classe_year.avgDismissalTh, classe_year.repeatUB, classe_year.passMark, 
                            classe_year.totalAbsTh, classe_year.totalExclusionTh
                            FROM 
	                            classe
			                    LEFT OUTER JOIN classe_year
			                        ON classe.classe_id = classe_year.classe_id
			                    LEFT OUTER JOIN speciality
			                        ON speciality.speciality_id = classe_year.speciality_id
			                    LEFT OUTER JOIN staff
			                        ON staff.staff_id = classe_year.classe_master
                            WHERE 
	                            classe_year.sy_id = $sy_id
	                            AND
	                            classe_year.section_id = $section_id
                                ORDER BY classe.level, classe.classe_name ASC;"
            );
            if (count($classes) > 0) {
                return response()->json($classes, 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'No classes found for the specified section and year.',
                ], 404);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error occurred while fetching classes: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function allClasseOfSection(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'section_id' => 'required|integer|min:1',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }
        $connection = $request->input("connection");
        $year = $request->input("year");
        $section_id = $request->input("section_id");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $classes = DB::select(
                "SELECT  
	                        classe.classe_id, classe.classe_name, classe.level,
	                        speciality.speciality_id, speciality.speciality_name,
	                        classe_year.classe_master AS classe_master_id, 
	                        staff.name AS classe_master_name, 
	                        classe_year.sg_id, classe_year.vp_id, 'sg' AS sg_name,	 
	                        0 AS nb_girls, 0 AS nb_boys, 0 As nb_repeating, 0 As nb_solvable, 0 As num,
                            classe_year.avgDismissalTh, classe_year.repeatUB, classe_year.passMark, 
                            classe_year.totalAbsTh, classe_year.totalExclusionTh
                            FROM 
	                            classe
			                    LEFT OUTER JOIN classe_year
			                        ON classe.classe_id = classe_year.classe_id
			                    LEFT OUTER JOIN speciality
			                        ON speciality.speciality_id = classe_year.speciality_id
			                    LEFT OUTER JOIN staff
			                        ON staff.staff_id = classe_year.classe_master
                            WHERE 
	                            classe_year.sy_id = $sy_id
	                            AND
	                            classe_year.section_id = $section_id
                                ORDER BY classe.level, classe.classe_name ASC;"
            );
            if (count($classes) > 0) {
                return response()->json($classes, 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'No classes found for this year and section [year: ' . $year . ', section_id: ' . $section_id . ']',
                ], 404);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error occurred while fetching classes: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function saveClasse(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'speciality_name' => 'nullable|string',
                'classe_name' => 'required|string',
                'classe_master_id' => 'nullable|integer|min:1',
                'section' => 'required|string',
                'level' => 'required|integer|min:1',
                'sg_id' => 'nullable|integer|min:1',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }
        $connection = $request->input("connection");
        $year = $request->input("year");
        $speciality_name = $request->input("speciality_name");
        $classe_name = $request->input("classe_name");
        $classe_master = $request->input("classe_master_id");
        $section_name = $request->input("section");
        $level = $request->input("level");
        $sg_id = $request->input("sg_id");
        config(["database.default" => $connection]);

        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $section_id = MyHelper::getSectionID($section_name);

            $cl = new Classe();
            $cl->classe_name = $classe_name;
            $cl->level = $level;
            $cl_id = 1;

            $clYear = new ClasseYear();
            try {
                $clTmp = Classe::where("classe_name", "=", $classe_name)
                    ->first();
                if (is_null($clTmp)) {
                    $cl->save();
                    $cl_id = $cl->classe_id;
                } else {
                    $cl_id = $clTmp->classe_id;
                }


                $sp = Speciality::where("speciality_name", $speciality_name)->first();
                if (!is_null($sp)) {
                    $clYear->speciality_id = $sp->speciality_id;
                    //$sp will be null if speciality_name is Empty or speciality_name is null
                }
                $clYear->classe_id = $cl_id; //ID OF cl CAN ONLY BE OBTAINED AFTER SAVING the cl (classe)                
                $clYear->sy_id = $sy_id;
                $clYear->section_id = $section_id;
                if ($classe_master != "null") {
                    $clYear->classe_master = $classe_master;
                }
                if ($sg_id != "null") {
                    $clYear->sg_id = $sg_id;
                }

                $clYearTmp = DB::select("SELECT * FROM classe_year WHERE classe_id = ? AND sy_id = ?", [$cl_id, $sy_id]);
                if (count($clYearTmp) > 0) {
                    return response()->json([
                        'status' => false,
                        'message' => 'ClasseYear already exists for Classe[' . $cl_id . '] and SchoolYear[' . $sy_id . ']. We consider that [' . $classe_name . '] already exists',
                    ], 409);
                } else {
                    $clYear->save();
                }

                //echo "1"; //Operation is successfull
                return response()->json([
                    'status' => true,
                    'message' => 'Classe[' . $cl_id . '] and ClasseYear[' . $clYear->classe_year_id . '] created successfully.',
                ], 201);
            } catch (Exception $ex) {
                //If exception then cl or clYear failed to save. We delete it to avoid inconsitency
                $cl->delete();
                try {
                    $clYear->delete();
                } catch (Exception $exx) {
                    //echo '<br/>Message: ' . $exx->getMessage() . '<br>';
                }
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to save Classe[' . $classe_name . '] or ClasseYear: ' . $ex->getMessage(),
                ], 500);
            }
        } catch (Exception $e) {
            //echo '<br/>Message: ' . $e->getMessage();
            return response()->json([
                'status' => false,
                'message' => 'Classe[' . $request->input("classe_name") . '] already exists: ' . $e->getMessage(),
            ], 409);
        }
    }


    public function saveManyClasses(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'section' => 'required|string',
                'data' => 'required|json',
                'data_size' => 'nullable|integer|min:1',

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
        $year = $request->input("year");
        $section = $request->input("section");

        $clList = json_decode($data, true);
        //$n = count($fList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]";


        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        $section_id = MyHelper::getSectionID($section);
        $msg = "";
        $allAffected = 1; //1--> Success. 0--> Some classes not saved [Exists allready in other sections or contain wrong characters]
        foreach ($clList as $cl) {
            $classe_name = $cl["classe_name"];
            $level = $cl["level"];
            $sg_id = $cl["sg_id"];
            $classe_master_id = $cl["classe_master_id"];
            $speciality_name = $cl["speciality_name"];
            $clTmp = Classe::where("classe_name", "=", $classe_name)->first();
            try {
                $classe_id = -1;
                if (MyHelper::validateStr($classe_name)) {
                    if (is_null($clTmp)) { //LA CLASSE N'EXISTE PAS
                        echo "$classe_name n'existe pas";
                        $clToSave = new Classe();
                        $clToSave->classe_name = $classe_name;
                        $clToSave->level = $level;
                        $clTmp = Classe::where("classe_name", "=", $classe_name)
                            ->first();
                        if (is_null($clTmp)) {
                            $clToSave->save();
                        } else {
                            $allAffected = 0; // the classe already exists for this classe_name.
                            $msg = $msg . "'$classe_name' already exists<br/>";
                            continue; //We skip this classe and continue with the next one
                        }
                        $classe_id =  $clToSave->classe_id;
                    } else { //LA CLASSE EXISTE DEJA  
                        //$classe_id = $clTmp->$classe_id;
                        $classe_id = $clTmp['classe_id'];
                    }

                    $clYear = new ClasseYear();
                    $clYear->classe_id = $classe_id;
                    $clYear->sy_id = $sy_id;
                    $clYear->section_id = $section_id;
                    $clYear->sg_id = $sg_id;
                    $clYear->classe_master = $classe_master_id;
                    $sp = Speciality::where("speciality_name", $speciality_name)->first();
                    if (!is_null($sp)) {
                        $clYear->speciality_id = $sp->speciality_id;
                    }

                    $clYearTmp = DB::select("SELECT * FROM classe_year WHERE classe_id = ? AND sy_id = ?", [$classe_id, $sy_id]);
                    if (count($clYearTmp) > 0) {
                        $allAffected = 0; // the classe_year already exists for this classe and school year. 
                        $msg = $msg . "'$classe_name' already exists has a classe_year record for the [' . $sy_id . '] school year. So we consider the classe exists already<br/>";
                        continue; //We skip this classe and continue with the next one
                    } else {
                        $clYear->save();
                    }
                } else {
                    $msg = $msg . "'$classe_name' contains invalid characters<br/>";
                    $allAffected = 0;
                }
            } catch (Exception $exx) {
                $msg = $msg . "<br/>" . $exx->getMessage() . "<br/>";
            }
        } //END FOR

        if ($allAffected == 1) {
            return response()->json([
                'status' => true,
                'message' => 'All classes successfully saved.',
            ], 201);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Failed to save at least one class. Details: <br/>' . $msg,
            ], 500);
        } //allAffected=1--> All classes successfully saved; allAffected=0--> Failed to save at least one
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
    public function show(Classe $classe)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Classe $classe)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Classe $classe)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Classe $classe)
    {
        //
    }
}
