<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\StudParent;
use \Throwable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudParentController extends Controller
{
    // Connection-scoped only - stud_parent has no sy_id/year-link table, a parent is a permanent
    // row like Account/Staff's base row (see StaffController::allStaffs1 for the closest join
    // precedent).
    public function allParents(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
            ]);
        } catch (Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

        $connection = $request->input('connection');
        config(["database.default" => $connection]);

        try {
            $parents = DB::select(
                "SELECT stud_parent.p_id, stud_parent.p_name, stud_parent.p_surname, stud_parent.p_phone1,
                        stud_parent.acc_id, account.login, account.email
                    FROM stud_parent, account
                    WHERE stud_parent.acc_id = account.acc_id
                    ORDER BY stud_parent.p_name"
            );
            return response()->json($parents, 200);
        } catch (Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Mirrors StaffController::saveStaff's exact shape: explicit pre-check for a duplicate login
    // (friendlier than letting the DB throw), then Account (type=6/PARENT) followed by StudParent,
    // rolling the account back if the parent insert fails. A duplicate p_phone1 (bigint UNIQUE) is
    // not pre-checked - it surfaces via the unique-constraint exception's raw "Duplicate entry ..."
    // message, already matched by the frontend's isDuplicateNameError regex.
    public function saveParent(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'p_name' => 'required|string|max:50|min:2',
                'p_surname' => 'nullable|string|max:50',
                'p_phone1' => 'required|string|max:20|min:4',
                'login' => 'required|string|max:25|min:4',
                'pwd' => 'required|string|max:25|min:4',
                'email' => 'required|email|max:100',
            ]);
        } catch (Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

        $connection = $request->input('connection');
        $p_name = $request->input('p_name');
        $p_surname = $request->input('p_surname');
        $p_phone1 = $request->input('p_phone1');
        $login = $request->input('login');
        $pwd = $request->input('pwd');
        $email = $request->input('email');
        config(["database.default" => $connection]);

        $accTmp = Account::all()->where('login', '=', $login)->first();
        if (!is_null($accTmp)) {
            return response()->json([
                'status' => false,
                'message' => 'Operation failed: An account with the login [' . $login . '] already exists.',
            ], 409);
        }

        $acc = new Account();
        $acc->login = $login;
        $acc->pwd = $pwd;
        $acc->email = $email;
        $acc->type = 6; //PARENT account type
        try {
            $acc->save();
        } catch (Throwable $e1) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to save account: ' . $e1->getMessage(),
            ], 500);
        }

        try {
            $parent = new StudParent();
            $parent->p_name = $p_name;
            $parent->p_surname = $p_surname;
            $parent->p_phone1 = $p_phone1;
            $parent->acc_id = $acc->acc_id;
            $parent->save();
            return response()->json([
                'status' => true,
                'message' => 'Parent saved successfully',
            ], 200);
        } catch (Throwable $e2) {
            try {
                $acc->delete();
            } catch (Throwable $exx) { //DO NOTHING
            }
            return response()->json([
                'status' => false,
                'message' => 'Failed to save parent: ' . $e2->getMessage(),
            ], 500);
        }
    }

    // `pwd` is optional - omit/leave empty to keep the account password unchanged, same convention
    // as StaffReader.updateStaff's optional pwd.
    public function updateParent(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'p_id' => 'required|integer|min:1',
                'p_name' => 'required|string|max:50|min:2',
                'p_surname' => 'nullable|string|max:50',
                'p_phone1' => 'required|string|max:20|min:4',
                'login' => 'required|string|max:25|min:4',
                'email' => 'required|email|max:100',
                'pwd' => 'nullable|string|max:25',
            ]);
        } catch (Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

        $connection = $request->input('connection');
        $p_id = $request->input('p_id');
        $p_name = $request->input('p_name');
        $p_surname = $request->input('p_surname');
        $p_phone1 = $request->input('p_phone1');
        $login = $request->input('login');
        $email = $request->input('email');
        $pwd = $request->input('pwd');
        config(["database.default" => $connection]);

        try {
            $parent = StudParent::find($p_id);
            if (is_null($parent)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Parent not found.',
                ], 404);
            }
            $parent->p_name = $p_name;
            $parent->p_surname = $p_surname;
            $parent->p_phone1 = $p_phone1;
            $parent->save();

            $acc = Account::find($parent->acc_id);
            if (!is_null($acc)) {
                $acc->login = $login;
                $acc->email = $email;
                if (!empty($pwd)) {
                    $acc->pwd = $pwd;
                }
                $acc->save();
            }

            return response()->json([
                'status' => true,
                'message' => 'Parent updated successfully',
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update parent: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Per parent: unlink every child (student.p_id = NULL, students themselves are never deleted),
    // then delete the stud_parent row, then the linked account row. Much simpler cascade than
    // MyHelper::deleteAStaff since nothing else references p_id.
    public function deleteManyParents(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'data' => 'required|json',
                'data_size' => 'nullable|integer|min:0',
            ]);
        } catch (Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

        $connection = $request->input('connection');
        $data = $request->input('data');
        config(["database.default" => $connection]);

        $parentList = json_decode($data, true);
        $allAffected = 1; //interpreted as true. 0-->false
        foreach ($parentList as $p) {
            $p_id = $p['p_id'];
            try {
                $parent = StudParent::find($p_id);
                if (!is_null($parent)) {
                    DB::select("UPDATE student SET p_id = NULL WHERE p_id = $p_id");
                    $acc_id = $parent->acc_id;
                    $parent->delete();
                    $acc = Account::find($acc_id);
                    if (!is_null($acc)) {
                        $acc->delete();
                    }
                }
            } catch (Throwable $th) {
                $allAffected = 0;
            }
        } //END FOR

        if ($allAffected == 1) {
            return response()->json([
                'status' => true,
                'message' => 'All parents deleted successfully',
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete at least one parent',
            ], 500);
        }
    }

    // Verbatim mirror of StudentController::uploadStudentPhoto/studentPhoto, swapped to
    // StudParent/p_id - same mediumblob column, same 500KB/jpeg constraint.
    public function uploadParentPhoto(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'p_id' => 'required|integer|min:1',
                'photo' => 'required|image|mimes:jpeg,png,jpg|max:500',
            ]);
        } catch (Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

        $connection = $request->input('connection');
        $p_id = $request->input('p_id');
        config(["database.default" => $connection]);

        $parent = StudParent::find($p_id);
        if (is_null($parent)) {
            return response()->json([
                'status' => false,
                'message' => 'Parent not found.',
            ], 404);
        }

        try {
            $parent->photo = file_get_contents($request->file('photo')->getRealPath());
            $parent->save();
        } catch (Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to save photo: ' . $th->getMessage(),
            ], 500);
        }

        return response()->json([
            'status' => true,
            'message' => 'Photo successfully saved.',
        ], 200);
    }

    public function parentPhoto(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'p_id' => 'required|integer|min:1',
            ]);
        } catch (Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

        $connection = $request->input('connection');
        $p_id = $request->input('p_id');
        config(["database.default" => $connection]);

        $parent = StudParent::find($p_id);
        if (is_null($parent) || is_null($parent->photo)) {
            return response()->json([
                'status' => false,
                'message' => 'Photo not found.',
            ], 404);
        }

        return response($parent->photo, 200)->header('Content-Type', 'image/jpeg');
    }

    // Backs both the ADMIN parent-management screen's "children of selected parent" panel and the
    // PARENT portal's own "my children" list - year-scoped (student_classe/classe_year are), unlike
    // the parent record itself. Mirrors StudentController::allStudentsSummaryOfSection's join shape.
    public function childrenOfParent(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'p_id' => 'required|integer|min:1',
            ]);
        } catch (Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

        $connection = $request->input('connection');
        $year = $request->input('year');
        $p_id = $request->input('p_id');
        config(["database.default" => $connection]);

        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $children = DB::select(
                "SELECT student.stud_id, student.matricule, student.name, student.surname, student.sexe,
                        student_classe.classe_id, classe.classe_name, classe.level, section.section_name
                    FROM student, student_classe, classe, classe_year, section
                    WHERE student.stud_id = student_classe.stud_id
                        AND student_classe.classe_id = classe.classe_id
                        AND student_classe.classe_id = classe_year.classe_id
                        AND student_classe.sy_id = classe_year.sy_id
                        AND classe_year.section_id = section.section_id
                        AND student.p_id = $p_id
                        AND student_classe.sy_id = $sy_id
                    ORDER BY student.name"
            );
            return response()->json($children, 200);
        } catch (Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Roster of one classe (current year) for the "browse a classe to add a student to the
    // selected parent" panel - a LEFT JOIN surfaces each student's current parent (if any) so the
    // admin can see a conflict before reassigning.
    public function studentsOfClasseForAssignment(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'classe_id' => 'required|integer|min:1',
            ]);
        } catch (Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

        $connection = $request->input('connection');
        $year = $request->input('year');
        $classe_id = $request->input('classe_id');
        config(["database.default" => $connection]);

        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $students = DB::select(
                "SELECT student.stud_id, student.matricule, student.name, student.surname, student.p_id,
                        stud_parent.p_name, stud_parent.p_surname
                    FROM student
                    LEFT JOIN stud_parent ON stud_parent.p_id = student.p_id
                    WHERE student.stud_id IN (
                        SELECT student_classe.stud_id FROM student_classe
                        WHERE student_classe.sy_id = $sy_id AND student_classe.classe_id = $classe_id
                    )
                    ORDER BY student.name"
            );
            return response()->json($students, 200);
        } catch (Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    // student.p_id is evergreen (not year-scoped), so assign/remove take no year param - only the
    // classe-browse roster above needs one.
    public function assignStudentsToParent(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'p_id' => 'required|integer|min:1',
                'data' => 'required|json',
                'data_size' => 'nullable|integer|min:0',
            ]);
        } catch (Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

        $connection = $request->input('connection');
        $p_id = $request->input('p_id');
        $data = $request->input('data');
        config(["database.default" => $connection]);

        $studList = json_decode($data, true);
        $allAffected = 1;
        foreach ($studList as $s) {
            $stud_id = $s['stud_id'];
            try {
                DB::select("UPDATE student SET p_id = $p_id WHERE stud_id = $stud_id");
            } catch (Throwable $th) {
                $allAffected = 0;
            }
        }

        if ($allAffected == 1) {
            return response()->json([
                'status' => true,
                'message' => 'Students assigned successfully',
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Failed to assign at least one student',
            ], 500);
        }
    }

    public function removeStudentsFromParent(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'data' => 'required|json',
                'data_size' => 'nullable|integer|min:0',
            ]);
        } catch (Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

        $connection = $request->input('connection');
        $data = $request->input('data');
        config(["database.default" => $connection]);

        $studList = json_decode($data, true);
        $allAffected = 1;
        foreach ($studList as $s) {
            $stud_id = $s['stud_id'];
            try {
                DB::select("UPDATE student SET p_id = NULL WHERE stud_id = $stud_id");
            } catch (Throwable $th) {
                $allAffected = 0;
            }
        }

        if ($allAffected == 1) {
            return response()->json([
                'status' => true,
                'message' => 'Students removed successfully',
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Failed to remove at least one student',
            ], 500);
        }
    }
}
