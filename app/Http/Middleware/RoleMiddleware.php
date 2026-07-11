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

        if (!$payload || !in_array($payload->role, $roles, true)) {
            return response()->json(['status' => false, 'message' => 'Forbidden: insufficient permissions'], 403);
        } 
        return $next($request);
    }
}
