<?php

namespace Tests\Feature;

use App\Mail\PopularPersonNotification;
use App\Models\Person;
use App\Models\PersonActivity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CheckPopularPersonsCommandTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test command successfully identifies popular persons with more than 50 likes.
     */
    public function test_command_identifies_popular_persons(): void
    {
        Mail::fake();

        // Create a person
        $person = Person::factory()->create([
            'name' => 'Popular Person',
            'age' => 25,
            'location' => 'Jakarta',
        ]);

        // Create 55 users who liked this person
        for ($i = 0; $i < 55; $i++) {
            $user = User::factory()->create();
            PersonActivity::create([
                'user_id' => $user->id,
                'person_id' => $person->id,
                'action_type' => 'like',
                'action_at' => now(),
            ]);
        }

        // Run the command
        $this->artisan('persons:check-popular')
            ->expectsOutput('Checking for persons with more than 50 likes...')
            ->expectsOutput('Found 1 popular person(s).')
            ->expectsOutput('Email notification sent to admin@tinderapi.com')
            ->expectsOutput('Popular persons check completed successfully.')
            ->assertExitCode(0);

        // Assert email was sent
        Mail::assertSent(PopularPersonNotification::class, function ($mail) {
            return $mail->hasTo('admin@tinderapi.com');
        });
    }

    /**
     * Test command handles no popular persons correctly.
     */
    public function test_command_handles_no_popular_persons(): void
    {
        Mail::fake();

        // Create a person with only 20 likes
        $person = Person::factory()->create();
        
        for ($i = 0; $i < 20; $i++) {
            $user = User::factory()->create();
            PersonActivity::create([
                'user_id' => $user->id,
                'person_id' => $person->id,
                'action_type' => 'like',
                'action_at' => now(),
            ]);
        }

        // Run the command
        $this->artisan('persons:check-popular')
            ->expectsOutput('Checking for persons with more than 50 likes...')
            ->expectsOutput('No persons found with more than 50 likes.')
            ->assertExitCode(0);

        // Assert no email was sent
        Mail::assertNotSent(PopularPersonNotification::class);
    }

    /**
     * Test command with custom threshold option.
     */
    public function test_command_with_custom_threshold(): void
    {
        Mail::fake();

        // Create a person with 25 likes
        $person = Person::factory()->create([
            'name' => 'Moderately Popular Person',
        ]);

        for ($i = 0; $i < 25; $i++) {
            $user = User::factory()->create();
            PersonActivity::create([
                'user_id' => $user->id,
                'person_id' => $person->id,
                'action_type' => 'like',
                'action_at' => now(),
            ]);
        }

        // Run the command with threshold of 20
        $this->artisan('persons:check-popular', ['--threshold' => 20])
            ->expectsOutput('Checking for persons with more than 20 likes...')
            ->expectsOutput('Found 1 popular person(s).')
            ->assertExitCode(0);

        // Assert email was sent
        Mail::assertSent(PopularPersonNotification::class);
    }

    /**
     * Test command with custom admin email option.
     */
    public function test_command_with_custom_admin_email(): void
    {
        Mail::fake();

        // Create a person with 60 likes
        $person = Person::factory()->create();

        for ($i = 0; $i < 60; $i++) {
            $user = User::factory()->create();
            PersonActivity::create([
                'user_id' => $user->id,
                'person_id' => $person->id,
                'action_type' => 'like',
                'action_at' => now(),
            ]);
        }

        // Run the command with custom admin email
        $customEmail = 'custom-admin@example.com';
        $this->artisan('persons:check-popular', ['--admin-email' => $customEmail])
            ->assertExitCode(0);

        // Assert email was sent to custom email
        Mail::assertSent(PopularPersonNotification::class, function ($mail) use ($customEmail) {
            return $mail->hasTo($customEmail);
        });
    }

    /**
     * Test command identifies multiple popular persons.
     */
    public function test_command_identifies_multiple_popular_persons(): void
    {
        Mail::fake();

        // Create 3 persons with more than 50 likes
        for ($p = 0; $p < 3; $p++) {
            $person = Person::factory()->create([
                'name' => "Popular Person $p",
            ]);

            for ($i = 0; $i < 55; $i++) {
                $user = User::factory()->create();
                PersonActivity::create([
                    'user_id' => $user->id,
                    'person_id' => $person->id,
                    'action_type' => 'like',
                    'action_at' => now(),
                ]);
            }
        }

        // Run the command
        $this->artisan('persons:check-popular')
            ->expectsOutput('Found 3 popular person(s).')
            ->assertExitCode(0);

        // Assert email was sent with multiple persons
        Mail::assertSent(PopularPersonNotification::class, function ($mail) {
            return $mail->popularPersons->count() === 3;
        });
    }

    /**
     * Test command only counts likes, not dislikes.
     */
    public function test_command_only_counts_likes_not_dislikes(): void
    {
        Mail::fake();

        // Create a person with 30 likes and 30 dislikes
        $person = Person::factory()->create();

        for ($i = 0; $i < 30; $i++) {
            $user = User::factory()->create();
            PersonActivity::create([
                'user_id' => $user->id,
                'person_id' => $person->id,
                'action_type' => 'like',
                'action_at' => now(),
            ]);
        }

        for ($i = 0; $i < 30; $i++) {
            $user = User::factory()->create();
            PersonActivity::create([
                'user_id' => $user->id,
                'person_id' => $person->id,
                'action_type' => 'dislike',
                'action_at' => now(),
            ]);
        }

        // Run the command
        $this->artisan('persons:check-popular')
            ->expectsOutput('No persons found with more than 50 likes.')
            ->assertExitCode(0);

        // Assert no email was sent (only 30 likes, not 60 total actions)
        Mail::assertNotSent(PopularPersonNotification::class);
    }

    /**
     * Test email content contains correct person information.
     */
    public function test_email_content_contains_correct_information(): void
    {
        Mail::fake();

        // Create a person with specific details
        $person = Person::factory()->create([
            'name' => 'John Doe',
            'age' => 30,
            'location' => 'Bandung',
        ]);

        for ($i = 0; $i < 51; $i++) {
            $user = User::factory()->create();
            PersonActivity::create([
                'user_id' => $user->id,
                'person_id' => $person->id,
                'action_type' => 'like',
                'action_at' => now(),
            ]);
        }

        // Run the command
        $this->artisan('persons:check-popular')->assertExitCode(0);

        // Assert email was sent with correct person data
        Mail::assertSent(PopularPersonNotification::class, function ($mail) use ($person) {
            $popularPerson = $mail->popularPersons->first();
            return $popularPerson->id === $person->id
                && $popularPerson->name === 'John Doe'
                && $popularPerson->age === 30
                && $popularPerson->location === 'Bandung'
                && $popularPerson->likes_count === 51;
        });
    }
}
