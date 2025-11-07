<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="Tinder API",
 *     version="1.0.0",
 *     description="API documentation for Tinder-like application",
 *     @OA\Contact(
 *         email="hello@andrepangestu.com",
 *         name="Andre Pangestu"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="https://andrepangestu.com/api",
 *     description="Production API Server"
 * )
 * 
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Local Development Server"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 * 
 * @OA\Schema(
 *     schema="Person",
 *     type="object",
 *     title="Person",
 *     description="Person model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="age", type="integer", example=25),
 *     @OA\Property(property="image_url", type="string", example="https://randomuser.me/api/portraits/women/1.jpg"),
 *     @OA\Property(property="location", type="string", example="15 km"),
 *     @OA\Property(property="likes_count", type="integer", example=10),
 *     @OA\Property(property="dislikes_count", type="integer", example=2),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-11-06T12:00:00.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-11-06T12:00:00.000000Z")
 * )
 * 
 * @OA\Schema(
 *     schema="Pagination",
 *     type="object",
 *     title="Pagination",
 *     description="Pagination metadata",
 *     @OA\Property(property="current_page", type="integer", example=1),
 *     @OA\Property(property="per_page", type="integer", example=10),
 *     @OA\Property(property="total", type="integer", example=100),
 *     @OA\Property(property="last_page", type="integer", example=10),
 *     @OA\Property(property="from", type="integer", example=1),
 *     @OA\Property(property="to", type="integer", example=10),
 *     @OA\Property(property="has_more_pages", type="boolean", example=true),
 *     @OA\Property(property="next_page_url", type="string", nullable=true),
 *     @OA\Property(property="prev_page_url", type="string", nullable=true)
 * )
 */
abstract class Controller
{
    //
}

