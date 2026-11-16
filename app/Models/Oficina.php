<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Oficina extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $table = 'oficinas';

    protected $fillable = [
        'razao_social',
        'nome_fantasia',
        'cnpj',
        'usuario_id',
        'endereco_id',
    ];

    protected static function booted(): void
    {
        // Espelha o CascadeType.ALL do relacionamento OneToOne em Oficina.java:
        // remover a oficina também remove o usuário e o endereço associados.
        static::deleting(function (Oficina $oficina) {
            $oficina->usuario()->delete();
            $oficina->endereco()->delete();
        });
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function endereco(): BelongsTo
    {
        return $this->belongsTo(Endereco::class, 'endereco_id');
    }

    public function servicos(): HasMany
    {
        return $this->hasMany(Servico::class, 'oficina_id');
    }

    public function agendamentos(): HasMany
    {
        return $this->hasMany(Agendamento::class, 'oficina_id');
    }
}
