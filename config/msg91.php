<?php

return [
    'auth_key' => env('MSG91_AUTH_KEY'),
    'sender_id' => env('MSG91_SENDER_ID'),
    'template_id' => env('MSG91_TEMPLATE_ID'),
    'api_url' => env('MSG91_API_URL', 'https://api.msg91.com/api/v5/flow/'),
    'route' => env('MSG91_ROUTE', '4'),
    'country' => env('MSG91_COUNTRY', '91'),
];
