<?php

namespace App\Payment;

use Illuminate\Http\Request;

interface Payment {

    public function init(Request $request);

    public function pay();

    public function isAvailable(Request $request): array;

    public function fail();
}
