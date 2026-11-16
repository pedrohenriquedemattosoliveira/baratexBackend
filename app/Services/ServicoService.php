<?php

namespace App\Services;

use App\Exceptions\NegocioException;
use App\Models\Oficina;
use App\Models\Servico;

class ServicoService
{
    public function salvar(array $dados): Servico
    {
        $oficina = Oficina::find($dados['oficinaId']);

        if (! $oficina) {
            throw new NegocioException("Oficina não encontrada com o ID: {$dados['oficinaId']}");
        }

        return Servico::create([
            'oficina_id' => $oficina->id,
            'nome' => $dados['nome'],
            'descricao' => $dados['descricao'] ?? null,
            'preco_base' => $dados['preco'],
            'tempo_estimado_minutos' => $dados['tempoEstimadoMinutos'] ?? null,
        ]);
    }

    public function listarPorOficina(string $oficinaId)
    {
        return Servico::where('oficina_id', $oficinaId)->get();
    }
}
