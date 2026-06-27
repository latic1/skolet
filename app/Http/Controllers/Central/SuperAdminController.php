<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Jobs\SendBroadcastJob;
use App\Models\Central\Broadcast;
use App\Models\Central\ImpersonationLog;
use App\Models\Central\PlatformAnalytics;
use App\Models\Central\SubscriptionPayment;
use App\Models\Central\SubscriptionPlan;
use App\Models\Central\Tenant;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

        $metrics = PlatformAnalytics::get()->keyBy('metric');
        $analyticsData = [
            'kpi'            => $metrics->get('kpi')?->value ?? [],
            'new_schools'    => $metrics->get('monthly_new_schools')?->value ?? [],
            'revenue'        => $metrics->get('monthly_revenue')?->value ?? [],
            'status'         => $metrics->get('subscription_status')?->value ?? [],
            'student_history'=> $metrics->get('student_snapshot')?->value ?? [],
            'adoption'       => $metrics->get('feature_adoption')?->value ?? [],
            'computed_at'    => $metrics->first()?->computed_at,
        ];

        return view('central.super-admin.dashboard', compact('tenants', 'stats', 'analyticsData'));
    }

    public function rebuildAnalytics(): RedirectResponse
    {
        try {
            Artisan::call('schoolflow:build-platform-analytics');
            return redirect()->route('super-admin.dashboard', ['tab' => 'analytics'])
                ->with('success', 'Platform analytics rebuilt successfully.');
        } catch (\Throwable $e) {
            Log::error('[SuperAdminController::rebuildAnalytics] ' . $e->getMessage());
            return redirect()->route('super-admin.dashboard', ['tab' => 'analytics'])
                ->with('error', 'Could not rebuild analytics.');
        }
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
            'cycle_start'       => ['required', 'date'],
            'cycle_end'         => ['required', 'date', 'after:cycle_start'],
            'payment_reference' => ['nullable', 'string', 'max:255'],
            'notes'             => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $plan = SubscriptionPlan::where('tenant_id', $tenant->id)->firstOrFail();

            $plan->update([
                'payment_status' => 'paid',
                'status'         => 'active',
                'cycle_start'    => $data['cycle_start'],
                'cycle_end'      => $data['cycle_end'],
            ]);

            SubscriptionPayment::create([
                'tenant_id'         => $tenant->id,
                'amount'            => $plan->amount_due,
                'cycle_start'       => $data['cycle_start'],
                'cycle_end'         => $data['cycle_end'],
                'payment_reference' => $data['payment_reference'] ?? null,
                'notes'             => $data['notes'] ?? null,
                'recorded_by'       => Auth::guard('super_admin')->id(),
                'created_at'        => now(),
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

    public function tenantDetail(Tenant $tenant): View
    {
        $plan     = SubscriptionPlan::where('tenant_id', $tenant->id)->first();
        $payments = SubscriptionPayment::on('central')
            ->with('recordedBy')
            ->where('tenant_id', $tenant->id)
            ->orderByDesc('created_at')
            ->get();

        return view('central.super-admin.tenant-detail', compact('tenant', 'plan', 'payments'));
    }

    public function downloadInvoice(Tenant $tenant, SubscriptionPayment $payment): Response
    {
        abort_if($payment->tenant_id !== $tenant->id, 404);

        $payment->load('recordedBy');

        $pdf = Pdf::loadView('central.super-admin.invoice-pdf', compact('tenant', 'payment'))
            ->setPaper('a4', 'portrait');

        $filename = 'invoice-' . $tenant->subdomain . '-' . $payment->created_at->format('Y-m-d') . '.pdf';

        return response($pdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
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

    public function broadcasts(): View
    {
        $broadcasts = Broadcast::on('central')
            ->with('sentBy')
            ->orderByDesc('created_at')
            ->get();

        return view('central.super-admin.broadcasts', compact('broadcasts'));
    }

    public function storeBroadcast(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject'  => ['required', 'string', 'max:255'],
            'message'  => ['required', 'string', 'max:5000'],
            'severity' => ['required', 'in:info,warning,critical'],
            'send_at'  => ['nullable', 'date', 'after_or_equal:now'],
        ]);

        try {
            $broadcast = Broadcast::on('central')->create([
                'id'       => (string) Str::uuid(),
                'subject'  => $data['subject'],
                'message'  => $data['message'],
                'severity' => $data['severity'],
                'send_at'  => $data['send_at'] ?? null,
                'sent_at'  => null,
                'sent_by'  => Auth::guard('super_admin')->id(),
            ]);

            // Send immediately if no schedule date, otherwise queue for later
            if (empty($data['send_at'])) {
                SendBroadcastJob::dispatch($broadcast->id);
            } else {
                SendBroadcastJob::dispatch($broadcast->id)->delay(now()->parse($data['send_at']));
            }

            return redirect(route('super-admin.broadcasts'))->with('success', 'Broadcast queued for delivery.');
        } catch (\Throwable $e) {
            Log::error('[SuperAdminController::storeBroadcast] ' . $e->getMessage());

            return back()->with('error', 'Could not send broadcast: ' . $e->getMessage());
        }
    }

    public function auditLog(Request $request): View
    {
        $logs = ImpersonationLog::on('central')
            ->with(['superAdmin', 'tenant'])
            ->when($request->filled('date_from'), fn ($q) => $q->where('started_at', '>=', $request->date_from . ' 00:00:00'))
            ->when($request->filled('date_to'), fn ($q) => $q->where('started_at', '<=', $request->date_to . ' 23:59:59'))
            ->when($request->filled('search'), fn ($q) => $q->whereHas('tenant', fn ($tq) => $tq->where('name', 'like', '%' . $request->search . '%')))
            ->orderByDesc('started_at')
            ->paginate(25)
            ->withQueryString();

        return view('central.super-admin.audit-log', compact('logs'));
    }

    public function exportAuditLog(Request $request): StreamedResponse
    {
        $logs = ImpersonationLog::on('central')
            ->with(['superAdmin', 'tenant'])
            ->when($request->filled('date_from'), fn ($q) => $q->where('started_at', '>=', $request->date_from . ' 00:00:00'))
            ->when($request->filled('date_to'), fn ($q) => $q->where('started_at', '<=', $request->date_to . ' 23:59:59'))
            ->when($request->filled('search'), fn ($q) => $q->whereHas('tenant', fn ($tq) => $tq->where('name', 'like', '%' . $request->search . '%')))
            ->orderByDesc('started_at')
            ->get();

        $filename = 'impersonation-audit-log-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($logs) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', 'Super Admin', 'School', 'Started At', 'Ended At', 'Duration', 'Status']);

            foreach ($logs as $log) {
                $status   = $this->resolveSessionStatus($log);
                $duration = $log->ended_at
                    ? $log->started_at->diffInMinutes($log->ended_at) . ' min'
                    : ($log->started_at->lt(now()->subHour()) ? 'Timed out' : 'Active');

                fputcsv($out, [
                    $log->started_at->format('d M Y'),
                    $log->superAdmin?->name ?? '—',
                    $log->tenant?->name ?? '—',
                    $log->started_at->format('d M Y H:i:s'),
                    $log->ended_at?->format('d M Y H:i:s') ?? '—',
                    $duration,
                    $status,
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function resolveSessionStatus(ImpersonationLog $log): string
    {
        if ($log->ended_at) {
            return 'Normal exit';
        }

        return $log->started_at->lt(now()->subHour()) ? 'Timed out' : 'Active';
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
