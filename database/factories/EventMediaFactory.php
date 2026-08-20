<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventMedia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventMedia>
 */
class EventMediaFactory extends Factory
{
    protected $model = EventMedia::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'file_path' => 'events/placeholder-'.fake()->uuid().'.webp',
            'type' => 'image',
            'is_featured' => false,
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }

    public function featured(): static
    {
        return $this->state(['is_featured' => true]);
    }
}
