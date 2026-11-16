<?php

namespace App\Services;

use App\Enums\StatusAgendamento;
use App\Exceptions\NegocioException;
use App\Models\Agendamento;
use App\Models\Motorista;
use App\Models\Oficina;
use App\Models\Servico;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

class AgendamentoService
{
    /**
     * Relações sempre carregadas junto do Agendamento, para o
     * AgendamentoResource conseguir montar os resumos de "oficina" e
     * "veiculo" sem N+1 queries.
     */
    private const COM_RELACOES = [
        'itens.servico',
        'oficina',
        'veiculo.modelo.marca',
        'veiculo.motorista.usuario',
    ];

    public function __construct(
        private readonly MensagemChatService $mensagemChatService,
    ) {}

    public function criarAgendamento(array $dados): Agendamento
    {
        return DB::transaction(function () use ($dados) {
            $agendamento = new Agendamento([
                'oficina_id' => $dados['oficinaId'],
                'veiculo_id' => $dados['veiculoId'],
                'data_hora' => $dados['dataHora'],
                'status' => StatusAgendamento::PENDENTE,
                'valor_total' => 0,
            ]);
            $agendamento->save();

            $valorTotal = 0;

            foreach ($dados['itens'] as $itemDados) {
                $precoPraticado = $itemDados['precoPraticado'] ?? null;

                if ($precoPraticado === null) {
                    $servico = Servico::find($itemDados['servicoId']);

                    if (! $servico) {
                        throw new NegocioException("Serviço não encontrado: {$itemDados['servicoId']}");
                    }

                    $precoPraticado = $servico->preco_base;
                }

                $agendamento->itens()->create([
                    'servico_id' => $itemDados['servicoId'],
                    'preco_praticado' => $precoPraticado,
                ]);

                $valorTotal += $precoPraticado;
            }

            $agendamento->valor_total = $valorTotal;
            $agendamento->save();

            return $agendamento->load(self::COM_RELACOES);
        });
    }

    public function buscarPorId(string $id): Agendamento
    {
        $agendamento = Agendamento::with(self::COM_RELACOES)->find($id);

        if (! $agendamento) {
            throw new NegocioException("Agendamento não encontrado com o ID: {$id}");
        }

        return $agendamento;
    }

    public function listarPorOficina(string $oficinaId)
    {
        return Agendamento::with(self::COM_RELACOES)->where('oficina_id', $oficinaId)->get();
    }

    public function listarPorMotorista(string $motoristaId)
    {
        return Agendamento::with(self::COM_RELACOES)
            ->whereHas('veiculo', fn ($q) => $q->where('motorista_id', $motoristaId))
            ->get();
    }

    /**
     * Agendamentos do motorista autenticado — usado por
     * GET /api/agendamentos/minhas (app mobile não conhece o UUID do
     * motorista de antemão).
     */
    public function listarMinhas(Usuario $usuario)
    {
        $motorista = Motorista::where('usuario_id', $usuario->id)->first();

        if (! $motorista) {
            throw new NegocioException('Nenhum cadastro de motorista encontrado para este usuário.');
        }

        return $this->listarPorMotorista($motorista->id);
    }

    /**
     * Agendamentos recebidos pela oficina do usuário autenticado — usado
     * por GET /api/agendamentos/minha-oficina.
     */
    public function listarDaMinhaOficina(Usuario $usuario)
    {
        $oficina = Oficina::where('usuario_id', $usuario->id)->first();

        if (! $oficina) {
            throw new NegocioException('Nenhum cadastro de oficina encontrado para este usuário.');
        }

        return $this->listarPorOficina($oficina->id);
    }

    public function atualizarStatus(string $id, StatusAgendamento $status): Agendamento
    {
        $agendamento = $this->buscarPorId($id);
        $agendamento->status = $status;
        $agendamento->save();

        if (in_array($status, [StatusAgendamento::CONCLUIDO, StatusAgendamento::CANCELADO], true)) {
            $this->mensagemChatService->apagarHistoricoPorAgendamento($id);
        }

        return $agendamento;
    }
}
