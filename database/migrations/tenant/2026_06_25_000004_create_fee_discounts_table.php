<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_discounts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUuid('fee_structure_id')->nullable()->constrained('fee_structures')->cascadeOnDelete();
            $table->enum('discount_type', ['percentage', 'fixed']);
            $table->decimal('discount_value', 10, 2);
            $table->text('reason');
            $table->foreignUuid('approved_by')->constrained('users')->cascadeOnDelete();
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->timestamps();

            $table->index('student_id');
            $table->index(['student_id', 'fee_structure_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_discounts');
    }
};
