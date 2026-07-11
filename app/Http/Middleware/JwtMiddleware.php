<?php

namespace App\Http\Middleware;

use Closure;

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Illuminate\Http\Request;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken(); // reads "Authorization: Bearer <token>"

        if (!$token) {
            return response()->json(['status' => false, 'message' => 'Token not provided, please include it in the Authorization header'], 401);
        }

        try {
            $decoded = JWT::decode($token, new Key(env('JWT_SECRET'), 'HS256'));
            $request->attributes->set('auth_payload', $decoded); // stash for later middleware/controllers
        } catch (ExpiredException $e) {
            return response()->json(['status' => false, 'message' => 'Token expired'], 401);
        } catch (\Throwable $e) {
            return response()->json(['status' => true, 'message' => 'Invalid token'], 401);
        }

        return $next($request);
    }
}
