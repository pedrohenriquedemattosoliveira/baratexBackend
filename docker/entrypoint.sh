#!/bin/sh
set -e

cd /var/www/html

# Gera um APP_KEY temporário se nenhum foi informado.
# (Em produção, defina APP_KEY fixo no .env.)
if [ -z "$APP_KEY" ]; then
    export APP_KEY="$(php artisan key:generate --show)"
    echo "[entrypoint] APP_KEY não definido: gerado um temporário."
fi

# Só o container "app" roda migrations/seed (o reverb pula isso)
if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "[entrypoint] Rodando migrations..."
    php artisan migrate --force

    if [ "$RUN_SEED" = "true" ]; then
        echo "[entrypoint] Rodando seeders..."
        php artisan db:seed --force
    fi
fi

exec "$@"
