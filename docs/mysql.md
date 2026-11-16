# MySQL / XAMPP — banco de dados e busca por proximidade

A API original usava PostgreSQL + PostGIS (`ST_DWithin`) para a busca de
oficinas próximas. Este porte usa **MySQL/MariaDB puro** (compatível com
o MySQL que vem no XAMPP) — sem nenhuma extensão espacial — substituindo
o PostGIS pela **fórmula de Haversine calculada em SQL puro**.

## 1. Configuração

No XAMPP, suba o Apache + MySQL pelo painel de controle e crie o banco
(via phpMyAdmin ou linha de comando):

```sql
CREATE DATABASE baratex_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

No `.env` (valores padrão do XAMPP — ajuste se o seu usuário/senha do
MySQL forem diferentes):

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=baratex_db
DB_USERNAME=root
DB_PASSWORD=
```

Depois:

```bash
php artisan migrate
```

## 2. Extensão PHP

As extensões `pdo_mysql` e `mysqli` precisam estar habilitadas no
`php.ini` (já configuradas neste ambiente de desenvolvimento). Confirme
com:

```bash
php -m | grep -i mysql
```

## 3. Busca de oficinas próximas (`GET /api/oficinas/proximas`)

Em vez de `ST_DWithin` (PostGIS), a consulta em
`App\Services\OficinaService::buscarProximos()` calcula a distância em
km com a fórmula de Haversine, usando só as colunas `latitude` e
`longitude` de `enderecos`:

```sql
SELECT o.* FROM oficinas o
JOIN enderecos e ON o.endereco_id = e.id
WHERE e.latitude IS NOT NULL AND e.longitude IS NOT NULL
  AND 6371 * ACOS(LEAST(1,
        COS(RADIANS(:lat)) * COS(RADIANS(e.latitude)) * COS(RADIANS(e.longitude) - RADIANS(:lng))
        + SIN(RADIANS(:lat)) * SIN(RADIANS(e.latitude))
      )) <= :raioKm
```

Isso funciona em qualquer instalação de MySQL/MariaDB (inclusive no
XAMPP), sem precisar habilitar nenhuma extensão espacial. O índice
`(latitude, longitude)` criado na migration de `enderecos` ajuda a
filtrar os candidatos antes do cálculo, mas o cálculo exato de
distância é sempre feito em memória pela fórmula — ok para o volume de
oficinas de uma aplicação desse porte; se a tabela crescer muito, vale
revisitar com uma extensão espacial de verdade.

## 4. Testando

```bash
curl "http://localhost:8000/api/oficinas/proximas?latitude=-23.55&longitude=-46.63&raioKm=10"
```
