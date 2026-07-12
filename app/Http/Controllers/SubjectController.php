<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\StudCompMark;
use App\Models\Subject;
use App\Models\SubjectClasse;
use App\Models\SubjectClasseStaff;
use App\Models\SubjectCompetence;
use App\Models\SubjectYear;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubjectController extends Controller
{

    public function deleteCompetencesWithNoMarks(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $year  = $request->input("year");
        $data = $request->input("data");
        $data_size = $request->input("data_size");

        $compList = json_decode($data, true);
        $n = count($compList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]";
        //$allAffected = 1; //interpreted as true. 0-->false
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        //echo "sy_id: $section</br>"; 
        foreach ($compList as $sub) {
            $comp_id = $sub["subject_competence_id"];
            $allAffected = 1;
            try {
                $x = DB::select(
                    "DELETE FROM subject_competences 
                WHERE subject_competence_id = ?",
                    [$comp_id]
                );
            } catch (Exception $ex) {
                $allAffected = 0;
                //echo "ERROR " . $ex->getMessage();
            }
        } //END FOR
        //return response($allAffected, 200);
        echo (string) $allAffected; //1--> All competences successfully deleted; 0--> Failed to delete at least one
    }

    public function deleteCompetencesWithNoMarksPOST(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $year  = $request->input("year");
        $data = $request->input("data");
        $data_size = $request->input("data_size");

        $compList = json_decode($data, true);
        $n = count($compList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]";
        //$allAffected = 1; //interpreted as true. 0-->false
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        //echo "sy_id: $section</br>"; 
        foreach ($compList as $sub) {
            $comp_id = $sub["subject_competence_id"];
            $allAffected = 1;
            try {
                $x = DB::select(
                    "DELETE FROM subject_competences 
                WHERE subject_competence_id = ?",
                    [$comp_id]
                );
            } catch (Exception $ex) {
                $allAffected = 0;
                //echo "ERROR " . $ex->getMessage();
            }
        } //END FOR
        //return response($allAffected, 200);
        echo (string) $allAffected; //1--> All competences successfully deleted; 0--> Failed to delete at least one
    }

    public function saveManySubjectsWithPOST(Request $request)
    {
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $year = $request->input("year");
        $section = $request->input("section");

        $subList = json_decode($data, true);
        //$n = count($fList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]";
        $k = 1; //1--> Success. 0--> Some classes not saved [Exists allready in other sections or contain wrong characters]
        $allAffected2 = 1;

        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        $section_id = MyHelper::getSectionID($section);
        $msg = "";
        foreach ($subList as $sub) {
            $subject_title = $sub["subject_title"];
            $subTmp = Subject::where("subject_title", "=", $subject_title)->first();
            try {
                $sub = new Subject();
                $subYear = new SubjectYear();

                $sub->subject_title = $subject_title;
                //$sub->subject_code = null; //SET SUBJECT CODE TO NULL FOR NOW
                $subTmp = Subject::where("subject_title", "=", $subject_title)->first();
                try {
                    $id = 1;
                    if (is_null($subTmp)) {
                        $sub->save();
                        $id = $sub->subject_id;
                    } else {
                        $id = $subTmp->subject_id;
                    }
                    $subYear->subject_id = $id;
                    $subYear->sy_id = $sy_id;
                    $subYear->section_id = $section_id;
                    $subYear->save();
                    //Operation is successfull FOR CURRENT SUBJECT
                } catch (Exception $ex) {
                    $k = 0; //To mean AT LEAST ONE SUBJECT FAILED TO SAVE
                    try {
                        $sub->delete();
                    } catch (Exception $exx) {
                    }
                    try {
                        $subYear->delete();
                    } catch (Exception $exx) {
                    }
                    //echo '<br/>Message: ' . $ex->getMessage() . '<br>';
                    //Operation failed FOR CURRENT SUBJECT
                }
            } catch (Exception $exx) {
                $msg = $msg . "\n" . $exx->getMessage() . "\n";
                $k = -1; //Exception surfaces
            }
        } //END FOR

        if ($k == 1) {
            echo $k;
        } else {
            echo "$k|$msg";
        } //K=1--> All subjects successfully modified; K=0--> Failed to save at least one
    }

    public function saveManySubjects(Request $request)
    {
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $year = $request->input("year");
        $section = $request->input("section");

        $subList = json_decode($data, true);
        //$n = count($fList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]";
        $k = 1; //1--> Success. 0--> Some classes not saved [Exists allready in other sections or contain wrong characters]
        $allAffected2 = 1;

        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        $section_id = MyHelper::getSectionID($section);
        $msg = "";
        foreach ($subList as $sub) {
            $subject_title = $sub["subject_title"];
            $subTmp = Subject::where("subject_title", "=", $subject_title)->first();
            try {
                $sub = new Subject();
                $subYear = new SubjectYear();

                $sub->subject_title = $subject_title;
                //$sub->subject_code = null; //SET SUBJECT CODE TO NULL FOR NOW
                $subTmp = Subject::where("subject_title", "=", $subject_title)->first();
                try {
                    $id = 1;
                    if (is_null($subTmp)) {
                        $sub->save();
                        $id = $sub->subject_id;
                    } else {
                        $id = $subTmp->subject_id;
                    }
                    $subYear->subject_id = $id;
                    $subYear->sy_id = $sy_id;
                    $subYear->section_id = $section_id;
                    $subYear->save();
                    //Operation is successfull FOR CURRENT SUBJECT
                } catch (Exception $ex) {
                    $k = 0; //To mean AT LEAST ONE SUBJECT FAILED TO SAVE
                    try {
                        $sub->delete();
                    } catch (Exception $exx) {
                    }
                    try {
                        $subYear->delete();
                    } catch (Exception $exx) {
                    }
                    //echo '<br/>Message: ' . $ex->getMessage() . '<br>';
                    //Operation failed FOR CURRENT SUBJECT
                }
            } catch (Exception $exx) {
                $msg = $msg . "\n" . $exx->getMessage() . "\n";
                $k = -1; //Exception surfaces
            }
        } //END FOR

        if ($k == 1) {
            echo $k;
        } else {
            echo "$k|$msg";
        } //K=1--> All subjects successfully modified; K=0--> Failed to save at least one
    }

    public function deleteCompetencesOfAClasse(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $year = $request->input("year");
        $classe_id = $request->input("classe_id");
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        try {
            //DELETE ALL STUD_COMP_MARKS RELATED
            //$res = StudCompMark::
            //where("subject_competence_id", "$subject_competence_id")
            //->delete();
            $res = DB::select("DELETE FROM subject_competences 
                WHERE classe_id = $classe_id and sy_id = $sy_id");
            echo "1";
        } catch (Exception $e) {
            echo "<br/>" . $e->getMessage();
            echo "-1";
        }
    }

    public function deleteManyCompetences(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $year = $request->input("year");
        $section = $request->input("section");

        $compList = json_decode($data, true);
        //$n = count($subList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]";
        $allAffected = 1; //interpreted as true. 0-->false
        config(["database.default" => $connection]);

        $sy_id = MyHelper::getSchoolYearID($year);
        $section_id = MyHelper::getSectionID($section);

        foreach ($compList as $comp) {
            $subject_competence_id = $comp["subject_competence_id"];
            $compRef = SubjectCompetence::find($subject_competence_id);
            $affected = 1;
            try {
                //DELETE ALL STUD_COMP_MARKS RELATED
                //$res = StudCompMark::
                //where("subject_competence_id", "$subject_competence_id")
                //->delete();
                $res = DB::select("DELETE FROM stud_comp_mark 
                WHERE stud_comp_mark.subject_competence_id = $subject_competence_id");
                //echo"<br/>$res<br/>";
                $affected = $compRef->delete();
            } catch (Exception $e) {
                $affected = 0;
                echo "<br/>" . $e->getMessage();
            }
            if ($affected != 1) {
                $allAffected = 0;
            }
        }
        echo "$allAffected"; //1--> All stud_comp_mark successfully deleted; 0--> Failed to delete at least one
    }




    public function updateManyCompetences(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $year = $request->input("year");
        $section = $request->input("section");

        $compList = json_decode($data, true);
        //$n = count($subList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]";
        $allAffected = 1; //interpreted as true. 0-->false
        config(["database.default" => $connection]);
        foreach ($compList as $comp) {
            // code
            $subject_competence_id = $comp["subject_competence_id"];
            $compRef = SubjectCompetence::find($subject_competence_id);
            $compRef->competence_text = $comp["competence_text"];
            $affected = $compRef->update();
            if ($affected != 1) {
                $allAffected = 0;
            }
        }
        echo "$allAffected"; //1--> All groupes successfully modified; 0--> Failed to save at least one
    }

    public function allCompetences(Request $request)
    {
        $connection = $request->input("connection");
        $year = $request->input("year");
        $section_name = $request->input("section");
        $term_id = $request->input("term_id");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Nom_Filiere: $nom_filiere -- Section: $section";

        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $section_id = MyHelper::getSectionID($section_name);
            $competences = DB::select("SELECT  subject_competences.subject_competence_id, 
                subject_competences.classe_id, subject_competences.sy_id, 
                subject_competences.term_id, subject_competences.subject_id,
                subject_competences.section_id, subject_competences.competence_text
                    FROM subject_competences WHERE sy_id = $sy_id
                        AND section_id = $section_id 
                        AND term_id = $term_id");
            return response()->json($competences, 200);
        } catch (Exception $e) {
            //echo '<br/>ERROR: ' .$e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }

    public function allCompetencesOfSection(Request $request)
    {
        $connection = $request->input("connection");
        $year = $request->input("year");
        $section_name = $request->input("section");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Nom_Filiere: $nom_filiere -- Section: $section";

        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $section_id = MyHelper::getSectionID($section_name);
            $competences = DB::select("SELECT  subject_competences.subject_competence_id, 
                subject_competences.classe_id, subject_competences.sy_id, 
                subject_competences.term_id, subject_competences.subject_id,
                subject_competences.section_id, subject_competences.competence_text
                    FROM subject_competences WHERE sy_id = $sy_id
                        AND section_id = $section_id");
            return response()->json($competences, 200);
        } catch (Exception $e) {
            //echo '<br/>ERROR: ' .$e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }

    public function allCompetences1(Request $request)
    {
        $connection = $request->input("connection");
        $year = $request->input("year");
        $section_name = $request->input("section");


        $classe_id = $request->input("classe_id");
        $subject_id = $request->input("subject_id");
        $term_id = $request->input("term_id");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Nom_Filiere: $nom_filiere -- Section: $section";

        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $section_id = MyHelper::getSectionID($section_name);
            $competences = DB::select("SELECT  subject_competences.subject_competence_id, 
                subject_competences.classe_id, subject_competences.sy_id, 
                subject_competences.term_id, subject_competences.subject_id,
                subject_competences.section_id, subject_competences.competence_text
                    FROM subject_competences WHERE sy_id = $sy_id
                        AND section_id = $section_id
                        AND classe_id = $classe_id
                        AND subject_id = $subject_id
                        AND term_id = $term_id");
            return response()->json($competences, 200);
        } catch (Exception $e) {
            //echo '<br/>ERROR: ' .$e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }

    public function allCompetences2(Request $request)
    {
        $connection = $request->input("connection");
        $year = $request->input("year");
        $section_name = $request->input("section");


        $classe_id = $request->input("classe_id");
        $subject_id = $request->input("subject_id");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Nom_Filiere: $nom_filiere -- Section: $section";

        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $section_id = MyHelper::getSectionID($section_name);
            $competences = DB::select("SELECT  subject_competences.subject_competence_id, 
                subject_competences.classe_id, subject_competences.sy_id, 
                subject_competences.term_id, subject_competences.subject_id,
                subject_competences.section_id, subject_competences.competence_text
                    FROM subject_competences WHERE sy_id = $sy_id
                        AND section_id = $section_id
                        AND classe_id = $classe_id
                        AND subject_id = $subject_id");
            return response()->json($competences, 200);
        } catch (Exception $e) {
            //echo '<br/>ERROR: ' .$e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }
    public function saveCompetence(Request $request)
    {
        $connection = $request->input("connection");
        $year = $request->input("year");
        $sectionName = $request->input("section");


        $classe_id = $request->input("classe_id");
        $subject_id = $request->input("subject_id");
        $term_id = $request->input("term_id");
        $competence_text = $request->input("competence_text");
        config(["database.default" => $connection]);

        //echo "Connection: $connection -- Year: $year -- Nom_Filiere: $nom_filiere -- Section: $section";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $section_id = MyHelper::getSectionID($sectionName);

            $comp = new SubjectCompetence();
            $comp->sy_id = $sy_id;
            $comp->section_id = $section_id;
            $comp->subject_id = $subject_id;
            $comp->term_id = $term_id;
            $comp->classe_id = $classe_id;
            $comp->competence_text = $competence_text;
            $query = $comp->save();
            echo $query; //1->Operation is successfull
        } catch (Exception $e) {
            //echo '<br/>Message: ' .$e->getMessage();
            echo "-2"; //La competence existe déja. Tres improblable dans notre 
            //cas car la matiere peut avoir plusieurs competences au cours d'une année et dans un trimestre donné
        }
    }

    public function calquerSubjects(Request $request)
    {
        $connection = $request->input("connection");
        $year = $request->input("year");
        $section = $request->input("section");
        $classe_id = $request->input("classe_id"); //Classe from
        $classe_name = $request->input("classe_name"); //Classe to
        config(["database.default" => $connection]);
        $ok = 1;
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $section_id = MyHelper::getSectionID($section);

            $classeTo = Classe::where("classe_name", "=", "$classe_name")->first();
            $subClassFrom = SubjectClasse::where("classe_id", "=", $classe_id)
                ->where("sy_id", "=", $sy_id)
                ->where("section_id", "=", $section_id)
                ->get();
            if (!is_null($classeTo) && !is_null($subClassFrom)) {
                $classeTo_id = $classeTo->classe_id;

                //DELETE ALL THE SUBJECT_CLASSES OF classeTo
                $scList = SubjectClasse::where("classe_id", "=", $classeTo_id)
                    ->where("sy_id", "=", $sy_id)
                    ->where("section_id", "=", $section_id)
                    ->get();
                if (!is_null($scList)) { //THE CLASS MAY NOT HAVE ANY SC WITH THE sy_id AND section_id
                    foreach ($scList as $sc) {
                        $res = MySubjectHelper::deleteASubjectOfAClasse($sy_id, $section_id, $sc->subject_id, $classeTo_id);
                    }
                } //here we assume they have been deleted


                //CREATE AND SAVE SUBJECTS CLASSES OF classeTo FROM CLASSE_FROM
                foreach ($subClassFrom as $sc) {
                    $newSc = new SubjectClasse();
                    $newSc->sy_id = $sy_id;
                    $newSc->section_id = $section_id;
                    $newSc->subject_id = $sc->subject_id;
                    $newSc->classe_id = $classeTo_id;
                    $newSc->coef = $sc->coef;
                    $newSc->groupe_id = $sc->groupe_id;
                    try {
                        $newSc->save();
                    } catch (Exception $ex1) {
                        echo '<br/>ERROR: ' . $ex1->getMessage();
                        $ok = 0; //Failed to save at least one
                    }
                }
            } else {
                echo "\$classeTo or \$subClassFrom is null<br/>";
                $ok = 0;
            }
        } catch (Exception $e) {
            //echo '<br/>ERROR: ' . $e->getMessage(); 
            $ok = 0;
        }
        echo $ok;
    } //END calquerSubjects


    /* CETTE VERSION CALQUE LES COMPETENCES EN EVITANT LES DOUBLONS
    public function claquerCompetences(Request $request)
    {
        $connection = $request->input("connection");
        $data = $request->input("data");
        $classe_id_from = $request->input("classe_id_from");
        $year = $request->input("year");
        $section = $request->input("section");

        $classe_ids = json_decode($data, true);
        //$n = count($subList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]";
        $allAffected = 1; //interpreted as true. 0-->false
        config(["database.default" => $connection]);

        $sy_id = MyHelper::getSchoolYearID($year);
        $section_id = MyHelper::getSectionID($section);

        $competences = DB::select("SELECT  subject_competences.subject_competence_id, 
                subject_competences.classe_id, subject_competences.sy_id, 
                subject_competences.term_id, subject_competences.subject_id,
                subject_competences.section_id, subject_competences.competence_text
                    FROM subject_competences WHERE sy_id = $sy_id
                        AND section_id = $section_id
                        AND classe_id = $classe_id_from");
        if (count($competences) > 0) {
            foreach ($classe_ids as $cl) {
                $classe_id_to = $cl["classe_id"];
                //echo "$classe_id_to<br/>";
                foreach ($competences as $cmp) {
                    $competence_text = $cmp->competence_text;
                    $subject_id = $cmp->subject_id;
                    $term_id = $cmp->term_id;
                    if (MySubjectHelper::checkSujectClass(
                        $sy_id,
                        $classe_id_to,
                        $subject_id
                    ) == 1) {
                        //echo "$competence_text will be copied to $classe_id_to<br/>";
                        try {
                            //remember to chech if the competence text exixts already in that classe before saving
                            $ref = new SubjectCompetence();
                            $ref->sy_id = $sy_id;
                            $ref->subject_id = $subject_id;
                            $ref->term_id = $term_id;
                            $ref->section_id = $section_id;
                            $ref->competence_text = $competence_text;
                            $ref->classe_id = $classe_id_to;
                            $res = MySubjectHelper::checkCompetenceText(
                                $sy_id,
                                $classe_id_to,
                                $subject_id,
                                $term_id,
                                $competence_text
                            );
                            if ($res == 1) {
                                //The competence text already exists for that subject in that classe IN that term
                                //echo "$competence_text is already defined for $subject_id in $classe_id_to. To avoid duplicate it will not be copied<br/>";
                            } else {
                                //$ref->save();
                                DB::insert(
                                    'insert into subject_competences (classe_id, sy_id, term_id, subject_id, section_id, competence_text) values (?, ?, ?, ?, ?, ?)',
                                    [$classe_id_to, $sy_id, $term_id, $subject_id, $section_id, $competence_text]
                                );
                            }
                        } catch (Exception $e) {
                            echo '<br/>ERROR: ' . $e->getMessage();
                            $allAffected = 0;
                        }
                    }
                }
            }
        }

        echo $allAffected;
    }
    */


    public function calquerCompetences(Request $request)
    {
        $connection = $request->input("connection");
        $data = $request->input("data");
        $classe_id_from = $request->input("classe_id_from");
        $year = $request->input("year");
        $section = $request->input("section");

        $classe_ids = json_decode($data, true);
        //$n = count($subList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]";
        $allAffected = 1; //interpreted as true. 0-->false
        config(["database.default" => $connection]);

        $sy_id = MyHelper::getSchoolYearID($year);
        $section_id = MyHelper::getSectionID($section);

        $competences = DB::select("SELECT *
                    FROM subject_competences WHERE sy_id = $sy_id
                        AND section_id = $section_id
                        AND classe_id = $classe_id_from");

        if (count($competences) > 0) {
            foreach ($classe_ids as $cl) {
                $classe_id_to = $cl["classe_id"];
                //echo "$classe_id_to<br/>";
                $scList = MySubjectHelper::subjectClasseList($sy_id, $classe_id_to);
                if (is_null($scList) || count($scList) == 0) {
                    //The classe to has no subject class yet
                    //Continue with next class
                } else {
                    foreach ($competences as $cmp) {
                        foreach ($scList as $sc) {
                            if ($sc->subject_id == $cmp->subject_id) {
                                //$subjectExists = true;
                                $competence_text = $cmp->competence_text;
                                $subject_id = $cmp->subject_id;
                                $term_id = $cmp->term_id;
                                try {
                                    //remember to chech if the competence text exixts already in that classe before saving
                                    $ref = new SubjectCompetence();
                                    $ref->sy_id = $sy_id;
                                    $ref->subject_id = $subject_id;
                                    $ref->term_id = $term_id;
                                    $ref->section_id = $section_id;
                                    $ref->competence_text = $competence_text;
                                    $ref->classe_id = $classe_id_to;
                                    $res = MySubjectHelper::checkCompetenceText(
                                        $sy_id,
                                        $classe_id_to,
                                        $subject_id,
                                        $term_id,
                                        $competence_text
                                    );
                                    //$ref->save();
                                    DB::insert(
                                        'insert into subject_competences (classe_id, sy_id, term_id, subject_id, section_id, competence_text) values (?, ?, ?, ?, ?, ?)',
                                        [$classe_id_to, $sy_id, $term_id, $subject_id, $section_id, $competence_text]
                                    );
                                } catch (Exception $e) {
                                    //echo '<br/>ERROR: ' . $e->getMessage();
                                    $allAffected = 0;
                                }
                                break;
                            } else {
                                //$subjectExists = false;
                            }
                        }
                    }
                }
                //The classe to has no subject class yet

            }
        }

        echo $allAffected;
    }

    public function calquerCompetencesOfTerm(Request $request)
    {
        $connection = $request->input("connection");
        $data = $request->input("data");
        $classe_id_from = $request->input("classe_id_from");
        $year = $request->input("year");
        $section = $request->input("section");
        $term_id = $request->input("term");

        $classe_ids = json_decode($data, true);
        //$n = count($subList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]";
        $allAffected = 1; //interpreted as true. 0-->false
        config(["database.default" => $connection]);

        $sy_id = MyHelper::getSchoolYearID($year);
        $section_id = MyHelper::getSectionID($section);

        $competences = DB::select("SELECT *
                    FROM subject_competences WHERE sy_id = $sy_id
                        AND section_id = $section_id
                        AND classe_id = $classe_id_from
                        AND term_id = $term_id");

        if (count($competences) > 0) {
            foreach ($classe_ids as $cl) {
                $classe_id_to = $cl["classe_id"];
                //echo "$classe_id_to<br/>";
                $scList = MySubjectHelper::subjectClasseList($sy_id, $classe_id_to);
                if (is_null($scList) || count($scList) == 0) {
                    //The classe to has no subject class yet
                    //Continue with next class
                } else {
                    foreach ($competences as $cmp) {
                        foreach ($scList as $sc) {
                            if ($sc->subject_id == $cmp->subject_id) {
                                //$subjectExists = true;
                                $competence_text = $cmp->competence_text;
                                $subject_id = $cmp->subject_id;
                                //$term_id = $cmp->term_id;//Pour cette methode on utilise plutot la competence passee en parametre
                                try {
                                    //remember to chech if the competence text exixts already in that classe before saving
                                    $ref = new SubjectCompetence();
                                    $ref->sy_id = $sy_id;
                                    $ref->subject_id = $subject_id;
                                    $ref->term_id = $term_id;
                                    $ref->section_id = $section_id;
                                    $ref->competence_text = $competence_text;
                                    $ref->classe_id = $classe_id_to;
                                    $res = MySubjectHelper::checkCompetenceText(
                                        $sy_id,
                                        $classe_id_to,
                                        $subject_id,
                                        $term_id,
                                        $competence_text
                                    );
                                    //$ref->save();
                                    DB::insert(
                                        'insert into subject_competences (classe_id, sy_id, term_id, subject_id, section_id, competence_text) values (?, ?, ?, ?, ?, ?)',
                                        [$classe_id_to, $sy_id, $term_id, $subject_id, $section_id, $competence_text]
                                    );
                                } catch (Exception $e) {
                                    //echo '<br/>ERROR: ' . $e->getMessage();
                                    $allAffected = 0;
                                }
                                break;
                            } else {
                                //$subjectExists = false;
                            }
                        }
                    }
                }
                //The classe to has no subject class yet

            }
        }

        echo $allAffected;
    }


    public function saveManySC(Request $request)
    {   //update MANY SUBJECT_CLASSES
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $year = $request->input("year");
        $section = $request->input("section");

        $scList = json_decode($data, true);
        $n = count($scList);
        //echo "DATA Lenght = $n [size transmitted is $data_size] <br/>";
        $k = 1; //1--> Success. 0--> Some classes not saved [Exists allready in other sections or contain wrong characters]

        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        $section_id = MyHelper::getSectionID($section);
        $msg = "";
        foreach ($scList as $sc) {
            $found = false;
            $subject_id = $sc["subject_id"];
            $coef = $sc["coef"];
            $classe_id = $sc["classe_id"];
            $groupe_id = $sc["groupe_id"];
            //echo "subject_id: $subject_id | classe_id: $classe_id | coef: $coef | groupe_id: $groupe_id |";
            $scTmp = SubjectClasse::where("subject_id", "=", $subject_id)
                ->where("sy_id", "=", $sy_id)
                ->where("section_id", "=", $section_id)
                ->where("classe_id", "=", "$classe_id")
                ->first();

            //$scTmp = DB::select("");

            try {
                if (is_null($scTmp)) { //subjectClasse not found
                    if ($subject_id == "null" || $classe_id == "null") {
                        //Sc can't be saved in this case  
                        $k = 0;
                    } else {
                        $scToSave = new SubjectClasse();
                        $scToSave->subject_id = $subject_id;
                        $scToSave->sy_id = $sy_id;
                        $scToSave->section_id = $section_id;
                        $scToSave->coef = $coef;
                        $scToSave->classe_id = $classe_id;
                        $scToSave->groupe_id = $groupe_id;
                        $scToSave->save();
                    }
                    $found = false;
                } else {
                    //update instead
                    $found = true;
                    $scTmp->coef = $coef;
                    $scTmp->update();
                    $scTmp->groupe_id =  $groupe_id;
                    $scTmp->update();
                }
                //echo "  Found[$found] group_id is '".($scTmp->groupe_id)."' | SC_id[".($scTmp->subject_classe_id)."]<br/>";
            } catch (Exception $exx) {
                $msg = $msg . "\n" . "failed to save SC of" . $subject_id . "" . $exx->getMessage() . "\n";
                $k = 0; //Exception surfaces
                echo "  Error[$msg]<br/>";
            }
        } //END FOR

        if ($k == 1) {
            echo $k;
        } else {
            echo "$k|$msg";
        } //K=1--> All subjects successfully modified; K=0--> Failed to save at least one
    }

    public function saveManySCWithPost(Request $request)
    {   //update MANY SUBJECT_CLASSES
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $year = $request->input("year");
        $section = $request->input("section");

        $scList = json_decode($data, true);
        $n = count($scList);
        //echo "DATA Lenght = $n [size transmitted is $data_size] <br/>";
        $k = 1; //1--> Success. 0--> Some classes not saved [Exists allready in other sections or contain wrong characters]

        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        $section_id = MyHelper::getSectionID($section);
        $msg = "";
        foreach ($scList as $sc) {
            $found = false;
            $subject_id = $sc["subject_id"];
            $coef = $sc["coef"];
            $classe_id = $sc["classe_id"];
            $groupe_id = $sc["groupe_id"];
            //echo "subject_id: $subject_id | classe_id: $classe_id | coef: $coef | groupe_id: $groupe_id |";
            $scTmp = SubjectClasse::where("subject_id", "=", $subject_id)
                ->where("sy_id", "=", $sy_id)
                ->where("section_id", "=", $section_id)
                ->where("classe_id", "=", "$classe_id")
                ->first();

            //$scTmp = DB::select("");

            try {
                if (is_null($scTmp)) { //subjectClasse not found
                    if ($subject_id == "null" || $classe_id == "null") {
                        //Sc can't be saved in this case  
                        $k = 0;
                    } else {
                        $scToSave = new SubjectClasse();
                        $scToSave->subject_id = $subject_id;
                        $scToSave->sy_id = $sy_id;
                        $scToSave->section_id = $section_id;
                        $scToSave->coef = $coef;
                        $scToSave->classe_id = $classe_id;
                        $scToSave->groupe_id = $groupe_id;
                        $scToSave->save();
                    }
                    $found = false;
                } else {
                    //update instead
                    $found = true;
                    $scTmp->coef = $coef;
                    $scTmp->update();
                    $scTmp->groupe_id =  $groupe_id;
                    $scTmp->update();
                }
                //echo "  Found[$found] group_id is '".($scTmp->groupe_id)."' | SC_id[".($scTmp->subject_classe_id)."]<br/>";
            } catch (Exception $exx) {
                $msg = $msg . "\n" . "failed to save SC of" . $subject_id . "" . $exx->getMessage() . "\n";
                $k = 0; //Exception surfaces
                echo "  Error[$msg]<br/>";
            }
        } //END FOR

        if ($k == 1) {
            echo $k;
        } else {
            echo "$k|$msg";
        } //K=1--> All subjects successfully modified; K=0--> Failed to save at least one
    }


    public function saveManyAttricutionsWithPost(Request $request)
    {   //SAVES MANY ATTRIBUTIONS
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        //$year = $request->input("year");
        //$section = $request->input("section");

        $scList = json_decode($data, true);
        //$n = count($fList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]";
        $k = 1; //1--> Success. 0--> Some classes not saved [Exists allready in other sections or contain wrong characters]

        config(["database.default" => $connection]);
        //$sy_id = MyHelper::getSchoolYearID($year);
        //$section_id = MyHelper::getSectionID($section);
        $msg = "";
        foreach ($scList as $sc) {
            $subject_classe_id = $sc["subject_classe_id"];
            $staff_id = $sc["staff_id"];
            //echo "subject_id: $subject_id | classe_id: $classe_id | coef: $coef | groupe_id: $groupe_id |";

            $ref = new SubjectClasseStaff();
            $ref->subject_classe_id = $subject_classe_id;
            $ref->staff_id = $staff_id;
            try {
                $ref->save();
            } catch (Exception $ex) {
                $msg = $msg . "" . $ex->getMessage() . "<br/>";
                $k = 0;
            }
        } //END FOR

        if ($k == 1) {
            echo $k;
        } else {
            echo "$k|$msg";
        } //K=1--> All attributions successfully saved; K=0--> Failed to save at least one
    }


    public function deleteAllSubjectsOfSectionAndYear(Request $request)
    {
        $connection = $request->input("connection");
        $year = $request->input("year");
        $section = $request->input("section");
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        $section_id = MyHelper::getSectionID($section);
        $res = MySubjectHelper::deleteSubjects($sy_id, $section_id);
        echo $res; //1--> success; negative int --> Failed
    }
    public function deleteManySubjects(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $year  = $request->input("year");
        $section  = $request->input("section");
        $data = $request->input("data");
        $data_size = $request->input("data_size");

        $subList = json_decode($data, true);
        $n = count($subList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]";
        //$allAffected = 1; //interpreted as true. 0-->false
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        //echo "sy_id: $section</br>";
        $section_id = MyHelper::getSectionID($section);
        foreach ($subList as $sub) {
            $subject_id = $sub["subject_id"];
            $subRef = Subject::find($subject_id);
            $allAffected = 1;
            try {
                $res = MySubjectHelper::deleteASubject($sy_id, $section_id, $subject_id);
            } catch (Exception $ex) {
                $allAffected = 0;
                //echo "ERROR " . $ex->getMessage();
            }
        } //END FOR
        //return response($allAffected, 200);
        echo (string) $allAffected; //1--> All groupes successfully deleted; 0--> Failed to save at least one
    }
    public function deleteManySubjectsWithPOST(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $year  = $request->input("year");
        $section  = $request->input("section");
        $data = $request->input("data");
        $data_size = $request->input("data_size");

        $subList = json_decode($data, true);
        $n = count($subList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]";
        //$allAffected = 1; //interpreted as true. 0-->false
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        //echo "sy_id: $section</br>";
        $section_id = MyHelper::getSectionID($section);
        foreach ($subList as $sub) {
            $subject_id = $sub["subject_id"];
            $subRef = Subject::find($subject_id);
            $allAffected = 1;
            try {
                $res = MySubjectHelper::deleteASubject($sy_id, $section_id, $subject_id);
            } catch (Exception $ex) {
                $allAffected = 0;
                //echo "ERROR " . $ex->getMessage();
            }
        } //END FOR
        //return response($allAffected, 200);
        echo (string) $allAffected; //1--> All groupes successfully deleted; 0--> Failed to save at least one
    }

    public function updateManySubjects(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");

        $subList = json_decode($data, true);
        //$n = count($subList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]";
        $allAffected = 1; //interpreted as true. 0-->false
        config(["database.default" => $connection]);
        foreach ($subList as $sub) {
            // code
            $subject_id = $sub["subject_id"];
            $subject_title = $sub["subject_title"];
            $affected = DB::table('subject')
                ->where('subject_id', $subject_id)
                ->update(['subject_title' => $subject_title]);
            if ($affected != 1) {
                $allAffected = 0;
            }
        }
        echo "$allAffected"; //1--> All groupes successfully modified; 0--> Failed to save at least one
    }

    public function updateManySubjectsWithPOST(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");

        $subList = json_decode($data, true);
        //$n = count($subList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]";
        $allAffected = 1; //interpreted as true. 0-->false
        config(["database.default" => $connection]);
        foreach ($subList as $sub) {
            // code
            $subject_id = $sub["subject_id"];
            $subject_title = $sub["subject_title"];
            $affected = DB::table('subject')
                ->where('subject_id', $subject_id)
                ->update(['subject_title' => $subject_title]);
            if ($affected != 1) {
                $allAffected = 0;
            }
        }
        echo "$allAffected"; //1--> All groupes successfully modified; 0--> Failed to save at least one
    }

    public function saveSubject(Request $request)
    {
        $connection = $request->input("connection");
        $year = $request->input("year");
        $subject_title = $request->input("subject_title");
        $section_name = $request->input("section");
        config(["database.default" => $connection]);
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $section_id = MyHelper::getSectionID($section_name);

            $sub = new Subject();
            $subYear = new SubjectYear();

            $sub->subject_title = $subject_title;
            //$sub->subject_code = null; //SET SUBJECT CODE TO NULL FOR NOW
            $subTmp = Subject::where("subject_title", "=", $subject_title)->first();
            try {
                $id = 1;
                if (is_null($subTmp)) {
                    $sub->save();
                    $id = $sub->subject_id;
                } else {
                    $id = $subTmp->subject_id;
                }
                $subYear->subject_id = $id;
                $subYear->sy_id = $sy_id;
                $subYear->section_id = $section_id;
                $subYear->save();
                echo "1"; //Operation is successfull*/
            } catch (Exception $ex) {
                //A subject with may exist already [in another section school_year]
                //If exception then sub or subYear failed to save. We delete them to avoid inconsitency
                $sub->delete();
                try {
                    $subYear->delete();
                } catch (Exception $exx) {
                }
                echo '<br/>Message: ' . $ex->getMessage() . '<br>';
                echo "-2"; //Operation failed OR groupe exists already
            }
        } catch (Exception $e) {
            echo "-1"; //Le groupe existe déja
            //echo '<br/>Message: ' . $e->getMessage();
        }
    }

    public function allSubjectOfSectionAndYear(Request $request)
    {
        $connection = $request->input("connection");
        $year = $request->input("year");
        $section_name = $request->input("section");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $section_id = MyHelper::getSectionID($section_name);

            $subjects = MySubjectHelper::getSubjectsOfYearOfSection($sy_id, $section_id);
            return response()->json($subjects, 200);
        } catch (Exception $e) {
            //echo '<br/>ERROR: ' .$e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }

    public function subjectsNotOfClasse(Request $request)
    {
        $connection = $request->input("connection");
        $year = $request->input("year");
        $section_name = $request->input("section");
        $classe_id = $request->input("classe_id");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $section_id = MyHelper::getSectionID($section_name);

            $subjects = DB::select("SELECT subject.subject_id, subject.subject_title FROM subject 
                        WHERE subject_id IN(SELECT subject_year.subject_id FROM subject_year 
                            WHERE subject_year.sy_id = $sy_id
                                AND subject_year.section_id = $section_id)
                                AND subject_id NOT IN(SELECT subject_classe.subject_id FROM subject_classe 
                                    WHERE subject_classe.classe_id =$classe_id AND subject_classe.sy_id = $sy_id)");
            return response()->json($subjects, 200);
        } catch (Exception $e) {
            //echo '<br/>ERROR: ' .$e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }

    public function subjectOfClasse(Request $request)
    {
        $connection = $request->input("connection");
        $year = $request->input("year");
        $section_name = $request->input("section");
        $classe_id = $request->input("classe_id");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $section_id = MyHelper::getSectionID($section_name);

            $subjects = DB::select("SELECT  Distinct subject.subject_id, subject.subject_title,subject_classe.coef, 
                    subject_classe.groupe_id, groupe.groupe_name, subject_classe.classe_id 
	                FROM subject, groupe, subject_classe, subject_year	  
                        WHERE 
		                    subject.subject_id = subject_year.subject_id 
		                    AND
		                    subject.subject_id = subject_classe.subject_id
		                    AND
		                    subject_classe.groupe_id = groupe.groupe_id		
	                        AND
	                        subject_year.sy_id = $sy_id
	                        AND 
	                        subject_year.section_id = $section_id
	                        AND 
                            subject_classe.classe_id = $classe_id");


            return response()->json($subjects, 200);
        } catch (Exception $e) {
            //echo '<br/>ERROR: ' .$e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }

    public function allSubjectOfClasse(Request $request)
    {
        $connection = $request->input("connection");
        $year = $request->input("year");
        $section_name = $request->input("section");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $section_id = MyHelper::getSectionID($section_name);

            $subjects = DB::select("SELECT DISTINCT subject_classe.subject_classe_id, subject.subject_id, subject.subject_title,subject_classe.coef, 
                    subject_classe.groupe_id, groupe.groupe_name, subject_classe.classe_id 
	                FROM subject, groupe, subject_classe, subject_year	  
                        WHERE 
		                    subject.subject_id = subject_year.subject_id 
		                    AND
		                    subject.subject_id = subject_classe.subject_id
		                    AND
		                    subject_classe.groupe_id = groupe.groupe_id		
	                        AND
	                        subject_classe.sy_id = $sy_id
	                        AND 
	                        subject_year.section_id = $section_id
	                        order by groupe.created_at, subject.subject_title, subject_classe.subject_classe_id, subject_classe.groupe_id");


            return response()->json($subjects, 200);
        } catch (Exception $e) {
            //echo '<br/>ERROR: ' .$e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }

    public function deleteASubjectOfAClasseYearAndSection(Request $request)
    {
        $connection = $request->input("connection");
        $year = $request->input("year");
        $section_name = $request->input("section");
        $classe_id = $request->input("classe_id");
        $subject_id = $request->input("subject_id");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $section_id = MyHelper::getSectionID($section_name);
            //echo "sy_id: $sy_id | section_id: $section_id | subject_id: $subject_id | classe_id: $classe_id";
            $res = MySubjectHelper::deleteASubjectOfAClasse($sy_id, $section_id, $subject_id, $classe_id);
            echo $res;
        } catch (Exception $e) {
            //echo '<br/>ERROR: ' .$e->getMessage();
            //return response()->json([], 500); //ERROR OCCURS
            echo -2; //Error occurs
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
    public function show(Subject $subject)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Subject $subject)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Subject $subject)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subject $subject)
    {
        //
    }
}
