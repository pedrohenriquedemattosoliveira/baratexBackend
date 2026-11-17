<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GaragemVirtualResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'agendamentoId' => $this->agendamento_id,
            'dataServico' => $this->data_servico?->toDateString(),
            'quilometragemRegistro' => $this->quilometragem_registro,
            'descricaoServico' => $this->descricao_servico,
            'validadoOficina' => $this->validado_oficina,
            'dataValidacao' => $this->data_validacao?->toIso8601String(),

            // Resumo aninhado (sem equivalente na API original): o app
            // mobile precisa exibir qual oficina realizou o serviço.
            'oficina' => $this->whenLoaded('agendamento', fn () => $this->agendamento?->oficina ? [
                'id' => $this->agendamento->oficina->id,
                'nomeFantasia' => $this->agendamento->oficina->nome_fantasia,
                'cnpj' => $this->agendamento->oficina->cnpj,
            ] : null),
            'servicos' => $this->when(
                $this->relationLoaded('agendamento') && $this->agendamento?->relationLoaded('itens'),
                fn () => $this->agendamento->itens->map(fn ($item) => $item->servico?->nome)->filter()->values(),
            ),
        ];
    }
}
