#!/bin/bash

# Check Configuration Script
# Verify all configurations are correct

echo "🔍 Checking Configuration..."
echo ""

# Detect docker compose
if docker compose version &> /dev/null; then
    DC="docker compose"
else
    DC="docker-compose"
fi

echo "1️⃣ Checking .env file content..."
echo "----------------------------------------"
$DC exec -T app cat /var/www/.env 2>/dev/null | grep -E "^APP_|^DB_|^CACHE_|^SESSION_|^REDIS_" || echo "⚠️ Cannot read .env"
echo ""

echo "2️⃣ Checking PHP Configuration..."
echo "----------------------------------------"
$DC exec -T app php -i | grep -E "display_errors|error_reporting|memory_limit" || true
echo ""

echo "3️⃣ Checking Laravel Configuration..."
echo "----------------------------------------"
$DC exec -T app php artisan about 2>&1 | head -n 30 || echo "⚠️ Cannot run artisan about"
echo ""

echo "4️⃣ Checking File Permissions..."
echo "----------------------------------------"
echo "Storage:"
$DC exec -T app ls -la /var/www/storage/ | head -n 5
echo ""
echo "Bootstrap/cache:"
$DC exec -T app ls -la /var/www/bootstrap/cache/ | head -n 5
echo ""

echo "5️⃣ Checking Database Connection..."
echo "----------------------------------------"
$DC exec -T app php artisan migrate:status 2>&1 | head -n 10
echo ""

echo "6️⃣ Checking Routes..."
echo "----------------------------------------"
$DC exec -T app php artisan route:list 2>&1 | head -n 20
echo ""

echo "7️⃣ Checking Composer Autoload..."
echo "----------------------------------------"
$DC exec -T app php -r "require '/var/www/vendor/autoload.php'; echo 'Autoload OK' . PHP_EOL;" 2>&1
echo ""

echo "8️⃣ Testing Database Query..."
echo "----------------------------------------"
$DC exec -T app php artisan tinker --execute="echo 'Database test: ' . \App\Models\Person::count() . ' people';" 2>&1
echo ""

echo "9️⃣ Checking Environment Variables in Container..."
echo "----------------------------------------"
$DC exec -T app env | grep -E "APP_|DB_|CACHE_|SESSION_" | sort
echo ""

echo "🔟 Testing Internal API Call..."
echo "----------------------------------------"
$DC exec -T app curl -v http://127.0.0.1:80/api/test 2>&1 | tail -n 20
echo ""

echo "✅ Configuration check complete!"
