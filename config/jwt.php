<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Segredo e expiração do token JWT
    |--------------------------------------------------------------------------
    |
    | Equivalente a api.jwt.secret / api.jwt.expiration no
    | application.properties da API original (Spring Boot + JJWT).
    |
    */

    'secret' => env('JWT_SECRET'),

    'ttl_minutes' => (int) env('JWT_TTL_MINUTES', 1440),

    'algo' => 'HS256',
];
