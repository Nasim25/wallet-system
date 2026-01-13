<?php

namespace App\Services\Payments\Gateways;

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


    public function createAgreement(array $data): array
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
                'callbackURL' => route('bkash.callback'),
            ]
        );

        if ($response->failed()) {
            Log::error('bKash Create Agreement Error', ['response' => $response->json()]);
            throw new \Exception('Failed to create agreement');
        }

        Redis::setex($response['paymentID'], 3600, $data['user_id']); // Store paymentID temporarily

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

        if ($response->failed()) {
            Log::error('bKash Execute Agreement Error', ['response' => $response->json()]);
            throw new \Exception('Failed to execute agreement');
        }

        return $response->json();
    }

    public function chargeWithAgreement(array $data): array
    {
        return [];
    }

    public function refund(array $data): array
    {
        return [];
    }
}
