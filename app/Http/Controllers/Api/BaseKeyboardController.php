<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BaseKeyboard;
use Illuminate\Http\Request;

class BaseKeyboardController extends Controller
{
    public function getBaseKeyboards() {
        $baseKeyboards = BaseKeyboard::all();
        return $this->handleResponse($baseKeyboards);
    }

    public function saveBaseKeyboards(Request $request) {
        $data = $request->all();
        foreach($data as $key => $keyboard) {
            BaseKeyboard::query()->where('type', $key)->update(['name' => $keyboard]);
        }
        return $this->handleResponse(1);
    }
}
