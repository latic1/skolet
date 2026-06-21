<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id');
            $table->date('date');
            $table->string('status');  // present / absent / late
            $table->uuid('marked_by'); // references users (teacher/admin)
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'date']); // one record per student per day
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('marked_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
