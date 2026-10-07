<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGateway;
use App\Models\OrderGroup;

class CodGateway implements PaymentGateway
{
    public function process(OrderGroup $orderGroup, array $paymentDetails = []): array
    {
        return [
            'success' => true,
            'redirect_url' => null,
            'gateway_ref' => 'COD-' . $orderGroup->id . '-' . strtoupper(substr(uniqid(), -5)),
            'last4' => null,
            'status' => 'unpaid',
            'message' => 'Cash on Delivery selected. Payment will be collected at delivery.',
        ];
    }
}
