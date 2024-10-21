<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class TelegramToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $hash = md5($request->telegram_id . '/' . env('TELEGRAM_BOT_TOKEN'));
        if($request->hash != $hash) {
            return response()->json(['Unauthorized'], 401);
        }
        return $next($request);
    }
    // No 'Access-Control-Allow-Origin' header is present on the requested resource.
}
