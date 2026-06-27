<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('provider_subscriptions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('plan_id')
          ->constrained('subscription_plans')
          ->cascadeOnDelete();
    $table->enum('provider_type', ['expert', 'salon', 'beauty_center']);
    $table->unsignedBigInteger('provider_id');
    $table->unsignedBigInteger('payment_id')->nullable(); // ← بدون FK
    $table->date('start_date');
    $table->date('end_date');
    $table->date('trial_ends_at')->nullable();
    $table->enum('status', ['trial', 'active', 'expired', 'cancelled'])->default('trial');
    $table->boolean('auto_renew')->default(true);
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_subscriptions');
    }
};
