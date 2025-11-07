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
        $interests = [
            'Hiking', 'Photography', 'Cooking', 'Reading', 'Traveling', 
            'Music', 'Dancing', 'Sports', 'Art', 'Movies', 'Gaming',
            'Fitness', 'Yoga', 'Swimming', 'Running', 'Cycling'
        ];

        return [
            'name' => $this->faker->name(),
            'age' => $this->faker->numberBetween(18, 45),
            'location' => $this->faker->city() . ', ' . $this->faker->stateAbbr(),
            'bio' => $this->faker->paragraph(2),
            'image_url' => $this->faker->imageUrl(400, 600, 'people'),
            'interests' => $this->faker->randomElements($interests, $this->faker->numberBetween(2, 5)),
        ];
    }
}