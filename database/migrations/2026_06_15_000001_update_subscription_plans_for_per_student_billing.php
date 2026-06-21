<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            // Drop old plan-based columns
            $table->dropColumn(['plan_name', 'renews_at']);

            // Per-student billing columns
            $table->decimal('rate_per_student', 8, 2)->default(0)->after('tenant_id');
            $table->unsignedInteger('student_count')->default(0)->after('rate_per_student');
            $table->timestamp('student_count_synced_at')->nullable()->after('student_count');
            $table->decimal('amount_due', 10, 2)->default(0)->after('student_count_synced_at');
            $table->string('payment_status')->default('unpaid')->after('amount_due'); // unpaid / paid
            $table->date('cycle_start')->nullable()->after('payment_status');
            $table->date('cycle_end')->nullable()->after('cycle_start');
            // status column already exists: trial / active / expired
        });
    }

    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn([
                'rate_per_student',
                'student_count',
                'student_count_synced_at',
                'amount_due',
                'payment_status',
                'cycle_start',
                'cycle_end',
            ]);

            $table->string('plan_name')->after('tenant_id');
            $table->date('renews_at')->nullable()->after('status');
        });
    }
};
