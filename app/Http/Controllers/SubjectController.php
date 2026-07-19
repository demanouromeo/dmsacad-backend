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

    public function saveManyAttributions(Request $request)
    {   //SAVES MANY ATTRIBUTIONS
        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");


        $scList = json_decode($data, true);
        config(["database.default" => $connection]);

        $msg = "";
        $k = 1; //1--> Success. 0--> Some classes not saved [Exists allready in other sections or contain wrong characters]
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

        return response()->json([
            'status' => $k == 1,
            'message' => $k == 1 ? 'All attributions successfully saved.' : "Failed to save at least an attribution. " . $msg,
        ], $k == 1 ? 200 : 400);
    }

    public function deleteCompetencesWithNoMarks(Request $request)
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

        $compList = json_decode($data, true);
        $n = count($compList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]";

        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);

        $msg = "";
        $allAffected = 1; //interpreted as true. 0-->false 
        foreach ($compList as $sub) {
            $comp_id = $sub["subject_competence_id"];
            try {
                $x = DB::select(
                    "DELETE FROM subject_competences 
                WHERE subject_competence_id = ?",
                    [$comp_id]
                );
            } catch (\Throwable $ex) {
                $allAffected = 0;
                $msg = $msg . "" . $ex->getMessage() . "<br/>";
            }
        } //END FOR
        return response()->json([
            'status' => $allAffected == 1,
            'message' => $allAffected == 1 ? 'All competences successfully deleted.' : "Failed to delete at least a competence. " . $msg,
        ], $allAffected == 1 ? 200 : 400);
    }

    public function saveManySubjects(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'data' => 'required|string',
                'data_size' => 'nullable|integer|min:0',
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
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $year = $request->input("year");
        $section = $request->input("section");

        $subList = json_decode($data, true);
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        $section_id = MyHelper::getSectionID($section);

        $msg = "";
        $k = 1; //1--> Success. 0--> Some classes not saved [Exists allready in other sections or contain wrong characters] 
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
                } catch (\Throwable $ex) {
                    $k = 0; //To mean AT LEAST ONE SUBJECT FAILED TO SAVE
                    try {
                        $sub->delete();
                    } catch (\Throwable $exx) {
                    }
                    try {
                        $subYear->delete();
                    } catch (\Throwable $exx) {
                    }
                    //echo '<br/>Message: ' . $ex->getMessage() . '<br>';
                    //Operation failed FOR CURRENT SUBJECT
                    $msg = $msg . 'Subject with title [' . $subject_title . '] and id ['
                        . $subTmp->subject_id . '] already exists. in section ['
                        . $section . '].  This year [' . $year . ']. \n' . $ex->getMessage();
                }
            } catch (\Throwable $exx) {
                $msg = $msg . "\n" . $exx->getMessage() . "\n";
                $k = -1; //Exception surfaces
            }
        } //END FOR

        if ($k == 1) {
            return response()->json([
                'status' => true,
                'message' => 'All subjects successfully modified.',
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => "Failed to save at least a subject " . $msg,
            ], 400);
        } //K=1--> All subjects successfully modified; K=0--> Failed to save at least one
    }

    public function deleteCompetencesOfAClasse(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
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
        $classe_id = $request->input("classe_id");
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);

        try {
            $res = DB::select("DELETE FROM subject_competences 
                WHERE classe_id = $classe_id and sy_id = $sy_id");
            return response()->json([
                'status' => true,
                'message' => 'All competences of the classe successfully deleted.',
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => "Failed to delete competences of the classe " . $e->getMessage(),
            ], 500);
        }
    }


    public function deleteACompetence(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'subject_competence_id' => 'required|integer|min:1',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

        $connection = $request->input("connection");
        $subject_competence_id = $request->input("subject_competence_id");
        config(["database.default" => $connection]);

        $compRef = SubjectCompetence::find($subject_competence_id);
        try {
            DB::select("DELETE FROM stud_comp_mark 
                WHERE stud_comp_mark.subject_competence_id = $subject_competence_id");
            if (!is_null($compRef)) {
                $compRef->delete();
            }
            return response()->json([
                'status' => true,
                'message' => 'Competence successfully deleted.',
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => "Failed to delete competence " . $e->getMessage(),
            ], 400);
        }
    }

    public function deleteManyCompetences(Request $request)
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

        $compList = json_decode($data, true);
        config(["database.default" => $connection]);

        $msg = "";
        $allAffected = 1; //interpreted as true. 0-->false
        foreach ($compList as $comp) {
            $subject_competence_id = $comp["subject_competence_id"];
            $compRef = SubjectCompetence::find($subject_competence_id);
            try {
                DB::select("DELETE FROM stud_comp_mark 
                WHERE stud_comp_mark.subject_competence_id = $subject_competence_id");
                if (!is_null($compRef)) {
                    $compRef->delete();
                }
            } catch (\Throwable $e) {
                $allAffected = 0;
                $msg .= "\n" . $e->getMessage();
            }
        }

        if ($allAffected == 1) {
            return response()->json([
                'status' => true,
                'message' => 'All competences successfully deleted.',
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => "Failed to delete at least a competence " . $msg,
            ], 400);
        } //allAffected=1--> All competences successfully deleted; K=0--> Failed to delete at least one     
    }


    public function updateACompetence(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'subject_competence_id' => 'required|integer|min:1',
                'competence_text' => 'required|string|min:2',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

        $connection = $request->input("connection");
        $subject_competence_id = $request->input("subject_competence_id");
        $competence_text = $request->input("competence_text");
        config(["database.default" => $connection]);

        try {
            $compRef = SubjectCompetence::find($subject_competence_id);
            if (is_null($compRef)) {
                return response()->json([
                    'status' => false,
                    'message' => "Competence with ID [$subject_competence_id] not found.",
                ], 404);
            }
            $compRef->competence_text = $competence_text;
            $compRef->update();
            return response()->json([
                'status' => true,
                'message' => 'Competence successfully modified.',
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => "Failed to save competence " . $e->getMessage(),
            ], 400);
        }
    }

    public function updateManyCompetences(Request $request)
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

        $compList = json_decode($data, true);
        config(["database.default" => $connection]);

        $msg = "";
        $allAffected = 1; //interpreted as true. 0-->false
        foreach ($compList as $comp) {
            $subject_competence_id = $comp["subject_competence_id"];
            $compRef = SubjectCompetence::find($subject_competence_id);
            if (is_null($compRef)) {
                $allAffected = 0;
                $msg .= "\nCompetence with ID $subject_competence_id not found.";
                continue; // Skip to the next competence
            }
            $compRef->competence_text = $comp["competence_text"];
            try {
                $compRef->update();
            } catch (\Throwable $e) {
                $allAffected = 0;
                $msg .= "\n" . $e->getMessage();
            }
        }

        if ($allAffected == 1) {
            return response()->json([
                'status' => true,
                'message' => 'All competences successfully modified.',
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => "Failed to save at least a competence " . $msg,
            ], 400);
        } //allAffected=1--> All competences successfully modified; K=0--> Failed to save at least one        
    }

    public function allCompetences(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'section' => 'required|string',
                'term_id' => 'required|integer|min:1|max:3',
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
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function allCompetencesOfSection(Request $request)
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
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function allCompetences1(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'section' => 'required|string',
                'classe_id' => 'required|integer',
                'subject_id' => 'required|integer',
                'term_id' => 'required|integer|min:1|max:3',
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
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error occurred: ' . $e->getMessage(),
            ], 500); //ERROR OCCURS
        }
    }

    public function allCompetences2(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'section' => 'required|string',
                'classe_id' => 'required|integer',
                'subject_id' => 'required|integer',
                'term_id' => 'required|integer|min:1|max:3',
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
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error occurred: ' . $e->getMessage(),
            ], 500); //ERROR OCCURS
        }
    }
    public function saveCompetence(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'section' => 'required|string',
                'classe_id' => 'required|integer|min:1',
                'subject_id' => 'required|integer|min:1',
                'term_id' => 'required|integer|min:1|max:3',
                'competence_text' => 'required|string',
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
        $classe_id = $request->input("classe_id");
        $subject_id = $request->input("subject_id");
        $term_id = $request->input("term_id");
        $competence_text = $request->input("competence_text");
        config(["database.default" => $connection]);

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
            return response()->json([
                'status' => true,
                'message' => 'Competence successfully saved.',
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 400);
        }
    }

    public function calquerSubjects(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'section' => 'required|string',
                'classe_id' => 'required|integer|min:1',  //Class From
                'classe_name' => 'required|string',  //Class To
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
        $classe_id = $request->input("classe_id"); //Class From  
        $classe_name = $request->input("classe_name"); //Class To
        config(["database.default" => $connection]);

        $msg = "";
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
                    } catch (\Throwable $ex1) {
                        $msg .= '<br/>ERROR: ' . $ex1->getMessage();
                        $ok = 0; //Failed to save at least one
                    }
                }
            } else {
                $msg .= "\nclasseTo or subClassFrom is null<br/>";
                $ok = 0;
            }
        } catch (\Throwable $e) {
            $msg .= '<br/>ERROR: ' . $e->getMessage();
            $ok = 0;
        }

        if ($ok == 1) {
            return response()->json([
                'status' => true,
                'message' => 'All subjects successfully copied.',
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => "Failed to copy at least a subject " . $msg,
            ], 500);
        } //K=1--> All subjects successfully modified; K=0--> Failed to save at least one   
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
                        } catch (\Throwable $e) {
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
                                } catch (\Throwable $e) {
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
        try {
            $request->validate([
                'connection' => 'required|string',
                'data' => 'required|string',
                'classe_id_from' => 'required|integer|min:1',
                'year' => 'required|string',
                'section' => 'required|string',
                'term' => 'required|integer|min:1|max:3',

            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }
        $connection = $request->input("connection");
        $data = $request->input("data");
        $classe_id_from = $request->input("classe_id_from");
        $year = $request->input("year");
        $section = $request->input("section");
        $term_id = $request->input("term");

        $classe_ids = json_decode($data, true);
        config(["database.default" => $connection]);

        $sy_id = MyHelper::getSchoolYearID($year);
        $section_id = MyHelper::getSectionID($section);

        $competences = DB::select("SELECT *
                    FROM subject_competences WHERE sy_id = $sy_id
                        AND section_id = $section_id
                        AND classe_id = $classe_id_from
                        AND term_id = $term_id");

        $allAffected = 1; //interpreted as true. 0-->false
        if (count($competences) > 0) {
            foreach ($classe_ids as $cl) {
                $classe_id_to = $cl["classe_id"];
                //echo "$classe_id_to<br/>";
                $scList = MySubjectHelper::subjectClasseList($sy_id, $classe_id_to);
                try {
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
                                    } catch (\Throwable $e) {
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
                } catch (\Throwable $th) {
                    $allAffected = 0;
                }
            }
        }

        return response()->json([
            'status' => $allAffected == 1,
            'message' => $allAffected == 1 ? 'All competences successfully copied.' : 'Failed to copy at least one competence.',
        ], $allAffected == 1 ? 200 : 500);
    }


    public function saveManySC(Request $request)
    {   //update MANY SUBJECT_CLASSES
        try {
            $request->validate([
                'connection' => 'required|string',
                'data' => 'required|string',
                'data_size' => 'nullable|integer|min:0',
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
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $year = $request->input("year");
        $section = $request->input("section");

        $scList = json_decode($data, true);
        $n = count($scList);
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        $section_id = MyHelper::getSectionID($section);

        $msg = "";
        $k = 1; //1--> Success. 0--> Some classes not saved [Exists allready in other sections or contain wrong characters]
        foreach ($scList as $sc) {
            $found = false;
            $subject_id = $sc["subject_id"];
            $coef = $sc["coef"];
            $classe_id = $sc["classe_id"];
            $groupe_id = $sc["groupe_id"];

            //LET'S CHECK IF THE GROUPE BELONGS TO THE SECTION
            $res = DB::select("SELECT * FROM groupe_year WHERE groupe_id = ? AND sy_id = ? AND section_id = ?", [$groupe_id, $sy_id, $section_id]);
            if (count($res) == 0) {
                $msg = $msg . ' ' . "Groupe with id $groupe_id does not belong to section with id $section_id for year with id $sy_id\n";
                $k = 0;
                continue; //Skip this iteration and continue with the next one
            }
            //echo "subject_id: $subject_id | classe_id: $classe_id | coef: $coef | groupe_id: $groupe_id |";
            $scTmp = SubjectClasse::where("subject_id", "=", $subject_id)
                ->where("sy_id", "=", $sy_id)
                ->where("section_id", "=", $section_id)
                ->where("classe_id", "=", "$classe_id")
                ->first();

            try {
                if (is_null($scTmp)) { //subjectClasse not found
                    if ($subject_id == "null" || $classe_id == "null") {
                        //Sc can't be saved in this case 
                        $msg = $msg . ' ' . "Impossible to save, since subject_id or classe_id is null";
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
            } catch (\Throwable $exx) {
                $msg = "\n" . "failed to save SC of" . $subject_id . "" . $exx->getMessage() . "\n";
                $k = 0; //Exception surfaces
                //echo "  Error[$msg]<br/>";
            }
        } //END FOR

        if ($k == 1) {
            return response()->json([
                'status' => true,
                'message' => 'All subject_classes successfully saved.',
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Failed to save at least one subject_class. ' . $msg,
            ], 500);
        } //K=1--> All subjects successfully modified; K=0--> Failed to save at least one
    }




    public function deleteAllSubjectsOfSectionAndYear(Request $request)
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
            $res = MySubjectHelper::deleteSubjects($sy_id, $section_id);
            return response()->json([
                'status' => true,
                'message' => 'All subjects of section [' . $section . '] and year [' . $year . '] successfully deleted.',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete subjects of section [' . $section . '] and year [' . $year . ']. ' . $th->getMessage(),
            ], 500);
        }
    }


    public function deleteASubject(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'section' => 'required|string',
                'subject_id' => 'required|integer|min:1'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

        $connection = $request->input("connection");
        $year  = $request->input("year");
        $section  = $request->input("section");
        $subject_id = $request->input("subject_id");

        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        $section_id = MyHelper::getSectionID($section);

        $subRef = Subject::find($subject_id);
        if (!is_null($subRef)) {
            try {
                $res = MySubjectHelper::deleteASubject($sy_id, $section_id, $subject_id);
            } catch (\Throwable $ex) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to delete subject with id [' . $subject_id . ']. ' . $ex->getMessage(),
                ], 500);
            }
        }
        return response()->json([
            'status' => true,
            'message' => 'Subject with id [' . $subject_id . '] successfully deleted.',
        ], 200);
    }


    public function deleteManySubjects(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'section' => 'required|string',
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
        $section  = $request->input("section");
        $data = $request->input("data");
        $data_size = $request->input("data_size");

        $subList = json_decode($data, true);
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        $section_id = MyHelper::getSectionID($section);

        $msg = "";
        $allAffected = 1; //interpreted as true. 0-->false
        foreach ($subList as $sub) {
            $subject_id = $sub["subject_id"];
            $subRef = Subject::find($subject_id);
            try {
                $res = MySubjectHelper::deleteASubject($sy_id, $section_id, $subject_id);
            } catch (\Throwable $ex) {
                $allAffected = 0;
                $msg .= " Failed to delete subject with id [$subject_id]. ";
            }
        } //END FOR
        //return response($allAffected, 200);
        //echo (string) $allAffected; //1--> All groupes successfully deleted; 0--> Failed to save at least one
        if ($allAffected == 1) {
            return response()->json([
                'status' => true,
                'message' => 'All subjects successfully deleted.',
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete at least one subject. ' . $msg,
            ], 500);
        }
    }


    public function updateSubject(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'subject_id' => 'required|integer|min:1',
                'subject_title' => 'required|string|max:255|min:2',
                'section' => 'required|string|max:20|min:5',
                'year' => 'required|string',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

        $connection = $request->input("connection");
        $subject_id = $request->input("subject_id");
        $subject_title = $request->input("subject_title");
        $section = $request->input("section");
        $year = $request->input("year");

        $sy_id = MyHelper::getSchoolYearID($year);
        $section_id = MyHelper::getSectionID($section);
        config(["database.default" => $connection]);

        $ref = Subject::find($subject_id);
        if (is_null($ref)) {
            return response()->json([
                'status' => false,
                'message' => 'Subject with id [' . $subject_id . '] not found.',
            ], 404);
        }

        // LETS CHECK NO SUBJECT WITH THE SAME TITLE and different SUBJECT_ID EXISTS ALREADY in the same section and year
        $res = DB::select(
            "SELECT*FROM subject WHERE subject.subject_id != ? 
                            AND subject.subject_title = ? AND subject.subject_id 
                            IN(SELECT subject_year.subject_id FROM subject_year 
                                WHERE subject_year.sy_id=? AND subject_year.section_id = ?)",
            [$subject_id, $subject_title, $sy_id, $section_id]
        );
        if (count($res) > 0) {
            return response()->json([
                'status' => false,
                'message' => 'Subject with title [' . $subject_title . '] already exists in the same section and year. with id [' . $res[0]->subject_id . ']',
            ], 400); //BAD request
        }

        try {
            $ref->subject_title = $subject_title;
            $ref->update();
            return response()->json([
                'status' => true,
                'message' => 'Subject successfully updated.',
            ], 200);
        } catch (\Throwable $ex) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update subject with id [' . $subject_id . ']: ' . $ex->getMessage(),
            ], 400);
        }
    }


    public function updateManySubjects(Request $request)
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
        $section = $request->input("section");
        $year = $request->input("year");

        $sy_id = MyHelper::getSchoolYearID($year);
        $section_id = MyHelper::getSectionID($section);

        $subList = json_decode($data, true);
        config(["database.default" => $connection]);

        $msg = "";
        $allAffected = 1; //interpreted as true. 0-->false
        foreach ($subList as $sub) {
            // code
            $subject_id = $sub["subject_id"];
            $subject_title = $sub["subject_title"];

            $ref = Subject::find($subject_id);
            if (is_null($ref)) {
                $allAffected = 0;
                $msg = $msg . ' ' . "Subject with id [$subject_id] not found\n";
                continue; //Skip this iteration and continue with the next one
            }

            $res = DB::select(
                "SELECT*FROM subject WHERE subject.subject_id != ? 
                            AND subject.subject_title = ? AND subject.subject_id 
                            IN(SELECT subject_year.subject_id FROM subject_year 
                                WHERE subject_year.sy_id=? AND subject_year.section_id = ?)",
                [$subject_id, $subject_title, $sy_id, $section_id]
            );
            if (count($res) > 0) {
                $allAffected = 0;
                $msg = $msg . ' ' . "Subject with title [$subject_title] already exists in the same section and year. with id [" . $res[0]->subject_id . "]\n";
                continue; //Skip this iteration and continue with the next one
            }

            try {
                $ref->subject_title = $subject_title;
                $ref->update();
                /*
                $affected = DB::table('subject')
                ->where('subject_id', $subject_id)
                ->update(['subject_title' => $subject_title]);
                */
            } catch (\Throwable $ex) {
                $allAffected = 0;
                $msg = $msg . ' ' . "Failed to update subject with id [$subject_id]: " . $ex->getMessage() . "\n";
            }
        }
        //echo "$allAffected"; //1--> All groupes successfully modified; 0--> Failed to save at least one
        if ($allAffected == 1) {
            return response()->json([
                'status' => true,
                'message' => 'All subjects successfully updated.',
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update at least one subject. ' . $msg,
            ], 500);
        }
    }


    public function saveSubject(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'subject_title' => 'required|string',
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
            /*------ WE won't do this because in our system a subject can have the same title but be in different sections and years. So we will allow it 
            if(!is_null($subTmp)){
                return response()->json([
                    'status' => false,
                    'message' => 'Subject with title [' . $subject_title . '] and id [' . $subTmp->subject_id . '] already exists.',
                    'subject_id' => $subTmp->subject_id,
                ], 409);
            }*/
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
                return response()->json([
                    'status' => true,
                    'message' => 'Subject saved successfully.',
                    'subject_id' => $id,
                ], 200);
            } catch (\Throwable $ex) {
                //A subject with may exist already [in another section school_year]
                //If exception then sub or subYear failed to save. We delete them to avoid inconsitency
                //Operation Failed: because we couldn\'t save subject_year. Exceptionnaly(With very low probability) we may have failed to also save the subject
                $sub->delete();
                try {
                    $subYear->delete();
                } catch (\Throwable $exx) {
                }
                return response()->json([
                    'status' => false,
                    'message' => 'Subject with title [' . $subject_title . '] and id ['
                        . $subTmp->subject_id . '] already exists. in section ['
                        . $section_name . '].  This year [' . $year . ']. \n' . $ex->getMessage(),
                ], 400);
            }
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function allSubjectOfSectionAndYear(Request $request)
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
        $section_name = $request->input("section");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Section: $sectionParam \n";
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $section_id = MyHelper::getSectionID($section_name);

            $subjects = MySubjectHelper::getSubjectsOfYearOfSection($sy_id, $section_id);
            return response()->json($subjects, 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function subjectsNotOfClasse(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'section' => 'required|string',
                'classe_id' => 'required|integer',
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
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function subjectOfClasse(Request $request)
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
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function allSubjectOfClasse(Request $request)
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
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function deleteASubjectOfAClasseYearAndSection(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'section' => 'required|string',
                'classe_id' => 'required|integer|min:1',
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
            return response()->json([
                'status' => true,
                'message' => 'Subject with id [' . $subject_id . '] successfully deleted from classe with id [' . $classe_id . '] for year [' . $year . '] and section [' . $section_name . '].',
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error occurred: ' . $e->getMessage(),
            ], 500); //Error occurs
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
