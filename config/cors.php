<?php

/*
|--------------------------------------------------------------------------
| Cross-Origin Resource Sharing (CORS)
|--------------------------------------------------------------------------
|
| As origens liberadas vêm de CORS_ALLOWED_ORIGINS (lista separada por
| vírgula; "*" libera todas). A API é stateless e autentica por
| "Authorization: Bearer <token>" — sem cookies —, então
| supports_credentials fica desligado (o que também permite o "*").
|
*/

$origens = array_values(array_filter(array_map(
    fn (string $origem) => rtrim(trim($origem), '/'),
    explode(',', (string) env('CORS_ALLOWED_ORIGINS', '*')),
)));

return [

    'paths' => ['api/*'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => $origens ?: ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Authorization', 'Content-Type', 'X-Requested-With', 'Accept'],

    'exposed_headers' => [],

    'max_age' => 3600,

    'supports_credentials' => false,

];
