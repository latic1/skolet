<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            $table->string('target_class')->default('all')->after('term_id');
            $table->boolean('is_mandatory')->default(true)->after('target_class');
        });

        // Migrate existing class_id values into target_class
        \DB::table('fee_structures')
            ->whereNotNull('class_id')
            ->update(['target_class' => \DB::raw('class_id')]);

        Schema::table('fee_structures', function (Blueprint $table) {
            $table->dropForeign(['class_id']);
            $table->dropColumn('class_id');
        });
    }

    public function down(): void
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            $table->uuid('class_id')->nullable()->after('term_id');
        });

        Schema::table('fee_structures', function (Blueprint $table) {
            $table->dropColumn(['target_class', 'is_mandatory']);
        });
    }
};
