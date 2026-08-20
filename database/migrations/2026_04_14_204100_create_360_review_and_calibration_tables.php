<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calibration_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Calibration session name');
            $table->text('description')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->foreignId('facilitator_id')->constrained('users')->onDelete('restrict');
            $table->integer('participants_count')->default(0);
            $table->timestamps();

            $table->index(['status']);
        });

        Schema::create('calibration_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calibration_session_id')->constrained('calibration_sessions')->onDelete('cascade');
            $table->foreignId('manager_id')->constrained('users')->onDelete('cascade');
            $table->enum('role', ['facilitator', 'participant', 'observer'])->default('participant');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['calibration_session_id', 'manager_id'], 'calibration_session_manager_unique');
        });

        Schema::create('calibration_employee_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calibration_session_id')->constrained('calibration_sessions')->onDelete('cascade');
            $table->foreignId('performance_review_id')->constrained('performance_reviews')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->decimal('initial_score', 5, 2)->nullable()->comment('Score before calibration');
            $table->decimal('calibrated_score', 5, 2)->nullable()->comment('Score after calibration');
            $table->text('discussion_notes')->nullable();
            $table->enum('rating_category', ['exceeds', 'meets', 'developing', 'needs_improvement'])->nullable();
            $table->boolean('aligned')->default(false)->comment('Whether score is aligned across managers');
            $table->timestamps();

            $table->index(['calibration_session_id']);
            $table->unique(['calibration_session_id', 'performance_review_id'], 'calibration_perf_review_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calibration_employee_reviews');
        Schema::dropIfExists('calibration_participants');
        Schema::dropIfExists('calibration_sessions');
    }
};
