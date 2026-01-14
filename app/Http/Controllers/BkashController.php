<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use App\Services\Payments\Gateways\BkashGateway;

class BkashController extends Controller
{
    public function __construct(private BkashGateway $bkash) {}

    public function agreementCallback(Request $request)
    {
        return $this->handleBkashCallback(
            $request,
            fn($paymentId) => $this->bkash->executeAgreement($paymentId),
            function (User $user, array $response) {

                if (empty($response['agreementID'])) {
                    throw new \Exception('Agreement ID missing');
                }

                $user->wallet()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'agreement_token' => $response['agreementID'],
                        'masked_number'   => $response['payerAccount'] ?? null,
                    ]
                );

                return $this->redirectSuccess('agreement=created');
            }
        );
    }


    public function paymentCallback(Request $request)
    {
        return $this->handleBkashCallback(
            $request,
            fn($paymentId) => $this->bkash->executeAgreement($paymentId),
            function (User $user, array $response) {

                if (
                    empty($response['trxID']) ||
                    ($response['transactionStatus'] ?? null) !== 'Completed'
                ) {
                    throw new \Exception('Invalid payment response');
                }

                $wallet = $user->wallet;

                if (!$wallet) {
                    throw new \Exception('Wallet not found');
                }

                $exists = Transaction::where('trx_id', $response['trxID'])->exists();

                if (!$exists) {
                    DB::transaction(function () use ($wallet, $response) {
                        $wallet->credit(
                            amount: (float) $response['amount'],
                            trxId: $response['trxID'],
                            paymentId: $response['paymentID']
                        );
                    });
                }

                return $this->redirectSuccess('payment=success');
            }
        );
    }

    private function handleBkashCallback(
        Request $request,
        callable $executor,
        callable $onSuccess
    ) {
        $status    = $request->string('status');
        $paymentId = $request->string('paymentID');

        if (in_array($status, ['cancel', 'failure'], true)) {
            return $this->redirectError('cancelled_by_user');
        }

        $userId = Redis::get("bkash:payment:{$paymentId}");

        if (!$userId) {
            return $this->redirectError('invalid_callback');
        }

        $user = User::find($userId);

        if (!$user) {
            return $this->redirectError('user_not_found');
        }

        try {
            $response = $executor($paymentId);

            if (($response['statusCode'] ?? null) !== '0000') {
                throw new \Exception($response['statusMessage'] ?? 'bKash execution failed');
            }

            $result = $onSuccess($user, $response, $paymentId);

            Redis::del("bkash:payment:{$paymentId}");

            return $result;
        } catch (\Throwable $e) {
            Log::error('bKash Callback Error', [
                'paymentID' => $paymentId,
                'error'     => $e->getMessage(),
            ]);

            return $this->redirectError('something_went_wrong');
        }
    }

    private function redirectError(string $code)
    {
        return redirect()->to(
            rtrim(config('app.url'), '/') . "/dashboard?error={$code}"
        );
    }

    private function redirectSuccess(string $query)
    {
        return redirect()->to(
            rtrim(config('app.url'), '/') . "/dashboard?{$query}"
        );
    }
}
