<?php

return [

    'recruiter' => [
        'directory' => 'images/recruiter', // Directory name for uploaded files
        'public_url' => env('APP_URL') . '/storage/images/recruiter/', // Public URL for accessing files
    ],

    // 'candidate' => [
    //     'directory' => 'images/candidate', // Directory name for uploaded files
    //     'public_url' => env('APP_URL') . '/storage/images/candidate/', // Public URL for accessing files
    // ],
    
     'candidate' => [

        /*
        |--------------------------------------------------------------------------
        | Candidate Profile Image
        |--------------------------------------------------------------------------
        */

        'directory' => 'images/candidate',

        'public_url' => env('APP_URL') . '/storage/images/candidate/',

    'before_url'   => env('APP_URL').'/storage/images/candidate/before/',

    'after_url'    => env('APP_URL').'/storage/images/candidate/after/',

        /*
        |--------------------------------------------------------------------------
        | Candidate Documents
        |--------------------------------------------------------------------------
        */

        'document_directory' => 'documents/candidate',

        'document_url' => env('APP_URL') . '/storage/documents/candidate/',
    ],
    
    

    'profession' => [
        'directory' => 'images/profession', // Directory name for uploaded files
        'public_url' => env('APP_URL') . '/storage/images/profession/', // Public URL for accessing files
    ],

    'clinic' => [
        'directory' => 'images/clinic', // Directory name for uploaded files
        'public_url' => env('APP_URL') . '/storage/images/clinic/', // Public URL for accessing files
    ],
    
    'review' => [
        'directory' => 'images/reviews', // Directory name for uploaded files
        'public_url' => env('APP_URL') . '/storage/images/reviews/', // Public URL for accessing files
    ],

    'report' => [
        'directory' => 'images/report', // Directory name for uploaded files
        'public_url' => env('APP_URL') . '/storage/images/report/', // Public URL for accessing files
    ],

    'admin' => [
        'directory' => 'images/admin', // Directory name for uploaded files
        'public_url' => env('APP_URL') . '/storage/images/admin/', // Public URL for accessing files
    ],
];

