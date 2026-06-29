<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Central\SubscriptionPlan;
use Illuminate\Support\Facades\DB;

final class SyncTenantStudentCount
{
    public static function run(): void
    {
        $tenantId = tenant('id');
        if (! $tenantId) {
            return;
        }

        $count = DB::table('students')
            ->whereNull('deleted_at')
            ->where(fn ($q) => $q->where('status', 'active')->orWhereNull('status'))
            ->count();

        $plan = SubscriptionPlan::on('central')
            ->where('tenant_id', $tenantId)
            ->first();

        if (! $plan) {
            return;
        }

        $plan->update([
            'student_count'            => $count,
            'student_count_synced_at'  => now(),
            'amount_due'               => round((float) $plan->rate_per_student * $count, 2),
        ]);
    }
}
