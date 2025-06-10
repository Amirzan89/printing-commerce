<?php

return [
    'credentials' => [
        'file' => storage_path('app/json/firebase-credentials.json'),
    ],
    'database' => [
        'url' => env('FIREBASE_DATABASE_URL'),
    ],
    'project_id' => env('FIREBASE_PROJECT_ID'),
    'storage' => [
        'bucket' => env('FIREBASE_STORAGE_BUCKET'),
    ],
]; 