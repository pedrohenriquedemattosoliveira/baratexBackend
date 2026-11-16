<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModeloVeiculo extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $table = 'modelos_veiculos';

    protected $fillable = [
        'nome_modelo',
        'ano_inicio',
        'ano_fim',
        'marca_id',
    ];

    public function marca(): BelongsTo
    {
        return $this->belongsTo(Marca::class, 'marca_id');
    }

    public function veiculos(): HasMany
    {
        return $this->hasMany(Veiculo::class, 'modelo_id');
    }
}
