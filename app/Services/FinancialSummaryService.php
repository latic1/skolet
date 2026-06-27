<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tenant\AcademicYear;
use App\Models\Tenant\Expense;
use App\Models\Tenant\FeePayment;
use App\Models\Tenant\Term;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

final class FinancialSummaryService
{
    public function build(string $academicYearId, ?string $termId): array
    {
        $academicYear = AcademicYear::with('terms')->findOrFail($academicYearId);
        $term         = $termId ? Term::findOrFail($termId) : null;

        [$dateFrom, $dateTo] = $this->resolveDateRange($academicYear, $term);

        // ---------- Income ----------
        $incomeTotals  = $this->fetchIncomeTotals($academicYear, $term);
        $incomeTotal   = $incomeTotals['total'];
        $incomeByFeeItem = $incomeTotals['byCategory'];
        $monthlyIncome = $incomeTotals['monthly'];

        // ---------- Expenses ----------
        $expenseTotals       = $this->fetchExpenseTotals($dateFrom, $dateTo);
        $expenseTotal        = $expenseTotals['total'];
        $expenseByCategory   = $expenseTotals['byCategory'];
        $monthlyExpenses     = $expenseTotals['monthly'];

        // ---------- Monthly trend for Chart.js ----------
        $monthlyTrend = $this->buildMonthlyTrend($dateFrom, $dateTo, $monthlyIncome, $monthlyExpenses);

        return [
            'academic_year'       => $academicYear,
            'term'                => $term,
            'date_from'           => $dateFrom,
            'date_to'             => $dateTo,
            'income_total'        => $incomeTotal,
            'expense_total'       => $expenseTotal,
            'net'                 => $incomeTotal - $expenseTotal,
            'income_by_category'  => $incomeByFeeItem,
            'expense_by_category' => $expenseByCategory,
            'monthly_trend'       => $monthlyTrend,
        ];
    }

    private function fetchIncomeTotals(AcademicYear $year, ?Term $term): array
    {
        $base = fn () => FeePayment::query()
            ->join('fee_structures', 'fee_payments.fee_structure_id', '=', 'fee_structures.id')
            ->when($term,
                fn ($q) => $q->where('fee_structures.term_id', $term->id),
                fn ($q) => $q->where('fee_structures.academic_year_id', $year->id),
            );

        $total = (float) $base()->sum('fee_payments.amount');

        $byCategory = $base()
            ->select('fee_structures.fee_item', DB::raw('SUM(fee_payments.amount) as total'))
            ->groupBy('fee_structures.fee_item')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => ['label' => $r->fee_item, 'amount' => (float) $r->total])
            ->toArray();

        $monthly = $base()
            ->whereNotNull('fee_payments.paid_at')
            ->select(
                DB::raw('YEAR(fee_payments.paid_at) as yr'),
                DB::raw('MONTH(fee_payments.paid_at) as mo'),
                DB::raw('SUM(fee_payments.amount) as total')
            )
            ->groupBy('yr', 'mo')
            ->orderBy('yr')
            ->orderBy('mo')
            ->get()
            ->keyBy(fn ($r) => "{$r->yr}-{$r->mo}");

        return compact('total', 'byCategory', 'monthly');
    }

    private function fetchExpenseTotals(Carbon $dateFrom, Carbon $dateTo): array
    {
        $base = fn () => Expense::query()
            ->join('expense_categories', 'expenses.category_id', '=', 'expense_categories.id')
            ->whereBetween('expenses.date', [$dateFrom->toDateString(), $dateTo->toDateString()]);

        $total = (float) $base()->sum('expenses.amount');

        $byCategory = $base()
            ->select('expense_categories.name as category', DB::raw('SUM(expenses.amount) as total'))
            ->groupBy('expense_categories.name')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => ['label' => $r->category, 'amount' => (float) $r->total])
            ->toArray();

        $monthly = $base()
            ->select(
                DB::raw('YEAR(expenses.date) as yr'),
                DB::raw('MONTH(expenses.date) as mo'),
                DB::raw('SUM(expenses.amount) as total')
            )
            ->groupBy('yr', 'mo')
            ->orderBy('yr')
            ->orderBy('mo')
            ->get()
            ->keyBy(fn ($r) => "{$r->yr}-{$r->mo}");

        return compact('total', 'byCategory', 'monthly');
    }

    private function resolveDateRange(AcademicYear $year, ?Term $term): array
    {
        if ($term) {
            $from = $term->start_date ? Carbon::parse($term->start_date)->startOfDay() : null;
            $to   = $term->end_date   ? Carbon::parse($term->end_date)->endOfDay()     : null;
        } else {
            $from = $year->start_date ? Carbon::parse($year->start_date)->startOfDay() : null;
            $to   = $year->end_date   ? Carbon::parse($year->end_date)->endOfDay()     : null;
        }

        return [$from ?? now()->startOfYear(), $to ?? now()->endOfYear()];
    }

    private function buildMonthlyTrend(
        Carbon $dateFrom,
        Carbon $dateTo,
        $monthlyIncome,
        $monthlyExpenses,
    ): array {
        $trend   = [];
        $current = $dateFrom->copy()->startOfMonth();
        $end     = $dateTo->copy()->startOfMonth();

        while ($current->lte($end)) {
            $key     = "{$current->year}-{$current->month}";
            $trend[] = [
                'month'    => $current->format('M Y'),
                'income'   => (float) ($monthlyIncome->get($key)?->total  ?? 0),
                'expenses' => (float) ($monthlyExpenses->get($key)?->total ?? 0),
            ];
            $current->addMonth();
        }

        return $trend;
    }
}
