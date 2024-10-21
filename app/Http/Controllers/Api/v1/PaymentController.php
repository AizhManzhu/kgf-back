<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

require_once(app_path('Payment') . "/kkb.utils.php");


class PaymentController extends Controller
{

    public function __construct(Request $request)
    {
        $middlewareClass = 'App\\Http\\Middleware\\'.ucfirst($request->app);
        if (class_exists($middlewareClass)) {
            $this->middleware($request->app);
        }
    }

    public function pay($app, Request $request)
    {
        $className = 'App\\Payment\\' . ucfirst($app);
        $instance = new $className();

        $instance->init($request);

        $isAvailable = $instance->isAvailable($request);

        if (!$isAvailable[0]) {
            return $instance->fail($isAvailable[1]);
        }

        return $instance->pay();
    }

    // TODO REFACTOR
    public function success($app, Request $request)
    {
        Log::channel('payment')->debug($request->all());
    }

    //TODO make failure link
    public function fail($app, Request $request)
    {
        Log::channel('payment')->debug($request->all());
    }
}
