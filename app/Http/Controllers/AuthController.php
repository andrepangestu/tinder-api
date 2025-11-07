<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Register a guest user
     *
     * @return JsonResponse
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
