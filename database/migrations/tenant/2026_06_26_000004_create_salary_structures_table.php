<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('staff_id')->unique()->constrained('staff')->cascadeOnDelete();
            $table->decimal('gross', 15, 2)->default(0);
            $table->json('allowances')->nullable();
            $table->json('deductions')->nullable();
            $table->date('effective_from')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_structures');
    }
};
