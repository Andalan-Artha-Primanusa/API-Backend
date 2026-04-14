<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('okrs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('period_id')->nullable()->constrained('review_cycles')->onDelete('set null');
            $table->string('objective', 500)->comment('Main OKR objective statement');
            $table->text('description')->nullable()->comment('Detailed description of OKR');
            $table->integer('weight')->default(100)->comment('Weight percentage for scoring');
            $table->enum('status', ['draft', 'submitted', 'approved', 'in_progress', 'completed', 'cancelled'])->default('draft');
            $table->decimal('target_value', 10, 2)->nullable()->comment('Target numeric value if applicable');
            $table->decimal('current_value', 10, 2)->nullable()->comment('Current progress value');
            $table->enum('unit', ['count', 'percentage', 'amount', 'hours', 'items', 'yes/no'])->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['employee_id', 'period_id']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('okrs');
    }
};
