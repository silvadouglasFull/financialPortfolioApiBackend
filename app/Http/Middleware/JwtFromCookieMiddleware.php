<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class JwtFromCookieMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if ($token = $request->cookie('token')) {
            // Insere o token no header Authorization para que JWTAuth funcione normalmente
            $request->headers->set('Authorization', "Bearer {$token}");
        }
        return $next($request);
    }
}
