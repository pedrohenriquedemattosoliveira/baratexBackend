<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Motorista extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $table = 'motoristas';

    protected $fillable = [
        'cpf',
        'usuario_id',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function veiculos(): HasMany
    {
        return $this->hasMany(Veiculo::class, 'motorista_id');
    }
}
