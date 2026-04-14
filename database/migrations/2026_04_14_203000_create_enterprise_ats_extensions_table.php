<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interview_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();
            $table->string('interview_type')->default('technical');
            $table->timestamp('scheduled_at');
            $table->unsignedSmallInteger('duration_minutes')->default(60);
            $table->string('mode')->default('online');
            $table->string('location')->nullable();
            $table->string('meeting_link')->nullable();
            $table->string('status')->default('scheduled');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['candidate_id', 'status']);
        });

        Schema::create('interview_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('interview_schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('evaluator_user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('score', 5, 2)->nullable();
            $table->string('recommendation')->nullable();
            $table->text('strengths')->nullable();
            $table->text('concerns')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['candidate_id', 'evaluator_user_id']);
        });

        Schema::create('offer_letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_opening_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('offered_salary', 12, 2)->nullable();
            $table->date('joining_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('status')->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['candidate_id', 'status']);
        });

        Schema::create('background_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->date('requested_at')->nullable();
            $table->date('completed_at')->nullable();
            $table->text('result_notes')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('candidate_id');
        });

        Schema::create('talent_pool_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();
            $table->string('pool_tag')->nullable();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('candidate_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('talent_pool_entries');
        Schema::dropIfExists('background_checks');
        Schema::dropIfExists('offer_letters');
        Schema::dropIfExists('interview_evaluations');
        Schema::dropIfExists('interview_schedules');
    }
};
