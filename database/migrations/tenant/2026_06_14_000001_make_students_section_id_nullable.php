<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // section_id is nullable because classes with no defined sections
            // are treated as a single implicit group — no section row exists.
            $table->uuid('section_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->uuid('section_id')->nullable(false)->change();
        });
    }
};
