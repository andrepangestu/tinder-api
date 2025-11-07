# Quick Start Deployment Script

# This script automates the initial server setup for DigitalOcean deployment
# Run this on your DigitalOcean droplet as root user

#!/bin/bash

set -e

echo "🚀 Starting Tinder API Deployment Setup..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Update system
echo -e "${GREEN}📦 Updating system packages...${NC}"
apt update && apt upgrade -y

# Install Docker
echo -e "${GREEN}🐳 Installing Docker...${NC}"
curl -fsSL https://get.docker.com -o get-docker.sh
sh get-docker.sh
systemctl start docker
systemctl enable docker
rm get-docker.sh

# Install Docker Compose
echo -e "${GREEN}🐳 Installing Docker Compose...${NC}"
apt install docker-compose-plugin -y

# Install Nginx
echo -e "${GREEN}🌐 Installing Nginx...${NC}"
apt install nginx -y
systemctl start nginx
systemctl enable nginx

# Install Certbot
echo -e "${GREEN}🔒 Installing Certbot...${NC}"
apt install certbot python3-certbot-nginx -y

# Install Git
echo -e "${GREEN}📚 Installing Git...${NC}"
apt install git -y

# Setup firewall
echo -e "${GREEN}🔥 Configuring firewall...${NC}"
ufw allow OpenSSH
ufw allow 'Nginx Full'
ufw --force enable

# Create application directory
echo -e "${GREEN}📁 Creating application directory...${NC}"
mkdir -p /var/www/tinder-api

# Generate SSH key for GitHub
echo -e "${GREEN}🔑 Generating SSH key for GitHub...${NC}"
if [ ! -f ~/.ssh/id_ed25519 ]; then
    ssh-keygen -t ed25519 -C "deploy@andrepangestu.com" -f ~/.ssh/id_ed25519 -N ""
    echo -e "${YELLOW}⚠️  Add this SSH public key to your GitHub repository (Settings → Deploy keys):${NC}"
    cat ~/.ssh/id_ed25519.pub
    echo ""
    read -p "Press enter after adding the key to GitHub..."
fi

# Clone repository
echo -e "${GREEN}📥 Cloning repository...${NC}"
cd /var/www/tinder-api
if [ ! -d ".git" ]; then
    echo "Enter your GitHub repository URL (e.g., git@github.com:username/tinder-api.git):"
    read REPO_URL
    git clone $REPO_URL .
fi

# Create Nginx configuration
echo -e "${GREEN}⚙️  Configuring Nginx...${NC}"
cat > /etc/nginx/sites-available/andrepangestu.com <<EOF
server {
    listen 80;
    server_name andrepangestu.com www.andrepangestu.com;

    client_max_body_size 20M;

    location / {
        proxy_pass http://localhost:8080;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
        proxy_read_timeout 300;
        proxy_connect_timeout 300;
    }
}
EOF

ln -sf /etc/nginx/sites-available/andrepangestu.com /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl reload nginx

# Setup environment file
echo -e "${GREEN}🔧 Setting up environment file...${NC}"
if [ ! -f .env ]; then
    cp .env.production .env
    
    echo "Enter database password:"
    read -s DB_PASSWORD
    
    echo "Enter database root password:"
    read -s DB_ROOT_PASSWORD
    
    # Generate APP_KEY (simplified version)
    APP_KEY="base64:$(openssl rand -base64 32)"
    
    sed -i "s|DB_PASSWORD=|DB_PASSWORD=$DB_PASSWORD|g" .env
    sed -i "s|DB_ROOT_PASSWORD=|DB_ROOT_PASSWORD=$DB_ROOT_PASSWORD|g" .env
    sed -i "s|APP_KEY=|APP_KEY=$APP_KEY|g" .env
fi

# Start Docker containers
echo -e "${GREEN}🐳 Starting Docker containers...${NC}"
docker compose up -d

# Wait for containers to be ready
echo -e "${YELLOW}⏳ Waiting for containers to be ready...${NC}"
sleep 15

# Run migrations
echo -e "${GREEN}💾 Running database migrations...${NC}"
docker compose exec -T app php artisan migrate --force

# Seed database
echo -e "${GREEN}🌱 Seeding database...${NC}"
docker compose exec -T app php artisan db:seed --force

# Generate Swagger documentation
echo -e "${GREEN}📚 Generating API documentation...${NC}"
docker compose exec -T app php artisan l5-swagger:generate

# Setup SSL certificate
echo -e "${GREEN}🔒 Setting up SSL certificate...${NC}"
echo "Do you want to setup SSL now? (y/n)"
read SETUP_SSL

if [ "$SETUP_SSL" = "y" ]; then
    certbot --nginx -d andrepangestu.com -d www.andrepangestu.com
fi

echo -e "${GREEN}✅ Deployment setup complete!${NC}"
echo ""
echo -e "${GREEN}🎉 Your API is now running!${NC}"
echo ""
echo "Next steps:"
echo "1. Test your API: curl https://andrepangestu.com/api/test"
echo "2. View Swagger docs: https://andrepangestu.com/api/documentation"
echo "3. Configure GitHub Actions secrets for CI/CD"
echo ""
echo "Run 'docker compose logs -f' to view application logs"
