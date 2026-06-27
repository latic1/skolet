<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Central\SubscriptionPayment;
use App\Models\Central\SubscriptionPlan;
use App\Models\Central\Tenant as CentralTenant;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\View\View;

final class BillingController extends Controller
{
    public function index(): View
    {
        $tenantId = tenant('id');

        $plan = SubscriptionPlan::on('central')
            ->where('tenant_id', $tenantId)
            ->first();

        $payments = SubscriptionPayment::on('central')
            ->with('recordedBy')
            ->where('tenant_id', $tenantId)
            ->orderByDesc('created_at')
            ->get();

        return view('tenant.settings.billing', compact('plan', 'payments'));
    }

    public function downloadInvoice(string $paymentId): Response
    {
        $tenantId = tenant('id');

        $payment = SubscriptionPayment::on('central')
            ->with('recordedBy')
            ->where('id', $paymentId)
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        $tenant = CentralTenant::on('central')->find($tenantId);

        $pdf = Pdf::loadView('central.super-admin.invoice-pdf', compact('tenant', 'payment'))
            ->setPaper('a4', 'portrait');

        $filename = 'invoice-' . $payment->created_at->format('Y-m-d') . '-' . substr($payment->id, 0, 8) . '.pdf';

        return response($pdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
