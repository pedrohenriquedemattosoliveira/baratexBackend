<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Formato de erro padrão da API, equivalente ao record ErroResposta /
 * GlobalExceptionHandler.java.
 */
class ErroResposta
{
    public static function responder(
        Request $request,
        int $status,
        string $erro,
        ?string $mensagem,
        ?array $errosValidacao = null,
    ): JsonResponse {
        return response()->json([
            'timestamp' => now()->toIso8601String(),
            'status' => $status,
            'erro' => $erro,
            'mensagem' => $mensagem,
            'path' => $request->path(),
            'errosValidacao' => $errosValidacao,
        ], $status);
    }
}
