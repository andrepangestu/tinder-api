<?php

namespace Database\Seeders;

use App\Models\Person;
use App\Models\PersonActivity;
use App\Models\User;
use Illuminate\Database\Seeder;

class PopularPersonSeeder extends Seeder
{
    /**
     * Seed popular persons for testing cronjob email notification.
     */
    public function run(): void
    {
        $this->command->info('Creating popular persons with 50+ likes...');

        // Create 3 popular persons
        $popularPersonsData = [
            ['name' => 'Super Popular Person', 'age' => 28, 'location' => 'Jakarta', 'likes' => 75],
            ['name' => 'Very Liked Person', 'age' => 25, 'location' => 'Bandung', 'likes' => 62],
            ['name' => 'Trending Person', 'age' => 30, 'location' => 'Surabaya', 'likes' => 55],
        ];

        foreach ($popularPersonsData as $data) {
            $person = Person::factory()->create([
                'name' => $data['name'],
                'age' => $data['age'],
                'location' => $data['location'],
            ]);

            // Create likes for this person
            for ($i = 0; $i < $data['likes']; $i++) {
                $user = User::factory()->create();
                PersonActivity::create([
                    'user_id' => $user->id,
                    'person_id' => $person->id,
                    'action_type' => 'like',
                    'action_at' => now()->subDays(rand(0, 30)),
                ]);
            }

            $this->command->info("✓ Created '{$person->name}' with {$data['likes']} likes (ID: {$person->id})");
        }

        // Create 2 regular persons (less than 50 likes)
        $regularPersonsData = [
            ['name' => 'Regular Person A', 'age' => 27, 'location' => 'Yogyakarta', 'likes' => 25],
            ['name' => 'Regular Person B', 'age' => 29, 'location' => 'Bali', 'likes' => 35],
        ];

        foreach ($regularPersonsData as $data) {
            $person = Person::factory()->create([
                'name' => $data['name'],
                'age' => $data['age'],
                'location' => $data['location'],
            ]);

            // Create likes for this person
            for ($i = 0; $i < $data['likes']; $i++) {
                $user = User::factory()->create();
                PersonActivity::create([
                    'user_id' => $user->id,
                    'person_id' => $person->id,
                    'action_type' => 'like',
                    'action_at' => now()->subDays(rand(0, 30)),
                ]);
            }

            $this->command->info("  Created '{$person->name}' with {$data['likes']} likes (ID: {$person->id})");
        }

        $this->command->newLine();
        $this->command->info('✅ Seeding completed!');
        $this->command->info('Run: php artisan persons:check-popular --admin-email=test@example.com');
    }
}
