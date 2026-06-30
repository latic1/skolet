<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_bundles', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('target_class');       // 'all' or a class uuid
            $table->string('billing_cycle')->default('term'); // 'term' | 'annual'
            $table->uuid('term_id')->nullable();
            $table->uuid('academic_year_id')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamps();

            $table->foreign('term_id')->references('id')->on('terms')->nullOnDelete();
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_bundles');
    }
};
