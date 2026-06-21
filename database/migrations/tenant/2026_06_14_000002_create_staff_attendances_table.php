<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('staff_id');
            $table->date('date');
            $table->string('status'); // present / absent / late
            $table->uuid('marked_by');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['staff_id', 'date']);
            $table->foreign('staff_id')->references('id')->on('staff')->onDelete('cascade');
            $table->foreign('marked_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_attendances');
    }
};
