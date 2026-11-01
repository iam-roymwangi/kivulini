<?php

namespace Database\Factories;

use App\Models\Merchandise;
use App\Models\MerchandiseOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MerchandiseOrder>
 */
class MerchandiseOrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 2);
        $unitPrice = 1500;

        return [
            'merchandise_id' => Merchandise::factory()->published(),
            'contact_name' => fake()->name(),
            'contact_email' => fake()->safeEmail(),
            'contact_phone' => '+254700000001',
            'size' => 'M',
            'color' => 'Black',
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $quantity * $unitPrice,
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
