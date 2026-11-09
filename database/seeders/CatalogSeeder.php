<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::factory()->count(50)->create();

        $categories->take(5)->each(function (Category $category): void {
            Product::factory()
                ->count(10)
                ->create(['category_id' => $category->id]);
        });
    }
}
