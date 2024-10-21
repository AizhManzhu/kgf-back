<?php

namespace App\Http\Middleware;

use Closure;

class MailingAuth
{
    const AUTH_TOKEN_LIST = [
        'botan' => 'A78%q#',
        'botan_box' => 'F-12#F',
        'popeyes' => '#1ak84$'
    ];

    public function handle($request, Closure $next)
    {
        $hashToken = md5(self::AUTH_TOKEN_LIST[$request->get('partner')].$request->get('email'));

        if($request->get('hash') != $hashToken) {
            return response()->json(['Unauthorized'], 401);
        }

        return $next($request);
    }
}
