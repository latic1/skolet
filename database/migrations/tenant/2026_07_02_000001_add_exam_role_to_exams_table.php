<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            // 'ca' = counts toward the Continuous Assessment bucket; 'end_of_term' = the
            // single exam per term that CA is blended against for the final weighted grade.
            $table->enum('exam_role', ['none', 'ca', 'end_of_term'])->default('none')->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn('exam_role');
        });
    }
};
