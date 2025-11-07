# Debug Commands - Copy Paste Langsung di Server

## 1. LIHAT ERROR SEBENARNYA (Paling Penting!)

```bash
# SSH ke server
ssh root@andrepangestu.com

# Navigate ke project
cd /var/www/tinder-api

# Lihat logs container
docker compose logs --tail=100 app

# Lihat Laravel log
docker compose exec app cat /var/www/storage/logs/laravel.log

# Enable debug mode SEMENTARA untuk lihat error detail
docker compose exec app sed -i 's/APP_DEBUG=false/APP_DEBUG=true/' .env
docker compose exec app php artisan config:clear
docker compose restart app

# Test endpoint dan lihat error
curl -v https://andrepangestu.com/api/test

# MATIKAN debug mode setelah lihat error
docker compose exec app sed -i 's/APP_DEBUG=true/APP_DEBUG=false/' .env
docker compose restart app
```

---

## 2. FIX PERMISSION (Penyebab paling umum)

```bash
cd /var/www/tinder-api

# Fix permission storage
docker compose exec app chown -R www-data:www-data /var/www/storage
docker compose exec app chmod -R 775 /var/www/storage

# Fix permission bootstrap cache
docker compose exec app chown -R www-data:www-data /var/www/bootstrap/cache
docker compose exec app chmod -R 775 /var/www/bootstrap/cache

# Create directories jika belum ada
docker compose exec app mkdir -p /var/www/storage/logs
docker compose exec app mkdir -p /var/www/storage/framework/sessions
docker compose exec app mkdir -p /var/www/storage/framework/views
docker compose exec app mkdir -p /var/www/storage/framework/cache

# Restart
docker compose restart app
```

---

## 3. CEK .ENV FILE

```bash
# Cek apakah .env ada
docker compose exec app test -f /var/www/.env && echo "✅ .env exists" || echo "❌ .env NOT FOUND"

# Lihat isi .env
docker compose exec app cat /var/www/.env

# Cek APP_KEY
docker compose exec app grep "APP_KEY" /var/www/.env

# Jika APP_KEY kosong, generate
docker compose exec app php artisan key:generate --force
```

---

## 4. CLEAR SEMUA CACHE

```bash
cd /var/www/tinder-api

docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear
docker compose exec app php artisan route:clear
docker compose exec app php artisan view:clear
docker compose exec app php artisan optimize:clear

docker compose restart app
```

---

## 5. CEK DATABASE CONNECTION

```bash
# Cek MySQL running
docker compose ps

# Restart MySQL jika perlu
docker compose restart db

# Wait
sleep 10

# Test connection
docker compose exec db mysql -u root -p${DB_ROOT_PASSWORD} -e "SHOW DATABASES;"

# Test dari Laravel
docker compose exec app php artisan migrate:status
```

---

## 6. REINSTALL DEPENDENCIES

```bash
cd /var/www/tinder-api

# Install composer dependencies
docker compose exec app composer install --no-dev --optimize-autoloader --no-interaction

# Clear dan cache ulang
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
```

---

## 7. CEK VENDOR AUTOLOAD

```bash
# Cek apakah vendor ada
docker compose exec app ls -la /var/www/vendor

# Cek autoload
docker compose exec app test -f /var/www/vendor/autoload.php && echo "✅ Autoload OK" || echo "❌ Autoload MISSING"

# Regenerate autoload
docker compose exec app composer dump-autoload
```

---

## 8. TEST INTERNAL API

```bash
# Test dari dalam container
docker compose exec app curl -s http://localhost/api/test

# Test dari luar
curl -v https://andrepangestu.com/api/test
```

---

## 9. RESTART SEMUA CONTAINERS

```bash
cd /var/www/tinder-api

# Stop semua
docker compose down

# Clean orphans
docker compose down --remove-orphans

# Start ulang
docker compose up -d

# Wait
sleep 15

# Test
curl https://andrepangestu.com/api/test
```

---

## 10. NUCLEAR OPTION - REBUILD SEMUA

```bash
cd /var/www/tinder-api

# Backup .env
cp .env .env.backup

# Stop dan hapus semua
docker compose down -v

# Rebuild dari scratch
docker compose build --no-cache

# Start
docker compose up -d

# Wait for MySQL
sleep 20

# Install dependencies
docker compose exec app composer install --no-dev --optimize-autoloader

# Generate key
docker compose exec app php artisan key:generate --force

# Fix permissions
docker compose exec app chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
docker compose exec app chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Run migrations
docker compose exec app php artisan migrate --force

# Clear cache
docker compose exec app php artisan optimize:clear

# Cache config
docker compose exec app php artisan config:cache

# Test
curl https://andrepangestu.com/api/test
```

---

## 📊 DIAGNOSTIC COMMANDS

```bash
# Container status
docker compose ps

# Container logs
docker compose logs app

# PHP version
docker compose exec app php -v

# Laravel version
docker compose exec app php artisan --version

# Check routes
docker compose exec app php artisan route:list

# Check config
docker compose exec app php artisan about

# Check storage permissions
docker compose exec app ls -la /var/www/storage

# Check if PHP-FPM running
docker compose exec app ps aux | grep php-fpm

# Nginx error log
docker compose exec app cat /var/log/nginx/error.log
```

---

## 🎯 COPY-PASTE INI UNTUK QUICK FIX:

```bash
ssh root@andrepangestu.com
cd /var/www/tinder-api
docker compose exec app chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
docker compose exec app chmod -R 775 /var/www/storage /var/www/bootstrap/cache
docker compose exec app php artisan key:generate --force
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear
docker compose restart app
sleep 5
curl https://andrepangestu.com/api/test
```

---

## 🔍 CARA LIHAT ERROR DETAIL:

```bash
# Enable debug
docker compose exec app bash -c "echo 'APP_DEBUG=true' >> .env"
docker compose exec app php artisan config:clear
docker compose restart app

# Access API dan lihat error di browser
# https://andrepangestu.com/api/test

# Atau lihat di terminal
docker compose logs --tail=50 app

# JANGAN LUPA MATIKAN!
docker compose exec app sed -i 's/APP_DEBUG=true/APP_DEBUG=false/' .env
docker compose restart app
```

---

## 📱 MOBILE ACCESS TESTING

```bash
# Test CORS dari mobile origin
curl -H "Origin: https://mobile-app.example.com" \
     -H "User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X)" \
     -I https://andrepangestu.com/api/test

# Test dengan Android user agent
curl -H "Origin: https://localhost" \
     -H "User-Agent: Mozilla/5.0 (Linux; Android 10)" \
     -I https://andrepangestu.com/api/test

# Verify CORS headers muncul
curl -k -H "Origin: https://example.com" \
     -I https://andrepangestu.com/api/test | grep -i access-control

# Test endpoint dari mobile
curl -k https://andrepangestu.com/api/people/recommended

# Test POST from mobile
curl -X POST -k https://andrepangestu.com/api/people/1/like \
     -H "Content-Type: application/json" \
     -H "Origin: https://mobile-app.com"
```

**Expected CORS Headers:**
```
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH
Access-Control-Allow-Headers: *
Access-Control-Expose-Headers: Authorization, Content-Type, X-Requested-With
```
