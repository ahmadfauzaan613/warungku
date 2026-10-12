<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $hargaBeli = $this->faker->numberBetween(1_000, 20_000);

        return [
            'category_id' => null,
            'name' => $this->faker->unique()->words(2, true),
            'purchase_price' => $hargaBeli,
            'selling_price' => $hargaBeli + $this->faker->numberBetween(500, 4_000),
            'stock' => $this->faker->numberBetween(5, 60),
            'low_stock_threshold' => 5,
        ];
    }
}
