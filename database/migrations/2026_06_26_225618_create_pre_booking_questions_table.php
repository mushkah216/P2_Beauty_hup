<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pre_booking_questions', function (Blueprint $table) {
            $table->id();
            $table->enum('provider_type', ['expert', 'salon', 'beauty_center']);
            $table->unsignedBigInteger('provider_id');
            $table->foreignId('service_id')
                  ->nullable()
                  ->constrained('services')
                  ->cascadeOnDelete();
            $table->text('question_text');
            $table->enum('answer_type', ['yes_no', 'multiple_choice', 'free_text']);
            $table->json('options_json')->nullable();
            $table->boolean('is_required')->default(true);
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pre_booking_questions');
    }
};