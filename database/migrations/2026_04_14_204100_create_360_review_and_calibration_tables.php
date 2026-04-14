<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('review_360s', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cycle_id')->constrained('review_cycles')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('manager_id')->constrained('users')->onDelete('restrict');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'reviewed', 'approved'])->default('pending');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('feeders_required')->default(3)->comment('Number of feeders needed');
            $table->integer('feeders_received')->default(0)->comment('Number of feeders responded');
            $table->text('self_assessment')->nullable();
            $table->text('manager_notes')->nullable();
            $table->array('manager_competency_ratings')->nullable(); // JSON: {competency_id: score}
            $table->decimal('overall_score', 5, 2)->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['cycle_id', 'employee_id']);
            $table->index(['status']);
        });

        Schema::create('review_360_feeders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_360_id')->constrained('review_360s')->onDelete('cascade');
            $table->foreignId('feeder_id')->constrained('users')->onDelete('cascade');
            $table->enum('feeder_type', ['peer', 'subordinate', 'manager', 'cross_functional'])->default('peer');
            $table->enum('status', ['pending', 'submitted', 'read'])->default('pending');
            $table->text('feedback')->nullable();
            $table->array('competency_ratings')->nullable(); // JSON
            $table->integer('rating')->nullable()->comment('Overall rating 1-5');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['review_360_id', 'feeder_id']);
            $table->unique(['review_360_id', 'feeder_id']);
        });

        Schema::create('calibration_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cycle_id')->constrained('review_cycles')->onDelete('cascade');
            $table->string('name')->comment('Calibration session name');
            $table->text('description')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->foreignId('facilitator_id')->constrained('users')->onDelete('restrict');
            $table->integer('participants_count')->default(0);
            $table->timestamps();

            $table->index(['cycle_id']);
            $table->index(['status']);
        });

        Schema::create('calibration_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calibration_session_id')->constrained('calibration_sessions')->onDelete('cascade');
            $table->foreignId('manager_id')->constrained('users')->onDelete('cascade');
            $table->enum('role', ['facilitator', 'participant', 'observer'])->default('participant');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['calibration_session_id', 'manager_id']);
        });

        Schema::create('calibration_employee_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calibration_session_id')->constrained('calibration_sessions')->onDelete('cascade');
            $table->foreignId('review_360_id')->constrained('review_360s')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->decimal('initial_score', 5, 2)->nullable()->comment('Score before calibration');
            $table->decimal('calibrated_score', 5, 2)->nullable()->comment('Score after calibration');
            $table->text('discussion_notes')->nullable();
            $table->enum('rating_category', ['exceeds', 'meets', 'developing', 'needs_improvement'])->nullable();
            $table->boolean('aligned')->default(false)->comment('Whether score is aligned across managers');
            $table->timestamps();

            $table->index(['calibration_session_id']);
            $table->unique(['calibration_session_id', 'review_360_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calibration_employee_reviews');
        Schema::dropIfExists('calibration_participants');
        Schema::dropIfExists('calibration_sessions');
        Schema::dropIfExists('review_360_feeders');
        Schema::dropIfExists('review_360s');
    }
};
