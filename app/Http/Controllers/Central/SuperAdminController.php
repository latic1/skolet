<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Central\SubscriptionPlan;
use App\Models\Central\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

final class SuperAdminController extends Controller
{
    public function index(): View
    {
        // Auto-expire: mark status=expired for past-cycle_end unpaid tenants on every dashboard load
        SubscriptionPlan::where('status', '!=', 'expired')
            ->where('payment_status', 'unpaid')
            ->whereNotNull('cycle_end')
            ->where('cycle_end', '<', now()->toDateString())
            ->update(['status' => 'expired']);

        $tenants = Tenant::with(['domains', 'subscriptionPlan'])
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'total'            => $tenants->count(),
            'active'           => $tenants->filter(fn ($t) => $t->subscriptionPlan?->status === 'active')->count(),
            'trial'            => $tenants->filter(fn ($t) => $t->subscriptionPlan?->status === 'trial')->count(),
            'total_amount_due' => SubscriptionPlan::where('payment_status', 'unpaid')->sum('amount_due'),
        ];

        return view('central.super-admin.dashboard', compact('tenants', 'stats'));
    }

    public function toggleStatus(Tenant $tenant): RedirectResponse
    {
        try {
            $tenant->update([
                'status' => $tenant->status === 'active' ? 'suspended' : 'active',
            ]);

            return back()->with('success', "Tenant {$tenant->name} status updated.");
        } catch (\Throwable $e) {
            Log::error('[SuperAdminController::toggleStatus] ' . $e->getMessage());

            return back()->with('error', 'Could not update tenant status.');
        }
    }

    public function updateRate(Request $request, Tenant $tenant): RedirectResponse
    {
        $data = $request->validate([
            'rate_per_student' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            $plan = SubscriptionPlan::where('tenant_id', $tenant->id)->firstOrFail();

            $plan->update([
                'rate_per_student' => $data['rate_per_student'],
                'amount_due'       => round((float) $data['rate_per_student'] * $plan->student_count, 2),
            ]);

            return back()->with('success', "Rate updated for {$tenant->name}.");
        } catch (\Throwable $e) {
            Log::error('[SuperAdminController::updateRate] ' . $e->getMessage());

            return back()->with('error', 'Could not update rate.');
        }
    }

    public function markPaid(Request $request, Tenant $tenant): RedirectResponse
    {
        $data = $request->validate([
            'cycle_start' => ['required', 'date'],
            'cycle_end'   => ['required', 'date', 'after:cycle_start'],
        ]);

        try {
            $plan = SubscriptionPlan::where('tenant_id', $tenant->id)->firstOrFail();

            $plan->update([
                'payment_status' => 'paid',
                'status'         => 'active',
                'cycle_start'    => $data['cycle_start'],
                'cycle_end'      => $data['cycle_end'],
            ]);

            // Also ensure the tenant itself is active
            if ($tenant->status !== 'active') {
                $tenant->update(['status' => 'active']);
            }

            return back()->with('success', "{$tenant->name} marked as paid. Cycle updated.");
        } catch (\Throwable $e) {
            Log::error('[SuperAdminController::markPaid] ' . $e->getMessage());

            return back()->with('error', 'Could not mark as paid.');
        }
    }

    public function markUnpaid(Tenant $tenant): RedirectResponse
    {
        try {
            $plan = SubscriptionPlan::where('tenant_id', $tenant->id)->firstOrFail();

            $plan->update(['payment_status' => 'unpaid']);

            return back()->with('success', "{$tenant->name} marked as unpaid.");
        } catch (\Throwable $e) {
            Log::error('[SuperAdminController::markUnpaid] ' . $e->getMessage());

            return back()->with('error', 'Could not update payment status.');
        }
    }

    public function syncStudentCounts(): RedirectResponse
    {
        try {
            Artisan::call('skolet:sync-student-counts');

            return back()->with('success', 'Student counts synced successfully across all schools.');
        } catch (\Throwable $e) {
            Log::error('[SuperAdminController::syncStudentCounts] ' . $e->getMessage());

            return back()->with('error', 'Sync failed: ' . $e->getMessage());
        }
    }

    public function destroyTenant(Tenant $tenant): RedirectResponse
    {
        $name = $tenant->name;

        try {
            // subscription_plans has onDelete('cascade') — deleted automatically
            // TenantDeleted event fires DeleteDatabase — drops the tenant DB
            $tenant->delete();

            return back()->with('success', "School '{$name}' and all its data have been permanently deleted.");
        } catch (\Throwable $e) {
            Log::error('[SuperAdminController::destroyTenant] ' . $e->getMessage());

            return back()->with('error', "Could not delete '{$name}': " . $e->getMessage());
        }
    }
}
