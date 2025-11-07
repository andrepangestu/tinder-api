#!/bin/bash

# Quick Fix untuk Error 500
# Common issues yang menyebabkan error 500

set -e

echo "🔧 Fixing common Error 500 issues..."

# Detect docker compose version
if docker compose version &> /dev/null; then
    DOCKER_COMPOSE="docker compose"
else
    DOCKER_COMPOSE="docker-compose"
fi

# 1. Fix file permissions
echo ""
echo "1️⃣ Fixing file permissions..."
$DOCKER_COMPOSE exec -T app chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache 2>/dev/null || sudo chown -R www-data:www-data storage bootstrap/cache
$DOCKER_COMPOSE exec -T app chmod -R 775 /var/www/storage /var/www/bootstrap/cache 2>/dev/null || sudo chmod -R 775 storage bootstrap/cache

# 2. Create necessary directories
echo ""
echo "2️⃣ Creating necessary directories..."
$DOCKER_COMPOSE exec -T app mkdir -p /var/www/storage/logs
$DOCKER_COMPOSE exec -T app mkdir -p /var/www/storage/framework/sessions
$DOCKER_COMPOSE exec -T app mkdir -p /var/www/storage/framework/views
$DOCKER_COMPOSE exec -T app mkdir -p /var/www/storage/framework/cache
$DOCKER_COMPOSE exec -T app mkdir -p /var/www/bootstrap/cache

# 3. Clear all caches
echo ""
echo "3️⃣ Clearing all caches..."
$DOCKER_COMPOSE exec -T app php artisan config:clear || true
$DOCKER_COMPOSE exec -T app php artisan cache:clear || true
$DOCKER_COMPOSE exec -T app php artisan route:clear || true
$DOCKER_COMPOSE exec -T app php artisan view:clear || true

# 4. Check if .env exists
echo ""
echo "4️⃣ Checking .env file..."
if [ ! -f .env ]; then
    echo "⚠️  .env file not found! Creating from .env.example..."
    cp .env.example .env
    echo "Please configure .env file manually!"
fi

# 5. Generate APP_KEY if missing
echo ""
echo "5️⃣ Checking APP_KEY..."
if ! grep -q "APP_KEY=base64:" .env 2>/dev/null; then
    echo "Generating APP_KEY..."
    $DOCKER_COMPOSE exec -T app php artisan key:generate
fi

# 6. Run migrations
echo ""
echo "6️⃣ Running migrations..."
$DOCKER_COMPOSE exec -T app php artisan migrate --force 2>&1 || echo "⚠️  Migration failed - check database connection"

# 7. Install dependencies
echo ""
echo "7️⃣ Installing/updating dependencies..."
$DOCKER_COMPOSE exec -T app composer install --no-dev --optimize-autoloader --no-interaction || echo "⚠️  Composer install failed"

# 8. Optimize for production
echo ""
echo "8️⃣ Optimizing application..."
$DOCKER_COMPOSE exec -T app php artisan config:cache || true
$DOCKER_COMPOSE exec -T app php artisan route:cache || true

# 9. Restart containers
echo ""
echo "9️⃣ Restarting containers..."
$DOCKER_COMPOSE restart app

echo ""
echo "✅ Quick fix completed!"
echo ""
echo "Test your API now:"
echo "  curl https://andrepangestu.com/api/test"
echo ""
echo "If still error 500, run debug script:"
echo "  ./debug-500.sh"
