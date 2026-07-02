<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_profile', function (Blueprint $table) {
            $table->unsignedTinyInteger('ca_weight')->default(40);
            $table->unsignedTinyInteger('exam_weight')->default(60);
        });
    }

    public function down(): void
    {
        Schema::table('school_profile', function (Blueprint $table) {
            $table->dropColumn(['ca_weight', 'exam_weight']);
        });
    }
};
