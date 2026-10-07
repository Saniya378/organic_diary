<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway Driver
    |--------------------------------------------------------------------------
    */
    'default' => env('PAYMENT_GATEWAY', 'mock'),

    /*
    |--------------------------------------------------------------------------
    | Available Payment Gateways
    |--------------------------------------------------------------------------
    */
    'gateways' => [
        'cod' => [
            'name' => 'Cash on Delivery',
            'driver' => \App\Services\Payments\CodGateway::class,
        ],
        'mock' => [
            'name' => 'Mock Digital Gateway',
            'driver' => \App\Services\Payments\MockGateway::class,
        ],
        'wallet' => [
            'name' => 'Mobile Wallet (JazzCash / Easypaisa)',
            'driver' => \App\Services\Payments\MockGateway::class,
        ],
        'card' => [
            'name' => 'Credit / Debit Card (Visa / Mastercard)',
            'driver' => \App\Services\Payments\MockGateway::class,
        ],
    ],
];
