<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/auth/guest",
     *     summary="Register as guest user",
     *     description="Create a new guest user and return an authentication token",
     *     operationId="registerGuest",
     *     tags={"Authentication"},
     *     @OA\Response(
     *         response=201,
     *         description="Guest user created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Guest user created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Guest abc123")
     *                 ),
     *                 @OA\Property(property="token", type="string", example="1|abcdefghijklmnopqrstuvwxyz...")
     *             )
     *         )
     *     )
     * )
     */
    public function registerGuest(): JsonResponse
    {
        // Create a new guest user
        $user = User::create([
            'name' => 'Guest ' . Str::random(8),
            'email' => null,
            'password' => null,
        ]);

        // Create API token using Sanctum
        $token = $user->createToken('guest-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Guest user created successfully',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                ],
                'token' => $token,
            ]
        ], 201);
    }
}
