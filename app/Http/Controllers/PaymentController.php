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
}
