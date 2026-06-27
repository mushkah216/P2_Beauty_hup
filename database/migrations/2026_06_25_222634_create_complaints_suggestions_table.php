<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaints_suggestions', function (Blueprint $table) {
            $table->id();
            $table->enum('submitted_by_type', ['user', 'expert', 'salon', 'beauty_center']);
            $table->unsignedBigInteger('submitted_by_id');
            $table->enum('target_type', ['platform', 'expert', 'salon', 'beauty_center'])->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->enum('type', ['complaint', 'suggestion'])->default('complaint');
            $table->string('subject', 200);
            $table->text('description');
            $table->string('attachment')->nullable();
            $table->enum('status', ['new', 'under_review', 'resolved', 'closed'])->default('new');
            $table->text('admin_notes')->nullable();
            $table->foreignId('resolved_by')
                  ->nullable()
                  ->constrained('admins')
                  ->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaints_suggestions');
    }
};