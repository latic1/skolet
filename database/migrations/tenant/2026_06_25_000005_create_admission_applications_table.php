<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_applications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('applicant_name');
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('class_applying_for');
            $table->string('guardian_name');
            $table->string('guardian_contact');
            $table->string('guardian_email')->nullable();
            $table->string('previous_school')->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->foreignUuid('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index(['status', 'class_applying_for']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_applications');
    }
};
