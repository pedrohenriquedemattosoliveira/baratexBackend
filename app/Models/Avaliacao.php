<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Avaliacao extends Model
{
    use HasUuids;

    const CREATED_AT = 'criado_em';

    const UPDATED_AT = null;

    protected $table = 'avaliacoes';

    protected $fillable = [
        'agendamento_id',
        'nota',
        'comentario',
    ];

    public function agendamento(): BelongsTo
    {
        return $this->belongsTo(Agendamento::class, 'agendamento_id');
    }
}
