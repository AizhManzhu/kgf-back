<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckToken
{
    const AUTH_TOKEN = '/k19&ajk';

    public function handle(Request $request, Closure $next)
    {
        $hash = md5($request->get('email') . self::AUTH_TOKEN);

        if($request->get('hash') != $hash) {
            return response()->json(['Unauthorized'], 401);
        }

        return $next($request);
    }
}
