<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tenant\Expense;
use App\Models\Tenant\ExpenseCategory;
use App\Models\Tenant\PayrollItem;
use App\Models\Tenant\PayrollRun;
use App\Models\Tenant\SalaryStructure;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class PayrollService
{
    public function runPayroll(int $month, int $year): PayrollRun
    {
        return DB::transaction(function () use ($month, $year) {
            $run = PayrollRun::create([
                'month'        => $month,
                'year'         => $year,
                'status'       => 'processed',
                'processed_by' => Auth::id(),
                'processed_at' => now(),
            ]);

            $structures = SalaryStructure::with('staff')
                ->whereHas('staff', fn ($q) => $q->where('status', 'active'))
                ->get();

            $rows = [];
            $now  = now();

            foreach ($structures as $structure) {
                $gross           = (float) $structure->gross;
                $allowancesTotal = (float) array_sum($structure->allowances ?? []);
                $deductionsTotal = (float) array_sum($structure->deductions ?? []);

                // Ghana statutory deductions (applied to gross basic only)
                $ssnitEmployee = round($gross * config('payroll.ssnit_employee_rate'), 2);
                $tier2Employee = round($gross * config('payroll.tier2_employee_rate'), 2);

                // Taxable income for PAYE = gross less SSNIT & Tier 2 employee contributions
                $taxableIncome = max(0.0, $gross - $ssnitEmployee - $tier2Employee);
                $paye          = $this->computePaye($taxableIncome);

                // Employer statutory contributions (school liability, not deducted from staff net)
                $ssnitEmployer = round($gross * config('payroll.ssnit_employer_rate'), 2);
                $tier2Employer = round($gross * config('payroll.tier2_employer_rate'), 2);

                // Net = gross + allowances − manual deductions − statutory employee deductions
                $net = max(0.0, $gross + $allowancesTotal - $deductionsTotal - $ssnitEmployee - $tier2Employee - $paye);

                $rows[] = [
                    'id'               => (string) Str::uuid(),
                    'payroll_run_id'   => $run->id,
                    'staff_id'         => $structure->staff_id,
                    'gross'            => $gross,
                    'allowances_total' => $allowancesTotal,
                    'deductions_total' => $deductionsTotal,
                    'ssnit_employee'   => $ssnitEmployee,
                    'tier2_employee'   => $tier2Employee,
                    'paye'             => $paye,
                    'ssnit_employer'   => $ssnitEmployer,
                    'tier2_employer'   => $tier2Employer,
                    'net'              => $net,
                    'payment_status'   => 'pending',
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ];
            }

            if (! empty($rows)) {
                PayrollItem::insert($rows);
            }

            return $run->load(['items.staff', 'processedBy']);
        });
    }

    public function logAsExpense(PayrollRun $run): Expense
    {
        $category  = ExpenseCategory::firstOrCreate(['name' => 'Salaries']);
        $monthName = Carbon::createFromDate($run->year, $run->month, 1)->format('F Y');
        $total     = $run->items()->sum('net');

        return Expense::create([
            'category_id' => $category->id,
            'amount'      => $total,
            'date'        => Carbon::createFromDate($run->year, $run->month, 1)->endOfMonth()->toDateString(),
            'description' => "Payroll — {$monthName}",
            'recorded_by' => Auth::id(),
        ]);
    }

    private function computePaye(float $income): float
    {
        $tax   = 0.0;
        $bands = config('payroll.paye_bands');

        foreach ($bands as $band) {
            if ($income <= 0.0) {
                break;
            }

            $slice   = $band['limit'] === null ? $income : min($income, (float) $band['limit']);
            $tax    += $slice * $band['rate'];
            $income -= $slice;
        }

        return round($tax, 2);
    }
}
