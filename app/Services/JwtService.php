<?php

namespace App\Services;

use App\Models\Usuario;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use RuntimeException;
use UnexpectedValueException;

/**
 * Equivalente a com.baratex.api.security.JwtTokenProvider: gera e valida
 * tokens JWT assinados com HMAC-SHA256, usando o e-mail do usuário como
 * subject e os claims "id" e "tipo".
 */
class JwtService
{
    private string $secret;

    private int $ttlMinutes;

    public function __construct()
    {
        $this->secret = (string) config('jwt.secret');

        if (strlen($this->secret) < 32) {
            throw new RuntimeException('JWT_SECRET ausente ou curto demais: defina no ambiente um segredo com pelo menos 32 caracteres.');
        }
        $this->ttlMinutes = (int) config('jwt.ttl_minutes');
    }

    public function gerarToken(Usuario $usuario): string
    {
        $agora = time();

        $payload = [
            'sub' => $usuario->email,
            'id' => $usuario->id,
            'tipo' => $usuario->tipo_usuario->value,
            'iat' => $agora,
            'exp' => $agora + ($this->ttlMinutes * 60),
        ];

        return JWT::encode($payload, $this->secret, config('jwt.algo'));
    }

    public function validarToken(string $token): bool
    {
        try {
            JWT::decode($token, new Key($this->secret, config('jwt.algo')));

            return true;
        } catch (ExpiredException|SignatureInvalidException|UnexpectedValueException $e) {
            return false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function obterEmailDoToken(string $token): ?string
    {
        try {
            $claims = JWT::decode($token, new Key($this->secret, config('jwt.algo')));

            return $claims->sub ?? null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
