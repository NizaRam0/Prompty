<?php

$frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
$frontendParts = parse_url($frontendUrl);

$frontendOrigin = isset($frontendParts['scheme'], $frontendParts['host'])
    ? $frontendParts['scheme'].'://'.$frontendParts['host'].(isset($frontendParts['port']) ? ':'.$frontendParts['port'] : '')
    : 'http://localhost:3000';

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['*'],//allow all paths for CORS

    'allowed_methods' => ['*'],//allow all HTTP methods for CORS

    'allowed_origins' => [$frontendOrigin],//allow only the frontend origin for CORS

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
