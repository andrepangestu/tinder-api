<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\PersonActivity;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class PersonController extends Controller
{
    /**
     * @OA\Get(
     *     path="/people/recommended",
     *     summary="Get recommended people",
     *     description="Get a paginated list of recommended people in random order",
     *     operationId="getRecommendedPeople",
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
     *             @OA\Property(property="message", type="string", example="Recommended people retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="people", type="array", @OA\Items(ref="#/components/schemas/Person")),
     *                 @OA\Property(property="pagination", ref="#/components/schemas/Pagination")
     *             )
     *         )
     *     )
     * )
     */
    public function recommended(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 10); // Default 10 items per page
        $perPage = min($perPage, 50); // Max 50 items per page

        $people = Person::recommended()
            ->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'message' => 'Recommended people retrieved successfully',
            'data' => [
                'people' => $people->items(),
                'pagination' => [
                    'current_page' => $people->currentPage(),
                    'per_page' => $people->perPage(),
                    'total' => $people->total(),
                    'last_page' => $people->lastPage(),
                    'from' => $people->firstItem(),
                    'to' => $people->lastItem(),
                    'has_more_pages' => $people->hasMorePages(),
                    'next_page_url' => $people->nextPageUrl(),
                    'prev_page_url' => $people->previousPageUrl(),
                ]
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/people",
     *     summary="Get all people",
     *     description="Get a paginated list of all people",
     *     operationId="getAllPeople",
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
     *             @OA\Property(property="message", type="string", example="People retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="people", type="array", @OA\Items(ref="#/components/schemas/Person")),
     *                 @OA\Property(property="pagination", ref="#/components/schemas/Pagination")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 10);
        $perPage = min($perPage, 50);

        $people = Person::paginate($perPage);

        return response()->json([
            'status' => 'success',
            'message' => 'People retrieved successfully',
            'data' => [
                'people' => $people->items(),
                'pagination' => [
                    'current_page' => $people->currentPage(),
                    'per_page' => $people->perPage(),
                    'total' => $people->total(),
                    'last_page' => $people->lastPage(),
                    'from' => $people->firstItem(),
                    'to' => $people->lastItem(),
                    'has_more_pages' => $people->hasMorePages(),
                    'next_page_url' => $people->nextPageUrl(),
                    'prev_page_url' => $people->previousPageUrl(),
                ]
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/people/{id}",
     *     summary="Get a specific person",
     *     description="Get details of a specific person by ID",
     *     operationId="getPersonById",
     *     tags={"People"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Person ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Person retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Person")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Person not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Person not found")
     *         )
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        $person = Person::find($id);

        if (!$person) {
            return response()->json([
                'status' => 'error',
                'message' => 'Person not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Person retrieved successfully',
            'data' => $person
        ]);
    }

    /**
     * @OA\Post(
     *     path="/people/{id}/like",
     *     summary="Like a person",
     *     description="Increment the like count for a specific person",
     *     operationId="likePerson",
     *     tags={"People"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Person ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Person liked successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="person_id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="likes_count", type="integer"),
     *                 @OA\Property(property="dislikes_count", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Person not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Person not found")
     *         )
     *     )
     * )
     */
    public function like(int $id): JsonResponse
    {
        $person = Person::find($id);

        if (!$person) {
            return response()->json([
                'status' => 'error',
                'message' => 'Person not found'
            ], 404);
        }

        // Record activity
        PersonActivity::create([
            'user_id' => Auth::id(), // Will be null for guest users
            'person_id' => $person->id,
            'action_type' => 'like',
            'action_at' => now(),
        ]);

        // Refresh person to get updated counts from accessor
        $person->refresh();

        return response()->json([
            'status' => 'success',
            'message' => 'Person liked successfully',
            'data' => [
                'person_id' => $person->id,
                'name' => $person->name,
                'likes_count' => $person->likes_count,
                'dislikes_count' => $person->dislikes_count
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/people/{id}/dislike",
     *     summary="Dislike a person",
     *     description="Increment the dislike count for a specific person",
     *     operationId="dislikePerson",
     *     tags={"People"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Person ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Person disliked successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="person_id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="likes_count", type="integer"),
     *                 @OA\Property(property="dislikes_count", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Person not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Person not found")
     *         )
     *     )
     * )
     */
    public function dislike(int $id): JsonResponse
    {
        $person = Person::find($id);

        if (!$person) {
            return response()->json([
                'status' => 'error',
                'message' => 'Person not found'
            ], 404);
        }

        // Record activity
        PersonActivity::create([
            'user_id' => Auth::id(), // Will be null for guest users
            'person_id' => $person->id,
            'action_type' => 'dislike',
            'action_at' => now(),
        ]);

        // Refresh person to get updated counts from accessor
        $person->refresh();

        return response()->json([
            'status' => 'success',
            'message' => 'Person disliked successfully',
            'data' => [
                'person_id' => $person->id,
                'name' => $person->name,
                'likes_count' => $person->likes_count,
                'dislikes_count' => $person->dislikes_count
            ]
        ]);
    }
}