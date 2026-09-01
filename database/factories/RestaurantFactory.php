<?php

namespace Database\Factories;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Restaurant>
 */
class RestaurantFactory extends Factory
{
    protected $model = Restaurant::class;

    public function definition(): array
    {
        $name = fake()->company();

        return [
            'user_id' => User::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 99999),
            'template' => fake()->randomElement(Restaurant::templateKeys()),
            'status' => Restaurant::STATUS_DRAFT,
            'accent_color' => fake()->hexColor(),
            'font_family' => 'Inter',
            'logo_size' => 'medium',
        ];
    }

    public function live(): static
    {
        return $this->state(fn () => [
            'status' => Restaurant::STATUS_APPROVED,
            'subdomain' => fake()->unique()->userName(),
            'approved_at' => now(),
            'published_at' => now(),
        ]);
    }
}
