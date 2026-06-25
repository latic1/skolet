<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disciplinary_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id');
            $table->uuid('reported_by');
            $table->enum('incident_type', ['warning', 'detention', 'suspension', 'expulsion', 'commendation']);
            $table->text('description');
            $table->text('action_taken')->nullable();
            $table->date('date');
            $table->boolean('parent_notified')->default(false);
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
            $table->foreign('reported_by')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['student_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disciplinary_records');
    }
};
