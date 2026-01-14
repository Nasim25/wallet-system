<?php

namespace App\Contracts;

interface PaymentGatewayInterface
{
    public function createAgreement(array $data): array;

    public function createPayment(array $data): array;

    public function refund(array $data): array;
}
