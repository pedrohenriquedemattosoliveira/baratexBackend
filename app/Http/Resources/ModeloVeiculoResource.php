<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ModeloVeiculoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nomeModelo' => $this->nome_modelo,
            'anoInicio' => $this->ano_inicio,
            'anoFim' => $this->ano_fim,
        ];
    }
}
