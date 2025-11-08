#!/bin/bash

###############################################################################
# Quick Fix Script for Error 500
# Run this on the server to attempt automatic fix
###############################################################################

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${GREEN}================================================================${NC}"
echo -e "${GREEN}  🔧 QUICK FIX FOR ERROR 500${NC}"
echo -e "${GREEN}================================================================${NC}"
echo ""

cd /var/www/tinder-api

echo -e "${YELLOW}Step 1: Checking Docker containers...${NC}"
docker compose ps
echo ""

echo -e "${YELLOW}Step 2: Pulling latest code...${NC}"
git pull origin main || echo -e "${RED}Warning: Could not pull latest code${NC}"
echo ""

echo -e "${YELLOW}Step 3: Ensuring containers are up...${NC}"
docker compose up -d
sleep 5
echo ""

echo -e "${YELLOW}Step 4: Installing/updating dependencies...${NC}"
docker compose exec -T app composer install --no-dev --optimize-autoloader --no-interaction
echo ""

echo -e "${YELLOW}Step 5: Fixing file permissions...${NC}"
docker compose exec -T app chmod -R 775 storage bootstrap/cache
docker compose exec -T app chown -R www-data:www-data storage bootstrap/cache
echo -e "${GREEN}✓ Permissions fixed${NC}"
echo ""

echo -e "${YELLOW}Step 6: Clearing all caches...${NC}"
docker compose exec -T app php artisan config:clear
docker compose exec -T app php artisan route:clear
docker compose exec -T app php artisan cache:clear
docker compose exec -T app php artisan view:clear
echo -e "${GREEN}✓ All caches cleared${NC}"
echo ""

echo -e "${YELLOW}Step 7: Running database migrations...${NC}"
docker compose exec -T app php artisan migrate --force
echo -e "${GREEN}✓ Migrations completed${NC}"
echo ""

echo -e "${YELLOW}Step 8: Regenerating Swagger documentation...${NC}"
docker compose exec -T app php artisan l5-swagger:generate
echo -e "${GREEN}✓ Swagger regenerated${NC}"
echo ""

echo -e "${YELLOW}Step 9: Regenerating autoloader...${NC}"
docker compose exec -T app composer dump-autoload
echo -e "${GREEN}✓ Autoloader regenerated${NC}"
echo ""

echo -e "${YELLOW}Step 10: Restarting services...${NC}"
docker compose restart app nginx
echo -e "${GREEN}✓ Services restarted${NC}"
echo ""

echo -e "${YELLOW}Step 11: Waiting for services to be ready...${NC}"
sleep 10
echo ""

echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}  ✅ FIX COMPLETED!${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""

echo -e "${YELLOW}Testing endpoints...${NC}"
echo ""

# Test API
echo -n "Testing API endpoint... "
STATUS=$(docker compose exec -T app curl -s -o /dev/null -w "%{http_code}" http://localhost/api/people/recommended)
if [ "$STATUS" = "200" ]; then
    echo -e "${GREEN}✓ HTTP $STATUS OK${NC}"
else
    echo -e "${RED}✗ HTTP $STATUS${NC}"
fi

# Test Swagger
echo -n "Testing Swagger UI... "
STATUS=$(docker compose exec -T app curl -s -o /dev/null -w "%{http_code}" http://localhost/api/documentation)
if [ "$STATUS" = "200" ]; then
    echo -e "${GREEN}✓ HTTP $STATUS OK${NC}"
else
    echo -e "${RED}✗ HTTP $STATUS${NC}"
fi

echo ""
echo -e "${YELLOW}External test (from internet):${NC}"
echo "curl -I https://andrepangestu.com/api/people/recommended"
echo ""
echo -e "${YELLOW}If still not working, run diagnostic:${NC}"
echo "./diagnose-server.sh"
echo ""
