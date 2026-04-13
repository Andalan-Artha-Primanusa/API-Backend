<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_service_request_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hr_service_request_id')->constrained('hr_service_requests')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('message');
            $table->boolean('is_internal')->default(false);
            $table->timestamps();

            $table->index(['hr_service_request_id', 'created_at'], 'hsrc_req_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_service_request_comments');
    }
};