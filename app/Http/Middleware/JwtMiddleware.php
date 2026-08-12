<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json([
                'status' => 'n',
                'msg' => 'Token missing!'
            ], 401);
        }

        $decodedToken = jwtdecode($token);
        if (!$decodedToken) {
            return response()->json([
                'status' => 'n',
                'msg' => 'Invalid or expired!'
            ], 401);
        }
        
        return $next($request);
    }
}
