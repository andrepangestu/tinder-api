#!/bin/bash

# Deployment Script for Tinder API
# Run this script on your server to update the application

set -e

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${GREEN}🚀 Starting deployment...${NC}"

# Navigate to project directory
cd /var/www/tinder-api

# Check if docker compose v2 is available
if docker compose version &> /dev/null; then
    DOCKER_COMPOSE="docker compose"
else
    DOCKER_COMPOSE="docker-compose"
fi

echo "Using: $DOCKER_COMPOSE"

# Pull latest changes
echo -e "${GREEN}📥 Pulling latest changes from GitHub...${NC}"
git pull origin main

# Stop containers to avoid ContainerConfig errors
echo -e "${YELLOW}🛑 Stopping existing containers...${NC}"
$DOCKER_COMPOSE down --remove-orphans || true

# Install/update dependencies
echo -e "${GREEN}📦 Installing dependencies...${NC}"
$DOCKER_COMPOSE run --rm app composer install --no-dev --optimize-autoloader --no-interaction || true

# Start containers
echo -e "${GREEN}🐳 Starting Docker containers...${NC}"
$DOCKER_COMPOSE up -d

# Wait for containers
echo -e "${YELLOW}⏳ Waiting for containers to restart...${NC}"
sleep 10

# Fix permissions
echo -e "${GREEN}🔒 Fixing permissions...${NC}"
$DOCKER_COMPOSE exec -T app chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache || true
$DOCKER_COMPOSE exec -T app chmod -R 775 /var/www/storage /var/www/bootstrap/cache || true

# Create necessary directories
echo -e "${GREEN}📁 Creating necessary directories...${NC}"
$DOCKER_COMPOSE exec -T app mkdir -p /var/www/storage/logs
$DOCKER_COMPOSE exec -T app mkdir -p /var/www/storage/framework/sessions
$DOCKER_COMPOSE exec -T app mkdir -p /var/www/storage/framework/views
$DOCKER_COMPOSE exec -T app mkdir -p /var/www/storage/framework/cache
$DOCKER_COMPOSE exec -T app mkdir -p /var/www/bootstrap/cache

# Wait for MySQL to be ready
echo -e "${YELLOW}⏳ Waiting for MySQL to be ready...${NC}"
until $DOCKER_COMPOSE exec -T db mysqladmin ping -h localhost --silent 2>/dev/null; do
  echo "Waiting for database connection..."
  sleep 2
done
echo "MySQL is ready!"

# Clear caches
echo -e "${GREEN}🧹 Clearing application caches...${NC}"
$DOCKER_COMPOSE exec -T app php artisan config:clear
$DOCKER_COMPOSE exec -T app php artisan cache:clear
$DOCKER_COMPOSE exec -T app php artisan route:clear
$DOCKER_COMPOSE exec -T app php artisan view:clear

# Run migrations
echo -e "${GREEN}💾 Running database migrations...${NC}"
$DOCKER_COMPOSE exec -T app php artisan migrate --force

# Regenerate API documentation
echo -e "${GREEN}📚 Regenerating API documentation...${NC}"
$DOCKER_COMPOSE exec -T app php artisan l5-swagger:generate

# Optimize application
echo -e "${GREEN}⚡ Optimizing application...${NC}"
$DOCKER_COMPOSE exec -T app php artisan config:cache
$DOCKER_COMPOSE exec -T app php artisan route:cache

# Test if API is working
echo -e "${GREEN}🧪 Testing API...${NC}"
RESPONSE=$($DOCKER_COMPOSE exec -T app curl -s http://localhost/api/test || echo "failed")
if [[ $RESPONSE == *"API is working"* ]]; then
    echo -e "${GREEN}✅ API test passed!${NC}"
else
    echo -e "${YELLOW}⚠️  API test failed - checking logs...${NC}"
    $DOCKER_COMPOSE logs --tail=20 app
fi

echo -e "${GREEN}✅ Deployment complete!${NC}"
echo ""
echo "Run '$DOCKER_COMPOSE logs -f app' to view application logs"
echo "Test API: curl https://andrepangestu.com/api/test"
