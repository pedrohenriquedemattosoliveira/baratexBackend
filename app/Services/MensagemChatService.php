<?php

namespace App\Services;

use App\Events\MensagemEnviada;
use App\Models\MensagemChat;

class MensagemChatService
{
    public function enviarMensagem(array $dados): MensagemChat
    {
        $mensagem = MensagemChat::create([
            'agendamento_id' => $dados['agendamentoId'],
            'remetente_id' => $dados['remetenteId'],
            'conteudo' => $dados['conteudo'],
        ]);

        // Substitui o push via STOMP ("/topic/agendamento/{id}") do
        // ChatWebSocketController.java: o envio por REST já notifica,
        // em tempo real, todos os clientes inscritos no canal via Echo.
        broadcast(new MensagemEnviada($mensagem));

        return $mensagem;
    }

    public function listarPorAgendamento(string $agendamentoId)
    {
        return MensagemChat::where('agendamento_id', $agendamentoId)
            ->orderBy('enviado_em')
            ->get();
    }

    public function apagarHistoricoPorAgendamento(string $agendamentoId): void
    {
        MensagemChat::where('agendamento_id', $agendamentoId)->delete();
    }
}
