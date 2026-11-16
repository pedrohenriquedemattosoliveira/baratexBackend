<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Servico extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $table = 'servicos';

    protected $fillable = [
        'oficina_id',
        'nome',
        'descricao',
        'preco_base',
        'tempo_estimado_minutos',
    ];

    protected function casts(): array
    {
        return [
            'preco_base' => 'decimal:2',
        ];
    }

    public function oficina(): BelongsTo
    {
        return $this->belongsTo(Oficina::class, 'oficina_id');
    }
}
