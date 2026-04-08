<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leaves', function (Blueprint $table) {

            // 🔥 1. drop foreign key dulu
            $table->dropForeign(['user_id']);

            // 🔥 2. baru drop column
            $table->dropColumn('user_id');

            // 🔥 3. tambah employee_id
            $table->foreignId('employee_id')
                  ->constrained()
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropColumn('employee_id');

            $table->foreignId('user_id')->constrained();
        });
    }
};