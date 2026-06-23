<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_class_history', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUuid('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignUuid('class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignUuid('section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->enum('outcome', ['promoted', 'retained', 'graduated']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_class_history');
    }
};
