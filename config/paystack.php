<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Paystack API Keys
    |--------------------------------------------------------------------------
    |
    | Set PAYSTACK_SECRET_KEY and PAYSTACK_PUBLIC_KEY in .env.
    | Use test keys (sk_test_... / pk_test_...) for development and
    | live keys (sk_live_... / pk_live_...) for production.
    |
    */

    'secret_key' => env('PAYSTACK_SECRET_KEY', ''),

    'public_key' => env('PAYSTACK_PUBLIC_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    |
    | ISO 4217 currency code. Paystack amounts are sent in the smallest unit
    | (e.g. pesewas for GHS, kobo for NGN, cents for USD).
    |
    */

    'currency' => env('PAYSTACK_CURRENCY', 'GHS'),

];
