<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->uuid('term_id')->nullable()->after('id');
            $table->boolean('is_published')->default(false)->after('end_date');
        });

        DB::table('exams')->where('status', 'published')->update(['is_published' => true]);

        Schema::table('exams', function (Blueprint $table) {
            $table->foreign('term_id')->references('id')->on('terms')->onDelete('set null');
        });

        Schema::table('exams', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->dropColumn(['term', 'academic_year_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->string('term')->nullable();
            $table->uuid('academic_year_id')->nullable();
            $table->string('status')->default('upcoming');
        });

        Schema::table('exams', function (Blueprint $table) {
            $table->dropForeign(['term_id']);
            $table->dropColumn(['term_id', 'is_published']);
        });
    }
};
