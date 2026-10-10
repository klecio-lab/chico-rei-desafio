<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $categories = ['camisetas', 'canecas', 'moletons', 'acessorios'];

        return [
            'name' => fake()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'image' => fake()->optional()->passthrough(
                'https://placehold.co/600x600?text='.urlencode(fake()->words(2, true))
            ),
            'price' => fake()->randomFloat(2, 29, 130),
            'category' => fake()->randomElement($categories),
            'stock' => fake()->randomElement([0, 0, 5, 10, 25, 50]),
        ];
    }
}
