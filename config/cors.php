<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS)
    |--------------------------------------------------------------------------
    |
    | Equivalente ao CorsConfigurationSource definido em SecurityConfig.java:
    | libera qualquer origem, os métodos usados pela API e os headers
    | necessários para o envio do Bearer token.
    |
    */

    'paths' => ['api/*'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => ['*'],

    'allowed_headers' => ['Authorization', 'Content-Type', 'X-Requested-With', 'Accept'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
