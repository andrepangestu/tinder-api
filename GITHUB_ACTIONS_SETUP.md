# GitHub Actions Setup Instructions

## Overview
This guide helps you set up GitHub Actions for automated deployment to your DigitalOcean server.

## Step 1: Generate SSH Keys for GitHub Actions

On your local machine, run:

```bash
ssh-keygen -t ed25519 -C "github-actions-deploy" -f github-actions-key
```

This creates two files:
- `github-actions-key` (private key) - for GitHub Secrets
- `github-actions-key.pub` (public key) - for your server

## Step 2: Add Public Key to Your Server

1. Display the public key:
```bash
cat github-actions-key.pub
```

2. SSH into your server:
```bash
ssh root@206.189.84.142
```

3. Add the public key to authorized_keys:
```bash
nano ~/.ssh/authorized_keys
# Paste the public key content at the end
# Save and exit (Ctrl+X, then Y, then Enter)
```

4. Ensure correct permissions:
```bash
chmod 600 ~/.ssh/authorized_keys
chmod 700 ~/.ssh
```

## Step 3: Add Secrets to GitHub Repository

1. Go to your GitHub repository
2. Click **Settings** → **Secrets and variables** → **Actions**
3. Click **New repository secret** for each of these:

### Required Secrets:

#### SSH_PRIVATE_KEY
```bash
cat github-actions-key
# Copy the ENTIRE content including:
# -----BEGIN OPENSSH PRIVATE KEY-----
# ... key content ...
# -----END OPENSSH PRIVATE KEY-----
```

#### SERVER_HOST
```
206.189.84.142
```

#### SERVER_USER
```
root
```

#### APP_KEY
Generate a new Laravel key:
```bash
# On your local machine in the project directory:
php artisan key:generate --show

# OR manually generate:
echo "base64:$(openssl rand -base64 32)"
```

#### DB_DATABASE
```
tinder_api
```

#### DB_USERNAME
```
tinder_user
```

#### DB_PASSWORD
```
YourSecurePassword123!
```
**⚠️ Use a strong, unique password!**

#### DB_ROOT_PASSWORD
```
YourSecureRootPassword456!
```
**⚠️ Use a different strong password!**

## Step 4: Verify GitHub Actions Workflow

The workflow file is located at: `.github/workflows/deploy.yml`

It will automatically:
1. Run tests when you push to `main` branch
2. Deploy to your server if tests pass
3. Build Docker containers
4. Run database migrations
5. Clear and cache configurations

## Step 5: Test the Deployment

1. Make a small change to your code:
```bash
# Edit README.md or any file
git add .
git commit -m "Test CI/CD deployment"
git push origin main
```

2. Go to your GitHub repository → **Actions** tab
3. Watch the workflow run
4. Check the logs for any errors

## Step 6: Verify Deployment on Server

After the workflow completes:

```bash
# SSH into your server
ssh root@206.189.84.142

# Check Docker containers
cd /var/www/tinder-api
docker compose ps

# Check logs
docker compose logs -f app

# Test the API
curl http://localhost:8080/api/test
curl https://andrepangestu.com/api/test
```

## Troubleshooting

### Error: Permission denied (publickey)

**Problem**: GitHub Actions can't SSH into your server

**Solution**:
1. Verify the private key is correctly added to GitHub Secrets
2. Ensure the public key is in `~/.ssh/authorized_keys` on your server
3. Check SSH permissions on server:
```bash
chmod 600 ~/.ssh/authorized_keys
chmod 700 ~/.ssh
```

### Error: Host key verification failed

**Problem**: Server's SSH fingerprint not recognized

**Solution**: The workflow includes `ssh-keyscan` to handle this automatically. If it still fails:
```bash
# On your local machine:
ssh-keyscan 206.189.84.142 >> ~/.ssh/known_hosts
```

### Error: git pull failed

**Problem**: Git can't pull latest changes

**Solution**:
1. Ensure the deploy key is added to GitHub repository
2. SSH into server and test:
```bash
cd /var/www/tinder-api
git pull origin main
```

### Error: Docker build failed

**Problem**: Docker can't build the image

**Solution**:
1. Check Docker logs:
```bash
docker compose logs -f
```

2. Rebuild manually to see errors:
```bash
docker compose down
docker compose build --no-cache
docker compose up -d
```

### Error: Database connection refused

**Problem**: App can't connect to database

**Solution**:
1. Check if database container is running:
```bash
docker compose ps
```

2. Verify environment variables:
```bash
docker compose exec app env | grep DB_
```

3. Check database logs:
```bash
docker compose logs db
```

## Manual Deployment (Fallback)

If GitHub Actions fails, you can deploy manually:

```bash
# SSH into server
ssh root@206.189.84.142

# Navigate to project
cd /var/www/tinder-api

# Pull latest code
git pull origin main

# Update environment
nano .env

# Rebuild and restart
docker compose down
docker compose up -d --build

# Run migrations
docker compose exec app php artisan migrate --force
docker compose exec app php artisan config:cache
```

## Security Best Practices

1. **Use strong passwords** for database credentials
2. **Keep secrets secret** - never commit them to Git
3. **Rotate SSH keys** periodically
4. **Enable 2FA** on your GitHub account
5. **Limit SSH access** to specific IPs if possible:
```bash
ufw allow from YOUR_IP to any port 22
```

## Monitoring Deployments

### View GitHub Actions logs
- Go to GitHub repository → Actions
- Click on a workflow run to see detailed logs

### View server logs
```bash
# Application logs
docker compose logs -f app

# All logs
docker compose logs -f

# Nginx logs
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/error.log
```

### Check deployment status
```bash
# Check running containers
docker compose ps

# Check app health
curl http://localhost:8080/api/test

# Check disk space
df -h

# Check memory usage
free -h
```

## Workflow Customization

To customize the deployment workflow, edit `.github/workflows/deploy.yml`:

### Change deployment branch
```yaml
on:
  push:
    branches:
      - main      # Change to your branch
      - staging   # Add more branches
```

### Add Slack notifications
```yaml
- name: Notify Slack
  uses: 8398a7/action-slack@v3
  with:
    status: ${{ job.status }}
    webhook_url: ${{ secrets.SLACK_WEBHOOK }}
```

### Add more environments
Create separate workflow files for staging/production:
- `.github/workflows/deploy-staging.yml`
- `.github/workflows/deploy-production.yml`

---

**CI/CD Setup Complete! 🚀**

Your application will now automatically deploy when you push to the main branch!
