# 🚀 Quick Reference - Tinder API Deployment

## 📍 Server Information

-   **Domain**: https://andrepangestu.com
-   **IP Address**: 206.189.84.142
-   **Server**: DigitalOcean Ubuntu 22.04 LTS
-   **Application Path**: /var/www/tinder-api

## 🌐 Important URLs

| Resource      | URL                                         |
| ------------- | ------------------------------------------- |
| API Base      | https://andrepangestu.com/api               |
| Swagger Docs  | https://andrepangestu.com/api/documentation |
| Test Endpoint | https://andrepangestu.com/api/test          |
| GitHub Repo   | https://github.com/YOUR_USERNAME/tinder-api |

## 🔑 GitHub Secrets Required

| Secret Name      | Description                    |
| ---------------- | ------------------------------ |
| SSH_PRIVATE_KEY  | Private SSH key for deployment |
| SERVER_HOST      | 206.189.84.142                 |
| SERVER_USER      | root                           |
| APP_KEY          | Laravel application key        |
| DB_DATABASE      | tinder_api                     |
| DB_USERNAME      | tinder_user                    |
| DB_PASSWORD      | Your database password         |
| DB_ROOT_PASSWORD | MySQL root password            |

## 📦 Docker Commands

```bash
# SSH into server
ssh root@206.189.84.142
cd /var/www/tinder-api

# Container management
docker compose ps                    # List containers
docker compose up -d                 # Start containers
docker compose down                  # Stop containers
docker compose restart              # Restart all
docker compose logs -f              # View logs
docker compose logs -f app          # View app logs

# Rebuild containers
docker compose down
docker compose up -d --build

# Execute commands in container
docker compose exec app php artisan migrate
docker compose exec app php artisan l5-swagger:generate
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:cache
docker compose exec app php artisan queue:restart

# Database access
docker compose exec db mysql -u root -p tinder_api
```

## 🔧 Common Artisan Commands

```bash
# In Docker
docker compose exec app php artisan <command>

# Migrations
php artisan migrate                  # Run migrations
php artisan migrate:fresh --seed    # Fresh DB with data

# Cache
php artisan cache:clear             # Clear application cache
php artisan config:clear            # Clear config cache
php artisan route:clear             # Clear route cache
php artisan view:clear              # Clear view cache
php artisan config:cache            # Cache configurations
php artisan route:cache             # Cache routes

# Swagger
php artisan l5-swagger:generate     # Generate API docs

# Queue
php artisan queue:work              # Start queue worker
php artisan queue:restart           # Restart workers

# Maintenance
php artisan down                    # Enable maintenance mode
php artisan up                      # Disable maintenance mode
```

## 🧪 Testing Commands

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter=PeopleApiTest

# With coverage
php artisan test --coverage

# In Docker
docker compose exec app php artisan test
```

## 🔄 Deployment Workflow

### Automatic (GitHub Actions)

```bash
git add .
git commit -m "Your changes"
git push origin main
# Wait for GitHub Actions to deploy
```

### Manual

```bash
ssh root@206.189.84.142
cd /var/www/tinder-api
git pull origin main
docker compose down
docker compose up -d --build
docker compose exec app php artisan migrate --force
docker compose exec app php artisan config:cache
```

## 📊 Monitoring Commands

```bash
# System resources
docker stats                        # Container resource usage
df -h                              # Disk space
free -h                            # Memory usage
htop                               # System monitor

# Logs
docker compose logs -f app         # Application logs
tail -f /var/log/nginx/access.log # Nginx access log
tail -f /var/log/nginx/error.log  # Nginx error log
journalctl -xe                     # System logs

# Process management
docker compose ps                  # Container status
systemctl status nginx            # Nginx status
systemctl status docker           # Docker status
```

## 🔒 SSL/Security

```bash
# Renew SSL certificate
certbot renew

# Test renewal
certbot renew --dry-run

# Check certificate expiry
certbot certificates

# Firewall status
ufw status
```

## 🗄️ Database Management

```bash
# Backup database
docker compose exec db mysqldump -u root -p tinder_api > backup_$(date +%Y%m%d).sql

# Restore database
docker compose exec -T db mysql -u root -p tinder_api < backup_20251107.sql

# Access MySQL shell
docker compose exec db mysql -u root -p

# MySQL commands
USE tinder_api;
SHOW TABLES;
SELECT COUNT(*) FROM people;
DESCRIBE people;
EXIT;
```

## 🐛 Troubleshooting

### Container won't start

```bash
docker compose logs app
docker compose down
docker compose up -d
```

### Permission errors

```bash
docker compose exec app chown -R www-data:www-data /var/www/storage
docker compose exec app chmod -R 755 /var/www/storage
```

### Database connection failed

```bash
docker compose ps                    # Check if DB is running
docker compose logs db              # Check DB logs
docker compose exec app env | grep DB_  # Check env vars
```

### Swagger not loading

```bash
docker compose exec app php artisan l5-swagger:generate
docker compose exec app php artisan cache:clear
docker compose restart app
```

### 502 Bad Gateway

```bash
docker compose ps                    # Check if app is running
docker compose logs -f app          # Check app logs
systemctl status nginx              # Check Nginx
nginx -t                            # Test Nginx config
```

## 📞 Quick Test Endpoints

```bash
# Test API is working
curl https://andrepangestu.com/api/test

# Register guest user
curl -X POST https://andrepangestu.com/api/auth/guest \
  -H "Accept: application/json"

# Get recommended people
curl https://andrepangestu.com/api/people/recommended

# Like a person (ID 1)
curl -X POST https://andrepangestu.com/api/people/1/like \
  -H "Accept: application/json"
```

## 📚 Documentation Files

| File                    | Purpose                          |
| ----------------------- | -------------------------------- |
| SETUP_CHECKLIST.md      | Step-by-step setup guide         |
| DEPLOYMENT_GUIDE.md     | Complete deployment instructions |
| GITHUB_ACTIONS_SETUP.md | CI/CD configuration              |
| SWAGGER_SETUP.md        | API documentation setup          |
| API_DOCUMENTATION.md    | API endpoint reference           |
| TESTING_GUIDE.md        | Testing documentation            |

## 🎯 Success Indicators

✅ All containers running: `docker compose ps`
✅ API responds: `curl https://andrepangestu.com/api/test`
✅ Swagger loads: Open https://andrepangestu.com/api/documentation
✅ SSL active: Look for 🔒 in browser
✅ No errors in logs: `docker compose logs -f`

## 💡 Pro Tips

1. **Always test locally before pushing**
2. **Check GitHub Actions logs after each push**
3. **Keep backups of database regularly**
4. **Monitor disk space**: `df -h`
5. **Rotate logs to prevent disk fill up**
6. **Update packages regularly**: `composer update`
7. **Keep SSL certificate renewed**: `certbot renew`

---

**Need detailed help? Check the full documentation files!**
