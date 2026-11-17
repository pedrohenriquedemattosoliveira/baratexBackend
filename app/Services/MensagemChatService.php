<?php

namespace App\Services;

use App\Events\MensagemEnviada;
use App\Models\MensagemChat;
use App\Models\Usuario;

class MensagemChatService
{
    public function enviarMensagem(array $dados, Usuario $remetente): MensagemChat
    {
        $mensagem = MensagemChat::create([
            'agendamento_id' => $dados['agendamentoId'],
            'remetente_id' => $remetente->id,
            'conteudo' => $dados['conteudo'],
        ]);

        $mensagem->setRelation('remetente', $remetente);

        // Substitui o push via STOMP ("/topic/agendamento/{id}") do
        // ChatWebSocketController.java: o envio por REST já notifica,
        // em tempo real, todos os clientes inscritos no canal via Echo.
        broadcast(new MensagemEnviada($mensagem));

        return $mensagem;
    }

    public function listarPorAgendamento(string $agendamentoId)
    {
        return MensagemChat::with('remetente')
            ->where('agendamento_id', $agendamentoId)
            ->orderBy('enviado_em')
            ->get();
    }

    public function apagarHistoricoPorAgendamento(string $agendamentoId): void
    {
        MensagemChat::where('agendamento_id', $agendamentoId)->delete();
    }
}
