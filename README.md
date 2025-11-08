# 💘 Tinder API - Laravel Dating Application

A modern, RESTful API built with Laravel for a Tinder-like dating application. Features include people recommendations, like/dislike functionality, and guest authentication.

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2-blue.svg)](https://php.net)
[![Docker](https://img.shields.io/badge/Docker-Ready-blue.svg)](https://docker.com)
[![Swagger](https://img.shields.io/badge/API-Documented-green.svg)](https://andrepangestu.com/api/documentation)

## 🚀 Live Demo

-   **API Base URL**: [https://andrepangestu.com/api](https://andrepangestu.com/api)
-   **Swagger Documentation**: [https://andrepangestu.com/api/documentation](https://andrepangestu.com/api/documentation)

## ✨ Features

-   🎯 **People Recommendations** - Smart algorithm based on like ratio with random ordering
-   👍 **Like/Dislike System** - Swipe-like functionality with activity tracking
-   👤 **Guest Authentication** - Frictionless onboarding without signup requirements
-   📧 **Automated Notifications** - Daily cronjob monitoring for viral profiles (50+ likes threshold)
-   📊 **Activity Tracking** - Comprehensive user interaction history and analytics
-   � **Swagger Documentation** - Interactive API documentation with OpenAPI 3.0
-   🐳 **Docker Support** - Containerized deployment with Docker Compose
-   🚀 **CI/CD Pipeline** - Automated testing and deployment via GitHub Actions
-   🔒 **SSL Enabled** - Secure HTTPS connections with Let's Encrypt
-   ✅ **Full Test Coverage** - Comprehensive test suite with PHPUnit (Feature & Unit tests)
-   📬 **Email Integration** - SMTP support with Mailtrap for development and testing

## 📋 Table of Contents

-   [Quick Start](#-quick-start)
-   [API Endpoints](#-api-endpoints)
-   [Deployment](#-deployment)
-   [Documentation](#-documentation)
-   [Testing](#-testing)
-   [Tech Stack](#-tech-stack)
-   [Contributing](#-contributing)

## 🏃 Quick Start

### Local Development

```bash
# Clone the repository
git clone https://github.com/YOUR_USERNAME/tinder-api.git
cd tinder-api

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Create database
touch database/database.sqlite

# Run migrations and seed
php artisan migrate --seed

# Start development server
php artisan serve

# Visit Swagger docs
open http://localhost:8000/api/documentation
```

### Docker Development

```bash
# Build and start containers
docker compose up -d

# Run migrations
docker compose exec app php artisan migrate --seed

# Generate Swagger docs
docker compose exec app php artisan l5-swagger:generate

# Visit API
open http://localhost:8080/api/test
```

## 🌐 API Endpoints

### Authentication

```bash
POST   /api/auth/guest          # Register as guest user
```

### People

```bash
GET    /api/people/recommended  # Get recommended people (sorted by like ratio)
GET    /api/people              # Get all people (paginated)
GET    /api/people/{id}         # Get specific person
POST   /api/people/{id}/like    # Like a person
POST   /api/people/{id}/dislike # Dislike a person
```

### Activities

```bash
GET    /api/people/activities/liked     # Get all liked people by current user
GET    /api/people/activities/disliked  # Get all disliked people by current user
GET    /api/people/activities/all       # Get all activities (with optional filters)
```

### Scheduled Commands

```bash
# Cronjob: Check for popular persons and send email notifications
php artisan persons:check-popular [--threshold=50] [--admin-email=admin@tinderapi.com]
```

### Example Usage

```bash
# Register as guest
curl -X POST https://andrepangestu.com/api/auth/guest \
  -H "Accept: application/json"

# Get recommended people
curl https://andrepangestu.com/api/people/recommended?per_page=10

# Like someone
curl -X POST https://andrepangestu.com/api/people/1/like \
  -H "Accept: application/json"
```

## 🚀 Deployment

### Prerequisites

-   DigitalOcean droplet (Ubuntu 22.04 LTS)
-   Domain pointing to your server
-   GitHub repository

### Quick Deploy

Follow the comprehensive setup checklist:

1. **[SETUP_CHECKLIST.md](SETUP_CHECKLIST.md)** - Step-by-step deployment guide
2. **[DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md)** - Detailed server configuration
3. **[GITHUB_ACTIONS_SETUP.md](GITHUB_ACTIONS_SETUP.md)** - CI/CD pipeline setup

### One-Command Setup

```bash
curl -o setup.sh https://raw.githubusercontent.com/YOUR_USERNAME/tinder-api/main/deploy-setup.sh
chmod +x setup.sh
./setup.sh
```

## 📚 Documentation

-   **[API_DOCUMENTATION.md](API_DOCUMENTATION.md)** - Complete API reference with request/response examples
-   **[MAIL_SETUP.md](MAIL_SETUP.md)** - Email configuration guide for Mailtrap and production SMTP
-   **[IMPLEMENTATION_SUMMARY.md](IMPLEMENTATION_SUMMARY.md)** - Cronjob implementation overview
-   **[SWAGGER_TROUBLESHOOTING.md](SWAGGER_TROUBLESHOOTING.md)** - Swagger/API documentation troubleshooting guide
-   **[SWAGGER_SETUP.md](SWAGGER_SETUP.md)** - Swagger/OpenAPI configuration guide
-   **[TESTING_GUIDE.md](TESTING_GUIDE.md)** - Testing documentation and best practices
-   **[Swagger UI](https://andrepangestu.com/api/documentation)** - Interactive API documentation

### Quick Fixes

**Swagger not working?** Run this on the server:

```bash
# Download and run the fix script
curl -o fix-swagger.sh https://raw.githubusercontent.com/andrepangestu/tinder-api/main/fix-swagger.sh
chmod +x fix-swagger.sh
./fix-swagger.sh
```

Or manually:

```bash
docker compose exec -T app php artisan config:clear && \
docker compose exec -T app php artisan l5-swagger:generate
```

## ✅ Testing

The project includes comprehensive test coverage for all features.

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run specific test file
php artisan test --filter=CheckPopularPersonsCommandTest

# Run with coverage report
php artisan test --coverage

# Run in Docker environment
docker compose exec app php artisan test
```

### Test Structure

```
tests/
├── Feature/
│   ├── CheckPopularPersonsCommandTest.php  # Cronjob command tests (7 tests)
│   ├── LikeDislikeApiTest.php              # Like/Dislike API tests
│   └── ...
├── Unit/
│   ├── PopularPersonNotificationTest.php   # Email notification tests (4 tests)
│   └── ...
└── TestCase.php
```

**Test Coverage:**

-   ✅ 11 tests for cronjob email notifications
-   ✅ Feature tests for all API endpoints
-   ✅ Unit tests for business logic
-   ✅ Integration tests with database

## 🛠 Tech Stack

-   **Framework**: Laravel 12.x
-   **Language**: PHP 8.2
-   **Database**: MySQL 8.0 / SQLite (dev)
-   **Cache/Queue**: Redis
-   **Web Server**: Nginx
-   **Container**: Docker & Docker Compose
-   **CI/CD**: GitHub Actions
-   **API Docs**: L5-Swagger (OpenAPI 3.0)
-   **Testing**: PHPUnit
-   **Code Style**: Laravel Pint

## 📁 Project Structure

```
tinder-api/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       └── CheckPopularPersons.php    # Cronjob command
│   ├── Http/
│   │   ├── Controllers/                   # API Controllers
│   │   └── Middleware/                    # Custom middleware
│   ├── Mail/
│   │   └── PopularPersonNotification.php  # Email notification class
│   └── Models/                            # Eloquent Models
│       ├── Person.php
│       ├── PersonActivity.php
│       └── User.php
├── bootstrap/
│   └── app.php                            # Application bootstrap (includes scheduler)
├── database/
│   ├── factories/                         # Model Factories
│   ├── migrations/                        # Database Migrations
│   └── seeders/                           # Database Seeders
│       └── PopularPersonSeeder.php        # Seeder for testing cronjob
├── docker/                                # Docker configurations
│   ├── nginx/                             # Nginx configs
│   ├── php/                               # PHP-FPM configs
│   └── supervisor/                        # Supervisor configs
├── resources/
│   └── views/
│       └── emails/
│           └── popular-person-notification.blade.php  # Email template
├── routes/
│   ├── api.php                            # API Routes
│   └── console.php                        # Console routes
├── tests/                                 # Test Suite
│   ├── Feature/
│   │   └── CheckPopularPersonsCommandTest.php
│   └── Unit/
│       └── PopularPersonNotificationTest.php
├── .github/
│   └── workflows/
│       └── deploy.yml                     # GitHub Actions CI/CD
├── docker-compose.yml                     # Docker Compose config
├── Dockerfile                             # Docker image definition
└── Documentation files
    ├── API_DOCUMENTATION.md
    ├── MAIL_SETUP.md
    └── IMPLEMENTATION_SUMMARY.md
```

## 🔐 Environment Variables

Key environment variables for production:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://andrepangestu.com

DB_CONNECTION=mysql
DB_HOST=db
DB_DATABASE=tinder_api
DB_USERNAME=tinder_user
DB_PASSWORD=your_secure_password

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Mail Configuration for Notifications
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@tinderapi.com
```

## 📅 Scheduled Tasks

The application includes automated background tasks using Laravel's Task Scheduler.

### Popular Persons Notification System

Automatically monitors and alerts administrators about viral profiles.

**Features:**

-   ✅ Daily automated checks at 09:00 AM
-   ✅ Configurable like threshold (default: 50)
-   ✅ HTML email notifications with detailed reports
-   ✅ Customizable admin email recipients
-   ✅ Comprehensive test coverage

**Command Usage:**

```bash
# Run with default settings (threshold: 50, admin: admin@tinderapi.com)
php artisan persons:check-popular

# Custom threshold (e.g., alert for 30+ likes)
php artisan persons:check-popular --threshold=30

# Custom admin email
php artisan persons:check-popular --admin-email=admin@example.com

# Combine options
php artisan persons:check-popular --threshold=100 --admin-email=custom@example.com
```

**Server Setup:**

Add the following cron entry to enable Laravel's scheduler:

```bash
# For Docker deployment
* * * * * cd /var/www/tinder-api && docker compose exec -T app php artisan schedule:run >> /dev/null 2>&1

# For standard deployment
* * * * * cd /var/www/tinder-api && php artisan schedule:run >> /dev/null 2>&1
```

**Email Configuration:**

Configure SMTP settings in `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io    # Use Mailtrap for testing
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@tinderapi.com
MAIL_FROM_NAME="${APP_NAME}"
```

**Testing:**

```bash
# Seed test data (creates persons with 50+ likes)
php artisan db:seed --class=PopularPersonSeeder

# Run the command manually
php artisan persons:check-popular

# Check email in Mailtrap inbox
# Login to https://mailtrap.io to view sent emails
```

**Email Content:**

The notification includes:

-   Alert banner with detection summary
-   Total count of popular persons
-   Detailed table with person information (ID, Name, Age, Location, Likes)
-   Timestamp of the notification

For detailed setup instructions, see [MAIL_SETUP.md](MAIL_SETUP.md).

For complete cronjob documentation, see [API_DOCUMENTATION.md#scheduled-tasks](API_DOCUMENTATION.md#scheduled-tasks-cronjobs).

## 🤝 Contributing

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 👨‍💻 Author

**Andre Pangestu**

-   Website: [andrepangestu.com](https://andrepangestu.com)
-   Email: hello@andrepangestu.com

## 🙏 Acknowledgments

-   Laravel Framework
-   L5-Swagger for API documentation
-   DigitalOcean for hosting
-   GitHub Actions for CI/CD

---

**Made with ❤️ using Laravel**

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
