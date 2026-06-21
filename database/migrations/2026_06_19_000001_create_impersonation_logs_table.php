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
        Schema::connection('central')->create('impersonation_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('super_admin_id');
            $table->uuid('tenant_id');
            $table->uuid('impersonated_user_id');
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();

            $table->foreign('super_admin_id')
                ->references('id')->on('super_admins')
                ->onDelete('cascade');

            $table->foreign('tenant_id')
                ->references('id')->on('tenants')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::connection('central')->dropIfExists('impersonation_logs');
    }
};
