<?php

namespace App\Services;

use App\Models\Avaliacao;
use Illuminate\Validation\ValidationException;

class AvaliacaoService
{
    public function salvar(array $dados): Avaliacao
    {
        if ($dados['nota'] < 1 || $dados['nota'] > 5) {
            throw ValidationException::withMessages([
                'nota' => 'A nota deve estar entre 1 e 5.',
            ]);
        }

        return Avaliacao::create([
            'agendamento_id' => $dados['agendamentoId'],
            'nota' => $dados['nota'],
            'comentario' => $dados['comentario'] ?? null,
        ]);
    }

    public function listarPorOficina(string $oficinaId)
    {
        return Avaliacao::whereHas('agendamento', fn ($q) => $q->where('oficina_id', $oficinaId))->get();
    }
}
