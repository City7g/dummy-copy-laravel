<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "title" => fake()->words(3, true),
            "description" => fake()->paragraph(),
            "price" => fake()->randomFloat(2, 1.0, 500.0),
            "rating" => fake()->randomFloat(1, 1, 5),
            "stock" => fake()->numberBetween(0, 100),
        ];
    }
}
