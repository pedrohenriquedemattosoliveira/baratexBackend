<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MensagemChat extends Model
{
    use HasUuids;

    const CREATED_AT = 'enviado_em';

    const UPDATED_AT = null;

    protected $table = 'mensagens_chat';

    protected $fillable = [
        'agendamento_id',
        'remetente_id',
        'conteudo',
    ];

    public function agendamento(): BelongsTo
    {
        return $this->belongsTo(Agendamento::class, 'agendamento_id');
    }

    public function remetente(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'remetente_id');
    }
}
