<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServicoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'oficinaId' => $this->oficina_id,
            'nome' => $this->nome,
            'descricao' => $this->descricao,
            'precoBase' => $this->preco_base,
            'tempoEstimadoMinutos' => $this->tempo_estimado_minutos,
        ];
    }
}
