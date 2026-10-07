<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGateway;
use App\Models\OrderGroup;
use Illuminate\Support\Str;

class MockGateway implements PaymentGateway
{
    public function process(OrderGroup $orderGroup, array $paymentDetails = []): array
    {
        $last4 = null;
        if (!empty($paymentDetails['card_number'])) {
            $digits = preg_replace('/\D/', '', $paymentDetails['card_number']);
            $last4 = strlen($digits) >= 4 ? substr($digits, -4) : '4242';
        } elseif (!empty($paymentDetails['wallet_account_no'])) {
            $digits = preg_replace('/\D/', '', $paymentDetails['wallet_account_no']);
            $last4 = strlen($digits) >= 4 ? substr($digits, -4) : '9988';
        } elseif (!empty($paymentDetails['last4'])) {
            $last4 = $paymentDetails['last4'];
        }

        return [
            'success' => true,
            'redirect_url' => route('checkout.payment.mock', $orderGroup->id),
            'gateway_ref' => null,
            'last4' => $last4,
            'status' => 'pending',
            'message' => 'Redirecting to secure digital payment gateway simulation.',
        ];
    }

    /**
     * Simulate gateway response when customer clicks Success or Fail on mock gateway page.
     */
    public function simulate(string $outcome, array $details = []): array
    {
        if ($outcome === 'success') {
            return [
                'success' => true,
                'gateway_ref' => 'SIM-' . strtoupper(Str::random(10)),
                'last4' => $details['last4'] ?? '4242',
                'status' => 'paid',
                'message' => 'Online payment authorized and captured successfully.',
            ];
        }

        return [
            'success' => false,
            'gateway_ref' => null,
            'last4' => null,
            'status' => 'failed',
            'message' => 'Payment transaction was declined by bank simulator.',
        ];
    }
}
