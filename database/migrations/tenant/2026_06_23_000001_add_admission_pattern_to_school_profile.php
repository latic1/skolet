<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_profile', function (Blueprint $table): void {
            $table->string('admission_pattern')->default('{YEAR}/{SEQ:4}')->after('period_system');
            $table->unsignedInteger('admission_counter')->default(0)->after('admission_pattern');
        });

        // Seed counter from existing student count so existing admission numbers aren't re-used
        $count = DB::table('students')->count();
        if ($count > 0) {
            DB::table('school_profile')->update(['admission_counter' => $count]);
        }
    }

    public function down(): void
    {
        Schema::table('school_profile', function (Blueprint $table): void {
            $table->dropColumn(['admission_pattern', 'admission_counter']);
        });
    }
};
