<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add performance indexes to frequently queried columns.
     */
    public function up(): void
    {
        // Attendance: daily check-in lookup
        Schema::table('attendances', function (Blueprint $table) {
            $table->index(['user_id', 'date']);
        });

        // Leaves: filtered queries by user and status
        Schema::table('leaves', function (Blueprint $table) {
            $table->index(['user_id', 'status']);
        });

        // Approval steps: prevent duplicate step orders per flow
        Schema::table('approval_steps', function (Blueprint $table) {
            $table->unique(['approval_flow_id', 'step_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'date']);
        });

        Schema::table('leaves', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'status']);
        });

        Schema::table('approval_steps', function (Blueprint $table) {
            $table->dropUnique(['approval_flow_id', 'step_order']);
        });
    }
};
