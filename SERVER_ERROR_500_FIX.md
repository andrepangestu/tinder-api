# 🚨 Server Error 500 - Troubleshooting Checklist

## Current Status

**Date:** November 8, 2025  
**Issue:** All API endpoints returning HTTP 500 Internal Server Error

### Affected Endpoints:
- ❌ https://andrepangestu.com/api/documentation (500 Error)
- ❌ https://andrepangestu.com/api/api-docs.json (500 Error)
- ❌ https://andrepangestu.com/api/people/recommended (500 Error)
- ✅ https://andrepangestu.com/ (200 OK - main site working)

## 🔍 Diagnosis Steps

You need to SSH to the server and check these:

```bash
# SSH to server
ssh user@your-server-ip

# Navigate to project
cd /var/www/tinder-api
```

### 1. Check Docker Containers Status

```bash
# Check if containers are running
docker compose ps

# Expected output should show:
# - app (PHP-FPM) - Running
# - nginx - Running
# - db (MySQL) - Running
# - redis - Running

# If any container is not running:
docker compose up -d
```

### 2. Check Laravel Logs

```bash
# View recent errors
docker compose exec -T app tail -100 storage/logs/laravel.log

# Watch logs in real-time
docker compose exec -T app tail -f storage/logs/laravel.log

# Look for errors like:
# - Database connection errors
# - Missing .env variables
# - Permission issues
# - PHP fatal errors
```

### 3. Check Nginx Logs

```bash
# Check error logs
docker compose logs nginx | tail -50

# Check for:
# - Permission denied errors
# - PHP-FPM connection errors
# - File not found errors
```

### 4. Check PHP-FPM Status

```bash
# Check PHP-FPM logs
docker compose logs app | tail -50

# Test PHP
docker compose exec -T app php -v
```

### 5. Check Database Connection

```bash
# Test database connection
docker compose exec -T app php artisan migrate:status

# If fails, check database container
docker compose exec db mysql -u root -p${DB_ROOT_PASSWORD} -e "SHOW DATABASES;"
```

### 6. Check File Permissions

```bash
# Storage should be writable
docker compose exec -T app ls -la storage/

# Fix permissions if needed
docker compose exec -T app chmod -R 775 storage bootstrap/cache
docker compose exec -T app chown -R www-data:www-data storage bootstrap/cache
```

### 7. Check Environment Variables

```bash
# View .env file
cat .env | grep -v PASSWORD | grep -v SECRET

# Verify critical variables:
# - APP_KEY (should be set)
# - DB_* variables
# - MAIL_* variables
```

## 🔧 Common Fixes

### Fix 1: Clear All Caches

```bash
docker compose exec -T app php artisan config:clear
docker compose exec -T app php artisan route:clear
docker compose exec -T app php artisan cache:clear
docker compose exec -T app php artisan view:clear
```

### Fix 2: Regenerate Autoloader

```bash
docker compose exec -T app composer dump-autoload
```

### Fix 3: Fix Permissions

```bash
docker compose exec -T app chmod -R 775 storage bootstrap/cache
docker compose exec -T app chown -R www-data:www-data storage bootstrap/cache
```

### Fix 4: Restart Containers

```bash
docker compose restart

# Or restart specific services
docker compose restart app nginx
```

### Fix 5: Check and Run Migrations

```bash
# Check migration status
docker compose exec -T app php artisan migrate:status

# Run pending migrations
docker compose exec -T app php artisan migrate --force
```

### Fix 6: Rebuild Containers (if necessary)

```bash
docker compose down
docker compose build --no-cache
docker compose up -d
```

## 🎯 Quick Fix Commands (All-in-One)

```bash
# Navigate to project
cd /var/www/tinder-api

# Fix permissions
docker compose exec -T app chmod -R 775 storage bootstrap/cache
docker compose exec -T app chown -R www-data:www-data storage bootstrap/cache

# Clear all caches
docker compose exec -T app php artisan config:clear
docker compose exec -T app php artisan route:clear
docker compose exec -T app php artisan cache:clear
docker compose exec -T app php artisan view:clear

# Regenerate autoloader
docker compose exec -T app composer dump-autoload

# Run migrations
docker compose exec -T app php artisan migrate --force

# Regenerate Swagger
docker compose exec -T app php artisan l5-swagger:generate

# Restart containers
docker compose restart app nginx

# Test
curl -I https://andrepangestu.com/api/people/recommended
```

## 📋 Verification Checklist

After applying fixes, verify:

- [ ] Docker containers are running: `docker compose ps`
- [ ] No errors in Laravel logs: `docker compose exec -T app tail -20 storage/logs/laravel.log`
- [ ] No errors in Nginx logs: `docker compose logs nginx | tail -20`
- [ ] Database connection works: `docker compose exec -T app php artisan migrate:status`
- [ ] API endpoint works: `curl -I https://andrepangestu.com/api/people/recommended`
- [ ] Swagger UI works: `curl -I https://andrepangestu.com/api/documentation`

## 🚀 If Everything Fails - Redeploy

Trigger a fresh deployment from GitHub:

```bash
# From local machine
git commit --allow-empty -m "Trigger redeployment to fix server errors"
git push origin main
```

The GitHub Actions workflow will:
1. Run tests
2. Deploy fresh code
3. Run migrations
4. Clear caches
5. Regenerate Swagger

## 📞 Need More Help?

If the issue persists, gather this information:

```bash
# System info
docker compose version
docker compose ps

# Logs
docker compose logs --tail=100 > logs.txt

# Environment check
docker compose exec -T app php artisan about

# Send logs to developer for analysis
```

---

**Last Updated:** November 8, 2025
