<?php

namespace App\Services;

use App\Models\GaragemVirtual;
use Carbon\Carbon;

class GaragemVirtualService
{
    public function registrar(array $dados): GaragemVirtual
    {
        $validadoOficina = $dados['validadoOficina'] ?? false;

        return GaragemVirtual::create([
            'agendamento_id' => $dados['agendamentoId'],
            'data_servico' => $dados['dataServico'],
            'quilometragem_registro' => $dados['quilometragemRegistro'],
            'descricao_servico' => $dados['descricaoServico'],
            'validado_oficina' => $validadoOficina,
            'data_validacao' => $validadoOficina ? Carbon::now() : null,
        ]);
    }

    public function listarPorVeiculo(string $veiculoId)
    {
        return GaragemVirtual::with('agendamento.oficina')
            ->whereHas('agendamento', fn ($q) => $q->where('veiculo_id', $veiculoId))
            ->orderByDesc('data_servico')
            ->get();
    }
}
