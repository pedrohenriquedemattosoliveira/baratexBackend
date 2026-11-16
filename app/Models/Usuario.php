<?php

namespace App\Models;

use App\Enums\TipoUsuario;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Usuario extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $table = 'usuarios';

    protected $fillable = [
        'nome',
        'email',
        'senha_hash',
        'telefone',
        'tipo_usuario',
    ];

    protected $hidden = [
        'senha_hash',
    ];

    protected $casts = [
        'tipo_usuario' => TipoUsuario::class,
    ];

    public function motorista(): HasOne
    {
        return $this->hasOne(Motorista::class, 'usuario_id');
    }

    public function oficina(): HasOne
    {
        return $this->hasOne(Oficina::class, 'usuario_id');
    }
}
