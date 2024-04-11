<?php

use App\Http\Controllers\BazPaymentsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/baz/create-payment', [BazPaymentsController::class, 'createURL']);
