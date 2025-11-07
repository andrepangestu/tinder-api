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

echo -e "${GREEN}✅ Deployment complete!${NC}"
echo ""
echo "Run '$DOCKER_COMPOSE logs -f app' to view application logs"
