# 🚀 Complete Setup Checklist

Follow these steps in order to deploy your Tinder API to DigitalOcean with Docker and CI/CD.

## ✅ Prerequisites Checklist

-   [ ] GitHub account with repository
-   [ ] DigitalOcean droplet (Ubuntu 22.04 LTS)
-   [ ] Domain `andrepangestu.com` pointing to `206.189.84.142`
-   [ ] SSH access to droplet
-   [ ] Local development environment with PHP, Composer, Git

---

## 📋 Phase 1: Local Setup

### 1. Install Swagger Package

```powershell
composer require darkaonline/l5-swagger
composer update
```

### 2. Publish Swagger Configuration

```powershell
php artisan vendor:publish --provider="L5Swagger\L5SwaggerServiceProvider"
```

### 3. Generate Swagger Documentation

```powershell
php artisan l5-swagger:generate
```

### 4. Test Swagger Locally

```powershell
php artisan serve
```

Visit: http://localhost:8000/api/documentation

### 5. Commit Changes

```powershell
git add .
git commit -m "Add Docker, CI/CD, and Swagger configuration"
git push origin main
```

---

## 📋 Phase 2: Server Initial Setup

### 1. Connect to Your Droplet

```bash
ssh root@206.189.84.142
```

### 2. Run Automated Setup Script

```bash
# Download and run the setup script
curl -o setup.sh https://raw.githubusercontent.com/YOUR_USERNAME/tinder-api/main/deploy-setup.sh
chmod +x setup.sh
./setup.sh
```

**OR** Run commands manually (see `DEPLOYMENT_GUIDE.md`)

### 3. Add GitHub Deploy Key

The script will generate an SSH key. Copy it and:

1. Go to GitHub → Your Repository → Settings → Deploy keys
2. Click "Add deploy key"
3. Paste the key
4. ✅ Check "Allow write access"
5. Click "Add key"

### 4. Complete the Setup

Follow the script prompts to:

-   Clone repository
-   Set database passwords
-   Configure Nginx
-   Setup SSL certificate

---

## 📋 Phase 3: GitHub Actions Setup

### 1. Generate SSH Keys for CI/CD

On your **local machine**:

```powershell
ssh-keygen -t ed25519 -C "github-actions" -f github-actions-key
```

### 2. Add Public Key to Server

```powershell
# Display the public key
Get-Content github-actions-key.pub
```

On your **server**:

```bash
nano ~/.ssh/authorized_keys
# Paste the public key at the end
# Save: Ctrl+X, Y, Enter
```

### 3. Add Secrets to GitHub

Go to: GitHub Repository → Settings → Secrets and variables → Actions

Add these secrets (click "New repository secret" for each):

| Secret Name        | How to Get Value                                  |
| ------------------ | ------------------------------------------------- |
| `SSH_PRIVATE_KEY`  | `Get-Content github-actions-key` (entire content) |
| `SERVER_HOST`      | `206.189.84.142`                                  |
| `SERVER_USER`      | `root`                                            |
| `APP_KEY`          | `php artisan key:generate --show`                 |
| `DB_DATABASE`      | `tinder_api`                                      |
| `DB_USERNAME`      | `tinder_user`                                     |
| `DB_PASSWORD`      | Create a strong password                          |
| `DB_ROOT_PASSWORD` | Create a different strong password                |

### 4. Update Server .env File

SSH into your server and update the .env:

```bash
cd /var/www/tinder-api
nano .env
```

Make sure these match your GitHub Secrets:

```env
APP_KEY=base64:...
DB_DATABASE=tinder_api
DB_USERNAME=tinder_user
DB_PASSWORD=YourSecurePassword
DB_ROOT_PASSWORD=YourSecureRootPassword
```

---

## 📋 Phase 4: Test Deployment

### 1. Push to GitHub

```powershell
# Make a small change
echo "# Test deployment" >> README.md
git add .
git commit -m "Test CI/CD pipeline"
git push origin main
```

### 2. Watch GitHub Actions

1. Go to GitHub → Your Repository → Actions tab
2. Watch the workflow run
3. Verify both "Run Tests" and "Deploy to Server" succeed

### 3. Verify on Server

```bash
# SSH into server
ssh root@206.189.84.142

# Check containers
cd /var/www/tinder-api
docker compose ps

# Should show 3 running containers:
# - tinder-api (app)
# - tinder-mysql (database)
# - tinder-redis (cache)
```

### 4. Test API Endpoints

```bash
# Test endpoint
curl http://localhost:8080/api/test

# Test from outside
curl https://andrepangestu.com/api/test

# Create guest user
curl -X POST https://andrepangestu.com/api/auth/guest \
  -H "Accept: application/json"

# Get recommended people
curl https://andrepangestu.com/api/people/recommended
```

### 5. Check Swagger Documentation

Visit: https://andrepangestu.com/api/documentation

You should see the interactive API documentation!

---

## 📋 Phase 5: Final Verification

### ✅ Verify These URLs Work:

-   [ ] https://andrepangestu.com (Should show API)
-   [ ] https://andrepangestu.com/api/test (Should return JSON)
-   [ ] https://andrepangestu.com/api/documentation (Should show Swagger UI)
-   [ ] https://andrepangestu.com/api/people/recommended (Should return people)

### ✅ Verify Docker Containers:

```bash
docker compose ps
```

Expected output:

```
NAME            STATUS    PORTS
tinder-api      Up        0.0.0.0:8080->80/tcp
tinder-mysql    Up        0.0.0.0:3306->3306/tcp
tinder-redis    Up        0.0.0.0:6379->6379/tcp
```

### ✅ Verify SSL Certificate:

```bash
curl -I https://andrepangestu.com
```

Should show: `HTTP/2 200` (not HTTP/1.1)

### ✅ Verify Database:

```bash
docker compose exec db mysql -u root -p
# Enter your DB_ROOT_PASSWORD
```

Then:

```sql
USE tinder_api;
SHOW TABLES;
SELECT COUNT(*) FROM people;
EXIT;
```

---

## 🎉 Success Criteria

You've successfully deployed when:

1. ✅ GitHub Actions workflow completes without errors
2. ✅ All Docker containers are running
3. ✅ API endpoints return valid JSON responses
4. ✅ Swagger documentation loads correctly
5. ✅ SSL certificate is active (HTTPS works)
6. ✅ Database has data (people table populated)
7. ✅ Logs show no errors: `docker compose logs -f`

---

## 📚 Documentation Reference

-   **Deployment Guide**: `DEPLOYMENT_GUIDE.md` - Complete server setup
-   **GitHub Actions Setup**: `GITHUB_ACTIONS_SETUP.md` - CI/CD configuration
-   **Swagger Setup**: `SWAGGER_SETUP.md` - API documentation
-   **API Documentation**: `API_DOCUMENTATION.md` - Endpoint details
-   **Testing Guide**: `TESTING_GUIDE.md` - Running tests

---

## 🆘 Troubleshooting

### If deployment fails:

1. **Check GitHub Actions logs**

    - Go to GitHub → Actions → Click failed workflow
    - Read error messages

2. **Check server logs**

    ```bash
    docker compose logs -f app
    tail -f /var/log/nginx/error.log
    ```

3. **Check Docker status**

    ```bash
    docker compose ps
    docker compose logs -f
    ```

4. **Restart containers**

    ```bash
    docker compose down
    docker compose up -d
    ```

5. **Check permissions**
    ```bash
    docker compose exec app chown -R www-data:www-data /var/www/storage
    ```

### Common Issues:

| Issue                           | Solution                                                       |
| ------------------------------- | -------------------------------------------------------------- |
| "Permission denied (publickey)" | Check SSH keys in GitHub Secrets                               |
| "Database connection refused"   | Verify DB credentials in .env                                  |
| "502 Bad Gateway"               | Check if containers are running: `docker compose ps`           |
| "Certificate error"             | Run: `certbot renew`                                           |
| "Swagger 404"                   | Run: `docker compose exec app php artisan l5-swagger:generate` |

---

## 🔄 Updating Your Application

Every time you push to `main` branch, GitHub Actions will:

1. Run tests
2. Deploy to server
3. Build Docker containers
4. Run migrations
5. Clear caches
6. Restart services

Just commit and push:

```powershell
git add .
git commit -m "Your update message"
git push origin main
```

---

## 📞 Need Help?

1. Check documentation files in this repository
2. View logs: `docker compose logs -f`
3. Check GitHub Actions logs
4. Review error messages carefully

---

## 🎊 Congratulations!

Your Tinder API is now:

-   ✅ Deployed to DigitalOcean
-   ✅ Running in Docker containers
-   ✅ Automated with CI/CD
-   ✅ Documented with Swagger
-   ✅ Secured with SSL

**Your API is live at**: https://andrepangestu.com/api

**Your Swagger docs**: https://andrepangestu.com/api/documentation

Happy coding! 🚀
