<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_items', function (Blueprint $table) {
            $table->decimal('ssnit_employee', 15, 2)->default(0)->after('deductions_total');
            $table->decimal('tier2_employee', 15, 2)->default(0)->after('ssnit_employee');
            $table->decimal('paye', 15, 2)->default(0)->after('tier2_employee');
            $table->decimal('ssnit_employer', 15, 2)->default(0)->after('paye');
            $table->decimal('tier2_employer', 15, 2)->default(0)->after('ssnit_employer');
            $table->string('payment_method')->nullable()->after('payment_status');
            $table->timestamp('paid_at')->nullable()->after('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('payroll_items', function (Blueprint $table) {
            $table->dropColumn([
                'ssnit_employee', 'tier2_employee', 'paye',
                'ssnit_employer', 'tier2_employer',
                'payment_method', 'paid_at',
            ]);
        });
    }
};
