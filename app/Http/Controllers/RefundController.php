<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\Payments\PaymentService;
use Illuminate\Http\Request;

class RefundController extends Controller
{
    public function __construct(protected PaymentService $payment) {}

    public function processRefund(Request $request)
    {
        $request->validate([
            'transaction_id' => 'required|integer|exists:transactions,id',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:255',
        ]);

        $transaction = Transaction::findOrFail($request->transaction_id);
        $wallet = auth()->user()->wallet;

        // Check if transaction belongs to user
        if ($transaction->wallet_id !== $wallet->id) {
            abort(403, 'Unauthorized');
        }

        // Check if refundable
        if ($transaction->type !== 'credit') {
            return response()->json([
                'success' => false,
                'message' => __('wallet.not_refundable'),
            ], 400);
        }

        if ($request->amount > $transaction->amount) {
            return response()->json([
                'success' => false,
                'message' => __('wallet.refund_amount_exceed'),
            ], 400);
        }

        try {
            $response = $this->payment->refundPayment(
                $transaction->payment_id,
                $transaction->trx_id,
                $request->amount,
                $request->reason
            );

            if ($response['statusCode'] !== '0000') {
                throw new \Exception($response['statusMessage'] ?? 'Refund failed');
            }

            // Create refund transaction
            $wallet->refund(
                $request->amount,
                $response['refundTrxID'],
                $transaction->payment_id
            );

            // Deduct from transaction amount
            $transaction->decrement('amount', $request->amount);

            return response()->json([
                'success' => true,
                'message' => __('wallet.refund_success'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
