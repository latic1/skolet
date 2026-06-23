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
            $table->json('grading_scale')->nullable()->after('admission_counter');
        });
    }

    public function down(): void
    {
        Schema::table('school_profile', function (Blueprint $table) {
            $table->dropColumn('grading_scale');
        });
    }
};
