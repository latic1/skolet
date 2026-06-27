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
        Schema::connection('central')->create('platform_analytics', function (Blueprint $table) {
            $table->id();
            $table->string('metric')->unique();
            $table->json('value');
            $table->timestamp('computed_at');
        });
    }

    public function down(): void
    {
        Schema::connection('central')->dropIfExists('platform_analytics');
    }
};
