<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_profile', function (Blueprint $table) {
            $table->string('period_system')->default('3_term')->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('school_profile', function (Blueprint $table) {
            $table->dropColumn('period_system');
        });
    }
};
