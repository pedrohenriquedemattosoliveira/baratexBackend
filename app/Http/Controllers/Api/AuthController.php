<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    public function login(LoginRequest $request)
    {
        $dados = $request->validated();

        $resposta = $this->authService->login($dados['email'], $dados['senha']);

        return response()->json($resposta);
    }

    public function register(RegisterRequest $request)
    {
        $resultado = $this->authService->registrar($request->validated());

        if (is_string($resultado)) {
            return response()->json($resultado, 400);
        }

        return response()->json('Usuário cadastrado com sucesso!', 201);
    }
}
