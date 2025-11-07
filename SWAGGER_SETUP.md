# Swagger API Documentation Setup

## Overview

This project uses L5-Swagger (OpenAPI 3.0) to automatically generate interactive API documentation.

## Accessing Swagger Documentation

### Local Development

```
http://localhost:8000/api/documentation
```

### Production

```
https://andrepangestu.com/api/documentation
```

## Installation

The L5-Swagger package is already added to composer.json:

```bash
composer require darkaonline/l5-swagger
```

## Publishing Configuration

```bash
php artisan vendor:publish --provider="L5Swagger\L5SwaggerServiceProvider"
```

## Generating Documentation

### Manually Generate

```bash
php artisan l5-swagger:generate
```

### Auto-generate on Each Request (Development Only)

Set in `.env`:

```env
L5_SWAGGER_GENERATE_ALWAYS=true
```

For production, set to `false` and generate manually.

## Docker Commands

```bash
# Generate Swagger docs in Docker
docker compose exec app php artisan l5-swagger:generate

# View generated docs
ls -la storage/api-docs/
```

## Annotation Examples

### Basic Controller Annotation

```php
/**
 * @OA\Get(
 *     path="/api/endpoint",
 *     summary="Short description",
 *     description="Detailed description",
 *     operationId="uniqueOperationId",
 *     tags={"Tag Name"},
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation"
 *     )
 * )
 */
public function method() {}
```

### With Request Body

```php
/**
 * @OA\Post(
 *     path="/api/endpoint",
 *     summary="Create resource",
 *     tags={"Resources"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name","email"},
 *             @OA\Property(property="name", type="string", example="John Doe"),
 *             @OA\Property(property="email", type="string", format="email", example="john@example.com")
 *         )
 *     ),
 *     @OA\Response(response=201, description="Created")
 * )
 */
```

### With Authentication

```php
/**
 * @OA\Get(
 *     path="/api/protected",
 *     summary="Protected endpoint",
 *     security={{"bearerAuth": {}}},
 *     @OA\Response(response=200, description="Success")
 * )
 */
```

## Configuration

Edit `config/l5-swagger.php` to customize:

-   API title and description
-   Server URLs
-   Security schemes
-   UI settings

## Current API Structure

### Tags

-   **Authentication** - Guest user registration and token generation
-   **People** - CRUD operations for people profiles

### Endpoints

#### Authentication

-   `POST /api/auth/guest` - Register as guest user

#### People

-   `GET /api/people/recommended` - Get recommended people
-   `GET /api/people` - Get all people
-   `GET /api/people/{id}` - Get specific person
-   `POST /api/people/{id}/like` - Like a person
-   `POST /api/people/{id}/dislike` - Dislike a person

## Swagger UI Features

-   **Try it out** - Test endpoints directly from the documentation
-   **Authorization** - Add bearer token for protected endpoints
-   **Request/Response Examples** - See example payloads
-   **Schema Definitions** - View data models

## Troubleshooting

### Documentation not showing

```bash
# Clear caches
php artisan config:clear
php artisan cache:clear

# Regenerate docs
php artisan l5-swagger:generate
```

### In Docker

```bash
docker compose exec app php artisan config:clear
docker compose exec app php artisan l5-swagger:generate
docker compose restart app
```

### Permission Issues

```bash
docker compose exec app chown -R www-data:www-data storage/api-docs
docker compose exec app chmod -R 755 storage/api-docs
```

## Best Practices

1. **Always annotate public endpoints** - Keep documentation up to date
2. **Use meaningful descriptions** - Help API consumers understand usage
3. **Provide examples** - Show realistic example data
4. **Version your API** - Include version in URL or headers
5. **Document errors** - Show all possible error responses

## Resources

-   [L5-Swagger Documentation](https://github.com/DarkaOnLine/L5-Swagger)
-   [OpenAPI Specification](https://swagger.io/specification/)
-   [Swagger Editor](https://editor.swagger.io/)

---

**Swagger Documentation is ready! 📚**

Visit: https://andrepangestu.com/api/documentation
