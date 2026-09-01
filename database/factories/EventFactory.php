<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $title = fake()->unique()->sentence(rand(3, 6));
        $startDate = fake()->dateTimeBetween('now', '+6 months');
        $endDate = (clone $startDate)->modify('+'.rand(1, 4).' days');

        return [
            'title' => rtrim($title, '.'),
            'slug' => Str::slug($title).'-'.Str::random(4),
            'type' => fake()->randomElement(['cultural_heritage', 'wildlife_safari', 'food_music', 'road_trip', 'hiking', 'vacation']),
            'summary' => fake()->sentence(rand(12, 20)),
            'description' => collect(range(1, 4))->map(fn () => '<p>'.fake()->paragraph(rand(3, 6)).'</p>')->join("\n"),
            'location' => fake()->randomElement(['Nairobi', 'Mombasa', 'Nakuru', 'Naivasha', 'Diani', 'Kisumu', 'Malindi', 'Lake Nakuru']),
            'pickup_location' => fake()->boolean(60) ? fake()->randomElement(['CBD Nairobi', 'Westlands', 'Karen', 'Upperhill']) : null,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'price' => fake()->randomElement([1500, 2500, 3500, 5000, 7500, 10000, 15000]),
            'capacity' => fake()->randomElement([15, 20, 30, 40, 50]),
            'booked_slots' => 0,
            'status' => 'published',
            'liability_waiver_text' => fake()->boolean(70) ? 'By booking this event you acknowledge that you participate at your own risk. '.fake()->paragraph(3) : null,
        ];
    }

    /** Mark event as completed with a past date */
    public function completed(): static
    {
        return $this->state(function () {
            $startDate = fake()->dateTimeBetween('-6 months', '-1 week');
            $endDate = (clone $startDate)->modify('+'.rand(1, 3).' days');

            return [
                'status' => 'completed',
                'start_date' => $startDate,
                'end_date' => $endDate,
                'booked_slots' => fake()->numberBetween(5, 20),
            ];
        });
    }

    public function roadTrip(): static
    {
        return $this->state(['type' => 'road_trip']);
    }

    public function hiking(): static
    {
        return $this->state(['type' => 'hiking']);
    }

    public function draft(): static
    {
        return $this->state(['status' => 'draft']);
    }
}
