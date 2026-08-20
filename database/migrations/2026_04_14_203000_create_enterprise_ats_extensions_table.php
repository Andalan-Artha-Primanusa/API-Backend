<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Recruitment/ATS has been removed from the active backend surface.
        // Keep this historical migration as a no-op so fresh installs do not
        // try to create foreign keys to removed candidates/job_openings tables.
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
