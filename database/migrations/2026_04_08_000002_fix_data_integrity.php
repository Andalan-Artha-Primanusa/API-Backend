<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fix data integrity issues across multiple tables.
     */
    public function up(): void
    {
        // 1. Drop deprecated 'role' column from users table
        if (Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }

        // 2. Add unique constraint on user_profiles.user_id
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->unique('user_id');
        });

        // 3. Add unique constraint on approval_flows.module
        Schema::table('approval_flows', function (Blueprint $table) {
            $table->unique('module');
        });

        // 4. Fix attendances check_in/check_out column types
        Schema::table('attendances', function (Blueprint $table) {
            $table->dateTime('check_in')->nullable()->change();
            $table->dateTime('check_out')->nullable()->change();
        });

        // 5. Upgrade attendances [user_id, date] from index to unique
        // Dropping foreign key first to safely drop the indexed dependency
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'date']);
            $table->unique(['user_id', 'date'], 'attendances_user_id_date_unique');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role')->default('employee')->after('password');
            });
        }

        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
        });

        Schema::table('approval_flows', function (Blueprint $table) {
            $table->dropUnique(['module']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->time('check_in')->nullable()->change();
            $table->time('check_out')->nullable()->change();
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique('attendances_user_id_date_unique');
            $table->index(['user_id', 'date']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
