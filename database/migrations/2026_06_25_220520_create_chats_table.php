<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->enum('p1_type', ['user', 'expert', 'salon', 'beauty_center']);
            $table->unsignedBigInteger('p1_id');
            $table->enum('p2_type', ['user', 'expert', 'salon', 'beauty_center']);
            $table->unsignedBigInteger('p2_id');
            $table->timestamp('last_message_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['p1_type', 'p1_id', 'p2_type', 'p2_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};