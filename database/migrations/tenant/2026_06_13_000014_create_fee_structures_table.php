<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_structures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('class_id');
            $table->string('term');            // e.g. "Term 1"
            $table->uuid('academic_year_id');
            $table->string('fee_item');        // e.g. "Tuition", "Exam Fee", "PTA Levy"
            $table->decimal('amount', 10, 2);
            $table->date('due_date')->nullable();
            $table->timestamps();

            $table->foreign('class_id')->references('id')->on('school_classes');
            $table->foreign('academic_year_id')->references('id')->on('academic_years');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_structures');
    }
};
