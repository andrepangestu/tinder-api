#!/bin/bash
set -e

# Export environment variables (will be passed from GitHub Actions)
export APP_KEY="${APP_KEY}"
export DB_DATABASE="${DB_DATABASE}"
export DB_USERNAME="${DB_USERNAME}"
export DB_PASSWORD="${DB_PASSWORD}"
export DB_ROOT_PASSWORD="${DB_ROOT_PASSWORD}"
export MAIL_HOST="${MAIL_HOST}"
export MAIL_PORT="${MAIL_PORT}"
export MAIL_USERNAME="${MAIL_USERNAME}"
export MAIL_PASSWORD="${MAIL_PASSWORD}"
export MAIL_FROM_ADDRESS="${MAIL_FROM_ADDRESS}"

# Navigate to project directory
cd /var/www/tinder-api

# Pull latest code
git pull origin main || git pull origin master

# Check if docker compose v2 is installed, if not upgrade
if ! docker compose version &> /dev/null; then
  echo "Upgrading to Docker Compose V2..."
  apt-get update
  apt-get install -y docker-compose-plugin
fi

# Stop and remove old containers to avoid ContainerConfig error
echo "Stopping existing containers..."
docker compose down --remove-orphans || docker-compose down --remove-orphans || true

# Remove old containers completely
docker container prune -f

# Create .env file
cat > .env << EOF
APP_NAME="Tinder API"
APP_ENV=production
APP_DEBUG=false
APP_KEY=${APP_KEY}
APP_URL=https://andrepangestu.com

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=${DB_DATABASE}
DB_USERNAME=${DB_USERNAME}
DB_PASSWORD=${DB_PASSWORD}
DB_ROOT_PASSWORD=${DB_ROOT_PASSWORD}

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=redis
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=${MAIL_HOST}
MAIL_PORT=${MAIL_PORT}
MAIL_USERNAME=${MAIL_USERNAME}
MAIL_PASSWORD=${MAIL_PASSWORD}
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=${MAIL_FROM_ADDRESS}
MAIL_FROM_NAME="Tinder API"

L5_SWAGGER_CONST_HOST=https://andrepangestu.com
EOF

# Check if we need to rebuild (Dockerfile or composer.json changed)
REBUILD=false
if git diff HEAD~1 HEAD --name-only | grep -q -E "Dockerfile|composer.json|composer.lock"; then
  echo "Detected changes in Dockerfile or dependencies, rebuilding..."
  REBUILD=true
fi

# Build and restart containers using Docker Compose V2
if [ "$REBUILD" = true ]; then
  echo "Rebuilding Docker images..."
  docker compose build --no-cache
  docker compose up -d --force-recreate
else
  echo "Starting containers..."
  docker compose up -d
fi

# Wait for MySQL to be ready
echo "Waiting for MySQL to be ready..."
until docker compose exec -T db mysqladmin ping -h localhost -u root -p${DB_ROOT_PASSWORD} --silent 2>/dev/null; do
  echo "MySQL is unavailable - sleeping"
  sleep 2
done
echo "MySQL is ready!"

# Wait for app container to be fully ready
echo "Waiting for app container to be ready..."
until docker compose exec -T app php -v > /dev/null 2>&1; do
  echo "App container is not ready - sleeping"
  sleep 2
done
echo "App container is ready!"

# Additional wait to ensure all services are fully initialized
sleep 5

# Verify composer is available
echo "Verifying composer installation..."
docker compose exec -T app composer --version || {
  echo "Error: Composer not found in container!"
  exit 1
}

# Install/update composer dependencies
docker compose exec -T app composer install --no-dev --optimize-autoloader --no-interaction

# Run migrations
docker compose exec -T app php artisan migrate --force

# Clear caches
docker compose exec -T app php artisan config:clear
docker compose exec -T app php artisan route:clear
docker compose exec -T app php artisan view:clear
docker compose exec -T app php artisan cache:clear

# Regenerate API docs
docker compose exec -T app php artisan l5-swagger:generate

# Restart queue workers
docker compose exec -T app php artisan queue:restart

echo "✅ Deployment completed successfully!"
