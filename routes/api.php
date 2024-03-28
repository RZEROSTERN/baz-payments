<?php

use App\Http\Controllers\BazPaymentsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/baz/create-payment', [BazPaymentsController::class, 'createURL']);
