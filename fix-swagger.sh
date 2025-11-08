#!/bin/bash

###############################################################################
# Swagger Fix Script for Production Server
# This script regenerates Swagger documentation and clears all caches
###############################################################################

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}======================================${NC}"
echo -e "${GREEN}  Swagger Documentation Fix Script${NC}"
echo -e "${GREEN}======================================${NC}"
echo ""

# Check if running in Docker environment
if command -v docker &> /dev/null && docker compose ps &> /dev/null; then
    echo -e "${YELLOW}📦 Docker environment detected${NC}"
    DOCKER_PREFIX="docker compose exec -T app"
else
    echo -e "${YELLOW}💻 Standard environment detected${NC}"
    DOCKER_PREFIX=""
fi

# Function to run artisan commands
run_artisan() {
    if [ -n "$DOCKER_PREFIX" ]; then
        $DOCKER_PREFIX php artisan "$@"
    else
        php artisan "$@"
    fi
}

echo ""
echo -e "${YELLOW}Step 1:${NC} Clearing configuration cache..."
run_artisan config:clear
echo -e "${GREEN}✓ Configuration cache cleared${NC}"

echo ""
echo -e "${YELLOW}Step 2:${NC} Clearing route cache..."
run_artisan route:clear
echo -e "${GREEN}✓ Route cache cleared${NC}"

echo ""
echo -e "${YELLOW}Step 3:${NC} Clearing view cache..."
run_artisan view:clear
echo -e "${GREEN}✓ View cache cleared${NC}"

echo ""
echo -e "${YELLOW}Step 4:${NC} Clearing application cache..."
run_artisan cache:clear
echo -e "${GREEN}✓ Application cache cleared${NC}"

echo ""
echo -e "${YELLOW}Step 5:${NC} Checking storage permissions..."
if [ -n "$DOCKER_PREFIX" ]; then
    $DOCKER_PREFIX chmod -R 775 storage/api-docs 2>/dev/null || true
    $DOCKER_PREFIX chown -R www-data:www-data storage/api-docs 2>/dev/null || true
else
    chmod -R 775 storage/api-docs 2>/dev/null || true
fi
echo -e "${GREEN}✓ Storage permissions set${NC}"

echo ""
echo -e "${YELLOW}Step 6:${NC} Regenerating Swagger documentation..."
run_artisan l5-swagger:generate
echo -e "${GREEN}✓ Swagger documentation regenerated${NC}"

echo ""
echo -e "${YELLOW}Step 7:${NC} Verifying Swagger JSON file..."
if [ -n "$DOCKER_PREFIX" ]; then
    if $DOCKER_PREFIX test -f storage/api-docs/api-docs.json; then
        FILE_SIZE=$($DOCKER_PREFIX stat -c%s storage/api-docs/api-docs.json)
        echo -e "${GREEN}✓ Swagger JSON file exists (${FILE_SIZE} bytes)${NC}"
    else
        echo -e "${RED}✗ Swagger JSON file not found!${NC}"
        exit 1
    fi
else
    if [ -f storage/api-docs/api-docs.json ]; then
        FILE_SIZE=$(stat -c%s storage/api-docs/api-docs.json 2>/dev/null || stat -f%z storage/api-docs/api-docs.json)
        echo -e "${GREEN}✓ Swagger JSON file exists (${FILE_SIZE} bytes)${NC}"
    else
        echo -e "${RED}✗ Swagger JSON file not found!${NC}"
        exit 1
    fi
fi

echo ""
echo -e "${GREEN}======================================${NC}"
echo -e "${GREEN}  ✅ Swagger Fix Completed!${NC}"
echo -e "${GREEN}======================================${NC}"
echo ""
echo -e "You can now access Swagger UI at:"
echo -e "${YELLOW}  • Local: http://localhost:8000/api/documentation${NC}"
echo -e "${YELLOW}  • Production: https://andrepangestu.com/api/documentation${NC}"
echo ""
echo -e "To verify, run:"
echo -e "${YELLOW}  curl -I https://andrepangestu.com/api/documentation${NC}"
echo ""
