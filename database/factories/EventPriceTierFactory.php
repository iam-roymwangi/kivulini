<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventPriceTier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventPriceTier>
 */
class EventPriceTierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'name' => fake()->randomElement(['Regular', 'VIP', 'VVIP']),
            'price' => fake()->randomElement([1500, 3500, 7500]),
            'description' => fake()->boolean(50) ? fake()->sentence() : null,
            'sort_order' => 0,
        ];
    }
}
