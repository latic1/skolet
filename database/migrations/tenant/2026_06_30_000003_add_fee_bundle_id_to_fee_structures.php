<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_structures', function (Blueprint $table): void {
            $table->uuid('fee_bundle_id')->nullable()->after('id');
            $table->foreign('fee_bundle_id')->references('id')->on('fee_bundles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('fee_structures', function (Blueprint $table): void {
            $table->dropForeign(['fee_bundle_id']);
            $table->dropColumn('fee_bundle_id');
        });
    }
};
