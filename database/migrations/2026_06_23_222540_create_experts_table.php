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
        Schema::create('experts', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone');
            $table->string('profile_photo')->nullable();
            $table->string('cover_photo')->nullable();
            $table->text('bio')->nullable();
            $table->string('specialization');
            $table->unsignedInteger('experience_years')->default(0);
            $table->string('governorate')->nullable();
            $table->string('city')->nullable();
            $table->decimal('location_lat',10,7)->nullable();
            $table->decimal('location_lng',10,7)->nullable();
            $table->decimal('service_area_km',6,2)->nullable();
            $table->boolean('is_available_for_hire')->default(true);
            $table->decimal('rating_avg',3,2)->default(0);
            $table->unsignedInteger('rating_count')->default(0);
            $table->unsignedInteger('followers_count')->default(0);
            $table->enum('account_status',['pending','active','suspended','rejected','deleted'])->default('pending');
            $table->date('subscription_expires_at')->nullable();
            $table->date('birth_date')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('experts');
    }
};
