<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Marca extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $table = 'marcas';

    protected $fillable = ['nome'];

    public function modelos(): HasMany
    {
        return $this->hasMany(ModeloVeiculo::class, 'marca_id');
    }
}
