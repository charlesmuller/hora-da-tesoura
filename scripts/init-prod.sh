#!/usr/bin/env bash
set -euo pipefail

docker compose -f docker-compose.prod.yml up -d --build

docker exec hdt-app-prod bash -lc "\
  cd /var/www/html && \
  composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev && \
  php artisan key:generate --force && \
  php artisan storage:link || true && \
  php artisan config:cache && php artisan route:cache && php artisan view:cache \
"

# Migrações (ative quando for a hora)
# docker exec hdt-app-prod bash -lc "php artisan migrate --force"