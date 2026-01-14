<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BkashController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\TransactionController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::controller(PaymentController::class)->group(function () {
        Route::post('/wallet/agreement', 'createAgreement');
        Route::post('/payment/create', 'createPayment');
    });

    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/statement', [TransactionController::class, 'downloadStatement'])->name('transactions.statement');

    Route::controller(WalletController::class)->group(function () {
        Route::get('/wallet', 'index');
    });
});

Route::get('/bkash/agreement/callback', [BkashController::class, 'agreementCallback'])->name('bkash.agreement.callback');
Route::get('/bkash/payment/callback', [BkashController::class, 'paymentCallback'])->name('bkash.payment.callback');
