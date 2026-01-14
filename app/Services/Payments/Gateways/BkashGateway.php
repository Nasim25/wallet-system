<?php

namespace App\Services\Payments\Gateways;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use App\Contracts\PaymentGatewayInterface;

class BkashGateway implements PaymentGatewayInterface
{
    protected string $baseUrl;
    protected string $appKey;
    protected string $appSecret;
    protected string $username;
    protected string $password;

    public function __construct()
    {
        $this->baseUrl   = env('BKASH_BASE_URL');
        $this->appKey    = env('BKASH_APP_KEY');
        $this->appSecret = env('BKASH_APP_SECRET');
        $this->username  = env('BKASH_USERNAME');
        $this->password  = env('BKASH_PASSWORD');
    }

    public function getToken(): string
    {
        $redisKey = 'bkash_token';

        // Try to get token directly from Redis
        $token = Redis::get($redisKey);

        if ($token) {
            return $token;
        }

        // Fetch new token from bKash API
        $res = Http::withHeaders([
            'username' => $this->username,
            'password' => $this->password,
        ])->post("$this->baseUrl/tokenized/checkout/token/grant", [
            'app_key'    => $this->appKey,
            'app_secret' => $this->appSecret,
        ]);

        $idToken = $res['id_token'];

        // Store in Redis with expiration (TTL)
        Redis::setex($redisKey, 3600, $idToken); // 3600 seconds = 1 hour

        return $idToken;
    }


    public function createAgreement(array $data)
    {
        $token = $this->getToken();

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-APP-Key'     => $this->appKey,
            'Content-Type'  => 'application/json',
        ])->post(
            $this->baseUrl . '/tokenized/checkout/create',
            [
                'mode' => '0000',
                'payerReference' => 'USER_' . $data['user_id'],
                'callbackURL' => route('bkash.agreement.callback'),
            ]
        );

        if ($response->failed() || empty($response['paymentID'])) {
            throw new \Exception('Failed to create agreement');
        }

        Redis::setex(
            "bkash:payment:{$response['paymentID']}",
            900,
            $data['user_id']
        );

        return $response->json();
    }

    // Execute Agreement
    public function executeAgreement(string $paymentId): array
    {
        $token = $this->getToken();

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$token}",
            'X-APP-Key' => $this->appKey,
        ])->post("{$this->baseUrl}/tokenized/checkout/execute", [
            'paymentID' => $paymentId,
        ]);

        if ($response->failed() || empty($response['paymentID'])) {
            throw new \Exception('Failed Bkash execute');
        }

        return $response->json();
    }

    public function createPayment(array $data)
    {
        $token = $this->getToken();

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$token}",
            'X-APP-Key' => $this->appKey,
        ])->post("{$this->baseUrl}/tokenized/checkout/create", [
            'mode' => '0001',
            'payerReference' => $data['user_id'],
            'callbackURL' => route('bkash.payment.callback'),
            'agreementID' => $data['agreement_token'],
            'amount' => number_format($data['amount'], 2, '.', ''),
            'currency' => 'BDT',
            'intent' => 'sale',
            'merchantInvoiceNumber' => 'INV_' . $data['user_id'] . Str::random(10),
        ]);

        if ($response->failed() || empty($response['paymentID'])) {
            throw new \Exception('Failed bkash create payment');
        }

        Redis::setex(
            "bkash:payment:{$response['paymentID']}",
            900,
            $data['user_id']
        );
        return $response->json();
    }


    // Refund Transaction
    public function refundPayment(array $data)
    {
        $token = $this->getToken();

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$token}",
            'X-APP-Key' => $this->appKey,
        ])->post("{$this->baseUrl}/tokenized/checkout/payment/refund", [
            'paymentID' => $data['payment_id'],
            'trxID' => $data['trx_id'],
            'amount' => number_format($data['amount'], 2, '.', ''),
            'reason' => $data['reason'],
            'sku' => 'wallet-refund',
        ]);

        if ($response->failed()) {
            Log::error('bKash Refund Error', ['response' => $response->json()]);
            throw new \Exception('Failed to process refund');
        }

        return $response->json();
    }
}
