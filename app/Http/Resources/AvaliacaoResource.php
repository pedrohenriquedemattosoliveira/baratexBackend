<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AvaliacaoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'agendamentoId' => $this->agendamento_id,
            'nota' => $this->nota,
            'comentario' => $this->comentario,
            'criadoEm' => $this->criado_em?->toIso8601String(),
        ];
    }
}
