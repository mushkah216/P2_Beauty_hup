<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->enum('provider_type', ['expert', 'salon', 'beauty_center']);
            $table->unsignedBigInteger('provider_id');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['user_id', 'provider_type', 'provider_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_favorites');
    }
};