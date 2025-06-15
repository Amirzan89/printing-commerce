<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Configuration - FCM v1 API (No Server Key Needed)
    |--------------------------------------------------------------------------
    */
    
    'project_id' => env('FIREBASE_PROJECT_ID', 'tata-print'),
    'credentials_path' => storage_path('app/firebase/firebase-credential.json'),
    
    // FCM v1 hanya butuh service account JSON, tidak butuh server_key
    'fcm' => [
        'sender_id' => env('FIREBASE_SENDER_ID', '813275722990'),
    ],
]; 