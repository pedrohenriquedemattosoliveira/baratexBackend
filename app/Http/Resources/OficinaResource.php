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
            'cep' => $this->endereco?->cep,
            'latitude' => $this->endereco?->latitude,
            'longitude' => $this->endereco?->longitude,

            // Agregados das avaliações (sem equivalente na API original).
            'notaMedia' => $this->avaliacoes_avg_nota !== null ? round((float) $this->avaliacoes_avg_nota, 1) : null,
            'totalAvaliacoes' => (int) ($this->avaliacoes_count ?? 0),

            // Só vem preenchido em GET /api/oficinas/proximas.
            'distanciaKm' => $this->distancia_km,
        ];
    }
}
