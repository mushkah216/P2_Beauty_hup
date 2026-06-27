<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_requests', function (Blueprint $table) {
            $table->id();
            $table->enum('entity_type', ['expert', 'salon', 'beauty_center']);
            $table->string('applicant_name', 120);
            $table->string('email', 150);
            $table->string('phone', 20);
            $table->string('governorate', 80)->nullable();
            $table->text('service_types')->nullable();
            $table->string('commercial_license_doc')->nullable();
            $table->string('medical_license_doc')->nullable();
            $table->json('extra_docs_json')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')
                  ->nullable()
                  ->constrained('admins')
                  ->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_requests');
    }
};