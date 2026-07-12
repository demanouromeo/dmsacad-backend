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
        $result = "1";
        try {
            DB::select("update `classe_year` set totalAbsTh = $totalAbsTh, 
                    totalExclusionTh = $totalExclusionTh, avgDismissalTh = $avgDismissalTh,
                    repeatUb = $repeatUb, passMark = $passMark
                WHERE classe_id =$classe_id AND sy_id = $sy_id");
        } catch (Exception $e) {
            echo '<br/>ERROR: ' . $e->getMessage();
            $result = "-1"; //ERROR OCCURS
        }
        echo "$result";
    }

    public function assignVpAClass(Request $request)
    {
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
            echo '<br/>ERROR: ' . $e->getMessage();
            $result = "-1"; //ERROR OCCURS
        }
        echo "$result";
    }

    public function removeALLVpClasses(Request $request)
    {
        $connection = $request->input("connection");
        $year = $request->input("year");
        $vp_id = $request->input("vp_id");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        $sy_id = MyHelper::getSchoolYearID($year);
        $result = 1;
        try {
            DB::select("update `classe_year` set vp_id = NULL WHERE vp_id =$vp_id AND sy_id = $sy_id");
        } catch (Exception $e) {
            //echo '<br/>ERROR: ' . $e->getMessage();
            $result = -1; //ERROR OCCURS
        }
        echo $result;
    }

    public function getAllClassesOfSubject(Request $request)
    {
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
                return [];
            }
        } catch (Exception $e) {
            //echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }

    public function allClassesOfSubject(Request $request)
    {
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
                return [];
            }
        } catch (Exception $e) {
            //echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }

    public function allClasse1OfCM(Request $request)
    {
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
                return [];
            }
        } catch (Exception $e) {
            //echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }

    public function getAPCLevels(Request $request)
    {
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
                return [];
            }
        } catch (Exception  $e) {
            //echo '<br/>ERROR: ' . $e->getMessage();
            return [];
        }
    }

    //Get all the classes of the section
    public function getAllClassesOfSection(Request $request)
    {
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
                return [];
            }
        } catch (Exception $e) {
            //echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }

    public function getClassesOfSameLevel(Request $request)
    {
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
                    //return response()->json($classes, 200);
                } else {
                    return [];
                }
            }
            return []; //CLASSES OF SAME LEVEL NOT FOUND            
        } catch (Exception $e) {
            //echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }

    //Return classe ids along side with their nb boys nb girls, nb repeating for that school year and section
    public function getForClasseSize(Request $request)
    {
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
                return [];
            }
        } catch (Exception $e) {
            //echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }

    ///Find classes WHERE A SUJECT IS TAUGHT
    public function getClassesOfASuject(Request $request)
    {
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
                return [];
            }
        } catch (Exception $e) {
            //echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
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
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $data = $request->input("data");
        //$data = $request->input("year");
        $data_size = $request->input("data_size");
        $year = $request->input("year");

        $clList = json_decode($data, true);
        //$n = count($fList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]";
        $allAffected = 1; //interpreted as true. 0-->false
        $allAffected2 = 1;
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        foreach ($clList as $cl) {
            $classe_id = $cl["classe_id"];
            $classe_name = $cl["classe_name"];
            $level = $cl["level"];
            $speciality_id = $cl["speciality_id"];
            $classe_master = $cl["classe_master_id"];
            $sg_id = $cl["sg_id"];
            //echo "cl_id: $classe_id -- cl_name: $classe_name  --  Level: $level --  
            //    speciality_id: $speciality_id -- classe_master_id: $classe_master --  
            //   sg_id: $sg_id";
            //------- UPDATE THE CLASSE YEAR
            try {
                //code...
                /*$allAffected = DB::table('classe')
                    ->where('classe_id', $classe_id)
                    ->update(['classe_name' => $classe_name,'level' => $level]);
                */
                $cl = Classe::where('classe_id', $classe_id)->first();
                $cl->level = $level;
                $cl->classe_name = $classe_name;
                $allAffected = $cl->update();

                //------- UPDATE THE CLASSE YEAR
                /*$allAffected2 = DB::table('classe_year')
                    ->where('classe_id', $classe_id)
                    ->where('sy_id', $sy_id)
                    ->update([                        
                        'speciality_id' => $speciality_id,
                        'classe_master' => $classe_master,
                        'sg_id' => $sg_id
                    ]);
                    */
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
        }
        echo "$allAffected"; //1--> All classes successfully modified; 0--> Failed to save at least one
    }

    public function updateManyClassesWithPOST(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $data = $request->input("data");
        //$data = $request->input("year");
        $data_size = $request->input("data_size");
        $year = $request->input("year");

        $clList = json_decode($data, true);
        //$n = count($fList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]";
        $allAffected = 1; //interpreted as true. 0-->false
        $allAffected2 = 1;
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        foreach ($clList as $cl) {
            $classe_id = $cl["classe_id"];
            $classe_name = $cl["classe_name"];
            $level = $cl["level"];
            $speciality_id = $cl["speciality_id"];
            $classe_master = $cl["classe_master_id"];
            $sg_id = $cl["sg_id"];
            //echo "cl_id: $classe_id -- cl_name: $classe_name  --  Level: $level --  
            //    speciality_id: $speciality_id -- classe_master_id: $classe_master --  
            //   sg_id: $sg_id";
            //------- UPDATE THE CLASSE YEAR
            try {
                //code...
                /*$allAffected = DB::table('classe')
                    ->where('classe_id', $classe_id)
                    ->update(['classe_name' => $classe_name,'level' => $level]);
                */
                $cl = Classe::where('classe_id', $classe_id)->first();
                $cl->level = $level;
                $cl->classe_name = $classe_name;
                $allAffected = $cl->update();

                //------- UPDATE THE CLASSE YEAR
                /*$allAffected2 = DB::table('classe_year')
                    ->where('classe_id', $classe_id)
                    ->where('sy_id', $sy_id)
                    ->update([                        
                        'speciality_id' => $speciality_id,
                        'classe_master' => $classe_master,
                        'sg_id' => $sg_id
                    ]);
                    */
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
        }
        echo "$allAffected"; //1--> All classes successfully modified; 0--> Failed to save at least one
    }

    public function allClasse1(Request $request)
    {
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
                return [];
            }
        } catch (Exception $e) {
            //echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }

    public function allClasseOfSection(Request $request)
    {
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
                return [];
            }
        } catch (Exception $e) {
            //echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }


    public function saveClasse(Request $request)
    {
        /*        
           'level': selectedLevel.toString(),
         */
        $connection = $request->input("connection");
        $year = $request->input("year");
        $speciality_name = $request->input("speciality_name");
        $classe_name = $request->input("classe_name");
        $classe_master = $request->input("classe_master_id");
        $section_name = $request->input("section");
        $level = $request->input("level");
        $sg_id = $request->input("sg_id");
        config(["database.default" => $connection]);
        //echo "Connection: $connection <br/>Year: $year <br/>sp_name: $speciality_name"
        //   . "<br/>classe_name: $classe_name <br/> 
        //   Section: $section_name <br/>classe_master: $classe_master
        //   <br/>Level: $level<br/>sg_id: $sg_id<br/>";        
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
                    //$cl_id = $clTmp['classe_id'];
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
                $clYear->save();
                echo "1"; //Operation is successfull
            } catch (Exception $ex) {
                //If exception then sp or clYear failed to save. We delete them to avoid inconsitency
                $cl->delete();
                try {
                    $clYear->delete();
                } catch (Exception $exx) {
                    //echo '<br/>Message: ' . $exx->getMessage() . '<br>';
                }
                //echo '<br/>Message: ' . $ex->getMessage() . '<br>';
                echo "-2"; //Operation failed
            }
        } catch (Exception $e) {
            //echo '<br/>Message: ' . $e->getMessage();
            echo "-1"; //La classe existe existe déja
        }
    }


    public function saveManyClasses(Request $request)
    {
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $year = $request->input("year");
        $section = $request->input("section");

        $clList = json_decode($data, true);
        //$n = count($fList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]";
        $k = 1; //1--> Success. 0--> Some classes not saved [Exists allready in other sections or contain wrong characters]
        $allAffected2 = 1;

        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        $section_id = MyHelper::getSectionID($section);
        $msg = "";
        foreach ($clList as $cl) {
            $classe_name = $cl["classe_name"];
            $level = $cl["level"];
            $clTmp = Classe::where("classe_name", "=", $classe_name)->first();
            try {
                $classe_id = -1;
                if (MyHelper::validateStr($classe_name)) {
                    if (is_null($clTmp)) { //LA CLASSE N'EXISTE PAS
                        echo "$classe_name n'existe pas";
                        $clToSave = new Classe();
                        $clToSave->classe_name = $classe_name;
                        $clToSave->level = $level;
                        $clToSave->save();
                        $classe_id =  $clToSave->$classe_id;
                    } else { //LA CLASSE EXISTE DEJA  
                        //$classe_id = $clTmp->$classe_id;
                        $classe_id = $clTmp['classe_id'];
                    }
                    $clYear = new ClasseYear();
                    $clYear->classe_id = $classe_id;
                    $clYear->sy_id = $sy_id;
                    $clYear->section_id = $section_id;
                    $clYear->save();
                } else {
                    $msg = $msg . "'$classe_name' contains invalid characters\n";
                    $k = 0;
                }
            } catch (Exception $exx) {
                $msg = $msg . "\n" . $exx->getMessage() . "\n";
            }
        } //END FOR

        if ($k == 1) {
            echo $k;
        } else {
            echo $msg;
        } //K=1--> All classes successfully modified; K=0--> Failed to save at least one
    }

    public function saveManyClassesWithPOST(Request $request)
    {
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $year = $request->input("year");
        $section = $request->input("section");

        $clList = json_decode($data, true);
        //$n = count($fList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]";
        $k = 1; //1--> Success. 0--> Some classes not saved [Exists allready in other sections or contain wrong characters]
        $allAffected2 = 1;

        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        $section_id = MyHelper::getSectionID($section);
        $msg = "";
        foreach ($clList as $cl) {
            $classe_name = $cl["classe_name"];
            $level = $cl["level"];
            $clTmp = Classe::where("classe_name", "=", $classe_name)->first();
            try {
                $classe_id = -1;
                if (MyHelper::validateStr($classe_name)) {
                    if (is_null($clTmp)) { //LA CLASSE N'EXISTE PAS
                        $clToSave = new Classe();
                        $clToSave->classe_name = $classe_name;
                        $clToSave->level = $level;
                        $clToSave->save();
                        $classe_id =  $clToSave->$classe_id;
                    } else { //LA CLASSE EXISTE DEJA  
                        //$classe_id = $clTmp->$classe_id;
                        $classe_id = $clTmp['classe_id'];
                    }
                    $clYear = new ClasseYear();
                    $clYear->classe_id = $classe_id;
                    $clYear->sy_id = $sy_id;
                    $clYear->section_id = $section_id;
                    $clYear->save();
                } else {
                    $msg = $msg . "'$classe_name' contains invalid characters\n";
                    $k = 0;
                }
            } catch (Exception $exx) {
                $msg = $msg . "\n" . $exx->getMessage() . "\n";
            }
        } //END FOR

        if ($k == 1) {
            echo $k;
        } else {
            echo $msg;
        } //K=1--> All classes successfully modified; K=0--> Failed to save at least one
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
