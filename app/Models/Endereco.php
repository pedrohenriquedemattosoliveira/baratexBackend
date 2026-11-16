<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Endereco extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $table = 'enderecos';

    protected $fillable = [
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
        'cep',
        'latitude',
        'longitude',
    ];

    /**
     * Representação GeoJSON do ponto, equivalente ao
     * `getLocalizacaoGeoJson()` do Endereco.java original. Calculada
     * puramente a partir de latitude/longitude — não há coluna de
     * geometria no banco (MySQL/MariaDB puro, sem extensão espacial);
     * a busca por proximidade usa a fórmula de Haversine em
     * App\Services\OficinaService::buscarProximos().
     */
    protected function localizacao(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->latitude === null || $this->longitude === null) {
                    return null;
                }

                return [
                    'type' => 'Point',
                    'coordinates' => [(float) $this->longitude, (float) $this->latitude],
                ];
            },
        )->shouldCache();
    }

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    protected $appends = ['localizacao'];
}
