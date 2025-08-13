#!/usr/bin/env bash
set -euo pipefail

# 1) Sobe containers locais
docker compose -f docker-compose.local.yml up -d --build

# 2) Cria projeto Laravel caso /src esteja vazio
if [ -z "$(ls -A src 2>/dev/null)" ]; then
  docker exec hdt-app-local bash -lc "composer create-project laravel/laravel ."
fi

# 3) Copia .env.local
if [ ! -f .env.local ]; then
  cp .env.local.example .env.local
fi

# 4) Gera APP_KEY e instala dependências
docker exec hdt-app-local bash -lc "cd /var/www/html && composer install && php artisan key:generate && php artisan storage:link || true"

# 5) (Opcional) Instala Filament
# docker exec hdt-app-local bash -lc "cd /var/www/html && composer require filament/filament:^3.0"

# 6) Migrações
docker exec hdt-app-local bash -lc "cd /var/www/html && php artisan migrate"

echo "Local pronto: http://localhost:8080"