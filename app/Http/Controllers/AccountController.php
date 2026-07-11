<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\SchoolYear;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
{

    public function updateAccount(Request $request)
    {
        $connection = $request->input("connection");
        $login = $request->input("login");
        $pwd = $request->input("pwd");
        $acc_id = $request->input("acc_id");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Nom_Filiere: $nom_filiere -- Section: $section";

        try {
            $ref = Account::find($acc_id);
            if (!is_null($ref)) {
                $ref->login = $login;
                $ref->pwd = $pwd;
                $ref->update();
                echo "1";
            } else {
                echo "-1"; //ACCOUNT NOT FOUND
            }
        } catch (Exception $e) {
            //echo '<br/>Message: ' .$e->getMessage();
            echo "-2"; //ERROR OCCURS
        }
    }

    public function login(Request $request)
    {
        $connection = $request->input("connection");
        $login = $request->input("login");
        $pwd = $request->input("pwd");
        config(["database.default" => $connection]);
        $accounts = Account::where('login', $login)->where('pwd', $pwd)->first();
        //$obj = SchoolYear::where('year', '2024/2025')->first();
        //echo 'sy_id='. $obj->sy_id .'\n';
        return response()->json($accounts, 200);
    }

    public function allAccounts($connection)
    {
        config(["database.default" => $connection]);
        $accounts = Account::all();
        //$obj = SchoolYear::where('year', '2024/2025')->first();
        //echo 'sy_id='. $obj->sy_id .'\n';
        return response()->json($accounts, 200);
    }
    public function index()
    {
        $Accounts = Account::all();
        /*
        return response()->json([
            'status' => true,
            'message' => 'Accounts retrieved successfully',
            'data' => $Accounts
        ], 200);*/
        return response()->json($Accounts, 200);
    }

    public function show($id)
    {
        $Account = Account::findOrFail($id);
        return response()->json([
            'status' => true,
            'message' => 'Account found successfully',
            'data' => $Account
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required|string|max:255',
            'pwd' => 'required|string|max:255',
            'type' => 'required|int',
            'email' => 'required|string|email|unique:Accounts|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $Account = Account::create($request->all());
        return response()->json([
            'status' => true,
            'message' => 'Account created successfully',
            'data' => $Account
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required|string|max:255',
            'pwd' => 'required|string|max:255',
            'type' => 'required|int',
            'email' => 'required|string|email|max:255|unique:Accounts,email,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $Account = Account::findOrFail($id);
        $Account->update($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Account updated successfully',
            'data' => $Account
        ], 200);
    }

    public function destroy($id)
    {
        $Account = Account::findOrFail($id);
        $Account->delete();

        return response()->json([
            'status' => true,
            'message' => 'Account deleted successfully'
        ], 204);
    }
}
