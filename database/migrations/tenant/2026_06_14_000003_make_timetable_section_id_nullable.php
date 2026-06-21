<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // section_id is nullable for classes that have no sections defined.
        // MySQL allows NULL in FK columns — the constraint still applies for non-null values.
        DB::statement('ALTER TABLE timetables MODIFY COLUMN section_id CHAR(36) NULL');
    }

    public function down(): void
    {
        // Reverting requires all existing rows to have a non-null section_id — skip for safety.
    }
};
