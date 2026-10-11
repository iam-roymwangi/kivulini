<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Review;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'reviewer_name' => fake()->name(),
            'reviewer_email' => fake()->safeEmail(),
            'rating' => fake()->numberBetween(1, 5),
            'body' => fake()->paragraph(),
            'image_path' => null,
            'is_approved' => false,
        ];
    }

    public function approved(): static
    {
        return $this->state(['is_approved' => true]);
    }
}
