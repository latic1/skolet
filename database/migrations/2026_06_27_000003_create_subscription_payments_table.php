<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'central';

    public function up(): void
    {
        Schema::connection('central')->create('subscription_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->date('cycle_start');
            $table->date('cycle_end');
            $table->string('payment_reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignUuid('recorded_by')->constrained('super_admins')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::connection('central')->dropIfExists('subscription_payments');
    }
};
