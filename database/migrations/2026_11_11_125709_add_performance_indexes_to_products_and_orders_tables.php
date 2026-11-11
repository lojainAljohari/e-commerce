
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index(
                ['category_id', 'created_at'],
                'products_category_created_at_index'
            );
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index(
                ['user_id', 'created_at'],
                'orders_user_created_at_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_category_created_at_index');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_user_created_at_index');
        });
    }
};
