<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Person;
use Illuminate\Support\Facades\DB;

class PeopleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate table untuk reset auto increment
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Person::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Generate 100 people
        for ($i = 1; $i <= 100; $i++) {
            $distance = fake()->numberBetween(1, 50); // Random distance 1-50 km
            
            Person::create([
                'name' => fake()->name(),
                'age' => fake()->numberBetween(18, 45),
                'location' => "{$distance} km",
                'image_url' => "https://randomuser.me/api/portraits/women/{$i}.jpg",
            ]);
        }

        $this->command->info('100 people have been seeded successfully!');
    }
}