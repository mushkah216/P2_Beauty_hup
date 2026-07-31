<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) إضافة expert للـ enum
        DB::statement("ALTER TABLE `products` MODIFY `provider_type` ENUM('salon','beauty_center','warehouse','expert') NOT NULL");

        Schema::table('products', function (Blueprint $table) {
            // 2) المواد الخام ما إلها سعر بيع ولا تصنيف
            $table->decimal('price', 10, 2)->nullable()->change();
            $table->foreignId('category_id')->nullable()->change();

            // 3) الكميات لازم تقبل كسور (30.5 مل)
            $table->decimal('stock_quantity', 10, 2)->default(0)->change();
            $table->decimal('min_stock_threshold', 10, 2)->default(5)->change();
        });

        // 4) SKU فريد عند نفس المزود بس، مش على كل الجدول
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['sku']);
            $table->unique(['provider_type', 'provider_id', 'sku']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['provider_type', 'provider_id', 'sku']);
            $table->unique(['sku']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable(false)->change();
            $table->foreignId('category_id')->nullable(false)->change();
            $table->unsignedInteger('stock_quantity')->default(0)->change();
            $table->unsignedInteger('min_stock_threshold')->default(5)->change();
        });

        DB::statement("ALTER TABLE `products` MODIFY `provider_type` ENUM('salon','beauty_center','warehouse') NOT NULL");
    }
};