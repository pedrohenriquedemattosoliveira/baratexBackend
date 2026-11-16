<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemAgendamento extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $table = 'itens_agendamento';

    protected $fillable = [
        'agendamento_id',
        'servico_id',
        'preco_praticado',
    ];

    protected function casts(): array
    {
        return [
            'preco_praticado' => 'decimal:2',
        ];
    }

    public function agendamento(): BelongsTo
    {
        return $this->belongsTo(Agendamento::class, 'agendamento_id');
    }

    public function servico(): BelongsTo
    {
        return $this->belongsTo(Servico::class, 'servico_id');
    }
}
