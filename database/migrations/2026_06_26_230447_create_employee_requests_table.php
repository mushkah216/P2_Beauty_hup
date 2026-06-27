<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employment_requests', function (Blueprint $table) {
            $table->id();
            $table->enum('requester_type', ['salon', 'beauty_center']);
            $table->unsignedBigInteger('requester_id');
            $table->foreignId('expert_id')
                  ->constrained('experts')
                  ->cascadeOnDelete();
            $table->enum('employment_type', ['temporary', 'permanent']);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->text('compensation')->nullable();
            $table->text('message')->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employment_requests');
    }
};