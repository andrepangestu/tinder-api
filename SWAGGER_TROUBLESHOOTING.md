# 🔧 Swagger Troubleshooting Guide

This guide helps you fix Swagger/API Documentation issues in production and development environments.

## 🚨 Common Issues

### Issue 1: Swagger UI Returns 404 or Blank Page

**Symptoms:**
- Accessing `/api/documentation` shows 404 error
- Swagger UI loads but shows no endpoints
- "Failed to load API definition" error

**Solution:**

```bash
# SSH to server
ssh user@your-server-ip

# Navigate to project directory
cd /var/www/tinder-api

# Regenerate Swagger documentation
docker compose exec -T app php artisan l5-swagger:generate

# Clear all caches
docker compose exec -T app php artisan config:clear
docker compose exec -T app php artisan route:clear
docker compose exec -T app php artisan view:clear
docker compose exec -T app php artisan cache:clear

# Restart containers if needed
docker compose restart app nginx
```

---

### Issue 2: Swagger JSON File Not Found

**Symptoms:**
- Error: "api-docs.json not found"
- 404 when accessing `/api/api-docs.json`

**Solution:**

```bash
# Check if storage directory exists and has proper permissions
docker compose exec -T app ls -la storage/api-docs/

# If directory doesn't exist, create it
docker compose exec -T app mkdir -p storage/api-docs

# Set proper permissions
docker compose exec -T app chmod -R 775 storage/api-docs
docker compose exec -T app chown -R www-data:www-data storage/api-docs

# Regenerate documentation
docker compose exec -T app php artisan l5-swagger:generate
```

---

### Issue 3: Swagger Works Locally But Not in Production

**Symptoms:**
- Swagger works in development environment
- Doesn't work after deployment to server

**Causes & Solutions:**

#### A. Environment Configuration

Check `config/l5-swagger.php` or `.env`:

```bash
# View current config
docker compose exec -T app php artisan config:show l5-swagger

# Ensure these settings in config/l5-swagger.php
L5_SWAGGER_CONST_HOST=https://andrepangestu.com
```

#### B. File Permissions

```bash
# Fix storage permissions
docker compose exec -T app chmod -R 775 storage
docker compose exec -T app chown -R www-data:www-data storage
```

#### C. Nginx Configuration

Ensure nginx serves the swagger files correctly. Check `docker/nginx/default.conf`:

```nginx
# Should have this location block
location ~ ^/api/api-docs {
    try_files $uri $uri/ /index.php?$query_string;
}
```

---

### Issue 4: Swagger Shows Old API Endpoints

**Symptoms:**
- New endpoints don't appear in Swagger
- Documentation is outdated
- Recently added controllers are missing

**Solution:**

```bash
# Clear all caches and regenerate
docker compose exec -T app php artisan config:clear
docker compose exec -T app php artisan route:clear
docker compose exec -T app php artisan cache:clear
docker compose exec -T app php artisan l5-swagger:generate

# Verify routes are registered
docker compose exec -T app php artisan route:list | grep api
```

---

## 🔍 Verification Steps

After applying fixes, verify Swagger is working:

### 1. Check Swagger JSON Generated

```bash
# Check if file exists
docker compose exec -T app ls -la storage/api-docs/api-docs.json

# View file content (should be valid JSON)
docker compose exec -T app cat storage/api-docs/api-docs.json | head -20
```

### 2. Test Swagger UI Endpoint

```bash
# Using curl
curl -I https://andrepangestu.com/api/documentation

# Should return HTTP 200
```

### 3. Test API Documentation JSON

```bash
curl https://andrepangestu.com/api/api-docs.json | jq .

# Should return valid JSON with OpenAPI spec
```

### 4. Check Laravel Logs

```bash
# View recent logs
docker compose exec -T app tail -50 storage/logs/laravel.log

# Watch logs in real-time
docker compose exec -T app tail -f storage/logs/laravel.log
```

### 5. Check Nginx Logs

```bash
# Access logs
docker compose logs nginx | tail -50

# Error logs
docker compose logs nginx | grep error
```

---

## 🚀 Automated Fix via GitHub Actions

The deployment workflow automatically regenerates Swagger on every deployment. If Swagger is broken, you can trigger a re-deployment:

```bash
# Make a small change and push
git commit --allow-empty -m "Trigger deployment to regenerate Swagger"
git push origin main
```

The workflow will automatically:
1. ✅ Clear all caches
2. ✅ Regenerate Swagger documentation
3. ✅ Restart necessary services

---

## 📝 Manual Deployment Commands (One-Liner)

If you need to quickly fix Swagger on the server:

```bash
# Complete Swagger regeneration (copy and paste this entire block)
docker compose exec -T app php artisan config:clear && \
docker compose exec -T app php artisan route:clear && \
docker compose exec -T app php artisan cache:clear && \
docker compose exec -T app php artisan l5-swagger:generate && \
echo "✅ Swagger regenerated successfully!"
```

---

## 🛠 Development Environment

For local development issues:

```bash
# Clear caches
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear

# Regenerate Swagger
php artisan l5-swagger:generate

# Start server
php artisan serve

# Visit Swagger UI
# http://localhost:8000/api/documentation
```

---

## 📋 Checklist for Swagger Issues

- [ ] Swagger JSON file exists in `storage/api-docs/api-docs.json`
- [ ] Storage directory has proper permissions (775)
- [ ] All caches are cleared
- [ ] Routes are properly registered (`php artisan route:list`)
- [ ] Controllers have proper `@OA\` annotations
- [ ] `.env` has correct `L5_SWAGGER_CONST_HOST` value
- [ ] Nginx configuration allows access to `/api/documentation`
- [ ] No errors in Laravel logs
- [ ] API endpoints are working (test with curl)

---

## 🆘 Still Not Working?

### Check Controller Annotations

Ensure controllers have OpenAPI annotations:

```php
/**
 * @OA\Get(
 *     path="/api/people/recommended",
 *     summary="Get recommended people",
 *     tags={"People"},
 *     @OA\Response(response=200, description="Success")
 * )
 */
```

### Verify l5-swagger Package

```bash
# Check if package is installed
docker compose exec -T app composer show darkaonline/l5-swagger

# Reinstall if needed
docker compose exec -T app composer require darkaonline/l5-swagger
```

### Contact Support

If issues persist:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Check Nginx logs: `docker compose logs nginx`
3. Verify environment variables
4. Review recent code changes

---

## 📚 Related Documentation

- [L5-Swagger GitHub](https://github.com/DarkaOnLine/L5-Swagger)
- [OpenAPI Specification](https://swagger.io/specification/)
- [API_DOCUMENTATION.md](API_DOCUMENTATION.md)
- [Deployment Guide](DEPLOYMENT_GUIDE.md)

---

**Last Updated:** November 8, 2025
