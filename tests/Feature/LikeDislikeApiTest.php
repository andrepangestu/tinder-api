<?php

namespace Tests\Feature;

use App\Models\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LikeDislikeApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create some test people
        Person::factory(5)->create();
    }

    public function test_can_like_a_person()
    {
        $person = Person::first();
        $initialLikes = $person->likes_count;

        $response = $this->postJson("/api/people/{$person->id}/like");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'person_id',
                        'name',
                        'likes_count',
                        'dislikes_count'
                    ]
                ]);

        $responseData = $response->json();
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals('Person liked successfully', $responseData['message']);
        $this->assertEquals($person->id, $responseData['data']['person_id']);
        $this->assertEquals($initialLikes + 1, $responseData['data']['likes_count']);

        // Verify activity was created in database
        $this->assertDatabaseHas('person_activities', [
            'person_id' => $person->id,
            'action_type' => 'like'
        ]);
    }

    public function test_can_dislike_a_person()
    {
        $person = Person::first();
        $initialDislikes = $person->dislikes_count;

        $response = $this->postJson("/api/people/{$person->id}/dislike");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'person_id',
                        'name',
                        'likes_count',
                        'dislikes_count'
                    ]
                ]);

        $responseData = $response->json();
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals('Person disliked successfully', $responseData['message']);
        $this->assertEquals($person->id, $responseData['data']['person_id']);
        $this->assertEquals($initialDislikes + 1, $responseData['data']['dislikes_count']);

        // Verify activity was created in database
        $this->assertDatabaseHas('person_activities', [
            'person_id' => $person->id,
            'action_type' => 'dislike'
        ]);
    }

    public function test_can_like_multiple_times()
    {
        $person = Person::first();

        // Like the person multiple times
        $this->postJson("/api/people/{$person->id}/like");
        $this->postJson("/api/people/{$person->id}/like");
        $response = $this->postJson("/api/people/{$person->id}/like");

        $responseData = $response->json();
        $this->assertEquals(3, $responseData['data']['likes_count']);

        // Verify 3 like activities were created in database
        $this->assertDatabaseCount('person_activities', 3);
        $this->assertEquals(3, \App\Models\PersonActivity::where('person_id', $person->id)
            ->where('action_type', 'like')
            ->count());
    }

    public function test_can_dislike_multiple_times()
    {
        $person = Person::first();

        // Dislike the person multiple times
        $this->postJson("/api/people/{$person->id}/dislike");
        $this->postJson("/api/people/{$person->id}/dislike");
        $response = $this->postJson("/api/people/{$person->id}/dislike");

        $responseData = $response->json();
        $this->assertEquals(3, $responseData['data']['dislikes_count']);

        // Verify 3 dislike activities were created in database
        $this->assertDatabaseCount('person_activities', 3);
        $this->assertEquals(3, \App\Models\PersonActivity::where('person_id', $person->id)
            ->where('action_type', 'dislike')
            ->count());
    }

    public function test_like_returns_404_for_non_existent_person()
    {
        $response = $this->postJson('/api/people/999999/like');

        $response->assertStatus(404)
                ->assertJson([
                    'status' => 'error',
                    'message' => 'Person not found'
                ]);
    }

    public function test_dislike_returns_404_for_non_existent_person()
    {
        $response = $this->postJson('/api/people/999999/dislike');

        $response->assertStatus(404)
                ->assertJson([
                    'status' => 'error',
                    'message' => 'Person not found'
                ]);
    }

    public function test_likes_and_dislikes_are_independent()
    {
        $person = Person::first();

        // Like and dislike the same person
        $this->postJson("/api/people/{$person->id}/like");
        $this->postJson("/api/people/{$person->id}/like");
        $this->postJson("/api/people/{$person->id}/dislike");

        $person->refresh();

        $this->assertEquals(2, $person->likes_count);
        $this->assertEquals(1, $person->dislikes_count);
    }

    public function test_people_list_includes_like_dislike_counts()
    {
        $person = Person::first();
        
        // Add some likes and dislikes via API
        $this->postJson("/api/people/{$person->id}/like");
        $this->postJson("/api/people/{$person->id}/like");
        $this->postJson("/api/people/{$person->id}/dislike");

        $response = $this->getJson('/api/people/recommended');

        $response->assertStatus(200);
        
        $responseData = $response->json();
        $firstPerson = $responseData['data']['people'][0];
        
        $this->assertArrayHasKey('likes_count', $firstPerson);
        $this->assertArrayHasKey('dislikes_count', $firstPerson);
    }
}