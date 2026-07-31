<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('service_materials');

        Schema::create('service_materials', function (Blueprint $table) {
            $table->id();

            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();

            // الكمية المستهلكة من المادة بالجلسة الواحدة (مثلاً 30.00 مل)
            $table->decimal('quantity_per_session', 10, 2);

            // وحدة القياس: ml / g / piece
            $table->string('unit', 20)->nullable();

            $table->timestamps();

            // نفس المادة ما تتكرر مرتين بنفس الخدمة
            $table->unique(['service_id', 'product_id']);
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_materials');
    }
};