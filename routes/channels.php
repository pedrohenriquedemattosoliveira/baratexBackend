<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Canais de Broadcast
|--------------------------------------------------------------------------
|
| O chat (MensagemEnviada) é transmitido no canal público
| "agendamento.{agendamentoId}", espelhando o destino STOMP
| "/topic/agendamento/{id}" da API original — por isso não exige
| autorização aqui. Caso deseje restringir o acesso (ex: apenas o
| motorista e a oficina do agendamento), troque para um canal privado
| e defina a regra de autorização abaixo.
|
| Broadcast::channel('agendamento.{agendamentoId}', function ($user, $agendamentoId) {
|     return true;
| });
|
*/
