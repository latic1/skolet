<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            $table->uuid('term_id')->nullable()->after('class_id');
        });

        Schema::table('fee_structures', function (Blueprint $table) {
            $table->foreign('term_id')->references('id')->on('terms')->onDelete('set null');
        });

        Schema::table('fee_structures', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->dropColumn(['term', 'academic_year_id']);
        });
    }

    public function down(): void
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            $table->string('term')->nullable();
            $table->uuid('academic_year_id')->nullable();
        });

        Schema::table('fee_structures', function (Blueprint $table) {
            $table->dropForeign(['term_id']);
            $table->dropColumn('term_id');
        });
    }
};
