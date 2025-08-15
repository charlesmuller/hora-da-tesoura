#!/usr/bin/env bash
set -euo pipefail

# Script para corrigir permissões do Laravel no Docker
echo "🔧 Corrigindo permissões do Laravel..."

# Navegar para o diretório src
cd "$(dirname "$0")/../src"

# Corrigir permissões do storage
echo "📁 Ajustando permissões do diretório storage..."
sudo chown -R www-data:www-data storage/
sudo chmod -R 775 storage/

# Corrigir permissões do bootstrap/cache
echo "📁 Ajustando permissões do bootstrap/cache..."
sudo chown -R www-data:www-data bootstrap/cache/
sudo chmod -R 775 bootstrap/cache/

# Criar arquivo de log se não existir
if [ ! -f storage/logs/laravel.log ]; then
    echo "📝 Criando arquivo de log..."
    sudo touch storage/logs/laravel.log
    sudo chown www-data:www-data storage/logs/laravel.log
    sudo chmod 664 storage/logs/laravel.log
fi

# Limpar caches
echo "🧹 Limpando caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

echo "✅ Permissões corrigidas com sucesso!"
echo "🌐 Acesse: http://localhost:8080 (público) ou http://localhost:8080/admin (admin)"
