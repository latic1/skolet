<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id');
            $table->uuid('fee_structure_id');
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('unpaid'); // unpaid / paid / partial / overdue
            $table->string('payment_method')->nullable(); // cash / paystack
            $table->string('paystack_ref')->nullable();   // null for cash payments
            $table->uuid('recorded_by')->nullable();      // user who recorded cash payment
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('fee_structure_id')->references('id')->on('fee_structures');
            $table->foreign('recorded_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_payments');
    }
};
