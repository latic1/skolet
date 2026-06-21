<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('admission_no')->unique(); // e.g. "2026/0001"
            $table->uuid('user_id')->nullable();      // linked login account (student/parent)
            $table->uuid('class_id');
            $table->uuid('section_id');
            $table->string('full_name');
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();     // male / female / other
            $table->string('photo_path')->nullable();
            $table->string('guardian_name')->nullable();
            $table->string('guardian_contact')->nullable();
            $table->string('guardian_email')->nullable();
            $table->string('address')->nullable();
            $table->string('status')->default('active'); // active / inactive / graduated
            $table->timestamps();

            $table->foreign('class_id')->references('id')->on('school_classes');
            $table->foreign('section_id')->references('id')->on('sections');
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
