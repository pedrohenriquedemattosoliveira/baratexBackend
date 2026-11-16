<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GaragemVirtual extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $table = 'garagem_virtual';

    protected $fillable = [
        'agendamento_id',
        'data_servico',
        'quilometragem_registro',
        'descricao_servico',
        'validado_oficina',
        'data_validacao',
    ];

    protected function casts(): array
    {
        return [
            'data_servico' => 'date',
            'validado_oficina' => 'boolean',
            'data_validacao' => 'datetime',
        ];
    }

    public function agendamento(): BelongsTo
    {
        return $this->belongsTo(Agendamento::class, 'agendamento_id');
    }
}
