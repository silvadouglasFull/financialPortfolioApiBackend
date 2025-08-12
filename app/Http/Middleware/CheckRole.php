<?php

namespace App\Http\Middleware;

use App\Services\RoleUser\CheckRoleFactory;
use Closure;
use Illuminate\Http\Response;

class CheckRole
{
    public function handle($request, Closure $next, string $requiredRole)
    {
        $user = $request->user()->user_type;
        $strategy = CheckRoleFactory::make($requiredRole);
        if (!isset($user->user_type)) {
            return response()->json(["message" => "No role defined to user"], Response::HTTP_FORBIDDEN);
        } else if (!$strategy->check($user->user_type->value)) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }
        return $next($request);
    }
}
