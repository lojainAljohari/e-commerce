<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoProductsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $category = DB::table('categories')->where('name_en', 'Electronics')->first();
        $categoryId = $category?->id ?? DB::table('categories')->insertGetId([
            'name_en' => 'Electronics',
            'name_ar' => 'إلكترونيات',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $products = [
            ['Phone', 'هاتف'],
            ['Laptop', 'لابتوب'],
            ['Headphones', 'سماعات'],
        ];

        foreach ($products as [$en, $ar]) {
            if (! DB::table('products')->where('name_en', $en)->exists()) {
                DB::table('products')->insert([
                    'category_id' => $categoryId,
                    'name_en' => $en,
                    'name_ar' => $ar,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $this->command?->info('Products now in DB:');
        foreach (DB::table('products')->orderBy('id')->get(['id', 'name_en']) as $p) {
            $this->command?->line("  id={$p->id}  {$p->name_en}");
        }
    }
}
