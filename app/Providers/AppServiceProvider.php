<?php

namespace App\Providers;

use App\Auth\JwtGuard;
use App\Services\JwtService;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
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

        Auth::extend('jwt', function ($app, string $name, array $config) {
            return new JwtGuard($app->make(JwtService::class), $app);
        });
    }
}
