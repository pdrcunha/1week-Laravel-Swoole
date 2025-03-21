<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtMiddleware
{
  public function handle($request, Closure $next)
  {

    try {
      $user = JWTAuth::parseToken()->authenticate();
    } catch (Exception $e) {
      if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
        return response()->json(['error' => 'Token is Invalid'], 401);
      } else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
        return response()->json(['error' => 'Token is Expired'], 401);
      } else {
        return response()->json(['error' => 'Authorization Token not found'], 401);
      }
    }
    
    $request->user = $user;
    return $next($request);
  }
}
