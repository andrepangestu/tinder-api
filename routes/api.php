<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\PersonActivityController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Test route
Route::get('/test', function () {
    return response()->json([
        'message' => 'API is working!',
        'timestamp' => now(),
        'people_count' => \App\Models\Person::count()
    ]);
});

// People API routes
Route::prefix('people')->group(function () {
    Route::get('/recommended', [PersonController::class, 'recommended']);
    Route::get('/', [PersonController::class, 'index']);
    Route::get('/{id}', [PersonController::class, 'show']);
    Route::post('/{id}/like', [PersonController::class, 'like']);
    Route::post('/{id}/dislike', [PersonController::class, 'dislike']);
    
    // Activity routes
    Route::get('/activities/liked', [PersonActivityController::class, 'likedPeople']);
    Route::get('/activities/disliked', [PersonActivityController::class, 'dislikedPeople']);
    Route::get('/activities/all', [PersonActivityController::class, 'allActivities']);
});