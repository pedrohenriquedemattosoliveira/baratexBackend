# Chat em tempo real — de STOMP/SockJS para Laravel Reverb

A API original expunha um endpoint WebSocket STOMP em `/ws` (SockJS),
com um destino de aplicação `/app/chat/{agendamentoId}` (mensagem
entrante) e um tópico `/topic/agendamento/{agendamentoId}` (broadcast de
saída). Esse modelo é específico do Spring; o equivalente idiomático em
Laravel é **Broadcasting** (eventos que são transmitidos a um canal, com
Laravel Echo no cliente), por isso a arquitetura muda ligeiramente:

| Original (Spring) | Laravel |
|---|---|
| Conectar via SockJS/STOMP em `/ws` | Conectar via Laravel Echo (pusher-js) ao Reverb |
| Enviar mensagem: STOMP `SEND` para `/app/chat/{id}` | `POST /api/chat` (REST, já existia na API original como alternativa) |
| Receber mensagens: `SUBSCRIBE` em `/topic/agendamento/{id}` | `Echo.channel('agendamento.{id}').listen('MensagemEnviada', cb)` |

Ou seja: **toda mensagem é enviada por REST** (`POST /api/chat`), e o
servidor transmite (`broadcast`) o evento `App\Events\MensagemEnviada`
no canal público `agendamento.{agendamentoId}` — que é consumido em
tempo real por todos os clientes inscritos. O endpoint REST
`GET /api/chat/agendamento/{id}` continua servindo o histórico.

## Subindo o servidor de WebSocket (Reverb)

Em desenvolvimento, `BROADCAST_CONNECTION=log` (padrão do `.env`) é
suficiente para a API funcionar sem infraestrutura extra — os eventos
são apenas registrados no log, sem um servidor WS real.

Para o tempo real de fato:

1. No `.env`, troque:
   ```env
   BROADCAST_CONNECTION=reverb
   ```
   e confira as variáveis `REVERB_*` (já presentes em `.env.example`).
2. Rode o servidor Reverb:
   ```bash
   php artisan reverb:start
   ```
3. No cliente (app mobile/web), instale `laravel-echo` + `pusher-js` e
   conecte:
   ```js
   import Echo from 'laravel-echo';
   import Pusher from 'pusher-js';

   window.Pusher = Pusher;
   const echo = new Echo({
       broadcaster: 'reverb',
       key: import.meta.env.VITE_REVERB_APP_KEY,
       wsHost: import.meta.env.VITE_REVERB_HOST,
       wsPort: import.meta.env.VITE_REVERB_PORT,
       forceTLS: false,
   });

   echo.channel(`agendamento.${agendamentoId}`)
       .listen('MensagemEnviada', (e) => {
           // e.id, e.agendamentoId, e.remetenteId, e.conteudo, e.enviadoEm
       });
   ```

## Autorização do canal

O canal é **público** (`agendamento.{id}`), assim como o tópico STOMP
original não exigia autenticação própria na assinatura. Se quiser
restringir (por exemplo, só o motorista e a oficina do agendamento
podem ouvir), troque para um canal privado e defina a regra em
`routes/channels.php`:

```php
Broadcast::channel('agendamento.{agendamentoId}', function ($usuario, $agendamentoId) {
    // Verifique se $usuario participa do agendamento $agendamentoId
    return true;
});
```
