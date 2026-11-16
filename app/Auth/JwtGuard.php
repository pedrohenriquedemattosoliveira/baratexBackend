<?php

namespace App\Auth;

use App\Models\Usuario;
use App\Services\JwtService;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;

/**
 * Equivalente ao JwtAuthenticationFilter.java: lê o header
 * "Authorization: Bearer <token>", valida o JWT e resolve o Usuario
 * autenticado a partir do e-mail (subject do token).
 *
 * Diferente da API original — em que `Authentication#getName()` retornava
 * o Usuario.toString() (um bug, já que Usuario não implementa UserDetails)
 * — aqui o usuário autenticado fica disponível como o próprio model
 * Eloquent via `$request->user()`, então `->email` sempre funciona.
 */
class JwtGuard implements Guard
{
    private ?Usuario $usuarioForcado = null;

    /**
     * Memoiza por token (não de forma incondicional): o guard é cacheado
     * pelo AuthManager e pode ser reaproveitado entre requisições — em
     * testes (mesmo container/Application) ou sob Octane/workers de longa
     * duração — então resolver uma única vez e travar o resultado faria o
     * usuário de uma requisição anterior "vazar" para a próxima.
     */
    private ?string $tokenResolvido = null;

    private ?Usuario $usuarioResolvido = null;

    public function __construct(
        private readonly JwtService $jwtService,
        private readonly Application $app,
    ) {}

    /**
     * Resolve a requisição atual a cada chamada em vez de capturá-la uma
     * única vez no construtor: o guard é cacheado pelo AuthManager
     * (Illuminate\Auth\AuthManager::guard() memoiza por nome) e sobrevive
     * a várias requisições simuladas dentro do mesmo container — testes
     * de feature e workers (Octane) incluídos. Guardar a instância de
     * Request no construtor faria o guard sempre ler o Authorization
     * header da PRIMEIRA requisição.
     */
    private function request(): Request
    {
        return $this->app->make('request');
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function guest(): bool
    {
        return ! $this->check();
    }

    public function user()
    {
        if ($this->usuarioForcado !== null) {
            return $this->usuarioForcado;
        }

        $token = $this->obterTokenDaRequisicao();

        if ($token === null) {
            return null;
        }

        if ($token === $this->tokenResolvido) {
            return $this->usuarioResolvido;
        }

        $usuario = null;

        if ($this->jwtService->validarToken($token)) {
            $email = $this->jwtService->obterEmailDoToken($token);

            if ($email) {
                $usuario = Usuario::query()->whereRaw('LOWER(email) = LOWER(?)', [$email])->first();
            }
        }

        $this->tokenResolvido = $token;
        $this->usuarioResolvido = $usuario;

        return $usuario;
    }

    public function id()
    {
        return $this->user()?->id;
    }

    public function validate(array $credentials = []): bool
    {
        return false;
    }

    public function hasUser(): bool
    {
        return $this->user() !== null;
    }

    public function setUser($user)
    {
        $this->usuarioForcado = $user;

        return $this;
    }

    private function obterTokenDaRequisicao(): ?string
    {
        $header = $this->request()->header('Authorization');

        if ($header && str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }

        return null;
    }
}
