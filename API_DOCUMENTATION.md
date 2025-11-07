# Tinder API - People Endpoints

This API provides endpoints for managing and retrieving people data for a Tinder-like application.

## Base URL

```
http://127.0.0.1:8000/api
```

## Endpoints

### 1. Register Guest User

**POST** `/auth/guest`

Creates a new guest user account with a random name and returns an API token for authentication.

**Example Request:**

```
POST /api/auth/guest
```

**cURL Example:**

```bash
curl -X POST "http://127.0.0.1:8000/api/auth/guest" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json"
```

**Example Response:**

```json
{
    "status": "success",
    "message": "Guest user created successfully",
    "data": {
        "user": {
            "id": 1,
            "name": "Guest a8f3k9x2"
        },
        "token": "1|laravel_sanctum_token_here"
    }
}
```

**Status Code:** `201 Created`

**Notes:**

-   The guest user name is automatically generated as "Guest " plus a random 8-character string
-   Email and password are set to null for guest users
-   The returned token should be used for authenticated API requests
-   Include the token in the `Authorization: Bearer {token}` header for protected endpoints

---

### 2. Get Recommended People

**GET** `/people/recommended`

Returns a paginated list of recommended people.

**Query Parameters:**

-   `per_page` (optional, integer): Number of items per page (default: 10, max: 50)
-   `page` (optional, integer): Page number (default: 1)

**Example Request:**

```
GET /api/people/recommended?per_page=5&page=1
```

**cURL Example:**

```bash
curl -X GET "http://127.0.0.1:8000/api/people/recommended?per_page=5&page=1" \
  -H "Accept: application/json"
```

**Example Response:**

```json
{
    "status": "success",
    "message": "Recommended people retrieved successfully",
    "data": {
        "people": [
            {
                "id": 1,
                "name": "John Doe",
                "age": 28,
                "location": "New York, NY",
                "bio": "Love hiking and photography. Looking for someone to share adventures with!",
                "image_url": "https://via.placeholder.com/400x600/people",
                "interests": ["Hiking", "Photography", "Travel"],
                "created_at": "2025-11-06T10:30:00.000000Z",
                "updated_at": "2025-11-06T10:30:00.000000Z"
            }
        ],
        "pagination": {
            "current_page": 1,
            "per_page": 5,
            "total": 50,
            "last_page": 10,
            "from": 1,
            "to": 5,
            "has_more_pages": true,
            "next_page_url": "http://127.0.0.1:8000/api/people/recommended?page=2",
            "prev_page_url": null
        }
    }
}
```

### 3. Get All People

**GET** `/people`

Returns a paginated list of all people.

**Query Parameters:**

-   `per_page` (optional, integer): Number of items per page (default: 10, max: 50)
-   `page` (optional, integer): Page number (default: 1)

**Example Request:**

```
GET /api/people?per_page=10&page=1
```

**cURL Example:**

```bash
curl -X GET "http://127.0.0.1:8000/api/people?per_page=10&page=1" \
  -H "Accept: application/json"
```

### 4. Get Person by ID

**GET** `/people/{id}`

Returns a specific person by their ID.

**Path Parameters:**

-   `id` (required, integer): The person's ID

**Example Request:**

```
GET /api/people/1
```

**cURL Example:**

```bash
curl -X GET "http://127.0.0.1:8000/api/people/1" \
  -H "Accept: application/json"
```

**Example Response:**

```json
{
    "status": "success",
    "message": "Person retrieved successfully",
    "data": {
        "id": 1,
        "name": "John Doe",
        "age": 28,
        "location": "New York, NY",
        "bio": "Love hiking and photography. Looking for someone to share adventures with!",
        "image_url": "https://via.placeholder.com/400x600/people",
        "interests": ["Hiking", "Photography", "Travel"],
        "created_at": "2025-11-06T10:30:00.000000Z",
        "updated_at": "2025-11-06T10:30:00.000000Z"
    }
}
```

### 5. Like Person

**POST** `/people/{id}/like`

Increments the like count for a specific person.

**Path Parameters:**

-   `id` (required, integer): The person's ID

**Example Request:**

```
POST /api/people/1/like
```

**cURL Example:**

```bash
curl -X POST "http://127.0.0.1:8000/api/people/1/like" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json"
```

**Example Response:**

```json
{
    "status": "success",
    "message": "Person liked successfully",
    "data": {
        "person_id": 1,
        "name": "John Doe",
        "likes_count": 3,
        "dislikes_count": 1
    }
}
```

### 6. Dislike Person

**POST** `/people/{id}/dislike`

Increments the dislike count for a specific person.

**Path Parameters:**

-   `id` (required, integer): The person's ID

**Example Request:**

```
POST /api/people/1/dislike
```

**cURL Example:**

```bash
curl -X POST "http://127.0.0.1:8000/api/people/1/dislike" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json"
```

**Example Response:**

```json
{
    "status": "success",
    "message": "Person disliked successfully",
    "data": {
        "person_id": 1,
        "name": "John Doe",
        "likes_count": 3,
        "dislikes_count": 2
    }
}
```

### 7. Test Endpoint

**GET** `/test`

Simple test endpoint to verify API is working.

**cURL Example:**

```bash
curl -X GET "http://127.0.0.1:8000/api/test" \
  -H "Accept: application/json"
```

**Example Response:**

```json
{
    "message": "API is working!",
    "timestamp": "2025-11-06T15:45:30.000000Z",
    "people_count": 50
}
```

## Error Responses

### 404 Not Found

```json
{
    "status": "error",
    "message": "Person not found"
}
```

## Response Format

All responses follow this general structure:

-   `status`: Either "success" or "error"
-   `message`: Human-readable message describing the result
-   `data`: The actual data (for successful responses)

## Pagination

Pagination information includes:

-   `current_page`: Current page number
-   `per_page`: Items per page
-   `total`: Total number of items
-   `last_page`: Last page number
-   `from`: First item number on current page
-   `to`: Last item number on current page
-   `has_more_pages`: Boolean indicating if there are more pages
-   `next_page_url`: URL for next page (null if on last page)
-   `prev_page_url`: URL for previous page (null if on first page)

## Data Models

### Person

-   `id`: Unique identifier
-   `name`: Person's name
-   `age`: Person's age (nullable)
-   `location`: Person's location (nullable)
-   `bio`: Person's biography (nullable)
-   `image_url`: URL to person's profile image (nullable)
-   `interests`: Array of interests (nullable)
-   `likes_count`: Number of likes received (integer, default: 0)
-   `dislikes_count`: Number of dislikes received (integer, default: 0)
-   `created_at`: Creation timestamp
-   `updated_at`: Last update timestamp

## cURL Examples Reference

### Quick Testing Commands

**Register guest user:**

```bash
curl -X POST "http://127.0.0.1:8000/api/auth/guest" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json"
```

**Get recommended people (default pagination):**

```bash
curl -X GET "http://127.0.0.1:8000/api/people/recommended" \
  -H "Accept: application/json"
```

**Get recommended people with custom pagination:**

```bash
curl -X GET "http://127.0.0.1:8000/api/people/recommended?per_page=5&page=2" \
  -H "Accept: application/json"
```

**Get all people:**

```bash
curl -X GET "http://127.0.0.1:8000/api/people" \
  -H "Accept: application/json"
```

**Get specific person:**

```bash
curl -X GET "http://127.0.0.1:8000/api/people/1" \
  -H "Accept: application/json"
```

**Like a person:**

```bash
curl -X POST "http://127.0.0.1:8000/api/people/1/like" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json"
```

**Dislike a person:**

```bash
curl -X POST "http://127.0.0.1:8000/api/people/1/dislike" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json"
```

**Test API endpoint:**

```bash
curl -X GET "http://127.0.0.1:8000/api/test" \
  -H "Accept: application/json"
```

### PowerShell Examples (Windows)

**Register guest user:**

```powershell
Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/auth/guest" -Method POST -Headers @{"Accept"="application/json"}
```

**Get recommended people:**

```powershell
Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/people/recommended?per_page=5" -Method GET -Headers @{"Accept"="application/json"}
```

**Like a person:**

```powershell
Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/people/1/like" -Method POST -Headers @{"Accept"="application/json"}
```

**Dislike a person:**

```powershell
Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/people/1/dislike" -Method POST -Headers @{"Accept"="application/json"}
```
