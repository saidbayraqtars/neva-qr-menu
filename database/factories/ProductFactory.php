<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->words(2, true);

        return [
            'restaurant_id' => Restaurant::factory(),
            'category_id' => Category::factory(),
            'name' => Str::title($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 99999),
            'description' => fake()->sentence(),
            'price' => fake()->randomElement([85, 95, 120, 145, 165, 190]),
            'is_available' => true,
            'is_featured' => fake()->boolean(20),
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }
}
