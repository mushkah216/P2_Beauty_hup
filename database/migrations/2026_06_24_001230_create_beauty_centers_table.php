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
        Schema::create('beauty_centers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone');
            $table->string('profile_photo')->nullable();
            $table->string('cover_photo')->nullable();
            $table->text('description')->nullable();
            $table->string('governorate');
            $table->string('city');
            $table->string('address_detail')->nullable();
            $table->decimal('location_lat',10,7)->nullable();
            $table->decimal('location_lng',10,7)->nullable();
            $table->text('service_types')->nullable();
            $table->string('commercial_license_no')->nullable();
            $table->string('medical_license_doc');
            $table->enum('gender_served',['male','female','both'])->default('both');
            $table->decimal('rating_avg',3,2)->default(0);
            $table->unsignedInteger('rating_count')->default(0);
            $table->unsignedInteger('followers_count')->default(0);
            $table->enum('account_status',['pending','active','suspended','deleted','rejected'])->default('pending');
            $table->date('subscription_expires_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beauty_centers');
    }
};
