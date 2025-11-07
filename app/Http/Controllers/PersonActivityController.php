<?php

namespace App\Http\Controllers;

use App\Models\PersonActivity;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PersonActivityController extends Controller
{
    /**
     * @OA\Get(
     *     path="/people/activities/liked",
     *     summary="Get list of liked people",
     *     description="Get a paginated list of people that have been liked",
     *     operationId="getLikedPeople",
     *     tags={"People"},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page (max 50)",
     *         required=false,
     *         @OA\Schema(type="integer", default=10, maximum=50)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Liked people retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="activity_id", type="integer"),
     *                     @OA\Property(property="person_id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="age", type="integer"),
     *                     @OA\Property(property="gender", type="string"),
     *                     @OA\Property(property="location", type="string"),
     *                     @OA\Property(property="bio", type="string"),
     *                     @OA\Property(property="interests", type="string"),
     *                     @OA\Property(property="photo_url", type="string"),
     *                     @OA\Property(property="liked_at", type="string", format="date-time")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="last_page", type="integer")
     *             )
     *         )
     *     )
     * )
     */
    public function likedPeople(Request $request): JsonResponse
    {
        $perPage = min($request->input('per_page', 10), 50);
        
        // Get liked activities with person details
        $activities = PersonActivity::with('person')
            ->where('action_type', 'like')
            ->orderBy('action_at', 'desc')
            ->paginate($perPage);

        $data = $activities->map(function ($activity) {
            return [
                'activity_id' => $activity->id,
                'person_id' => $activity->person->id,
                'name' => $activity->person->name,
                'age' => $activity->person->age,
                'location' => $activity->person->location,
                'photo_url' => $activity->person->image_url,
                'liked_at' => $activity->action_at->toIso8601String(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Liked people retrieved successfully',
            'data' => $data,
            'meta' => [
                'current_page' => $activities->currentPage(),
                'total' => $activities->total(),
                'per_page' => $activities->perPage(),
                'last_page' => $activities->lastPage(),
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/people/activities/disliked",
     *     summary="Get list of disliked people",
     *     description="Get a paginated list of people that have been disliked",
     *     operationId="getDislikedPeople",
     *     tags={"People"},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page (max 50)",
     *         required=false,
     *         @OA\Schema(type="integer", default=10, maximum=50)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Disliked people retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="activity_id", type="integer"),
     *                     @OA\Property(property="person_id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="age", type="integer"),
     *                     @OA\Property(property="gender", type="string"),
     *                     @OA\Property(property="location", type="string"),
     *                     @OA\Property(property="bio", type="string"),
     *                     @OA\Property(property="interests", type="string"),
     *                     @OA\Property(property="photo_url", type="string"),
     *                     @OA\Property(property="disliked_at", type="string", format="date-time")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="last_page", type="integer")
     *             )
     *         )
     *     )
     * )
     */
    public function dislikedPeople(Request $request): JsonResponse
    {
        $perPage = min($request->input('per_page', 10), 50);
        
        // Get disliked activities with person details
        $activities = PersonActivity::with('person')
            ->where('action_type', 'dislike')
            ->orderBy('action_at', 'desc')
            ->paginate($perPage);

        $data = $activities->map(function ($activity) {
            return [
                'activity_id' => $activity->id,
                'person_id' => $activity->person->id,
                'name' => $activity->person->name,
                'age' => $activity->person->age,
                'location' => $activity->person->location,
                'photo_url' => $activity->person->image_url,
                'disliked_at' => $activity->action_at->toIso8601String(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Disliked people retrieved successfully',
            'data' => $data,
            'meta' => [
                'current_page' => $activities->currentPage(),
                'total' => $activities->total(),
                'per_page' => $activities->perPage(),
                'last_page' => $activities->lastPage(),
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/people/activities/all",
     *     summary="Get all activities",
     *     description="Get a paginated list of all like and dislike activities",
     *     operationId="getAllActivities",
     *     tags={"People"},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page (max 50)",
     *         required=false,
     *         @OA\Schema(type="integer", default=10, maximum=50)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="action_type",
     *         in="query",
     *         description="Filter by action type (like or dislike)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"like", "dislike"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Activities retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="activity_id", type="integer"),
     *                     @OA\Property(property="person_id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="age", type="integer"),
     *                     @OA\Property(property="gender", type="string"),
     *                     @OA\Property(property="location", type="string"),
     *                     @OA\Property(property="bio", type="string"),
     *                     @OA\Property(property="interests", type="string"),
     *                     @OA\Property(property="photo_url", type="string"),
     *                     @OA\Property(property="action_type", type="string", enum={"like", "dislike"}),
     *                     @OA\Property(property="action_at", type="string", format="date-time")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="last_page", type="integer")
     *             )
     *         )
     *     )
     * )
     */
    public function allActivities(Request $request): JsonResponse
    {
        $perPage = min($request->input('per_page', 10), 50);
        $actionType = $request->input('action_type');
        
        // Build query
        $query = PersonActivity::with('person')->orderBy('action_at', 'desc');
        
        // Filter by action type if provided
        if ($actionType && in_array($actionType, ['like', 'dislike'])) {
            $query->where('action_type', $actionType);
        }
        
        $activities = $query->paginate($perPage);

        $data = $activities->map(function ($activity) {
            return [
                'activity_id' => $activity->id,
                'person_id' => $activity->person->id,
                'name' => $activity->person->name,
                'age' => $activity->person->age,
                'location' => $activity->person->location,
                'photo_url' => $activity->person->image_url,
                'action_type' => $activity->action_type,
                'action_at' => $activity->action_at->toIso8601String(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Activities retrieved successfully',
            'data' => $data,
            'meta' => [
                'current_page' => $activities->currentPage(),
                'total' => $activities->total(),
                'per_page' => $activities->perPage(),
                'last_page' => $activities->lastPage(),
            ]
        ]);
    }
}