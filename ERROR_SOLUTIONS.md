# Common Error 500 Messages & Solutions

## Error: "Class 'Illuminate\Foundation\Application' not found"

### Cause: Missing vendor dependencies

### Fix:

```bash
docker compose exec app composer install --no-dev --optimize-autoloader
docker compose restart app
```

---

## Error: "No application encryption key has been specified"

### Cause: Missing APP_KEY in .env

### Fix:

```bash
docker compose exec app php artisan key:generate --force
docker compose exec app php artisan config:clear
docker compose restart app
```

---

## Error: "file_put_contents(...): Failed to open stream: Permission denied"

### Cause: Permission issues on storage or cache

### Fix:

```bash
docker compose exec app chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
docker compose exec app chmod -R 775 /var/www/storage /var/www/bootstrap/cache
docker compose restart app
```

---

## Error: "SQLSTATE[HY000] [2002] Connection refused"

### Cause: Cannot connect to MySQL

### Fix:

```bash
# Check if MySQL is running
docker compose ps

# Restart MySQL
docker compose restart db

# Wait for it to be ready
sleep 10

# Check .env has correct DB settings
docker compose exec app grep "DB_" /var/www/.env

# Test connection
docker compose exec app php artisan migrate:status
```

---

## Error: "419 Page Expired" or CSRF Token Mismatch

### Cause: Session issues

### Fix:

```bash
docker compose exec app php artisan session:table
docker compose exec app php artisan migrate
docker compose exec app php artisan config:clear
docker compose restart app
```

---

## Error: "Class '...' not found" (any class)

### Cause: Composer autoload not generated

### Fix:

```bash
docker compose exec app composer dump-autoload -o
docker compose exec app php artisan optimize:clear
docker compose restart app
```

---

## Error: "The stream or file ... could not be opened"

### Cause: Log file permission issue

### Fix:

```bash
docker compose exec app mkdir -p /var/www/storage/logs
docker compose exec app touch /var/www/storage/logs/laravel.log
docker compose exec app chown -R www-data:www-data /var/www/storage
docker compose exec app chmod -R 775 /var/www/storage
docker compose restart app
```

---

## Error: "RuntimeException: A facade root has not been set"

### Cause: Cached config is corrupted

### Fix:

```bash
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear
docker compose exec app php artisan view:clear
docker compose restart app
```

---

## Error: "Syntax error, unexpected '...'"

### Cause: PHP syntax error in code or cached config

### Fix:

```bash
# Clear all caches first
docker compose exec app php artisan optimize:clear

# Check Laravel log for specific file
docker compose exec app tail -50 /var/www/storage/logs/laravel.log

# If in config cache, clear it
docker compose exec app rm -rf /var/www/bootstrap/cache/*.php
docker compose restart app
```

---

## Error: "No such file or directory" for vendor/autoload.php

### Cause: Vendor directory not mounted correctly or empty

### Fix:

```bash
# Check if vendor exists
docker compose exec app ls -la /var/www/vendor

# Reinstall
docker compose exec app rm -rf /var/www/vendor
docker compose exec app composer install --no-dev --optimize-autoloader

docker compose restart app
```

---

## Error: "CORS policy" or "Access-Control-Allow-Origin"

### Cause: CORS not configured correctly

### Fix:

```bash
# Check CORS config
docker compose exec app cat /var/www/config/cors.php

# Clear config cache
docker compose exec app php artisan config:clear

# Restart
docker compose restart app

# Also check Nginx CORS headers in nginx-config.conf
```

---

## Error: "Route not found" or 404 on valid routes

### Cause: Route cache outdated

### Fix:

```bash
docker compose exec app php artisan route:clear
docker compose exec app php artisan route:cache
docker compose exec app php artisan route:list
docker compose restart app
```

---

## Error: "Maximum execution time exceeded"

### Cause: Long-running query or infinite loop

### Fix:

```bash
# Check what's running
docker compose exec app ps aux | grep php

# Check MySQL processes
docker compose exec db mysql -u root -p${DB_ROOT_PASSWORD} -e "SHOW PROCESSLIST;"

# Restart app
docker compose restart app

# Check logs for slow queries
docker compose logs app | grep "exceeded"
```

---

## Error: "Allowed memory size exhausted"

### Cause: PHP memory limit too low

### Fix:

```bash
# Check current memory limit
docker compose exec app php -i | grep memory_limit

# This should be configured in Dockerfile/php.ini
# For quick fix, restart containers
docker compose restart app
```

---

## Error: Container keeps restarting or exits immediately

### Cause: Fatal error on startup

### Fix:

```bash
# See container logs
docker compose logs app

# Check what's failing
docker compose ps

# Try to start in foreground to see error
docker compose up app

# Common fix: rebuild
docker compose build --no-cache app
docker compose up -d
```

---

## Error: "Connection reset by peer" or "Broken pipe"

### Cause: Nginx/PHP-FPM communication issue

### Fix:

```bash
# Check if PHP-FPM is running
docker compose exec app ps aux | grep php-fpm

# Restart app container
docker compose restart app

# Check Nginx config
docker compose exec app nginx -t

# Restart Nginx inside container
docker compose exec app nginx -s reload
```

---

## No Error Message, Just "500 Internal Server Error"

### Cause: Error display is disabled

### Fix:

```bash
# Enable debug temporarily
docker compose exec app sed -i 's/APP_DEBUG=false/APP_DEBUG=true/' /var/www/.env
docker compose exec app php artisan config:clear
docker compose restart app

# Now access the API to see actual error
curl https://andrepangestu.com/api/test

# Or check Laravel log
docker compose exec app tail -100 /var/www/storage/logs/laravel.log

# IMPORTANT: Disable debug after
docker compose exec app sed -i 's/APP_DEBUG=true/APP_DEBUG=false/' /var/www/.env
docker compose restart app
```

---

## 🆘 MASTER FIX (Try This If Nothing Above Works)

```bash
cd /var/www/tinder-api

# 1. Stop everything
docker compose down --remove-orphans

# 2. Remove old containers
docker container prune -f

# 3. Start fresh
docker compose up -d

# 4. Wait
sleep 20

# 5. Fix permissions
docker compose exec app chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
docker compose exec app chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# 6. Install dependencies
docker compose exec app composer install --no-dev --optimize-autoloader

# 7. Generate key
docker compose exec app php artisan key:generate --force

# 8. Clear everything
docker compose exec app php artisan optimize:clear

# 9. Run migrations
docker compose exec app php artisan migrate --force

# 10. Cache config
docker compose exec app php artisan config:cache

# 11. Test
curl https://andrepangestu.com/api/test
```
