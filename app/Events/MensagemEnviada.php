<?php

namespace App\Events;

use App\Models\MensagemChat;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Equivalente ao ChatWebSocketController.java: quando uma mensagem de chat
 * é criada, é transmitida no canal "agendamento.{agendamentoId}" — o mesmo
 * destino lógico do STOMP "/topic/agendamento/{id}" da API original.
 *
 * No cliente, troque o STOMP/SockJS por Laravel Echo:
 *   Echo.channel(`agendamento.${agendamentoId}`).listen('MensagemEnviada', (e) => { ... })
 */
class MensagemEnviada implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public MensagemChat $mensagem,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('agendamento.'.$this->mensagem->agendamento_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'MensagemEnviada';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->mensagem->id,
            'agendamentoId' => $this->mensagem->agendamento_id,
            'remetenteId' => $this->mensagem->remetente_id,
            'conteudo' => $this->mensagem->conteudo,
            'enviadoEm' => $this->mensagem->enviado_em?->toIso8601String(),
        ];
    }
}
