#!/bin/bash

###############################################################################
# Nginx Setup Script for Tinder API
# This script configures nginx on the host server to proxy to Docker container
###############################################################################

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${GREEN}================================================================${NC}"
echo -e "${GREEN}  🔧 NGINX CONFIGURATION SETUP${NC}"
echo -e "${GREEN}================================================================${NC}"
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}Please run as root (use sudo)${NC}"
    exit 1
fi

echo -e "${YELLOW}Option 1: Add /api location to existing andrepangestu.com config${NC}"
echo -e "${YELLOW}Option 2: Create separate api.andrepangestu.com subdomain${NC}"
echo ""
read -p "Choose option (1 or 2): " OPTION

if [ "$OPTION" = "1" ]; then
    echo ""
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${YELLOW}Adding /api location to main domain config${NC}"
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    
    # Find the main nginx config
    MAIN_CONFIG="/etc/nginx/sites-available/andrepangestu.com"
    
    if [ ! -f "$MAIN_CONFIG" ]; then
        echo -e "${RED}Main config not found at $MAIN_CONFIG${NC}"
        echo "Please specify the correct path:"
        read -p "Path: " MAIN_CONFIG
        
        if [ ! -f "$MAIN_CONFIG" ]; then
            echo -e "${RED}File not found!${NC}"
            exit 1
        fi
    fi
    
    # Backup original config
    cp "$MAIN_CONFIG" "${MAIN_CONFIG}.backup.$(date +%Y%m%d_%H%M%S)"
    echo -e "${GREEN}✓ Backup created${NC}"
    
    # Check if /api location already exists
    if grep -q "location /api" "$MAIN_CONFIG"; then
        echo -e "${YELLOW}⚠ /api location already exists in config${NC}"
        read -p "Do you want to replace it? (y/n): " REPLACE
        
        if [ "$REPLACE" = "y" ]; then
            # Remove existing /api location block
            sed -i '/location \/api {/,/}/d' "$MAIN_CONFIG"
            echo -e "${GREEN}✓ Removed old /api location${NC}"
        else
            echo "Keeping existing configuration"
            exit 0
        fi
    fi
    
    # Add /api location before the last closing brace of the server block
    # Find the HTTPS server block and add location before its closing brace
    cat > /tmp/api_location.conf << 'EOF'

    # API Laravel - Proxy to Docker container
    location /api {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header X-Forwarded-Host $server_name;
        
        proxy_buffering off;
        proxy_request_buffering off;
        
        proxy_connect_timeout 60s;
        proxy_send_timeout 60s;
        proxy_read_timeout 60s;
    }
EOF

    # Insert before the last } in the HTTPS server block
    awk '/listen 443.*ssl/ {https=1} https && /^}$/ && !done {system("cat /tmp/api_location.conf"); done=1} 1' "$MAIN_CONFIG" > "${MAIN_CONFIG}.new"
    mv "${MAIN_CONFIG}.new" "$MAIN_CONFIG"
    
    echo -e "${GREEN}✓ Added /api location to nginx config${NC}"
    
elif [ "$OPTION" = "2" ]; then
    echo ""
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${YELLOW}Creating api.andrepangestu.com subdomain${NC}"
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    
    # Copy the nginx config template
    cp /var/www/tinder-api/nginx-host-config.conf /etc/nginx/sites-available/tinder-api
    
    # Enable the site
    ln -sf /etc/nginx/sites-available/tinder-api /etc/nginx/sites-enabled/tinder-api
    
    echo -e "${GREEN}✓ Created api.andrepangestu.com config${NC}"
    echo ""
    echo -e "${YELLOW}Don't forget to:${NC}"
    echo "1. Add DNS A record: api.andrepangestu.com -> your-server-ip"
    echo "2. Get SSL certificate: sudo certbot --nginx -d api.andrepangestu.com"
else
    echo -e "${RED}Invalid option${NC}"
    exit 1
fi

# Test nginx configuration
echo ""
echo -e "${YELLOW}Testing nginx configuration...${NC}"
nginx -t

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Nginx configuration is valid${NC}"
    
    read -p "Reload nginx now? (y/n): " RELOAD
    if [ "$RELOAD" = "y" ]; then
        systemctl reload nginx
        echo -e "${GREEN}✓ Nginx reloaded${NC}"
    fi
else
    echo -e "${RED}✗ Nginx configuration has errors${NC}"
    echo "Restoring backup..."
    if [ -f "${MAIN_CONFIG}.backup."* ]; then
        cp "${MAIN_CONFIG}.backup."* "$MAIN_CONFIG"
        echo -e "${GREEN}✓ Backup restored${NC}"
    fi
    exit 1
fi

echo ""
echo -e "${GREEN}================================================================${NC}"
echo -e "${GREEN}  ✅ NGINX SETUP COMPLETE!${NC}"
echo -e "${GREEN}================================================================${NC}"
echo ""
echo -e "${YELLOW}Next steps:${NC}"
echo "1. Ensure Docker container is running: cd /var/www/tinder-api && docker compose ps"
echo "2. Test API: curl -I http://localhost:8080/api/people/recommended"
echo "3. Test via nginx: curl -I https://andrepangestu.com/api/people/recommended"
echo ""
