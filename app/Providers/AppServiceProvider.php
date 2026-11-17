<?php

namespace App\Providers;

use App\Auth\JwtGuard;
use App\Services\JwtService;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(JwtService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // A API original (Spring/Jackson) serializa os objetos diretamente,
        // sem envelope. Desativa o wrapper padrão {"data": ...} dos
        // JsonResource do Laravel para manter o mesmo formato de resposta.
        JsonResource::withoutWrapping();

        // Todos os IDs são UUID. No PostgreSQL as colunas são do tipo
        // "uuid" nativo, e comparar com um texto qualquer
        // (ex: GET /api/oficinas/abc) faria o banco lançar erro de sintaxe
        // (500). Restringindo o formato aqui, a rota simplesmente não
        // casa e a API responde 404.
        foreach (['id', 'oficinaId', 'motoristaId', 'veiculoId', 'agendamentoId'] as $parametro) {
            Route::pattern($parametro, '[0-9a-fA-F]{8}(-[0-9a-fA-F]{4}){3}-[0-9a-fA-F]{12}');
        }

        Auth::extend('jwt', function ($app, string $name, array $config) {
            return new JwtGuard($app->make(JwtService::class), $app);
        });
    }
}
