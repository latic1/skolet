<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Central\PlatformAnalytics;
use App\Models\Central\SubscriptionPayment;
use App\Models\Central\SubscriptionPlan;
use App\Models\Central\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BuildPlatformAnalyticsCommand extends Command
{
    protected $signature = 'schoolflow:build-platform-analytics';
    protected $description = 'Pre-compute platform analytics metrics and cache in platform_analytics table';

    public function handle(): int
    {
        $tenants = Tenant::all();
        $totalStudents = 0;
        $payrollCount  = 0;
        $apiTokenCount = 0;
        $paystackCount = 0;

        foreach ($tenants as $tenant) {
            try {
                $tenant->run(function () use (&$totalStudents, &$payrollCount, &$apiTokenCount, &$paystackCount): void {
                    $totalStudents += DB::table('students')->count();

                    if (DB::table('payroll_runs')->exists()) {
                        $payrollCount++;
                    }
                    if (DB::table('personal_access_tokens')->exists()) {
                        $apiTokenCount++;
                    }
                    if (DB::table('fee_payments')->whereNotNull('paystack_ref')->exists()) {
                        $paystackCount++;
                    }
                });
            } catch (\Throwable $e) {
                Log::error('[BuildPlatformAnalytics] tenant=' . $tenant->id . ' ' . $e->getMessage());
            }
        }

        $totalSchools = $tenants->count();
        $mrr = (float) SubscriptionPayment::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('amount');
        $avgPerSchool = $totalSchools > 0 ? round($totalStudents / $totalSchools, 1) : 0;

        // KPI snapshot
        $this->upsertMetric('kpi', [
            'total_schools'  => $totalSchools,
            'total_students' => $totalStudents,
            'mrr'            => $mrr,
            'avg_per_school' => $avgPerSchool,
        ]);

        // Monthly new schools — last 12 months
        $last12Start = now()->startOfMonth()->subMonths(11);
        $newSchoolsRaw = Tenant::where('created_at', '>=', $last12Start)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, count(*) as cnt")
            ->groupBy('month')
            ->pluck('cnt', 'month')
            ->toArray();

        $newSchoolsData = [];
        for ($i = 11; $i >= 0; $i--) {
            $m = now()->startOfMonth()->subMonths($i)->format('Y-m');
            $newSchoolsData[] = ['month' => $m, 'count' => (int) ($newSchoolsRaw[$m] ?? 0)];
        }
        $this->upsertMetric('monthly_new_schools', $newSchoolsData);

        // Monthly revenue — last 12 months
        $revenueRaw = SubscriptionPayment::where('created_at', '>=', $last12Start)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(amount) as total")
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $revenueData = [];
        for ($i = 11; $i >= 0; $i--) {
            $m = now()->startOfMonth()->subMonths($i)->format('Y-m');
            $revenueData[] = ['month' => $m, 'total' => (float) ($revenueRaw[$m] ?? 0)];
        }
        $this->upsertMetric('monthly_revenue', $revenueData);

        // Subscription status breakdown
        $statusCounts = SubscriptionPlan::selectRaw('status, count(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status')
            ->toArray();
        $suspendedCount = Tenant::where('status', 'suspended')->count();
        $this->upsertMetric('subscription_status', [
            'trial'     => (int) ($statusCounts['trial']   ?? 0),
            'active'    => (int) ($statusCounts['active']  ?? 0),
            'expired'   => (int) ($statusCounts['expired'] ?? 0),
            'suspended' => (int) $suspendedCount,
        ]);

        // Student count snapshot (monthly history, max 12 months)
        $existing = PlatformAnalytics::where('metric', 'student_snapshot')->first();
        $snapshotMap = collect($existing?->value ?? [])->keyBy('month')->toArray();
        $currentMonth = now()->format('Y-m');
        $snapshotMap[$currentMonth] = ['month' => $currentMonth, 'count' => $totalStudents];
        $snapshot = collect($snapshotMap)->sortKeys()->values()->slice(-12)->values()->toArray();
        $this->upsertMetric('student_snapshot', $snapshot);

        // Feature adoption counts
        $this->upsertMetric('feature_adoption', [
            'payroll'       => $payrollCount,
            'api_tokens'    => $apiTokenCount,
            'paystack'      => $paystackCount,
            'total_tenants' => $totalSchools,
        ]);

        $this->info("Analytics built. Schools: {$totalSchools}, Students: {$totalStudents}, MRR: GHS {$mrr}.");
        return self::SUCCESS;
    }

    private function upsertMetric(string $metric, array $value): void
    {
        PlatformAnalytics::updateOrCreate(
            ['metric' => $metric],
            ['value' => $value, 'computed_at' => now()]
        );
    }
}
