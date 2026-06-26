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
            $table->string('currency_code', 3)->default('GHS')->after('admissions_open');
            $table->string('currency_symbol', 5)->default('₵')->after('currency_code');
        });
    }

    public function down(): void
    {
        Schema::table('school_profile', function (Blueprint $table): void {
            $table->dropColumn(['currency_code', 'currency_symbol']);
        });
    }
};
