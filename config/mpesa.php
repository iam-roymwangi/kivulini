<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Daraja environment
    |--------------------------------------------------------------------------
    |
    | Use "sandbox" while integrating against Safaricom's test APIs and
    | "production" on the live shortcode. Cached config must be rebuilt
    | after changing this value on cPanel.
    |
    */

    'environment' => env('MPESA_ENVIRONMENT', 'sandbox'),

    'consumer_key' => env('MPESA_CONSUMER_KEY'),

    'consumer_secret' => env('MPESA_CONSUMER_SECRET'),

    'shortcode' => env('MPESA_SHORTCODE'),

    'passkey' => env('MPESA_PASSKEY'),

    /*
    |--------------------------------------------------------------------------
    | Transaction type
    |--------------------------------------------------------------------------
    |
    | CustomerPayBillOnline for a paybill, CustomerBuyGoodsOnline for a till.
    |
    */

    'transaction_type' => env('MPESA_TRANSACTION_TYPE', 'CustomerPayBillOnline'),

    'party_b' => env('MPESA_PARTY_B', env('MPESA_SHORTCODE')),

    /*
    |--------------------------------------------------------------------------
    | Callback authenticity
    |--------------------------------------------------------------------------
    |
    | Safaricom does not sign STK callbacks. The unguessable secret in the
    | callback path is the primary authenticity check, together with matching
    | CheckoutRequestID and amount against our records.
    |
    */

    'callback_secret' => env('MPESA_CALLBACK_SECRET'),

    /*
    | Leave blank to auto-generate the CallBackURL from APP_URL + MPESA_CALLBACK_SECRET
    | via the webhooks.mpesa.stk route. On cPanel this requires APP_URL to be the public
    | HTTPS origin (e.g. https://kivuliniadventures.com). Only set this manually if you
    | need to override (e.g. an ngrok tunnel for local testing). Do NOT set it to
    | "${APP_URL}/..." — .env does not support shell-style variable interpolation.
    */
    'callback_url' => env('MPESA_CALLBACK_URL'),

    'timeouts' => [
        'connect' => (int) env('MPESA_CONNECT_TIMEOUT', 5),
        'response' => (int) env('MPESA_RESPONSE_TIMEOUT', 20),
    ],

    'pending_ttl_minutes' => (int) env('MPESA_PENDING_TTL_MINUTES', 15),

    'query_after_seconds' => (int) env('MPESA_QUERY_AFTER_SECONDS', 45),

    'sandbox_base_url' => 'https://sandbox.safaricom.co.ke',

    'production_base_url' => 'https://api.safaricom.co.ke',

];
