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
     * `getLocalizacaoGeoJson()` do Endereco.java original, calculada a
     * partir de latitude/longitude.
     *
     * No PostgreSQL existe também a coluna física "localizacao"
     * (geography, gerada pelo banco — ver a migration
     * add_localizacao_postgis_to_enderecos), usada só nas consultas
     * espaciais de App\Services\OficinaService::buscarProximos(). Este
     * accessor tem precedência sobre o valor bruto (WKB) dela.
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
