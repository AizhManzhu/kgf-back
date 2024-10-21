<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::controller(\App\Http\Controllers\Api\v1\PaymentController::class)->group(function () {
    Route::prefix('v1')->group(function () {
        Route::get('{app}/pay', 'pay');
        Route::get('{app}/fail', 'fail');
        Route::post('{app}/success', 'success');
    });
});
