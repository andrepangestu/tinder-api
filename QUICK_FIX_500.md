# QUICK FIX - Error 500 Internal Server Error

## 🚨 If you're seeing Error 500, run this IMMEDIATELY:

```bash
# SSH to server
ssh root@andrepangestu.com

# Navigate to project
cd /var/www/tinder-api

# Pull latest fixes
git pull origin main

# Run emergency debug
chmod +x emergency-debug.sh
./emergency-debug.sh
```

This will show you the **actual error message**. Look for lines containing:
- `Fatal error`
- `Exception`
- `Error:`
- `failed`

---

## 🔧 Common Fixes

### Fix 1: Missing/Invalid .env
```bash
cd /var/www/tinder-api
cp .env.production .env
docker compose exec app php artisan key:generate
docker compose restart app
```

### Fix 2: Permission Issues
```bash
cd /var/www/tinder-api
chmod +x fix-500.sh
./fix-500.sh
```

### Fix 3: Corrupted Cache
```bash
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear
docker compose exec app php artisan view:clear
docker compose restart app
```

### Fix 4: Database Connection Failed
```bash
docker compose restart db
sleep 10
docker compose exec app php artisan migrate:status
```

### Fix 5: Missing Dependencies
```bash
docker compose exec app composer install --no-dev --optimize-autoloader
docker compose restart app
```

---

## 🆘 Nuclear Option (If Nothing Works)

```bash
cd /var/www/tinder-api
chmod +x force-fix.sh
./force-fix.sh
```

⚠️ This will rebuild everything from scratch (takes 5-10 minutes)

---

## 📊 Check What's Wrong

```bash
# See detailed configuration
chmod +x check-config.sh
./check-config.sh

# See live logs
docker compose logs -f app

# See Laravel logs
docker compose exec app tail -f /var/www/storage/logs/laravel.log
```

---

## 🧪 Test After Fix

```bash
# Test from server
docker compose exec app curl http://localhost/api/test

# Test from outside
curl https://andrepangestu.com/api/test

# Expected response:
# {"message":"API is working!","timestamp":"...","people_count":100}
```

---

## 📞 Get Help

If still failing after all fixes:

1. Run: `./emergency-debug.sh > debug.log`
2. Send the `debug.log` file
3. Include the output from: `docker compose logs app > app.log`

---

## 🔍 Most Common Issues (in order)

1. ❌ **Storage permissions** → Run `./fix-500.sh`
2. ❌ **Missing APP_KEY** → Run `docker compose exec app php artisan key:generate`
3. ❌ **Database not running** → Run `docker compose restart db`
4. ❌ **Corrupted cache** → Run `php artisan config:clear`
5. ❌ **Missing .env** → Copy from `.env.production`
6. ❌ **Missing vendor** → Run `composer install`
7. ❌ **Old containers** → Run `docker compose down && docker compose up -d`

---

**Start with `./emergency-debug.sh` to see the actual error!**
