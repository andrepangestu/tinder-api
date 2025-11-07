#!/bin/bash

# Debug Script untuk Error 500
# Run this on your server to diagnose the issue

echo "🔍 Debugging API Error 500..."
echo ""

# Check if containers are running
echo "=== Container Status ==="
docker compose ps
echo ""

# Check application logs
echo "=== Application Logs (Last 50 lines) ==="
docker compose logs --tail=50 app
echo ""

# Check PHP-FPM logs
echo "=== PHP-FPM Status ==="
docker compose exec app php-fpm -v || docker compose exec app php -v
echo ""

# Check Laravel logs
echo "=== Laravel Logs (Last 30 lines) ==="
docker compose exec app tail -n 30 /var/www/storage/logs/laravel.log 2>/dev/null || echo "No Laravel logs found"
echo ""

# Check permissions
echo "=== Storage Permissions ==="
docker compose exec app ls -la /var/www/storage
echo ""

# Check .env file
echo "=== Environment Configuration ==="
docker compose exec app php artisan config:show app 2>/dev/null || echo "Cannot show config"
echo ""

# Test database connection
echo "=== Database Connection ==="
docker compose exec app php artisan migrate:status 2>&1 || echo "Database connection failed"
echo ""

# Check routes
echo "=== Registered Routes ==="
docker compose exec app php artisan route:list 2>&1 | head -n 20
echo ""

echo "✅ Debug information collected!"
echo ""
echo "Next steps:"
echo "1. Check the logs above for specific error messages"
echo "2. Fix permissions if needed: chmod -R 775 storage bootstrap/cache"
echo "3. Clear cache: docker compose exec app php artisan cache:clear"
echo "4. Restart containers: docker compose restart"
