<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\PayrollItem;
use App\Models\Tenant\PayrollRun;
use App\Models\Tenant\SalaryStructure;
use App\Models\Tenant\SchoolProfile;
use App\Models\Tenant\Staff;
use App\Services\PayrollService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

final class PayrollController extends Controller
{
    public function __construct(private readonly PayrollService $payrollService) {}

    public function index(): View
    {
        $staffWithStructures = Staff::where('status', 'active')
            ->with('salaryStructure')
            ->orderBy('full_name')
            ->get();

        $runs = PayrollRun::with(['processedBy', 'items.staff'])
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->paginate(12);

        return view('tenant.payroll.index', compact('staffWithStructures', 'runs'));
    }

    public function updateSalaryStructure(Request $request, Staff $staff): RedirectResponse
    {
        $data = $request->validate([
            'gross'                => ['required', 'numeric', 'min:0'],
            'effective_from'       => ['nullable', 'date'],
            'allowances'           => ['nullable', 'array'],
            'allowances.housing'   => ['nullable', 'numeric', 'min:0'],
            'allowances.transport' => ['nullable', 'numeric', 'min:0'],
            'allowances.medical'   => ['nullable', 'numeric', 'min:0'],
            'allowances.other'     => ['nullable', 'numeric', 'min:0'],
            'deductions'           => ['nullable', 'array'],
            'deductions.tax'       => ['nullable', 'numeric', 'min:0'],
            'deductions.pension'   => ['nullable', 'numeric', 'min:0'],
            'deductions.loan'      => ['nullable', 'numeric', 'min:0'],
            'deductions.other'     => ['nullable', 'numeric', 'min:0'],
        ]);

        $defaultAllowances = ['housing' => 0, 'transport' => 0, 'medical' => 0, 'other' => 0];
        $defaultDeductions = ['tax' => 0, 'pension' => 0, 'loan' => 0, 'other' => 0];

        SalaryStructure::updateOrCreate(
            ['staff_id' => $staff->id],
            [
                'gross'          => $data['gross'],
                'effective_from' => $data['effective_from'] ?? null,
                'allowances'     => array_map('floatval', array_merge($defaultAllowances, $data['allowances'] ?? [])),
                'deductions'     => array_map('floatval', array_merge($defaultDeductions, $data['deductions'] ?? [])),
            ]
        );

        return redirect(request()->getSchemeAndHttpHost() . '/payroll')
            ->with('success', "Salary structure updated for {$staff->full_name}.");
    }

    public function runPayroll(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'year'  => ['required', 'integer', 'min:2020', 'max:2050'],
        ]);

        $existing = PayrollRun::where('month', $data['month'])
            ->where('year', $data['year'])
            ->first();

        if ($existing) {
            $label = Carbon::createFromDate($data['year'], $data['month'], 1)->format('F Y');
            return back()->with('error', "A payroll run already exists for {$label}.");
        }

        try {
            $this->payrollService->runPayroll((int) $data['month'], (int) $data['year']);
            $label = Carbon::createFromDate($data['year'], $data['month'], 1)->format('F Y');

            return redirect(request()->getSchemeAndHttpHost() . '/payroll?tab=runs')
                ->with('success', "Payroll for {$label} processed successfully.");
        } catch (\Throwable $e) {
            Log::error('[PayrollController::runPayroll] ' . $e->getMessage());
            return back()->with('error', 'Failed to process payroll. Please try again.');
        }
    }

    public function downloadPayslip(PayrollRun $payrollRun, PayrollItem $payrollItem): Response
    {
        abort_unless(Auth::user()->can('payroll.view'), 403);
        abort_unless($payrollItem->payroll_run_id === $payrollRun->id, 404);

        $payrollItem->load('staff');
        $school    = SchoolProfile::first();
        $structure = SalaryStructure::where('staff_id', $payrollItem->staff_id)->first();

        $pdf = Pdf::loadView('tenant.payroll.payslip-pdf', [
            'run'       => $payrollRun,
            'item'      => $payrollItem,
            'school'    => $school,
            'structure' => $structure,
        ])->setPaper('a4', 'portrait');

        $safeName = preg_replace('/[^A-Za-z0-9\-]/', '-', $payrollItem->staff->full_name);
        $period   = $payrollRun->year . '-' . str_pad((string) $payrollRun->month, 2, '0', STR_PAD_LEFT);
        $filename = "payslip-{$safeName}-{$period}.pdf";

        return response($pdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function logAsExpense(PayrollRun $payrollRun): RedirectResponse
    {
        abort_unless(Auth::user()->can('payroll.create'), 403);
        abort_unless($payrollRun->status === 'processed', 422);

        try {
            $this->payrollService->logAsExpense($payrollRun);
            $label = Carbon::createFromDate($payrollRun->year, $payrollRun->month, 1)->format('F Y');

            return redirect(request()->getSchemeAndHttpHost() . '/payroll?tab=runs')
                ->with('success', "Expense entry created for {$label} payroll.");
        } catch (\Throwable $e) {
            Log::error('[PayrollController::logAsExpense] ' . $e->getMessage());
            return back()->with('error', 'Failed to log expense. Please try again.');
        }
    }

    public function markPaid(Request $request, PayrollRun $payrollRun, PayrollItem $payrollItem): RedirectResponse
    {
        abort_unless(Auth::user()->can('payroll.create'), 403);
        abort_unless($payrollItem->payroll_run_id === $payrollRun->id, 404);

        $validated = $request->validate([
            'payment_method' => ['required', 'in:bank_transfer,mobile_money,cash'],
            'paid_at'        => ['required', 'date', 'before_or_equal:today'],
        ]);

        $payrollItem->update([
            'payment_status' => 'paid',
            'payment_method' => $validated['payment_method'],
            'paid_at'        => Carbon::parse($validated['paid_at']),
        ]);

        return back()->with('success', 'Payment recorded for ' . ($payrollItem->staff?->full_name ?? 'staff') . '.');
    }
}
