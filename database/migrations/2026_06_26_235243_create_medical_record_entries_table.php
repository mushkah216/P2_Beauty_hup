<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_record_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')
                  ->constrained('medical_records')
                  ->cascadeOnDelete();
            $table->foreignId('booking_id')
                  ->nullable()
                  ->constrained('bookings')
                  ->nullOnDelete();
            $table->string('service_name', 150);
            $table->enum('performed_by_type', ['expert', 'employee']);
            $table->unsignedBigInteger('performed_by_id');
            $table->date('date_performed');
            $table->text('materials_used')->nullable();
            $table->text('results_notes')->nullable();
            $table->text('provider_notes')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_record_entries');
    }
};