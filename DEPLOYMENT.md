# Deployment Guide

## Masalah yang Diperbaiki

Sebelumnya, kode di dalam Docker container tidak sync dengan repository karena:
- Dockerfile meng-copy kode saat build (frozen di dalam image)
- Volume hanya mount storage dan database, bukan source code

## Solusi

Sekarang `docker-compose.yml` sudah diupdate untuk mount seluruh source code sebagai volume. Ini berarti:
- ✅ Perubahan kode di server langsung terlihat di container
- ✅ Tidak perlu rebuild Docker image setiap deploy
- ✅ Deploy lebih cepat (hanya pull + restart)

## Cara Deploy ke Server

### SSH ke Server
```bash
ssh root@andrepangestu.com
```

### Deploy Pertama Kali (Setelah Update)
```bash
cd /var/www/tinder-api
git pull origin main

# Recreate containers dengan konfigurasi baru
docker compose down
docker compose up -d

# Install dependencies
docker compose exec app composer install --no-dev --optimize-autoloader

# Run migrations
docker compose exec app php artisan migrate --force
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear
```

### Deploy Berikutnya (Update Kode Saja)
```bash
cd /var/www/tinder-api
chmod +x deploy.sh
./deploy.sh
```

Script `deploy.sh` akan otomatis:
1. Pull perubahan dari GitHub
2. Install/update dependencies
3. Restart container
4. Clear cache
5. Run migrations
6. Regenerate API docs
7. Optimize aplikasi

## Struktur Volume Baru

```yaml
volumes:
  - .:/var/www                    # Mount seluruh source code
  - /var/www/vendor               # Exclude vendor (gunakan dari container)
  - /var/www/node_modules         # Exclude node_modules (gunakan dari container)
```

## Testing Sync

Setelah deploy, untuk test apakah sync bekerja:

```bash
# Di server
cd /var/www/tinder-api
echo "// test sync" >> routes/api.php
docker compose exec app cat routes/api.php  # Should show the test comment
```

## Rollback (Jika Ada Masalah)

Jika ada masalah dengan deployment baru:

```bash
cd /var/www/tinder-api
git log --oneline -5  # Lihat commit history
git reset --hard <commit-hash>  # Rollback ke commit sebelumnya
docker compose restart app
```

## Notes

- Setiap `git pull` akan langsung tersinkron ke container
- Vendor dan node_modules tetap di-exclude untuk performa
- Tidak perlu rebuild kecuali ada perubahan di Dockerfile atau dependencies system
