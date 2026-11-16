<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Documentação (equivalente ao /swagger-ui.html do springdoc na API
| original)
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => redirect('/docs'));

Route::get('/docs', fn () => view('docs'));

Route::get('/openapi.yaml', function () {
    return response()
        ->file(resource_path('docs/openapi.yaml'), ['Content-Type' => 'application/yaml']);
});
