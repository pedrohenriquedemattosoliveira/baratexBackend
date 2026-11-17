<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * PostGIS: adiciona em "enderecos" a coluna geográfica "localizacao"
 * (o mesmo nome da entidade Endereco.java original) e um índice GiST,
 * usados por GET /api/oficinas/proximas (ST_DWithin / ST_Distance).
 *
 * A coluna é GERADA pelo próprio Postgres a partir de latitude/longitude
 * — a aplicação nunca escreve nela, então não existe o risco de ficar
 * dessincronizada (o bug da API original, em que "localizacao" nunca
 * era preenchida e a busca por proximidade sempre voltava vazia).
 *
 * Só roda em PostgreSQL; em outros drivers (SQLite dos testes) é no-op.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('CREATE EXTENSION IF NOT EXISTS postgis');

        DB::statement(<<<'SQL'
            ALTER TABLE enderecos
            ADD COLUMN localizacao geography(Point, 4326)
            GENERATED ALWAYS AS (
                ST_SetSRID(ST_MakePoint(longitude, latitude), 4326)::geography
            ) STORED
        SQL);

        DB::statement('CREATE INDEX enderecos_localizacao_gist ON enderecos USING GIST (localizacao)');
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('DROP INDEX IF EXISTS enderecos_localizacao_gist');
        DB::statement('ALTER TABLE enderecos DROP COLUMN IF EXISTS localizacao');
    }
};
