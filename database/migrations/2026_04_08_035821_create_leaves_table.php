<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leaves', function (Blueprint $table) {

            // 🔥 TAMBAHAN FITUR HRIS
            $table->integer('total_days')->default(1)->after('end_date');

            $table->enum('type', ['annual', 'sick', 'unpaid'])
                  ->default('annual')
                  ->after('total_days');

            // 🔥 APPROVAL INFO
            $table->foreignId('approved_by')
                  ->nullable()
                  ->after('status')
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamp('approved_at')
                  ->nullable()
                  ->after('approved_by');

            $table->text('approval_note')
                  ->nullable()
                  ->after('approved_at');

            // 🔥 INDEX (biar cepat)
            $table->index('user_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('leaves', function (Blueprint $table) {

            $table->dropColumn([
                'total_days',
                'type',
                'approved_by',
                'approved_at',
                'approval_note'
            ]);

            $table->dropIndex(['user_id']);
            $table->dropIndex(['status']);
        });
    }
};