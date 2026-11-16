<?php

use App\Http\Controllers\Api\AgendamentoController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AvaliacaoController;
use App\Http\Controllers\Api\GaragemVirtualController;
use App\Http\Controllers\Api\MensagemChatController;
use App\Http\Controllers\Api\ModeloVeiculoController;
use App\Http\Controllers\Api\MotoristaController;
use App\Http\Controllers\Api\OficinaController;
use App\Http\Controllers\Api\ServicoController;
use App\Http\Controllers\Api\VeiculoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rotas públicas
|--------------------------------------------------------------------------
|
| Equivalente aos requestMatchers com permitAll() em SecurityConfig.java:
| /api/auth/**, GET /api/oficinas/**, GET /api/servicos/** e
| GET /api/modelos-veiculos/**.
|
*/

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

Route::get('/oficinas', [OficinaController::class, 'index']);
Route::get('/oficinas/proximas', [OficinaController::class, 'proximas']);

// IMPORTANTE: "/oficinas/me" precisa ser registrada ANTES de
// "/oficinas/{id}" — senão o Laravel casaria "me" como se fosse um
// {id} da rota pública. Mesma lógica vale para /motoristas/me e
// /agendamentos/minhas|minha-oficina mais abaixo.
Route::middleware('auth:api')->get('/oficinas/me', [OficinaController::class, 'me']);

Route::get('/oficinas/{id}', [OficinaController::class, 'show']);

Route::get('/servicos/oficina/{oficinaId}', [ServicoController::class, 'porOficina']);

Route::prefix('modelos-veiculos')->group(function () {
    Route::get('/marcas', [ModeloVeiculoController::class, 'marcas']);
    Route::get('/modelos', [ModeloVeiculoController::class, 'modelos']);
    Route::get('/anos', [ModeloVeiculoController::class, 'anos']);
});

/*
|--------------------------------------------------------------------------
| Rotas protegidas (JWT obrigatório)
|--------------------------------------------------------------------------
|
| Todo o restante cai em `anyRequest().authenticated()` na API original.
| Os endpoints "/me", "/minhas" e "/minha-oficina" não existiam na API
| original — foram adicionados para o app mobile conseguir resolver os
| próprios dados (motorista/oficina/agendamentos) sem precisar conhecer
| o UUID de antemão, no mesmo espírito self-scoped de "/api/veiculos".
|
*/

Route::middleware('auth:api')->group(function () {
    Route::post('/oficinas', [OficinaController::class, 'store']);

    Route::post('/servicos', [ServicoController::class, 'store']);

    Route::post('/veiculos', [VeiculoController::class, 'store']);
    Route::get('/veiculos', [VeiculoController::class, 'index']);

    Route::post('/garagem-virtual', [GaragemVirtualController::class, 'store']);
    Route::get('/garagem-virtual/veiculo/{veiculoId}', [GaragemVirtualController::class, 'porVeiculo']);

    Route::prefix('agendamentos')->group(function () {
        Route::post('/', [AgendamentoController::class, 'store']);
        Route::get('/minhas', [AgendamentoController::class, 'minhas']);
        Route::get('/minha-oficina', [AgendamentoController::class, 'minhaOficina']);
        Route::get('/oficina/{oficinaId}', [AgendamentoController::class, 'porOficina']);
        Route::get('/motorista/{motoristaId}', [AgendamentoController::class, 'porMotorista']);
        Route::get('/{id}', [AgendamentoController::class, 'show']);
        Route::patch('/{id}/status', [AgendamentoController::class, 'atualizarStatus']);
    });

    Route::prefix('avaliacoes')->group(function () {
        Route::post('/', [AvaliacaoController::class, 'store']);
        Route::get('/oficina/{oficinaId}', [AvaliacaoController::class, 'porOficina']);
    });

    Route::prefix('chat')->group(function () {
        Route::post('/', [MensagemChatController::class, 'store']);
        Route::get('/agendamento/{agendamentoId}', [MensagemChatController::class, 'porAgendamento']);
    });

    Route::prefix('motoristas')->group(function () {
        Route::post('/', [MotoristaController::class, 'store']);
        Route::get('/me', [MotoristaController::class, 'me']);
        Route::get('/{id}', [MotoristaController::class, 'show']);
    });
});
