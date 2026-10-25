<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Exception;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    /*
    public function handle($request, Closure $next, ...$roles)
    {
        try {
            $token = $request->bearerToken();

            if (!$token) {
                return response()->json([
                    'status' => false,
                    'message' => 'Missing access token'
                ], 401); //401 = Unauthorized
            }

            $decoded = JWT::decode($token, new Key(env('JWT_SECRET'), 'HS256'));

            if (!in_array($decoded->role, $roles)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Forbidden: insufficient permissions'
                ], 403); // 403 = Forbidden
            }

            // Attach user info to request
            $request->auth = $decoded;

            return $next($request);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid token',
                'error' => $e->getMessage()
            ], 401); //401 = Unauthorized
        }
    }
    */

    public function handle(Request $request, Closure $next, ...$roles)
    {
        $payload = $request->attributes->get('auth_payload');

        // SUBADMIN (account.type=9) has every ADMIN right: it passes any group that allows ADMIN,
        // unless the group lists "!SUBADMIN" (e.g. adding/deleting students).
        $role = $payload->role ?? null;
        if ($role === "SUBADMIN" && in_array("ADMIN", $roles, true) && !in_array("!SUBADMIN", $roles, true)) {
            $role = "ADMIN";
        }

        if (!$payload || !in_array($role, $roles, true)) {
            return response()->json(['status' => false, 'message' => 'Forbidden: insufficient permissions'], 403);
        } 
        return $next($request);
    }
}
