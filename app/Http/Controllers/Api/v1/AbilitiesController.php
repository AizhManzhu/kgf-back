<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;

class AbilitiesController extends Controller
{

    public function index()
    {
        return auth()->user()->roles()->with('permissions')->get()
            ->pluck('permissions')
            ->flatten()
            ->pluck('name')
            ->toArray();
    }
}
