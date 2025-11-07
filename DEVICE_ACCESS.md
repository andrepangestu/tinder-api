# Device Access Configuration Guide

## Masalah: Endpoint Tidak Bisa Diakses dari Device Lain

Jika API tidak bisa diakses dari mobile device atau komputer lain, berikut solusinya:

## Konfigurasi yang Sudah Ditambahkan

### 1. Trust Proxies Middleware

File: `app/Http/Middleware/TrustProxies.php`

-   Mengizinkan semua proxy (`*`)
-   Menangani header forwarded dengan benar

### 2. CORS Configuration

File: `config/cors.php`

-   ✅ Allow all origins: `'allowed_origins' => ['*']`
-   ✅ Allow all methods: GET, POST, PUT, DELETE, etc.
-   ✅ Allow credentials: `'supports_credentials' => true`
-   ✅ Exposed headers untuk Authorization
-   ✅ Max age: 86400 (24 jam)

### 3. Nginx Configuration

File: `nginx-config.conf`

-   ✅ CORS headers di Nginx level
-   ✅ Handle preflight OPTIONS requests
-   ✅ Proxy headers yang benar

### 4. Environment Configuration

File: `.env.production`

-   ✅ `TRUSTED_PROXIES=*`
-   ✅ `SANCTUM_STATEFUL_DOMAINS` includes localhost
-   ✅ Session domain configuration

## Penggunaan di Development (Local)

### Testing dari Device Lain di Network yang Sama

#### 1. Find Your Local IP Address

**Windows:**

```powershell
ipconfig
# Cari IPv4 Address, contoh: 192.168.1.100
```

**Mac/Linux:**

```bash
ifconfig
# atau
ip addr show
```

#### 2. Update .env Local

```env
APP_URL=http://192.168.1.100:8000
```

#### 3. Jalankan Server dengan Host 0.0.0.0

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

#### 4. Akses dari Device Lain

Dari mobile device atau komputer lain di network yang sama:

```
http://192.168.1.100:8000/api/people
```

### Jika Menggunakan Docker di Local

Update `docker-compose.yml`:

```yaml
services:
    app:
        ports:
            - "0.0.0.0:8080:80" # Bind ke semua network interfaces
```

## Penggunaan di Production

### 1. Pastikan Firewall Mengizinkan Port

Di server DigitalOcean:

```bash
# Allow HTTP/HTTPS
ufw allow 80/tcp
ufw allow 443/tcp

# Reload firewall
ufw reload
```

### 2. Test CORS dari Browser

Buka DevTools Console di browser:

```javascript
fetch("https://andrepangestu.com/api/test")
    .then((res) => res.json())
    .then((data) => console.log(data))
    .catch((err) => console.error(err));
```

### 3. Test dari Mobile Device

**Android/iOS (menggunakan browser):**

```
https://andrepangestu.com/api/people
```

**Atau menggunakan app seperti Postman Mobile**

## Troubleshooting

### Error: "Network request failed"

**Penyebab:**

-   Firewall memblok port
-   Server tidak binding ke 0.0.0.0
-   CORS configuration salah

**Solusi:**

```bash
# Di server, cek apakah port terbuka
netstat -tulpn | grep :80
netstat -tulpn | grep :443

# Restart Nginx
systemctl restart nginx

# Restart Docker containers
docker compose restart
```

### Error: "CORS policy: No 'Access-Control-Allow-Origin' header"

**Solusi:**

```bash
# Clear cache di server
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear

# Reload Nginx
nginx -s reload
```

### Tidak Bisa Akses dari WiFi Lain

**Development:**

-   Pastikan device di network yang sama
-   Gunakan IP address, bukan localhost
-   Disable VPN

**Production:**

-   Gunakan domain (https://andrepangestu.com)
-   Pastikan DNS sudah propagate

## Testing Commands

### Test dari Terminal

```bash
# Test basic connection
curl https://andrepangestu.com/api/test

# Test with CORS headers
curl -H "Origin: https://example.com" \
     -H "Access-Control-Request-Method: GET" \
     -H "Access-Control-Request-Headers: X-Requested-With" \
     -X OPTIONS \
     --verbose \
     https://andrepangestu.com/api/people

# Test dari IP tertentu
curl -H "X-Forwarded-For: 1.2.3.4" https://andrepangestu.com/api/test
```

### Test CORS dengan JavaScript

```javascript
// Test dari browser console
fetch("https://andrepangestu.com/api/people", {
    method: "GET",
    headers: {
        "Content-Type": "application/json",
    },
    credentials: "include", // Jika perlu cookies
})
    .then((response) => response.json())
    .then((data) => console.log("Success:", data))
    .catch((error) => console.error("Error:", error));
```

## Restrict Access di Production (Optional)

Jika ingin membatasi akses hanya dari domain tertentu di production:

Update `config/cors.php`:

```php
'allowed_origins' => [
    'https://andrepangestu.com',
    'https://www.andrepangestu.com',
    'https://app.andrepangestu.com',
],
```

## Security Notes

⚠️ **Current configuration allows ALL origins** (`*`)

Untuk production, sebaiknya:

1. Restrict `allowed_origins` ke domain yang spesifik
2. Set `supports_credentials` ke `false` jika tidak perlu cookies
3. Monitor access logs untuk suspicious activity

```bash
# Monitor Nginx access logs
tail -f /var/log/nginx/access.log

# Monitor application logs
docker compose logs -f app
```
