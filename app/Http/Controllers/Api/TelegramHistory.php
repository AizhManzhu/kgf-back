<?php

namespace App\Http\Controllers\Api;

use App\Repository\Base;
use Illuminate\Routing\Controller as BaseController;

class TelegramHistory extends BaseController
{
    use Base;

    public function index() {
        $telegramHistory = \App\Models\TelegramHistory::query()->select('to_id')->with('member')->groupBy('to_id')->paginate(20);
        return $this->handleResponse($telegramHistory);
    }
}
