<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_profile', function (Blueprint $table): void {
            $table->boolean('admissions_open')->default(false)->after('notification_settings');
        });
    }

    public function down(): void
    {
        Schema::table('school_profile', function (Blueprint $table): void {
            $table->dropColumn('admissions_open');
        });
    }
};
