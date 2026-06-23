<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_profile', function (Blueprint $table) {
            $table->boolean('onboarding_completed')->default(false)->after('grading_scale');
            $table->tinyInteger('onboarding_step')->default(1)->after('onboarding_completed');
        });
    }

    public function down(): void
    {
        Schema::table('school_profile', function (Blueprint $table) {
            $table->dropColumn(['onboarding_completed', 'onboarding_step']);
        });
    }
};
