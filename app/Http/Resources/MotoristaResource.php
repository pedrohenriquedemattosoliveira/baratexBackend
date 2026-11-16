<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Equivalente a MotoristaResponseDTO.java.
 */
class MotoristaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cpf' => $this->cpf,
            'nome' => $this->usuario->nome,
            'email' => $this->usuario->email,
            'telefone' => $this->usuario->telefone,
        ];
    }
}
