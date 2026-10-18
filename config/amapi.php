<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Android Management API Enterprise ID
    |--------------------------------------------------------------------------
    |
    | The enterprise resource name in the format: enterprises/{enterpriseId}
    |
    */
    'enterprise_id' => env('AMAPI_ENTERPRISE_ID', 'enterprises/LC041be11h'),

    /*
    |--------------------------------------------------------------------------
    | Google Service Account Credentials Path
    |--------------------------------------------------------------------------
    |
    | Path to the Google Cloud Service Account JSON file with AMAPI permissions.
    |
    */
    'credentials_path' => base_path(env('GOOGLE_APPLICATION_CREDENTIALS', 'storage/app/credentials/service-account.json')),
];
