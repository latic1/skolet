<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_structures', function (Blueprint $table): void {
            $table->string('billing_cycle')->default('term')->after('term_id');
            $table->uuid('academic_year_id')->nullable()->after('billing_cycle');
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('fee_structures', function (Blueprint $table): void {
            $table->dropForeign(['academic_year_id']);
            $table->dropColumn(['billing_cycle', 'academic_year_id']);
        });
    }
};
