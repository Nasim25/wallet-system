<?php

namespace App\Contracts;

interface PaymentGatewayInterface
{
    public function createAgreement(array $data);

    public function createPayment(array $data);

    public function refundPayment(array $data);
}
