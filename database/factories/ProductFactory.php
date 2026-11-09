<?php

namespace Database\Factories;

use App\Models\Category;
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
            'category_id' => Category::factory(),
            'name_en' => $this->faker->unique()->words(3, true),
            'name_ar' => 'منتج '.$this->faker->unique()->numberBetween(1, 999999),
            'description_en' => $this->faker->paragraph(),
            'description_ar' => 'وصف المنتج '.$this->faker->paragraph(),
        ];
    }
}
