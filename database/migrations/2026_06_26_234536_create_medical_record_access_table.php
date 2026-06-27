<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_record_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->enum('provider_type', ['expert', 'salon', 'beauty_center']);
            $table->unsignedBigInteger('provider_id');
            $table->boolean('is_granted')->default(false);
            $table->timestamp('granted_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['user_id', 'provider_type', 'provider_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_record_access');
    }
};