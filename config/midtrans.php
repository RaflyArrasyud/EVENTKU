<?php

return [
    // Sandbox only. Never point to production in this task.
    'sandbox' => true,
    'base_url' => 'https://api.sandbox.midtrans.com',
    'server_key' => env('MIDTRANS_SERVER_KEY', ''),
    // Optional trusted CA bundle for Windows PHP installations without one.
    // Keep HTTPS certificate verification enabled; never use verify=false.
    'ca_bundle' => env('MIDTRANS_CA_BUNDLE'),
    'expiry_duration' => 15,
    'expiry_unit' => 'minute',
    'currency' => 'IDR',
];
