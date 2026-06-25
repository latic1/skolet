<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('category_id')->constrained('expense_categories')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->date('date');
            $table->string('description');
            $table->string('receipt_path')->nullable();
            $table->foreignUuid('recorded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['date', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
