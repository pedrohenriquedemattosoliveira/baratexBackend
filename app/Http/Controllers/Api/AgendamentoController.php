<?php

namespace App\Http\Controllers\Api;

use App\Enums\StatusAgendamento;
use App\Http\Controllers\Controller;
use App\Http\Requests\AgendamentoRequest;
use App\Http\Resources\AgendamentoResource;
use App\Services\AgendamentoService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AgendamentoController extends Controller
{
    public function __construct(
        private readonly AgendamentoService $service,
    ) {}

    public function store(AgendamentoRequest $request)
    {
        $agendamento = $this->service->criarAgendamento($request->validated());

        // A API original responde 200 (ResponseEntity.ok) e não 201 aqui;
        // sem forçar o status, o Laravel marcaria 201 automaticamente por
        // detectar Model::$wasRecentlyCreated.
        return (new AgendamentoResource($agendamento))->response()->setStatusCode(200);
    }

    public function show(string $id)
    {
        return new AgendamentoResource($this->service->buscarPorId($id));
    }

    public function porOficina(string $oficinaId)
    {
        return AgendamentoResource::collection($this->service->listarPorOficina($oficinaId));
    }

    public function porMotorista(string $motoristaId)
    {
        return AgendamentoResource::collection($this->service->listarPorMotorista($motoristaId));
    }

    public function minhas(Request $request)
    {
        return AgendamentoResource::collection($this->service->listarMinhas($request->user()));
    }

    public function minhaOficina(Request $request)
    {
        return AgendamentoResource::collection($this->service->listarDaMinhaOficina($request->user()));
    }

    public function atualizarStatus(Request $request, string $id)
    {
        $validado = $request->validate([
            'status' => ['required', Rule::enum(StatusAgendamento::class)],
        ]);

        $status = StatusAgendamento::from($validado['status']);

        return new AgendamentoResource($this->service->atualizarStatus($id, $status));
    }
}
