<?php

namespace App\Models;

use App\Enums\StatusAgendamento;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Agendamento extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $table = 'agendamentos';

    protected $fillable = [
        'oficina_id',
        'veiculo_id',
        'data_hora',
        'status',
        'valor_total',
    ];

    protected static function booted(): void
    {
        // Espelha o cascade=ALL + orphanRemoval=true de Agendamento.java:
        // apagar o agendamento apaga também seus itens.
        static::deleting(function (Agendamento $agendamento) {
            $agendamento->itens()->delete();
        });
    }

    protected function casts(): array
    {
        return [
            'data_hora' => 'datetime',
            'status' => StatusAgendamento::class,
            'valor_total' => 'decimal:2',
        ];
    }

    public function oficina(): BelongsTo
    {
        return $this->belongsTo(Oficina::class, 'oficina_id');
    }

    public function veiculo(): BelongsTo
    {
        return $this->belongsTo(Veiculo::class, 'veiculo_id');
    }

    public function itens(): HasMany
    {
        return $this->hasMany(ItemAgendamento::class, 'agendamento_id');
    }

    public function avaliacao(): HasOne
    {
        return $this->hasOne(Avaliacao::class, 'agendamento_id');
    }

    public function mensagens(): HasMany
    {
        return $this->hasMany(MensagemChat::class, 'agendamento_id');
    }

    public function registrosGaragem(): HasMany
    {
        return $this->hasMany(GaragemVirtual::class, 'agendamento_id');
    }
}
