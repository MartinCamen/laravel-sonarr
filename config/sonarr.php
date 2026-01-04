<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sonarr Host
    |--------------------------------------------------------------------------
    |
    | The hostname or IP address of your Sonarr server.
    |
    */
    'host' => env('SONARR_HOST', 'localhost'),

    /*
    |--------------------------------------------------------------------------
    | Sonarr Port
    |--------------------------------------------------------------------------
    |
    | The port number your Sonarr server is running on.
    |
    */
    'port' => env('SONARR_PORT', 8989),

    /*
    |--------------------------------------------------------------------------
    | API Key
    |--------------------------------------------------------------------------
    |
    | Your Sonarr API key. You can find this in Sonarr under
    | Settings > General > Security.
    |
    */
    'api_key' => env('SONARR_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Use HTTPS
    |--------------------------------------------------------------------------
    |
    | Whether to use HTTPS when connecting to Sonarr.
    |
    */
    'use_https' => env('SONARR_USE_HTTPS', false),

    /*
    |--------------------------------------------------------------------------
    | Timeout
    |--------------------------------------------------------------------------
    |
    | The request timeout in seconds.
    |
    */
    'timeout' => env('SONARR_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | URL Base
    |--------------------------------------------------------------------------
    |
    | The URL base for your Sonarr installation if using a reverse proxy
    | with a subpath (e.g., '/sonarr').
    |
    */
    'url_base' => env('SONARR_URL_BASE', ''),

    /*
    |--------------------------------------------------------------------------
    | API Version
    |--------------------------------------------------------------------------
    |
    | The Sonarr API version to use (v3 or v5). Defaults to v3.
    |
    */
    'api_version' => env('SONARR_API_VERSION', 'v3'),
];
