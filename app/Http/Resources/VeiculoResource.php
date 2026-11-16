<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Equivalente a VeiculoResponseDTO.java.
 */
class VeiculoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'placa' => $this->placa,
            'marca' => $this->modelo->marca->nome,
            'modelo' => $this->modelo->nome_modelo,
            'ano' => $this->ano,
            'cor' => $this->cor,
        ];
    }
}
