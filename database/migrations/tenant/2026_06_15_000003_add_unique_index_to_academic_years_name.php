<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Deduplicate any existing conflicts before adding the index.
        $duplicates = \DB::table('academic_years')
            ->selectRaw('name, MIN(id) as keep_id')
            ->groupBy('name')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $dup) {
            \DB::table('academic_years')
                ->where('name', $dup->name)
                ->where('id', '!=', $dup->keep_id)
                ->update(['name' => \DB::raw("CONCAT(name, ' (duplicate)')")]);
        }

        Schema::table('academic_years', function (Blueprint $table) {
            $table->unique('name', 'academic_years_name_unique');
        });
    }

    public function down(): void
    {
        Schema::table('academic_years', function (Blueprint $table) {
            $table->dropUnique('academic_years_name_unique');
        });
    }
};
