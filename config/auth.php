<?php

use App\Models\Usuario;

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | API stateless, autenticada via JWT (equivalente ao
    | SessionCreationPolicy.STATELESS + JwtAuthenticationFilter da API
    | original). Não há guard "web" / sessão de navegador.
    |
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'api'),
        'passwords' => 'usuarios',
    ],

    'guards' => [
        'api' => [
            'driver' => 'jwt',
            'provider' => 'usuarios',
        ],
    ],

    'providers' => [
        'usuarios' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', Usuario::class),
        ],
    ],

    'passwords' => [
        'usuarios' => [
            'provider' => 'usuarios',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
