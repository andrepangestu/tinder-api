<?php

namespace Database\Factories;

use App\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Person>
 */
class PersonFactory extends Factory
{
    protected $model = Person::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $randomId = $this->faker->numberBetween(1, 99);
        $distance = $this->faker->numberBetween(1, 50);
        
        return [
            'name' => $this->faker->name(),
            'age' => $this->faker->numberBetween(18, 45),
            'location' => "{$distance} km",
            'image_url' => "https://randomuser.me/api/portraits/women/{$randomId}.jpg",
        ];
    }
}