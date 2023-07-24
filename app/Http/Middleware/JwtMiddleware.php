<?php

namespace App\Http\Middleware;
 
use Closure;
use JWTAuth;
use Exception;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Illuminate\Http\Request;

class JwtMiddleware extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next)
    {
    try {
        $user = JWTAuth::parseToken()->authenticate();
    } catch (Exception $e) {
        if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
            return response()->json(['status' => 'Token tidak valid']);
        }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
            return response()->json(['status' => 'Token Kadaluarsa']);
        }else{
            return response()->json(['status' => 'Authorization Token tidak ditemukan']); 
        }
    }
        return $next($request);
    }
}
