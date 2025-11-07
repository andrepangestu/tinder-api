# Deployment Guide - Tinder API to DigitalOcean

This guide will walk you through deploying your Laravel Tinder API to DigitalOcean using Docker Compose and GitHub Actions CI/CD.

## Prerequisites

-   GitHub account with your repository
-   DigitalOcean account with a droplet (Ubuntu 22.04 LTS recommended)
-   Domain: `andrepangestu.com` pointing to `206.189.84.142`
-   SSH access to your droplet

## Part 1: Server Setup (DigitalOcean Droplet)

### 1.1 Connect to Your Droplet

```bash
ssh root@206.189.84.142
```

### 1.2 Update System

```bash
apt update && apt upgrade -y
```

### 1.3 Install Docker

```bash
# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sh get-docker.sh

# Start and enable Docker
systemctl start docker
systemctl enable docker

# Verify installation
docker --version
```

### 1.4 Install Docker Compose

```bash
# Install Docker Compose
apt install docker-compose-plugin -y

# Verify installation
docker compose version
```

### 1.5 Install Nginx (Reverse Proxy)

```bash
apt install nginx -y
systemctl start nginx
systemctl enable nginx
```

### 1.6 Install Certbot for SSL

```bash
apt install certbot python3-certbot-nginx -y
```

### 1.7 Configure Nginx as Reverse Proxy

Create Nginx configuration:

```bash
nano /etc/nginx/sites-available/andrepangestu.com
```

Add this configuration:

```nginx
server {
    listen 80;
    server_name andrepangestu.com www.andrepangestu.com;

    location / {
        proxy_pass http://localhost:80;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

Enable the site:

```bash
ln -s /etc/nginx/sites-available/andrepangestu.com /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx
```

### 1.8 Get SSL Certificate

```bash
certbot --nginx -d andrepangestu.com -d www.andrepangestu.com
```

### 1.9 Setup Application Directory

```bash
mkdir -p /var/www/tinder-api
cd /var/www/tinder-api
```

### 1.10 Setup Git

```bash
# Install Git if not installed
apt install git -y

# Generate SSH key for GitHub
ssh-keygen -t ed25519 -C "your_email@example.com"
cat ~/.ssh/id_ed25519.pub
```

Copy the SSH public key and add it to your GitHub repository:

1. Go to GitHub → Your Repository → Settings → Deploy keys
2. Click "Add deploy key"
3. Paste the key and check "Allow write access"

### 1.11 Clone Repository

```bash
cd /var/www/tinder-api
git clone git@github.com:YOUR_USERNAME/tinder-api.git .
```

### 1.12 Create Environment File

```bash
cp .env.production .env
nano .env
```

Update these values:

```env
APP_KEY=base64:YOUR_GENERATED_KEY_HERE
DB_DATABASE=tinder_api
DB_USERNAME=tinder_user
DB_PASSWORD=YOUR_SECURE_PASSWORD_HERE
DB_ROOT_PASSWORD=YOUR_SECURE_ROOT_PASSWORD_HERE
```

Generate APP_KEY locally and copy it:

```bash
php artisan key:generate --show
```

### 1.13 Start Docker Containers

```bash
docker compose up -d
```

### 1.14 Run Migrations

```bash
docker compose exec app php artisan migrate --force
docker compose exec app php artisan db:seed --force
```

### 1.15 Generate Swagger Documentation

```bash
docker compose exec app php artisan l5-swagger:generate
```

## Part 2: GitHub Actions Setup

### 2.1 Generate SSH Key for GitHub Actions

On your local machine or server:

```bash
ssh-keygen -t ed25519 -C "github-actions" -f github-actions-key
```

### 2.2 Add Public Key to Server

Copy the public key to your server:

```bash
cat github-actions-key.pub
```

On your server:

```bash
nano ~/.ssh/authorized_keys
# Paste the public key
```

### 2.3 Add Secrets to GitHub

Go to your GitHub repository → Settings → Secrets and variables → Actions → New repository secret

Add these secrets:

| Secret Name        | Value                                         |
| ------------------ | --------------------------------------------- |
| `SSH_PRIVATE_KEY`  | Content of `github-actions-key` (private key) |
| `SERVER_HOST`      | `206.189.84.142`                              |
| `SERVER_USER`      | `root`                                        |
| `APP_KEY`          | Your generated Laravel APP_KEY                |
| `DB_DATABASE`      | `tinder_api`                                  |
| `DB_USERNAME`      | `tinder_user`                                 |
| `DB_PASSWORD`      | Your database password                        |
| `DB_ROOT_PASSWORD` | Your root password                            |

### 2.4 Commit and Push

```bash
git add .
git commit -m "Add Docker and CI/CD configuration"
git push origin main
```

The GitHub Action will automatically trigger and deploy your application!

## Part 3: Access Your Application

### 3.1 API Endpoints

-   **Base URL**: `https://andrepangestu.com/api`
-   **Swagger Documentation**: `https://andrepangestu.com/api/documentation`

### 3.2 Test Endpoints

```bash
# Test endpoint
curl https://andrepangestu.com/api/test

# Register guest user
curl -X POST https://andrepangestu.com/api/auth/guest \
  -H "Accept: application/json"

# Get recommended people
curl https://andrepangestu.com/api/people/recommended
```

## Part 4: Maintenance Commands

### View Logs

```bash
# Application logs
docker compose logs -f app

# Nginx logs
docker compose logs -f

# System logs
tail -f /var/log/nginx/error.log
```

### Restart Services

```bash
# Restart all containers
docker compose restart

# Restart specific service
docker compose restart app

# Rebuild and restart
docker compose down
docker compose up -d --build
```

### Run Artisan Commands

```bash
# Run migrations
docker compose exec app php artisan migrate

# Clear cache
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear
docker compose exec app php artisan route:clear

# Regenerate Swagger docs
docker compose exec app php artisan l5-swagger:generate
```

### Database Backup

```bash
# Backup database
docker compose exec db mysqldump -u root -p tinder_api > backup_$(date +%Y%m%d).sql

# Restore database
docker compose exec -T db mysql -u root -p tinder_api < backup_20251107.sql
```

### Update Application

```bash
cd /var/www/tinder-api
git pull origin main
docker compose down
docker compose up -d --build
docker compose exec app php artisan migrate --force
docker compose exec app php artisan config:cache
```

## Part 5: Monitoring & Security

### 5.1 Setup Firewall

```bash
ufw allow OpenSSH
ufw allow 'Nginx Full'
ufw enable
ufw status
```

### 5.2 Monitor Resources

```bash
# Docker stats
docker stats

# Disk usage
df -h

# Memory usage
free -h
```

### 5.3 Auto-renewal SSL

Certbot will auto-renew. Test renewal:

```bash
certbot renew --dry-run
```

## Troubleshooting

### Issue: Container won't start

```bash
docker compose logs app
docker compose down
docker compose up -d
```

### Issue: Database connection failed

Check database container:

```bash
docker compose ps
docker compose logs db
```

### Issue: Permission denied

Fix permissions:

```bash
docker compose exec app chown -R www-data:www-data /var/www/storage
docker compose exec app chmod -R 755 /var/www/storage
```

### Issue: Swagger not loading

Regenerate documentation:

```bash
docker compose exec app php artisan l5-swagger:generate
docker compose exec app php artisan cache:clear
```

## Support

For issues, check:

-   Application logs: `docker compose logs -f app`
-   Nginx logs: `tail -f /var/log/nginx/error.log`
-   System logs: `journalctl -xe`

---

**Deployment successful! 🚀**

Your API is now live at: https://andrepangestu.com/api
Swagger documentation: https://andrepangestu.com/api/documentation
