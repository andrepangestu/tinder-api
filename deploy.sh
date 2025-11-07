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

# Pull latest changes
echo -e "${GREEN}📥 Pulling latest changes from GitHub...${NC}"
git pull origin main

# Rebuild and restart containers
echo -e "${GREEN}🐳 Rebuilding Docker containers...${NC}"
docker compose down
docker compose up -d --build

# Wait for containers
echo -e "${YELLOW}⏳ Waiting for containers to start...${NC}"
sleep 10

# Clear caches
echo -e "${GREEN}🧹 Clearing application caches...${NC}"
docker compose exec -T app php artisan config:clear
docker compose exec -T app php artisan cache:clear
docker compose exec -T app php artisan route:clear
docker compose exec -T app php artisan view:clear

# Run migrations
echo -e "${GREEN}💾 Running database migrations...${NC}"
docker compose exec -T app php artisan migrate --force

# Regenerate API documentation
echo -e "${GREEN}📚 Regenerating API documentation...${NC}"
docker compose exec -T app php artisan l5-swagger:generate

# Optimize application
echo -e "${GREEN}⚡ Optimizing application...${NC}"
docker compose exec -T app php artisan config:cache
docker compose exec -T app php artisan route:cache

echo -e "${GREEN}✅ Deployment complete!${NC}"
echo ""
echo "Run 'docker compose logs -f app' to view application logs"
