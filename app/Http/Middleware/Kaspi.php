<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Kaspi
{
    /**
     * Handle an incoming request.
     *
     */
    public function handle(Request $request, Closure $next)
    {
        if (config('kaspi.users')->contains([$request->getUser(), $request->getPassword()])) {
            return $next($request);
        }

        return response()
            ->json([
                'txn_id' => $request->txn_id??0,
                'result' => 5,
                'comment' => 'Forbidden'
            ])->header('WWW-Authenticate', 'Basic');
    }
}
