<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name_en' => 'Category '.$this->faker->unique()->numberBetween(1, 999999),
            'name_ar' => 'التصنيف '.$this->faker->unique()->numberBetween(1, 999999),
        ];
    }
}
