<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')
                  ->constrained('chats')
                  ->cascadeOnDelete();
            $table->enum('sender_type', ['user', 'expert', 'salon', 'beauty_center']);
            $table->unsignedBigInteger('sender_id');
            $table->enum('message_type', ['text', 'image', 'file'])->default('text');
            $table->text('content')->nullable();
            $table->string('media_url')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};