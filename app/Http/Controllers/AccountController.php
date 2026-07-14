<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Administrateur;
use App\Models\Staff;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Cookie;

class AccountController extends Controller
{

    public function updateAccountWithPOST(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'login' => 'required|string',
                'new_pwd' => 'required|string',
                'acc_id' => 'required|integer',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }
        $connection = $request->input("connection");
        $login = $request->input("login");
        $pwd = $request->input("new_pwd");
        $acc_id = $request->input("acc_id");
        config(["database.default" => $connection]);
        //echo "Connection: $connection -- Year: $year -- Nom_Filiere: $nom_filiere -- Section: $section";

        try {
            $ref = Account::find($acc_id);
            if (is_null($ref)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid credentials [Old Password is wrong]',
                ], 401); //ACCOUNT NOT FOUND
            }

            //Make sure no other account exists with the new login
            $existingAccount = Account::where('login', $login)
                ->where('acc_id', '!=', $acc_id)
                ->first();
            if (!is_null($existingAccount)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Login already exists. Login is unique for each account. Please choose another login.',
                ], 400); //BAD REQUEST
            }


            $ref->login = $login;
            $ref->pwd = $pwd;
            $ref->update();
            return response()->json([
                'status' => true,
                'message' => 'Account updated successfully',
            ], 200);
        } catch (\Throwable $e) {
            //echo '<br/>Message: ' .$e->getMessage();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500); //INTERNAL SERVER ERROR
        }
    }

    public function login(Request $request)
    {
        try {
            // Validate request
            $data = $request->validate([
                'login' => 'required|string',
                'pwd' => 'required|string',
                'connection' => 'required|string'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

        try {
            $jwt_secret = env('JWT_SECRET');
            $access_token_duration = env('ACCESS_TOKEN_DURATION', 3600); // default to 1 hour or 3600 minutes
            $refresh_token_duration = env('REFRESH_TOKEN_DURATION', 60 * 24 * 7); // default to 7 days



            $login = $data['login'];
            $pwd = $data['pwd'];
            $connection = $data['connection'];

            // Switch DB connection dynamically
            config(["database.default" => $connection]);

            // Authenticate user ----------------------------------------------------------
            $account = Account::where(function ($q) use ($login) {
                $q->where('login', $login)
                    ->orWhere('email', $login);
            })
                ->where('pwd', $pwd) // ⚠️ You should hash passwords later
                ->first();

            /*--- Correction from claude
            $account = Account::where(function ($q) use ($login) {
                $q->where('login', $login)->orWhere('email', $login);
            })->first();

            if (!$account || !Hash::check($pwd, $account->pwd)) {
                return response()->json(['status' => false, 'message' => 'Invalid credentials'], 401);
            }
            */


            if (!$account) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid credentials [Login or Password]',
                ], 401);
            }

            $role = MyHelper::findRole($account->type);
            $user_name = "";
            $user_id = 1;
            if ($account->type == 1) { //ADMINISTRATOR 
                $admin = Administrateur::where('acc_id', $account->acc_id)->first();
                if (!$admin) {
                    $user_name = "ADMINISTRATEUR"; // --> BD INCONSISTENT, CAR C'EST PAS UTILE DE LAISSER UN COMPTE QUAND L'UTILISATEUR EST SUPPRIMÉ DE LA TABLE ADMINISTRATEUR
                } else {
                    $user_name = $admin->nom . ' ' . $admin->prenom;
                    $user_name = trim($user_name);
                    $user_id = $admin->admin_id;
                }
            } else { //CONNECTED USER  
                $user_name = "NAME_CONNECTED_USER";
                $staff = Staff::where('acc_id', $account->acc_id)->first();
                if (!$staff) {
                    $user_name = "PERSONNEL"; // --> BD INCONSISTENT, CAR C'EST PAS UTILE DE LAISSER UN COMPTE QUAND L'UTILISATEUR EST SUPPRIMÉ DE LA TABLE ADMINISTRATEUR
                } else {
                    $user_name = $staff->name . ' ' . $staff->surname;
                    $user_name = trim($user_name);
                    $user_id = $staff->staff_id;
                }
            }

            // -----------------------------
            // 1. Generate Access Token (JWT)
            // -----------------------------
            $accessTokenPayload = [
                'iss' => 'your-app',          // issuer
                'sub' => $account->acc_id,           // user ID
                'email' => $account->email,
                'role' => $role, //"ROLE_CONNECTED_USER",
                'name' => $user_name, //"NAME_CONNECTED_USER",
                'user_id' => $user_id, //this represents the actual user id in the staff or admin table, not the account id
                'iat' => time(),              // issued at
                'exp' => time() + $access_token_duration        // expires in 1 hour
            ];

            $accessToken = JWT::encode($accessTokenPayload, $jwt_secret, 'HS256');

            // -----------------------------
            // 2. Generate Refresh Token
            // -----------------------------
            $refreshTokenPayload = [
                'iss' => 'dmsacad_backend_dev', // issuer
                'sub' => $account->acc_id,
                'role' => $role, //"ROLE_CONNECTED_USER",
                'name' => $user_name, //"NAME_CONNECTED_USER",
                'user_id' => $user_id, //this represents the actual user id in the staff or admin table, not the account id
                'iat' => time(),
                'exp' => time() + $refresh_token_duration // 7 days
            ];

            $refreshToken = JWT::encode($refreshTokenPayload, $jwt_secret, 'HS256');



            // -----------------------------
            // 3. Return Access Token + User
            // -----------------------------
            return response()->json([
                'status' => true,
                'message' => 'Login successful',
                'access_token' => $accessToken,
                'token_type' => 'Bearer',
                'expires_in' => $access_token_duration,
                'user' => $account
            ], 200)->withCookie('refresh_token', $refreshToken, $refresh_token_duration, null, null, false, true, false, 'Strict');
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Login failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * To test this provide body as JSON example: {"connection": "mysql"}, or {"connection": "LY_MERI"}
     * Provide the refresh token in the cookie named 'refresh_token'. Cookie option is available in Postman under the "Cookies" tab.
     */
    public function refresh(Request $request)
    {
        try {
            // Validate request
            $data = $request->validate([
                'connection' => 'required|string'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $e->getMessage(), //'connection is required',
            ], 422);
        }
        $connection = $data['connection'];

        try {
            // 1. Read refresh token from cookie
            $refreshToken = $request->cookie('refresh_token');
            $access_token_duration = env('ACCESS_TOKEN_DURATION', 3600); // default to 1 hour
            //$refresh_token_duration = env('REFRESH_TOKEN_DURATION', 60 * 24 * 7); // default to 7 days

            if (!$refreshToken) {
                return response()->json([
                    'status' => false,
                    'message' => 'Refresh token missing'
                ], 401); //401 = Unauthorized
            }

            // Switch DB connection dynamically
            config(["database.default" => $connection]);

            // 2. Decode refresh token
            $jwt_secret = env('JWT_SECRET');

            try {
                $decoded = JWT::decode($refreshToken, new Key($jwt_secret, 'HS256'));
            } catch (Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid or expired refresh token',
                    'error' => $e->getMessage()
                ], 401); //401 = Unauthorized
            }

            // 3. Retrieve user from DB
            $account = Account::find($decoded->sub); //sub is the acc_id of the account in the token

            if (!$account) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found'
                ], 404); //404 = Not Found
            }

            $role = MyHelper::findRole($account->type);
            $user_name = "";
            $user_id = 1;
            if ($account->type == 1) { //ADMINISTRATOR 
                $admin = Administrateur::where('acc_id', $account->acc_id)->first();
                if (!$admin) {
                    $user_name = "ADMINISTRATEUR"; // --> BD INCONSISTENT, CAR C'EST PAS UTILE DE LAISSER UN COMPTE QUAND L'UTILISATEUR EST SUPPRIMÉ DE LA TABLE ADMINISTRATEUR
                } else {
                    $user_name = $admin->nom . ' ' . $admin->prenom;
                    $user_name = trim($user_name);
                    $user_id = $admin->admin_id;
                }
            } else { //CONNECTED USER  
                $user_name = "NAME_CONNECTED_USER";
                $staff = Staff::where('acc_id', $account->acc_id)->first();
                if (!$staff) {
                    $user_name = "PERSONNEL"; // --> BD INCONSISTENT, CAR C'EST PAS UTILE DE LAISSER UN COMPTE QUAND L'UTILISATEUR EST SUPPRIMÉ DE LA TABLE ADMINISTRATEUR
                } else {
                    $user_name = $staff->name . ' ' . $staff->surname;
                    $user_name = trim($user_name);
                    $user_id = $staff->staff_id;
                }
            }

            // -----------------------------
            // Generate Access Token (JWT)
            // -----------------------------
            $accessTokenPayload = [
                'iss' => 'your-app',          // issuer
                'sub' => $account->acc_id,           // user ID
                'email' => $account->email,
                'role' => $role, //"ROLE_CONNECTED_USER",
                'name' => $user_name, //"NAME_CONNECTED_USER",
                'user_id' => $user_id, //this represents the actual user id in the staff or admin table, not the account id
                'iat' => time(),              // issued at
                'exp' => time() + $access_token_duration        // expires in 1 hour
            ];

            $newAccessToken = JWT::encode($accessTokenPayload, $jwt_secret, 'HS256');

            // 5. Return new access token
            return response()->json([
                'status' => true,
                'message' => 'Token refreshed successfully',
                'access_token' => $newAccessToken,
                'token_type' => 'Bearer',
                'expires_in' => $access_token_duration
            ], 200); //200 = OK
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Refresh failed',
                'error' => $e->getMessage()
            ], 500); //500 = Internal Server Error
        }
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
