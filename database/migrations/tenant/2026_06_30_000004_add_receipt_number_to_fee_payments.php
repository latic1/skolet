<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_payments', function (Blueprint $table): void {
            $table->string('receipt_number')->nullable()->after('id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('fee_payments', function (Blueprint $table): void {
            $table->dropIndex(['receipt_number']);
            $table->dropColumn('receipt_number');
        });
    }
};
