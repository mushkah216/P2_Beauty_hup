<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_question_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')
                  ->constrained('bookings')
                  ->cascadeOnDelete();
            $table->foreignId('question_id')
                  ->constrained('pre_booking_questions')
                  ->cascadeOnDelete();
            $table->text('answer_text');

            $table->unique(['booking_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_question_answers');
    }
};