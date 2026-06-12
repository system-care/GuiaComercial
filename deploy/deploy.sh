#!/bin/bash
# Uso: bash /var/www/agendamento/deploy/deploy.sh
# Executar como root ou com sudo
set -euo pipefail

APP_DIR="/var/www/agendamento"
PHP="php8.3"

echo "──────────────────────────────────────────"
echo " Deploy — Guia Comercial"
echo "──────────────────────────────────────────"

# 1. Modo de manutenção
$PHP "$APP_DIR/artisan" down --render="errors::503" --retry=60

# 2. Atualizar código
git -C "$APP_DIR" pull origin main

# 3. Dependências PHP (sem dev, otimizado)
composer -d "$APP_DIR" install --no-dev --optimize-autoloader --no-interaction

# 4. Assets frontend
cd "$APP_DIR"
npm ci --ignore-scripts
npm run build

# 5. Migrations
$PHP "$APP_DIR/artisan" migrate --force

# 6. Storage symlink (seguro se já existir)
$PHP "$APP_DIR/artisan" storage:link || true

# 7. Limpar e reconstruir caches
$PHP "$APP_DIR/artisan" config:cache
$PHP "$APP_DIR/artisan" route:cache
$PHP "$APP_DIR/artisan" view:cache
$PHP "$APP_DIR/artisan" event:cache
$PHP "$APP_DIR/artisan" filament:cache-components

# 8. Permissões
chown -R www-data:www-data "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"

# 9. Reiniciar workers
$PHP "$APP_DIR/artisan" queue:restart

# 10. Sair do modo de manutenção
$PHP "$APP_DIR/artisan" up

echo ""
echo "✓ Deploy concluído!"
