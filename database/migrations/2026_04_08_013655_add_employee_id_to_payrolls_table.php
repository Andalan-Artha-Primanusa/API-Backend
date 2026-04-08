<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up()
    {
        Schema::table('payrolls', function (Blueprint $table) {

            // 🔥 kalau tabel sudah ada data, pakai nullable dulu
            if (!Schema::hasColumn('payrolls', 'employee_id')) {
                $table->foreignId('employee_id')
                      ->after('id')
                      ->constrained()
                      ->cascadeOnDelete();
            }

        });
    }

    public function down()
    {
        Schema::table('payrolls', function (Blueprint $table) {

            // 🔥 versi paling aman (Laravel 9+)
            if (Schema::hasColumn('payrolls', 'employee_id')) {
                $table->dropConstrainedForeignId('employee_id');
            }

        });
    }
};