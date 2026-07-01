<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ShopSphere Settings
    |--------------------------------------------------------------------------
    */

    'tax_rate' => env('TAX_RATE', 10), // percent
    'shipping_fee' => env('SHIPPING_FEE', 5.00),
    'currency' => env('CURRENCY', 'USD'),
    'currency_symbol' => env('CURRENCY_SYMBOL', '$'),

    'frontend_url' => env('FRONTEND_URL', 'http://localhost:3000'),

    'pagination' => [
        'per_page' => 15,
        'max_per_page' => 100,
    ],
];
