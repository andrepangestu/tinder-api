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

-   🎯 **People Recommendations** - Smart algorithm based on like ratio
-   👍 **Like/Dislike System** - Swipe-like functionality
-   👤 **Guest Authentication** - No signup required to start
-   � **Automated Notifications** - Daily cronjob to alert admins about popular persons (50+ likes)
-   �📖 **Swagger Documentation** - Interactive API documentation
-   🐳 **Docker Support** - Containerized deployment
-   🚀 **CI/CD Pipeline** - Automated deployment with GitHub Actions
-   🔒 **SSL Enabled** - Secure HTTPS connections
-   ✅ **Full Test Coverage** - Comprehensive test suite

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

-   **[API_DOCUMENTATION.md](API_DOCUMENTATION.md)** - Complete API reference
-   **[SWAGGER_SETUP.md](SWAGGER_SETUP.md)** - Swagger configuration guide
-   **[TESTING_GUIDE.md](TESTING_GUIDE.md)** - Testing documentation
-   **[Swagger UI](https://andrepangestu.com/api/documentation)** - Interactive API docs

## ✅ Testing

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage

# In Docker
docker compose exec app php artisan test
```

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
│   ├── Http/Controllers/      # API Controllers
│   └── Models/                 # Eloquent Models
├── database/
│   ├── factories/              # Model Factories
│   ├── migrations/             # Database Migrations
│   └── seeders/                # Database Seeders
├── docker/                     # Docker configurations
│   ├── nginx/                  # Nginx configs
│   ├── php/                    # PHP-FPM configs
│   └── supervisor/             # Supervisor configs
├── routes/
│   └── api.php                 # API Routes
├── tests/                      # Test Suite
├── .github/workflows/          # GitHub Actions
├── docker-compose.yml          # Docker Compose config
├── Dockerfile                  # Docker image definition
└── Documentation files
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

### Popular Persons Notification

The application automatically monitors popular persons and sends daily email notifications to administrators.

**Setup the scheduler:**

Add this cron entry to your server:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

**Manual execution:**

```bash
# Run with default settings (50+ likes)
php artisan persons:check-popular

# Custom threshold
php artisan persons:check-popular --threshold=30

# Custom admin email
php artisan persons:check-popular --admin-email=admin@example.com
```

**Schedule:** Daily at 09:00 AM

For more details, see the [API Documentation](API_DOCUMENTATION.md#scheduled-tasks-cronjobs).

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
