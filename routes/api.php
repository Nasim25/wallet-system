<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BkashController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WalletController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/wallet/agreement', [PaymentController::class, 'createAgreement']);
    Route::post('/payment/create', [PaymentController::class, 'create']);

    Route::controller(WalletController::class)->group(function () {
        Route::get('/wallet', 'index');
    });
});

Route::get('/bkash/callback', [BkashController::class, 'callback'])->name('bkash.callback');
