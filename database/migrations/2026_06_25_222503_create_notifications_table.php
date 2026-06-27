<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->enum('recipient_type', ['user', 'expert', 'salon', 'beauty_center', 'admin']);
            $table->unsignedBigInteger('recipient_id');
            $table->enum('type', [
                'booking_reminder',
                'booking_confirmed',
                'booking_cancelled',
                'payment_confirmed',
                'refund_issued',
                'new_message',
                'employment_request',
                'stock_alert',
                'birthday_points',
                'new_follower',
                'post_liked',
            ]);
            $table->string('title', 200);
            $table->text('body');
            $table->json('data_json')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};