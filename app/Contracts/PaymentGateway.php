<?php

namespace App\Contracts;

use App\Models\OrderGroup;

interface PaymentGateway
{
    /**
     * Process order group payment.
     *
     * @param OrderGroup $orderGroup
     * @param array $paymentDetails
     * @return array
     */
    public function process(OrderGroup $orderGroup, array $paymentDetails = []): array;
}
