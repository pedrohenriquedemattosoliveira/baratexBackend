# PostgreSQL + PostGIS

## Configuração

Banco com a extensão PostGIS disponível (imagem Docker `postgis/postgis:16-3.4`,
pacote `postgresql-16-postgis-3`, ou provedores gerenciados como Neon/Supabase/Render/RDS).

```sql
CREATE DATABASE baratex_db;
```

No `.env`: `DB_CONNECTION=pgsql` e `DB_HOST/DB_PORT/DB_DATABASE/DB_USERNAME/DB_PASSWORD`
(ou apenas `DB_URL=postgresql://...`). Em bancos gerenciados use `DB_SSLMODE=require`.

```bash
php artisan migrate --force
php artisan db:seed --force
```

A migration `add_localizacao_postgis_to_enderecos` executa
`CREATE EXTENSION IF NOT EXISTS postgis` (o usuário precisa de permissão; caso
contrário habilite a extensão antes, como superusuário) e cria a coluna
`enderecos.localizacao geography(Point,4326)`, **gerada** a partir de
`latitude`/`longitude`, com índice GiST.

## Busca por proximidade

`GET /api/oficinas/proximas?latitude=..&longitude=..&raioKm=10` usa
`ST_DWithin` + `ST_Distance` e devolve as oficinas ordenadas por distância,
com `distanciaKm` em cada item.

## Testes

A suíte roda em SQLite em memória (`phpunit.xml`); só a busca por proximidade
exige Postgres+PostGIS e por isso não é coberta ali.
