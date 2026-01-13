<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use App\Services\Payments\Gateways\BkashGateway;

class BkashController extends Controller
{
    public function __construct(private BkashGateway $bkash) {}

    public function callback(Request $request)
    {
        $status = $request->get('status');
        $paymentId = $request->get('paymentID');

        if ($status === 'cancel' || $status === 'failure') {
            return redirect()->route('wallet.index')
                ->with('error', __('wallet.payment_cancelled'));
        }

        $sessionPaymentId = Redis::get($paymentId);

        if (!$sessionPaymentId) {
            return redirect()->route('wallet.index')
                ->with('error', __('wallet.invalid_payment'));
        }

        try {
            $response = $this->bkash->executeAgreement($paymentId);

            if ($response['statusCode'] !== '0000') {
                throw new \Exception($response['statusMessage'] ?? 'Agreement execution failed');
            }

            // Check if this is agreement creation or payment
            if (isset($response['agreementID'])) {

                $user = User::find($sessionPaymentId);

                if (!$user) {
                    throw new \Exception('User not found for agreement');
                }
                $wallet = $user->wallet()->create([
                    'agreement_token' => $response['agreementID'],
                    'masked_number' => $response['payerAccount'] ?? null,
                ]);

                return redirect()->route('wallet.index')
                    ->with('success', __('wallet.agreement_created'));
            }


            return redirect()->route('wallet.index')->with('success', __('messages.payment_success'));
        } catch (\Exception $e) {
            Log::error('bKash Callback Error', ['error' => $e->getMessage()]);
            return redirect()->route('wallet.index')->with('error', __('messages.payment_failed'));
        }
    }
}
