<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Equivalente a OficinaResponseDTO.java.
 */
class OficinaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'razaoSocial' => $this->razao_social,
            'nomeFantasia' => $this->nome_fantasia,
            'cnpj' => $this->cnpj,
            'nomeUsuario' => $this->usuario?->nome,
            'email' => $this->usuario?->email,
            'telefone' => $this->usuario?->telefone,
            'logradouro' => $this->endereco?->logradouro,
            'numero' => $this->endereco?->numero,
            'bairro' => $this->endereco?->bairro,
            'cidade' => $this->endereco?->cidade,
            'estado' => $this->endereco?->estado,
            'latitude' => $this->endereco?->latitude,
            'longitude' => $this->endereco?->longitude,
        ];
    }
}
