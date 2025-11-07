<?php

namespace Tests\Feature;

use App\Models\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PeopleApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create some test people
        Person::factory(25)->create();
    }

    public function test_can_get_recommended_people()
    {
        $response = $this->get('/api/people/recommended');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'people' => [
                            '*' => [
                                'id',
                                'name',
                                'age',
                                'location',
                                'bio',
                                'image_url',
                                'interests',
                                'created_at',
                                'updated_at'
                            ]
                        ],
                        'pagination' => [
                            'current_page',
                            'per_page',
                            'total',
                            'last_page',
                            'from',
                            'to',
                            'has_more_pages',
                            'next_page_url',
                            'prev_page_url'
                        ]
                    ]
                ]);

        $responseData = $response->json();
        $this->assertEquals('success', $responseData['status']);
        $this->assertCount(10, $responseData['data']['people']); // Default per_page is 10
    }

    public function test_can_get_recommended_people_with_pagination()
    {
        $response = $this->get('/api/people/recommended?per_page=5&page=2');

        $response->assertStatus(200);
        
        $responseData = $response->json();
        $this->assertEquals(5, $responseData['data']['pagination']['per_page']);
        $this->assertEquals(2, $responseData['data']['pagination']['current_page']);
        $this->assertCount(5, $responseData['data']['people']);
    }

    public function test_pagination_limits_per_page_to_50()
    {
        $response = $this->get('/api/people/recommended?per_page=100');

        $response->assertStatus(200);
        
        $responseData = $response->json();
        $this->assertLessThanOrEqual(50, $responseData['data']['pagination']['per_page']);
    }

    public function test_can_get_all_people()
    {
        $response = $this->get('/api/people');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'people',
                        'pagination'
                    ]
                ]);

        $responseData = $response->json();
        $this->assertEquals('success', $responseData['status']);
    }

    public function test_can_get_person_by_id()
    {
        $person = Person::first();

        $response = $this->get("/api/people/{$person->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'id',
                        'name',
                        'age',
                        'location',
                        'bio',
                        'image_url',
                        'interests',
                        'created_at',
                        'updated_at'
                    ]
                ]);

        $responseData = $response->json();
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals($person->id, $responseData['data']['id']);
    }

    public function test_returns_404_for_non_existent_person()
    {
        $response = $this->get('/api/people/999999');

        $response->assertStatus(404)
                ->assertJson([
                    'status' => 'error',
                    'message' => 'Person not found'
                ]);
    }

    public function test_api_test_endpoint_works()
    {
        $response = $this->get('/api/test');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'timestamp',
                    'people_count'
                ]);

        $responseData = $response->json();
        $this->assertEquals('API is working!', $responseData['message']);
        $this->assertEquals(25, $responseData['people_count']);
    }
}