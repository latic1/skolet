<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('class_id');
            $table->uuid('section_id');
            $table->uuid('subject_id');
            $table->uuid('teacher_id'); // references staff
            $table->string('day');      // Monday … Friday
            $table->integer('period');  // 1, 2, 3 …
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->timestamps();

            $table->unique(['class_id', 'section_id', 'day', 'period']); // one subject per slot
            $table->foreign('class_id')->references('id')->on('school_classes');
            $table->foreign('section_id')->references('id')->on('sections');
            $table->foreign('subject_id')->references('id')->on('subjects');
            $table->foreign('teacher_id')->references('id')->on('staff');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetables');
    }
};
