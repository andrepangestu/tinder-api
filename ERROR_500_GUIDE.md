# Error 500 Troubleshooting Guide

## Quick Fix (Run on Server)

```bash
ssh root@andrepangestu.com
cd /var/www/tinder-api
chmod +x fix-500.sh
./fix-500.sh
```

## Common Causes of Error 500

### 1. **Missing or Invalid .env File**

**Check:**

```bash
docker compose exec app cat .env | head -n 10
```

**Fix:**

```bash
# If .env doesn't exist
cp .env.production .env

# Generate APP_KEY if missing
docker compose exec app php artisan key:generate

# Update required variables
docker compose exec app php artisan config:clear
```

### 2. **Permission Issues**

**Symptoms:** Cannot write to storage, cache, or logs

**Check:**

```bash
docker compose exec app ls -la /var/www/storage
docker compose exec app ls -la /var/www/bootstrap/cache
```

**Fix:**

```bash
docker compose exec app chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
docker compose exec app chmod -R 775 /var/www/storage /var/www/bootstrap/cache
```

### 3. **Database Connection Failed**

**Check:**

```bash
docker compose exec app php artisan migrate:status
docker compose logs db
```

**Fix:**

```bash
# Check if MySQL is running
docker compose ps

# Restart MySQL
docker compose restart db

# Wait for MySQL to be ready
sleep 10

# Test connection
docker compose exec db mysql -u root -p${DB_ROOT_PASSWORD} -e "SHOW DATABASES;"
```

### 4. **Composer Dependencies Missing**

**Check:**

```bash
docker compose exec app ls -la /var/www/vendor
```

**Fix:**

```bash
docker compose exec app composer install --no-dev --optimize-autoloader
```

### 5. **Cache Configuration Issues**

**Symptoms:** Config cached with wrong values

**Fix:**

```bash
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear
docker compose exec app php artisan route:clear
docker compose exec app php artisan view:clear
docker compose restart app
```

### 6. **PHP Errors / Syntax Errors**

**Check Logs:**

```bash
# Laravel logs
docker compose exec app tail -f /var/www/storage/logs/laravel.log

# Container logs
docker compose logs --tail=100 app

# Nginx error logs
docker compose exec app tail -f /var/log/nginx/error.log
```

**Enable Debug Mode (Temporarily):**

```bash
# Edit .env
APP_DEBUG=true

# Clear cache
docker compose exec app php artisan config:clear
docker compose restart app

# IMPORTANT: Set back to false after debugging!
APP_DEBUG=false
```

### 7. **CORS Middleware Issues**

**If error only on API calls from browser:**

Check `config/cors.php`:

```php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'allowed_origins' => ['*'],
```

**Fix:**

```bash
docker compose exec app php artisan config:clear
docker compose restart app
```

### 8. **Missing APP_KEY**

**Check:**

```bash
docker compose exec app php artisan key:generate --show
```

**Fix:**

```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan config:clear
docker compose restart app
```

## Debugging Steps

### Step 1: Run Debug Script

```bash
cd /var/www/tinder-api
chmod +x debug-500.sh
./debug-500.sh > debug-output.txt
cat debug-output.txt
```

### Step 2: Check Specific Endpoint

```bash
# Test basic endpoint
curl -v https://andrepangestu.com/api/test

# Check response headers
curl -I https://andrepangestu.com/api/test

# Test with localhost (bypass Nginx)
docker compose exec app curl -v http://localhost/api/test
```

### Step 3: Check Container Health

```bash
# All containers status
docker compose ps

# Application logs (live)
docker compose logs -f app

# Check if PHP-FPM is running
docker compose exec app ps aux | grep php
```

### Step 4: Test Database Connection

```bash
# MySQL connection from app
docker compose exec app php artisan tinker
# Then in tinker:
# \App\Models\Person::count()
# DB::connection()->getPdo()
# exit
```

### Step 5: Check Environment

```bash
# Show all config
docker compose exec app php artisan config:show

# Show specific config
docker compose exec app php artisan config:show database
docker compose exec app php artisan config:show app
```

## After Fixing

### Clear Everything and Restart

```bash
# Clear all caches
docker compose exec app php artisan optimize:clear

# Restart containers
docker compose restart

# Rebuild cache (production)
docker compose exec app php artisan optimize
```

### Test API

```bash
# Test endpoint
curl https://andrepangestu.com/api/test

# Expected response:
# {"message":"API is working!","timestamp":"...","people_count":100}
```

## Prevention

### 1. Always Check Before Deploy

```bash
# Local testing
php artisan test
php artisan config:clear
php artisan route:list
```

### 2. Monitor Logs

```bash
# Add to crontab for log rotation
docker compose exec app php artisan log:clear --days=7

# Or manually monitor
docker compose logs -f app
```

### 3. Use Proper Error Handling

Make sure `APP_DEBUG=false` in production and proper logging is configured in `.env`:

```env
APP_ENV=production
APP_DEBUG=false
LOG_CHANNEL=daily
LOG_LEVEL=error
```

## Getting Help

If error persists:

1. Run debug script: `./debug-500.sh > debug.log`
2. Check Laravel logs: `docker compose exec app cat /var/www/storage/logs/laravel.log`
3. Check container logs: `docker compose logs app > app.log`
4. Share the logs for further debugging

## Quick Recovery

If everything fails, rebuild from scratch:

```bash
cd /var/www/tinder-api

# Backup database (if needed)
docker compose exec db mysqldump -u root -p${DB_ROOT_PASSWORD} ${DB_DATABASE} > backup.sql

# Full rebuild
docker compose down -v
docker compose build --no-cache
docker compose up -d

# Restore
./fix-500.sh
```
