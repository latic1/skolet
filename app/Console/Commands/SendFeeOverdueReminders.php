<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Central\Tenant;
use App\Models\Tenant\FeePayment;
use App\Models\Tenant\FeeStructure;
use App\Models\Tenant\SchoolProfile;
use App\Models\Tenant\Student;
use App\Notifications\FeeOverdueReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

final class SendFeeOverdueReminders extends Command
{
    protected $signature = 'skolet:send-fee-overdue-reminders';

    protected $description = 'Send overdue fee reminder emails to guardians (runs weekly)';

    public function handle(): int
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            try {
                $tenant->run(function () use ($tenant): void {
                    $profile = SchoolProfile::first();

                    if (! ($profile?->isNotificationEnabled('fee_overdue_reminder'))) {
                        return;
                    }

                    $overdueFeeStructures = FeeStructure::whereNotNull('due_date')
                        ->where('due_date', '<', now()->toDateString())
                        ->get();

                    foreach ($overdueFeeStructures as $fs) {
                        $paidSums = FeePayment::where('fee_structure_id', $fs->id)
                            ->selectRaw('student_id, SUM(amount) as total_paid')
                            ->groupBy('student_id')
                            ->pluck('total_paid', 'student_id');

                        $students = Student::where('status', 'active')
                            ->where(function ($q) use ($fs): void {
                                $q->where('target_class', 'all')
                                  ->orWhereRaw('class_id = ?', [$fs->target_class]);
                            })
                            ->whereNotNull('guardian_email')
                            ->get();

                        foreach ($students as $student) {
                            $paid        = (float) ($paidSums[$student->id] ?? 0);
                            $outstanding = (float) $fs->amount - $paid;

                            if ($outstanding > 0) {
                                Notification::route('mail', $student->guardian_email)
                                    ->notify(new FeeOverdueReminder($student, $fs, $outstanding));
                            }
                        }
                    }
                });
            } catch (\Throwable $e) {
                Log::error('[SendFeeOverdueReminders] Tenant ' . $tenant->id . ': ' . $e->getMessage());
            }
        }

        $this->info('Fee overdue reminders dispatched.');

        return self::SUCCESS;
    }
}
