<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MensagemChatResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'agendamentoId' => $this->agendamento_id,
            'remetenteId' => $this->remetente_id,
            'conteudo' => $this->conteudo,
            'enviadoEm' => $this->enviado_em?->toIso8601String(),
        ];
    }
}
