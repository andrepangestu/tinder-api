#!/bin/bash

###############################################################################
# Complete Server Diagnostic Script
# Run this on the DigitalOcean server to diagnose error 500
###############################################################################

echo "================================================================"
echo "  🔍 TINDER API - SERVER DIAGNOSTIC REPORT"
echo "================================================================"
echo ""
echo "📅 Date: $(date)"
echo "🖥️  Server: $(hostname)"
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${YELLOW}1. DOCKER CONTAINERS STATUS${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
cd /var/www/tinder-api
docker compose ps

echo ""
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${YELLOW}2. NGINX CONFIGURATION${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo "Checking nginx config files..."
if [ -f "/etc/nginx/sites-available/andrepangestu.com" ]; then
    echo -e "${GREEN}✓ Main nginx config exists${NC}"
    echo "Location blocks:"
    grep -A 5 "location.*api" /etc/nginx/sites-available/andrepangestu.com || echo -e "${RED}✗ No /api location block found${NC}"
else
    echo -e "${YELLOW}Checking inside Docker container...${NC}"
    docker compose exec -T nginx cat /etc/nginx/conf.d/default.conf | grep -A 10 "location.*api" || echo -e "${RED}✗ No /api location block found${NC}"
fi

echo ""
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${YELLOW}3. LARAVEL APPLICATION STATUS${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

# Check if .env exists
if docker compose exec -T app test -f .env; then
    echo -e "${GREEN}✓ .env file exists${NC}"
    
    # Check critical env variables
    echo "Checking environment variables:"
    docker compose exec -T app bash -c 'source .env 2>/dev/null && echo "  APP_KEY: ${APP_KEY:0:20}..." && echo "  DB_HOST: $DB_HOST" && echo "  DB_DATABASE: $DB_DATABASE"'
else
    echo -e "${RED}✗ .env file missing!${NC}"
fi

# Check storage permissions
echo ""
echo "Storage permissions:"
docker compose exec -T app ls -la storage/ | head -5

echo ""
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${YELLOW}4. DATABASE CONNECTION${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
docker compose exec -T app php artisan migrate:status 2>&1 | head -10

echo ""
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${YELLOW}5. RECENT LARAVEL ERRORS (Last 20 lines)${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
if docker compose exec -T app test -f storage/logs/laravel.log; then
    docker compose exec -T app tail -20 storage/logs/laravel.log
else
    echo -e "${RED}✗ No Laravel log file found${NC}"
fi

echo ""
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${YELLOW}6. NGINX ERROR LOGS (Last 20 lines)${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
docker compose logs nginx --tail=20 2>&1 | grep -i error || echo "No recent errors"

echo ""
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${YELLOW}7. PHP-FPM LOGS (Last 20 lines)${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
docker compose logs app --tail=20 2>&1 | grep -i "error\|fatal\|warning" || echo "No recent errors"

echo ""
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${YELLOW}8. ROUTES CHECK${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo "API Routes:"
docker compose exec -T app php artisan route:list | grep "api/" | head -10

echo ""
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${YELLOW}9. TEST API INTERNALLY${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo "Testing from inside container:"
docker compose exec -T app curl -s -o /dev/null -w "HTTP Status: %{http_code}\n" http://localhost/api/people/recommended

echo ""
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${YELLOW}10. SWAGGER FILES CHECK${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
if docker compose exec -T app test -f storage/api-docs/api-docs.json; then
    SIZE=$(docker compose exec -T app stat -c%s storage/api-docs/api-docs.json)
    echo -e "${GREEN}✓ Swagger JSON exists ($SIZE bytes)${NC}"
else
    echo -e "${RED}✗ Swagger JSON missing${NC}"
fi

echo ""
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${YELLOW}11. DISK SPACE${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
df -h | grep -E "Filesystem|/var|/dev"

echo ""
echo "================================================================"
echo -e "${GREEN}  ✅ DIAGNOSTIC COMPLETE${NC}"
echo "================================================================"
echo ""
echo -e "${YELLOW}📋 RECOMMENDED ACTIONS:${NC}"
echo ""
echo "If you see errors above, try these fixes:"
echo ""
echo "1. Clear all caches:"
echo "   docker compose exec -T app php artisan config:clear"
echo "   docker compose exec -T app php artisan cache:clear"
echo "   docker compose exec -T app php artisan route:clear"
echo ""
echo "2. Fix permissions:"
echo "   docker compose exec -T app chmod -R 775 storage bootstrap/cache"
echo "   docker compose exec -T app chown -R www-data:www-data storage bootstrap/cache"
echo ""
echo "3. Regenerate Swagger:"
echo "   docker compose exec -T app php artisan l5-swagger:generate"
echo ""
echo "4. Restart containers:"
echo "   docker compose restart app nginx"
echo ""
echo "5. View this report:"
echo "   Save output to a file: ./diagnose.sh > diagnostic-report.txt"
echo ""
