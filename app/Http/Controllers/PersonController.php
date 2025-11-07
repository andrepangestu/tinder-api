<?php

namespace App\Http\Controllers;

use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PersonController extends Controller
{
    /**
     * Display a paginated list of recommended people.
     *
     * @param Request $request
     * @return JsonResponse
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
     * Display a listing of all people.
     *
     * @param Request $request
     * @return JsonResponse
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
     * Display the specified person.
     *
     * @param int $id
     * @return JsonResponse
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
     * Like a person
     *
     * @param int $id
     * @return JsonResponse
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

        $person->incrementLikes();

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
     * Dislike a person
     *
     * @param int $id
     * @return JsonResponse
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

        $person->incrementDislikes();

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