<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('label', 50)->default('home');
            $table->string('full_name', 100);
            $table->string('phone', 20);
            $table->string('governorate', 80);
            $table->string('city', 80);
            $table->string('district', 100)->nullable();
            $table->string('street', 200);
            $table->string('building_no', 20)->nullable();
            $table->string('apartment_no', 20)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};