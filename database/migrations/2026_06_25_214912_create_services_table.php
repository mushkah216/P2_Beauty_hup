<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->enum('provider_type', ['expert', 'salon', 'beauty_center']);
            $table->unsignedBigInteger('provider_id');
            $table->foreignId('category_id')->constrained('service_categories')->cascadeOnDelete();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->unsignedSmallInteger('duration_minutes');
            $table->decimal('deposit_percent', 5, 2)->default(20.00);
            $table->unsignedTinyInteger('min_bookings_remote')->nullable();
            $table->unsignedSmallInteger('cancellation_deadline_hrs')->default(24);
            $table->text('instructions')->nullable();
            $table->enum('gender_for', ['male', 'female', 'both'])->default('both');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};