<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BkashController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Auth\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/wallet/agreement', [PaymentController::class, 'createAgreement']);
    Route::post('/payment/create', [PaymentController::class, 'create']);
});

Route::get('/bkash/callback', [BkashController::class, 'callback'])->name('bkash.callback');
