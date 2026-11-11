<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PerformanceTestSeeder extends Seeder
{
    public function run(): void
    {
        $categoryId = DB::table('categories')
            ->where('name_en', 'Electronics')
            ->value('id');

        $userId = DB::table('users')
            ->where('email', 'user@ecommerce.com')
            ->value('id');

        if (! $categoryId || ! $userId) {
            $this->command->error('Required category or user not found.');
            return;
        }

        // Prevent duplicate test data
        if (DB::table('products')
            ->where('name_en', 'like', 'PERF Product %')
            ->exists()) {
            $this->command->error(
                'Performance test data already exists. No changes made.'
            );
            return;
        }

        DB::transaction(function () use ($categoryId, $userId) {

            $now = now();

            // 1. Create 10,000 products
            $this->command->info('Creating 10,000 products...');

            for ($batch = 0; $batch < 10; $batch++) {
                $products = [];

                for ($i = 1; $i <= 1000; $i++) {
                    $number = ($batch * 1000) + $i;

                    $products[] = [
                        'category_id' => $categoryId,
                        'name_en' => 'PERF Product ' . $number,
                        'name_ar' => 'منتج اختبار ' . $number,
                        'description_en' => 'Performance test product ' . $number,
                        'description_ar' => 'وصف منتج اختبار ' . $number,
                        'created_at' => $now->copy()->subSeconds($number),
                        'updated_at' => $now,
                    ];
                }

                DB::table('products')->insert($products);
            }

            $productIds = DB::table('products')
                ->where('name_en', 'like', 'PERF Product %')
                ->orderBy('id')
                ->pluck('id')
                ->toArray();

            // 2. Create 20,000 orders
            $this->command->info('Creating 20,000 orders...');

            $reservationIds = [];

            for ($batch = 0; $batch < 20; $batch++) {
                $orders = [];

                for ($i = 1; $i <= 1000; $i++) {
                    $number = ($batch * 1000) + $i;
                    $reservationId = (string) Str::uuid();

                    $reservationIds[] = $reservationId;

                    $orders[] = [
                        'user_id' => $userId,
                        'status' => 'confirmed',
                        'reservation_id' => $reservationId,
                        'created_at' => $now->copy()->subSeconds($number),
                        'updated_at' => $now,
                    ];
                }

                DB::table('orders')->insert($orders);
            }

            // Get only the IDs of the newly created test orders
            $orderIds = [];

            foreach (array_chunk($reservationIds, 1000) as $chunk) {
                $ids = DB::table('orders')
                    ->whereIn('reservation_id', $chunk)
                    ->pluck('id')
                    ->toArray();

                $orderIds = array_merge($orderIds, $ids);
            }

            if (count($productIds) !== 10000 || count($orderIds) !== 20000) {
                throw new \RuntimeException('Test data verification failed.');
            }

            // 3. Create 50,000 order items
            $this->command->info('Creating 50,000 order items...');

            $items = [];

            for ($i = 0; $i < 50000; $i++) {
                $items[] = [
                    'order_id' => $orderIds[$i % count($orderIds)],
                    'product_id' => $productIds[$i % count($productIds)],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if (count($items) === 1000) {
                    DB::table('order_items')->insert($items);
                    $items = [];
                }
            }
        }, 3);

        $this->command->info(
            'Performance test data created successfully!'
        );
    }
}
