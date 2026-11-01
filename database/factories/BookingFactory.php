<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 3);

        return [
            'event_id' => Event::factory(),
            'contact_name' => fake()->name(),
            'contact_email' => fake()->safeEmail(),
            'contact_phone' => '+254700000001',
            'quantity' => $quantity,
            'total_price' => $quantity * 1500,
            'payment_status' => 'pending',
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (): array => [
            'payment_status' => 'paid',
            'payment_reference' => 'NLJ7RT61SV',
        ]);
    }
}
