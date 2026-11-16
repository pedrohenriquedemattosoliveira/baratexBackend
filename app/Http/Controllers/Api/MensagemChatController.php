<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MensagemChatRequest;
use App\Http\Resources\MensagemChatResource;
use App\Services\MensagemChatService;

class MensagemChatController extends Controller
{
    public function __construct(
        private readonly MensagemChatService $service,
    ) {}

    public function store(MensagemChatRequest $request)
    {
        return (new MensagemChatResource($this->service->enviarMensagem($request->validated())))
            ->response()
            ->setStatusCode(200);
    }

    public function porAgendamento(string $agendamentoId)
    {
        return MensagemChatResource::collection($this->service->listarPorAgendamento($agendamentoId));
    }
}
