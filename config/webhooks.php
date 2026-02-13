<?php

return [
    /*
    |--------------------------------------------------------------------------
    | GoHighLevel Webhook URLs
    |--------------------------------------------------------------------------
    | V-19 FIX: All webhook URLs centralized here instead of hardcoded.
    | Set these in your .env file for each environment.
    */

    'gohighlevel' => [
        'payment' => env('WEBHOOK_GHL_PAYMENT', 'https://services.leadconnectorhq.com/hooks/eAE21tVIbkFC6dUHwja9/webhook-trigger/01b4e1c3-dd79-41a9-b383-cc485d4b917b'),
        'registration' => env('WEBHOOK_GHL_REGISTRATION', 'https://services.leadconnectorhq.com/hooks/eAE21tVIbkFC6dUHwja9/webhook-trigger/ff1314dc-fadc-4b7b-97a2-3a9fe275bf6b'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Pabbly Webhook URLs
    |--------------------------------------------------------------------------
    */
    'pabbly' => [
        'sale' => env('WEBHOOK_PABBLY_SALE', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | VBout List IDs
    |--------------------------------------------------------------------------
    */
    'vbout' => [
        'new_user_list_id' => env('VBOUT_NEW_USER_LIST_ID', '143046'),
        'existing_user_list_id' => env('VBOUT_EXISTING_USER_LIST_ID', '146283'),
    ],
];
