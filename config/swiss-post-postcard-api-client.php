<?php

// config for Gigerit/PostcardApi
return [

    /*
    |--------------------------------------------------------------------------
    | Swiss Post Postcard API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the Swiss Post Postcard API client. You'll need to
    | obtain these credentials from Swiss Post.
    |
    */

    'base_url' => env('SWISS_POST_POSTCARD_API_BASE_URL', 'https://apiint.post.ch/pcc/'),

    'oauth' => [
        'auth_url' => env('SWISS_POST_POSTCARD_API_AUTH_URL', 'https://apiint.post.ch/OAuth/authorization'),
        'token_url' => env('SWISS_POST_POSTCARD_API_TOKEN_URL', 'https://apiint.post.ch/OAuth/token'),
        'client_id' => env('SWISS_POST_POSTCARD_API_CLIENT_ID'),
        'client_secret' => env('SWISS_POST_POSTCARD_API_CLIENT_SECRET'),
        'scope' => env('SWISS_POST_POSTCARD_API_SCOPE', 'PCCAPI'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Campaign
    |--------------------------------------------------------------------------
    |
    | The default campaign UUID to use when creating postcards. This can be
    | overridden per request if needed.
    |
    */
    'default_campaign' => env('SWISS_POST_POSTCARD_API_DEFAULT_CAMPAIGN'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the HTTP client used to make API requests.
    |
    */
    'timeout' => env('SWISS_POST_POSTCARD_API_TIMEOUT', 30),
    'retry_times' => env('SWISS_POST_POSTCARD_API_RETRY_TIMES', 3),
    'retry_sleep' => env('SWISS_POST_POSTCARD_API_RETRY_SLEEP', 500),

    /*
    |--------------------------------------------------------------------------
    | Debug Mode
    |--------------------------------------------------------------------------
    |
    | When enabled, this will log all API requests and responses for debugging
    | purposes. Never enable this in production with real data.
    |
    */
    'debug' => env('SWISS_POST_POSTCARD_API_DEBUG', false),

];
