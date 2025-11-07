#!/bin/bash

# Emergency Debug Script - Get detailed error information

echo "🚨 Emergency Debug for Error 500..."
echo ""

# Detect docker compose version
if docker compose version &> /dev/null; then
    DC="docker compose"
else
    DC="docker-compose"
fi

# 1. Enable debug mode temporarily
echo "1️⃣ Enabling debug mode..."
$DC exec -T app sed -i 's/APP_DEBUG=false/APP_DEBUG=true/g' .env 2>/dev/null || true
$DC exec -T app php artisan config:clear 2>/dev/null || true

# 2. Check if app container is running
echo ""
echo "2️⃣ Container Status:"
$DC ps

# 3. Get the actual error from logs
echo ""
echo "3️⃣ Recent Application Logs:"
$DC logs --tail=50 app 2>&1

# 4. Check Laravel log file
echo ""
echo "4️⃣ Laravel Error Logs:"
$DC exec -T app tail -n 50 /var/www/storage/logs/laravel.log 2>/dev/null || echo "No Laravel log file found"

# 5. Check if storage directory exists and is writable
echo ""
echo "5️⃣ Storage Directory Check:"
$DC exec -T app ls -la /var/www/storage/ 2>&1

# 6. Check if .env file exists
echo ""
echo "6️⃣ Environment File Check:"
$DC exec -T app test -f /var/www/.env && echo ".env exists" || echo "❌ .env NOT FOUND!"

# 7. Check APP_KEY
echo ""
echo "7️⃣ APP_KEY Check:"
$DC exec -T app grep "^APP_KEY=" /var/www/.env 2>/dev/null || echo "❌ APP_KEY not set!"

# 8. Test PHP is working
echo ""
echo "8️⃣ PHP Test:"
$DC exec -T app php -v 2>&1 | head -n 1

# 9. Test artisan is working
echo ""
echo "9️⃣ Artisan Test:"
$DC exec -T app php artisan --version 2>&1

# 10. Test database connection
echo ""
echo "🔟 Database Connection Test:"
$DC exec -T db mysqladmin ping -h localhost 2>&1 || echo "❌ Database not responding"

# 11. Test simple PHP script
echo ""
echo "1️⃣1️⃣ Direct PHP Test:"
$DC exec -T app php -r "echo 'PHP is working: ' . phpversion() . PHP_EOL;" 2>&1

# 12. Check nginx error log
echo ""
echo "1️⃣2️⃣ Nginx Error Log:"
$DC exec -T app tail -n 20 /var/log/nginx/error.log 2>/dev/null || echo "Nginx log not accessible"

# 13. Test endpoint directly from container
echo ""
echo "1️⃣3️⃣ Internal API Test:"
$DC exec -T app curl -s http://localhost/api/test 2>&1 | head -n 20

# 14. Check vendor directory
echo ""
echo "1️⃣4️⃣ Vendor Directory:"
$DC exec -T app ls -la /var/www/vendor 2>&1 | head -n 5

# 15. Check composer autoload
echo ""
echo "1️⃣5️⃣ Composer Autoload:"
$DC exec -T app test -f /var/www/vendor/autoload.php && echo "✅ Autoload exists" || echo "❌ Autoload NOT FOUND!"

echo ""
echo "========================================"
echo "🔍 Debug Complete!"
echo "========================================"
echo ""
echo "Next steps:"
echo "1. Check the logs above for specific errors"
echo "2. Look for 'Fatal error', 'Exception', or 'Error' messages"
echo "3. If you see the error, try the fix suggested"
echo "4. Run './fix-500.sh' after identifying the issue"
echo ""
echo "⚠️  Debug mode is ON - remember to disable it:"
echo "   docker compose exec app sed -i 's/APP_DEBUG=true/APP_DEBUG=false/' .env"
echo "   docker compose exec app php artisan config:clear"
echo "   docker compose restart app"
