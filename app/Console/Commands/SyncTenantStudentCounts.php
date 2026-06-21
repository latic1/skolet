<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Central\SubscriptionPlan;
use App\Models\Central\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class SyncTenantStudentCounts extends Command
{
    protected $signature = 'schoolflow:sync-student-counts';

    protected $description = 'Sync student counts from each tenant DB into subscription_plans and auto-expire overdue tenants';

    public function handle(): int
    {
        $tenants = Tenant::all();
        $synced  = 0;
        $expired = 0;

        foreach ($tenants as $tenant) {
            try {
                // Count students inside the tenant's own database
                $studentCount = 0;

                $tenant->run(function () use (&$studentCount): void {
                    $studentCount = DB::table('students')->count();
                });

                $plan = SubscriptionPlan::where('tenant_id', $tenant->id)->first();

                if ($plan === null) {
                    $this->warn("No subscription_plan for tenant {$tenant->id} — skipping.");
                    continue;
                }

                $amountDue = round((float) $plan->rate_per_student * $studentCount, 2);

                $plan->update([
                    'student_count'           => $studentCount,
                    'student_count_synced_at' => now(),
                    'amount_due'              => $amountDue,
                ]);

                // Auto-expire: past cycle_end AND still unpaid
                if (
                    $plan->status !== 'expired'
                    && $plan->payment_status === 'unpaid'
                    && $plan->cycle_end !== null
                    && now()->isAfter($plan->cycle_end)
                ) {
                    $plan->update(['status' => 'expired']);
                    $expired++;
                }

                $synced++;
            } catch (\Throwable $e) {
                Log::error('[SyncTenantStudentCounts] tenant ' . $tenant->id . ': ' . $e->getMessage());
                $this->error("Failed for tenant {$tenant->id}: {$e->getMessage()}");
            }
        }

        $this->info("Synced {$synced} tenant(s). Auto-expired: {$expired}.");

        return self::SUCCESS;
    }
}
