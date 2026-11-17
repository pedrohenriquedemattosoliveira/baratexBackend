<?php

namespace App\Services;

use App\Exceptions\NegocioException;
use App\Models\Oficina;
use App\Models\Servico;
use App\Models\Usuario;

class ServicoService
{
    public function salvar(array $dados, Usuario $usuario): Servico
    {
        if (! empty($dados['oficinaId'])) {
            $oficina = Oficina::find($dados['oficinaId']);

            if (! $oficina) {
                throw new NegocioException("Oficina não encontrada com o ID: {$dados['oficinaId']}");
            }
        } else {
            $oficina = Oficina::where('usuario_id', $usuario->id)->first();

            if (! $oficina) {
                throw new NegocioException('Nenhum cadastro de oficina encontrado para este usuário.');
            }
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
        return Servico::where('oficina_id', $oficinaId)->orderBy('nome')->get();
    }
}
