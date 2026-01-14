<?php

namespace App\Services\Payments;

use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Contracts\PaymentGatewayInterface;

class PaymentService
{
    // Create agreement ( Link account)
    public function createAgreement($user, string $gatewayName)
    {
        $gateway = PaymentManager::make($gatewayName);

        return $gateway->createAgreement(['user_id' => $user->id]);
    }

    public function createPayment($agreement_token, $amount, $user, string $gatewayName)
    {
        $gateway = PaymentManager::make($gatewayName);

        return $gateway->createPayment([
            'agreement_token' => $agreement_token,
            'amount' => $amount,
            'user_id' => $user->id
        ]);
    }
}
