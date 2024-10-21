<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Role
{
    public function handle($request, Closure $next, $role)
    {
        if (!Auth::check())
            return response()->json(['unauthorised'], 401);

        $user = Auth::user();
        $role = "is".ucfirst($role);
        if($user->{$role}())
            return $next($request);
        else
            return response()->json(['forbidden'], 403);
    }
}
