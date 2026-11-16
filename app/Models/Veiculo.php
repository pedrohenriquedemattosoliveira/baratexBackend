<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Veiculo extends Model
{
    use HasUuids;

    public $timestamps = false;

    /**
     * Mantido como "tb_veiculo" para preservar o nome de tabela da
     * API original (Veiculo.java usa @Table(name = "tb_veiculo")).
     */
    protected $table = 'tb_veiculo';

    protected $fillable = [
        'placa',
        'ano',
        'cor',
        'modelo_id',
        'motorista_id',
    ];

    public function modelo(): BelongsTo
    {
        return $this->belongsTo(ModeloVeiculo::class, 'modelo_id');
    }

    public function motorista(): BelongsTo
    {
        return $this->belongsTo(Motorista::class, 'motorista_id');
    }

    public function agendamentos(): HasMany
    {
        return $this->hasMany(Agendamento::class, 'veiculo_id');
    }
}
