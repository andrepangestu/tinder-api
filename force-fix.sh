#!/bin/bash

# Force Fix - Rebuild everything from scratch

set -e

echo "⚠️  FORCE FIX - This will rebuild everything"
echo "Press Ctrl+C to cancel, or Enter to continue..."
read

# Detect docker compose
if docker compose version &> /dev/null; then
    DC="docker compose"
else
    DC="docker-compose"
fi

echo ""
echo "1️⃣ Stopping all containers..."
$DC down

echo ""
echo "2️⃣ Removing containers and orphans..."
$DC down --remove-orphans
docker container prune -f

echo ""
echo "3️⃣ Checking .env file..."
if [ ! -f .env ]; then
    echo "Creating .env from .env.production..."
    cp .env.production .env
    echo "⚠️  Please configure database passwords in .env!"
fi

echo ""
echo "4️⃣ Fixing permissions on host..."
sudo chown -R $(whoami):$(whoami) . 2>/dev/null || chown -R $(whoami):$(whoami) .
chmod -R 755 storage bootstrap/cache

echo ""
echo "5️⃣ Creating necessary directories..."
mkdir -p storage/logs
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/cache
mkdir -p storage/framework/testing
mkdir -p storage/app/public
mkdir -p bootstrap/cache

echo ""
echo "6️⃣ Building containers..."
$DC build --no-cache

echo ""
echo "7️⃣ Starting containers..."
$DC up -d

echo ""
echo "8️⃣ Waiting for containers to be ready..."
sleep 15

echo ""
echo "9️⃣ Waiting for MySQL..."
until $DC exec -T db mysqladmin ping -h localhost --silent 2>/dev/null; do
  echo "Waiting for database..."
  sleep 2
done
echo "✅ MySQL is ready!"

echo ""
echo "🔟 Installing Composer dependencies..."
$DC exec -T app composer install --no-dev --optimize-autoloader --no-interaction

echo ""
echo "1️⃣1️⃣ Generating APP_KEY..."
$DC exec -T app php artisan key:generate --force

echo ""
echo "1️⃣2️⃣ Fixing permissions inside container..."
$DC exec -T app chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
$DC exec -T app chmod -R 775 /var/www/storage /var/www/bootstrap/cache

echo ""
echo "1️⃣3️⃣ Running migrations..."
$DC exec -T app php artisan migrate:fresh --seed --force

echo ""
echo "1️⃣4️⃣ Clearing all caches..."
$DC exec -T app php artisan config:clear
$DC exec -T app php artisan cache:clear
$DC exec -T app php artisan route:clear
$DC exec -T app php artisan view:clear

echo ""
echo "1️⃣5️⃣ Generating API documentation..."
$DC exec -T app php artisan l5-swagger:generate

echo ""
echo "1️⃣6️⃣ Optimizing application..."
$DC exec -T app php artisan config:cache
$DC exec -T app php artisan route:cache
$DC exec -T app php artisan view:cache

echo ""
echo "1️⃣7️⃣ Restarting containers..."
$DC restart

echo ""
echo "1️⃣8️⃣ Testing API..."
sleep 5
RESPONSE=$($DC exec -T app curl -s http://localhost/api/test 2>&1)

echo ""
echo "API Response:"
echo "$RESPONSE"
echo ""

if echo "$RESPONSE" | grep -q "API is working"; then
    echo "✅✅✅ SUCCESS! API is working!"
else
    echo "❌ API still has issues. Checking logs..."
    $DC logs --tail=30 app
fi

echo ""
echo "========================================"
echo "Force fix completed!"
echo "========================================"
echo ""
echo "Test from outside:"
echo "  curl https://andrepangestu.com/api/test"
echo ""
echo "View logs:"
echo "  $DC logs -f app"
