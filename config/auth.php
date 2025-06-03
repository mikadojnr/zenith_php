<?php

return [
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect_uri' => env('GOOGLE_REDIRECT_URI'),
    ],
    
    'session' => [
        'lifetime' => env('SESSION_LIFETIME', 120),
        'encrypt' => env('SESSION_ENCRYPT', false),
        'files' => env('SESSION_FILES', 'storage/sessions'),
        'secure_cookie' => env('SESSION_SECURE_COOKIE', false),
    ],
];