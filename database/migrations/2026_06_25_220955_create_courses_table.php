<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->enum('provider_type', ['expert', 'salon', 'beauty_center']);
            $table->unsignedBigInteger('provider_id');
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->foreignId('category_id')
                  ->nullable()
                  ->constrained('service_categories')
                  ->nullOnDelete();
            $table->string('cover_image')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('duration_hours', 5, 1)->nullable();
            $table->unsignedSmallInteger('max_enrollments')->nullable();
            $table->unsignedSmallInteger('current_enrollments')->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_online')->default(false);
            $table->string('location')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};