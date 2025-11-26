<?php

return [
    // Server-side (keep private)
    'credentials' => env('FIREBASE_CREDENTIALS', '/var/www/firebase-admin.json'),
    'project_id' => env('FIREBASE_PROJECT_ID'),
    
    // Client-side (safe to expose)
    'client' => [
        'api_key' => env('FIREBASE_API_KEY'),
        'project_id' => env('FIREBASE_PROJECT_ID'),
        'messaging_sender_id' => env('FIREBASE_MESSAGING_SENDER_ID'),
        'app_id' => env('FIREBASE_APP_ID'),
    ],
];