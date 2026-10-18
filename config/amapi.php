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

    /*
    |--------------------------------------------------------------------------
    | Managed Google Play Parent Frame URL
    |--------------------------------------------------------------------------
    |
    | The HTTPS URL of the hosting parent frame for the Managed Google Play iframe.
    | Must use https:// protocol as required by Google AMAPI security standards.
    |
    */
    'parent_frame_url' => env('AMAPI_PARENT_FRAME_URL', null),
];
