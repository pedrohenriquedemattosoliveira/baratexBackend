<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AgendamentoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'oficinaId' => $this->oficina_id,
            'veiculoId' => $this->veiculo_id,
            'dataHora' => $this->data_hora?->toIso8601String(),
            'status' => $this->status?->value,
            'valorTotal' => $this->valor_total,
            'itens' => $this->whenLoaded('itens', fn () => $this->itens->map(fn ($item) => [
                'id' => $item->id,
                'servicoId' => $item->servico_id,
                'nome' => $item->relationLoaded('servico') ? $item->servico?->nome : null,
                'precoPraticado' => $item->preco_praticado,
            ])),

            // Resumos aninhados (sem equivalente na API original, que
            // serializava a entidade JPA completa): o app mobile precisa
            // exibir "qual oficina" / "qual veículo" nas listas de
            // agendamento sem um round-trip extra por item.
            'oficina' => $this->whenLoaded('oficina', fn () => [
                'id' => $this->oficina->id,
                'nomeFantasia' => $this->oficina->nome_fantasia,
                'telefone' => $this->oficina->relationLoaded('usuario') ? $this->oficina->usuario?->telefone : null,
            ]),
            'veiculo' => $this->whenLoaded('veiculo', fn () => [
                'id' => $this->veiculo->id,
                'placa' => $this->veiculo->placa,
                'ano' => $this->veiculo->ano,
                'marca' => $this->veiculo->relationLoaded('modelo') ? $this->veiculo->modelo->marca->nome : null,
                'modelo' => $this->veiculo->relationLoaded('modelo') ? $this->veiculo->modelo->nome_modelo : null,
                'motoristaNome' => $this->veiculo->relationLoaded('motorista') ? $this->veiculo->motorista->usuario->nome : null,
                'motoristaTelefone' => $this->veiculo->relationLoaded('motorista') ? $this->veiculo->motorista->usuario->telefone : null,
            ]),

            // Permitem ao app saber se ainda cabe "Avaliar" (motorista) e
            // "Registrar na Garagem Virtual" (oficina) neste agendamento.
            'avaliacao' => $this->whenLoaded('avaliacao', fn () => $this->avaliacao ? [
                'id' => $this->avaliacao->id,
                'nota' => $this->avaliacao->nota,
                'comentario' => $this->avaliacao->comentario,
            ] : null),
            'registradoNaGaragem' => $this->whenLoaded('registrosGaragem', fn () => $this->registrosGaragem->isNotEmpty()),
        ];
    }
}
