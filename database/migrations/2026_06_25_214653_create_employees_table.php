<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->enum('provider_type', ['salon', 'beauty_center']);
            $table->unsignedBigInteger('provider_id');
            $table->foreignId('expert_id')->nullable()->constrained('experts')->nullOnDelete();
            $table->string('full_name', 100);
            $table->string('phone', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('profile_photo')->nullable();
            $table->string('specialization', 150)->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->enum('employment_type', ['permanent', 'temporary'])->default('permanent');
            $table->date('contract_start')->nullable();
            $table->date('contract_end')->nullable();
            $table->boolean('is_available')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};