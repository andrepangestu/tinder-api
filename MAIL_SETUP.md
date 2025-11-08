# 📧 Mail Configuration Setup

This document explains how to configure email functionality for the Tinder API project using Mailtrap.

## 🔧 Local Development Setup

### 1. Update Your `.env` File

Copy the configuration from `.env.example` or manually add:

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=5c9978b63f8a5a
MAIL_PASSWORD=de747526a5c5be
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@tinderapi.com
MAIL_FROM_NAME="Tinder API"
```

### 2. Test Email Functionality

Run the cronjob command to test email sending:

```bash
php artisan persons:check-popular --admin-email=test@example.com
```

### 3. View Emails in Mailtrap

1. Login to [Mailtrap.io](https://mailtrap.io)
2. Go to your inbox
3. You'll see the email that was sent (it won't be delivered to actual recipients)

---

## 🚀 Production/CI-CD Setup

### GitHub Secrets Configuration

You need to add the following secrets to your GitHub repository for CI/CD deployment:

#### Navigate to GitHub Secrets:

1. Go to your repository: `https://github.com/andrepangestu/tinder-api`
2. Click **Settings** → **Secrets and variables** → **Actions**
3. Click **New repository secret**

#### Add These Secrets:

| Secret Name         | Value                      | Description            |
| ------------------- | -------------------------- | ---------------------- |
| `MAIL_HOST`         | `sandbox.smtp.mailtrap.io` | Mailtrap SMTP host     |
| `MAIL_PORT`         | `2525`                     | Mailtrap SMTP port     |
| `MAIL_USERNAME`     | `5c9978b63f8a5a`           | Your Mailtrap username |
| `MAIL_PASSWORD`     | `de747526a5c5be`           | Your Mailtrap password |
| `MAIL_FROM_ADDRESS` | `noreply@tinderapi.com`    | Sender email address   |

### Existing Secrets (Keep These):

Make sure these secrets are already configured:

-   `SERVER_HOST`
-   `SERVER_USER`
-   `SSH_PRIVATE_KEY`
-   `APP_KEY`
-   `DB_DATABASE`
-   `DB_USERNAME`
-   `DB_PASSWORD`
-   `DB_ROOT_PASSWORD`

---

## 📝 How to Get Mailtrap Credentials

### For Development (Sandbox):

1. Sign up at [Mailtrap.io](https://mailtrap.io)
2. Create a new inbox or use the default one
3. Go to **SMTP Settings** tab
4. Select **Laravel** as integration
5. Copy the credentials shown:
    - Host: `sandbox.smtp.mailtrap.io`
    - Port: `2525`
    - Username: (your unique username)
    - Password: (your unique password)

### For Production:

When ready for production, you have two options:

**Option 1: Use Mailtrap Sending (Paid)**

-   Upgrade to Mailtrap Sending service
-   Get production SMTP credentials
-   Update environment variables

**Option 2: Use Real SMTP Service**

-   Gmail SMTP (requires App Password)
-   SendGrid
-   Mailgun
-   Amazon SES
-   etc.

---

## ✅ Verify Configuration

### Test in Local Environment:

```bash
# Create some test data first (if not exists)
php artisan db:seed

# Test the cronjob command
php artisan persons:check-popular --threshold=0 --admin-email=yourtest@example.com
```

### Check Logs:

If email sending fails, check Laravel logs:

```bash
tail -f storage/logs/laravel.log
```

### Verify in Mailtrap:

Go to your Mailtrap inbox and verify:

-   ✅ Email was received
-   ✅ Subject: "Alert: Popular Persons Detected (50+ Likes)"
-   ✅ HTML formatting is correct
-   ✅ Person data is displayed in table

---

## 🤖 CI/CD Deployment

Once you've added all the GitHub secrets, the deployment workflow will automatically:

1. ✅ Run tests (including email tests)
2. ✅ Deploy to server
3. ✅ Configure mail settings from secrets
4. ✅ Set up cron scheduler

### Verify After Deployment:

SSH into your server and test:

```bash
cd /var/www/tinder-api
docker compose exec app php artisan persons:check-popular --admin-email=test@example.com
```

---

## 📅 Cronjob Schedule

The command runs automatically **daily at 09:00 AM** as configured in `bootstrap/app.php`.

To enable the scheduler on the server, make sure this cron entry exists:

```bash
* * * * * cd /var/www/tinder-api && docker compose exec -T app php artisan schedule:run >> /dev/null 2>&1
```

Or manually run the scheduler:

```bash
docker compose exec app php artisan schedule:work
```

---

## 🔍 Troubleshooting

### Email Not Sending

1. **Check credentials**: Verify Mailtrap username/password
2. **Check port**: Should be 2525 (or 587 for some providers)
3. **Check logs**: `storage/logs/laravel.log`
4. **Test connection**:
    ```bash
    php artisan tinker
    Mail::raw('Test email', function($msg) {
        $msg->to('test@example.com')->subject('Test');
    });
    ```

### GitHub Actions Failing

1. Verify all secrets are added correctly
2. Check GitHub Actions logs for error messages
3. Make sure secret names match exactly (case-sensitive)

### Production Issues

1. Check firewall allows SMTP port (2525 or 587)
2. Verify DNS settings if using custom domain
3. Check server logs: `docker compose logs app`

---

## 📚 Additional Resources

-   [Laravel Mail Documentation](https://laravel.com/docs/11.x/mail)
-   [Mailtrap Documentation](https://mailtrap.io/docs)
-   [Cronjob Documentation](API_DOCUMENTATION.md#scheduled-tasks-cronjobs)

---

**Need Help?** Contact: hello@andrepangestu.com
