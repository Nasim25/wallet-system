<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayInterface;
use App\Services\Payments\Gateways\BkashGateway;

class PaymentManager
{
    public static function make(string $gateway): PaymentGatewayInterface
    {
        return match ($gateway) {
            'bkash' => app(BkashGateway::class),
            default => throw new \Exception('Unsupported gateway'),
        };
    }
}
