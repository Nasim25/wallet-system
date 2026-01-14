<?php

namespace App\Http\Controllers;

use App\Services\Payments\PaymentService;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $payment) {}

    /**
     * Create agreement ( Link account)
     */
    public function createAgreement(Request $request)
    {
        try {
            $response = $this->payment->createAgreement($request->user(), $request->input('payment_method'));

            return response()->json([
                'success' => true,
                'bkashURL' => $response['bkashURL'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // Initiate payment with agreement
    public function createPayment(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10|max:25000',
            'payment_method' => 'required|string',
        ]);

        $wallet = auth()->user()->wallet;

        if (!$wallet) {
            return response()->json([
                'success' => false,
                'message' => __('wallet.no_agreement'),
            ], 400);
        }

        // Create idempotency key for this payment request
        $idempotencyKey = 'payment_create_' . auth()->id() . '_' . time();

        // Acquire Redis lock to prevent double submission
        $lock = Redis::set($idempotencyKey, 'locked', 'EX', 120, 'NX');

        if (!$lock) {
            return response()->json([
                'success' => false,
                'message' => __('wallet.payment_in_progress'),
            ], 429);
        }

        try {
            
            $response = $this->payment->createPayment(
                $wallet->agreement_token,
                $request->amount,
                $request->user(),
                $request->input('payment_method')
            );

            if (!isset($response['bkashURL'])) {
                throw new \Exception('Invalid response from bKash');
            }

            return response()->json([
                'success' => true,
                'bkashURL' => $response['bkashURL'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        } finally {
            // Release lock
            Redis::del($idempotencyKey);
        }
    }
}
