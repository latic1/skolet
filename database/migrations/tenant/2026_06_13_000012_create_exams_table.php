<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');          // e.g. "Mid-Term Exam", "End of Term 1"
            $table->string('term');          // e.g. "Term 1", "Term 2"
            $table->uuid('academic_year_id');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->default('upcoming'); // upcoming / ongoing / completed / published
            $table->timestamps();

            $table->foreign('academic_year_id')->references('id')->on('academic_years');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
