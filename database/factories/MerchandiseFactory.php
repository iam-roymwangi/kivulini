<?php

namespace Database\Factories;

use App\Models\Merchandise;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Merchandise>
 */
class MerchandiseFactory extends Factory
{
    protected $model = Merchandise::class;

    public function definition(): array
    {
        $name = $this->faker->words(3, true);
        $types = ['t_shirt', 'hoodie', 'hat', 'other'];
        $allSizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
        $allColors = ['Black', 'White', 'Amber', 'Navy', 'Green', 'Red', 'Grey'];

        return [
            'name' => ucwords($name),
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(5)),
            'type' => $this->faker->randomElement($types),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->randomFloat(2, 500, 5000),
            'sizes' => $this->faker->randomElements($allSizes, $this->faker->numberBetween(3, 6)),
            'colors' => $this->faker->randomElements($allColors, $this->faker->numberBetween(2, 4)),
            'stock' => $this->faker->numberBetween(0, 100),
            'status' => $this->faker->randomElement(['draft', 'published']),
        ];
    }

    public function published(): static
    {
        return $this->state(['status' => 'published', 'stock' => $this->faker->numberBetween(5, 100)]);
    }

    public function draft(): static
    {
        return $this->state(['status' => 'draft']);
    }
}
