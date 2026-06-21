<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('body');
            $table->uuid('posted_by');
            $table->boolean('is_public')->default(false); // shown on the public school page
            $table->timestamps();

            $table->foreign('posted_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
