<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreExpenseCategoryRequest;
use App\Http\Requests\Tenant\StoreExpenseRequest;
use App\Http\Requests\Tenant\UpdateExpenseRequest;
use App\Models\Tenant\Expense;
use App\Models\Tenant\ExpenseCategory;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

final class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $categories = ExpenseCategory::orderBy('name')->get();

        // Filters
        $categoryId = $request->input('category_id');
        $dateFrom   = $request->input('date_from');
        $dateTo     = $request->input('date_to');

        $query = Expense::with(['category', 'recordedBy'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc');

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        if ($dateFrom) {
            $query->whereDate('date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('date', '<=', $dateTo);
        }

        $expenses = $query->paginate(25)->withQueryString();

        // Summary totals
        $now           = Carbon::now();
        $totalThisMonth = Expense::whereYear('date', $now->year)
            ->whereMonth('date', $now->month)
            ->sum('amount');

        // Current term date range for "This Term" total
        $currentTerm = \App\Models\Tenant\Term::where('is_current', true)->first();
        $totalThisTerm = 0;
        if ($currentTerm) {
            $termQuery = Expense::query();
            if ($currentTerm->start_date) {
                $termQuery->whereDate('date', '>=', $currentTerm->start_date);
            }
            if ($currentTerm->end_date) {
                $termQuery->whereDate('date', '<=', $currentTerm->end_date);
            }
            $totalThisTerm = $termQuery->sum('amount');
        }

        $totalYtd = Expense::whereYear('date', $now->year)->sum('amount');

        return view('tenant.expenses.index', compact(
            'expenses',
            'categories',
            'categoryId',
            'dateFrom',
            'dateTo',
            'totalThisMonth',
            'totalThisTerm',
            'totalYtd',
        ));
    }

    public function store(StoreExpenseRequest $request): RedirectResponse
    {
        try {
            $data = $request->validated();

            $receiptPath = null;
            if ($request->hasFile('receipt')) {
                $file        = $request->file('receipt');
                $receiptPath = $file->store('expenses/receipts/' . tenant('id'), 'local');
            }

            Expense::create([
                'category_id'  => $data['category_id'],
                'amount'       => $data['amount'],
                'date'         => $data['date'],
                'description'  => $data['description'],
                'receipt_path' => $receiptPath,
                'recorded_by'  => Auth::id(),
            ]);

            return redirect(request()->getSchemeAndHttpHost() . '/expenses')
                ->with('success', 'Expense logged successfully.');
        } catch (\Throwable $e) {
            Log::error('[ExpenseController::store] ' . $e->getMessage());

            return back()->withInput()->with('error', 'Could not log expense. Please try again.');
        }
    }

    public function update(UpdateExpenseRequest $request, Expense $expense): RedirectResponse
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('receipt')) {
                // Delete old receipt if exists
                if ($expense->receipt_path) {
                    Storage::disk('local')->delete($expense->receipt_path);
                }
                $expense->receipt_path = $request->file('receipt')->store('expenses/receipts/' . tenant('id'), 'local');
            }

            $expense->update([
                'category_id' => $data['category_id'],
                'amount'      => $data['amount'],
                'date'        => $data['date'],
                'description' => $data['description'],
                'receipt_path' => $expense->receipt_path,
            ]);

            return redirect(request()->getSchemeAndHttpHost() . '/expenses')
                ->with('success', 'Expense updated successfully.');
        } catch (\Throwable $e) {
            Log::error('[ExpenseController::update] ' . $e->getMessage());

            return back()->withInput()->with('error', 'Could not update expense. Please try again.');
        }
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        try {
            if ($expense->receipt_path) {
                Storage::disk('local')->delete($expense->receipt_path);
            }

            $expense->delete();

            return redirect(request()->getSchemeAndHttpHost() . '/expenses')
                ->with('success', 'Expense deleted.');
        } catch (\Throwable $e) {
            Log::error('[ExpenseController::destroy] ' . $e->getMessage());

            return back()->with('error', 'Could not delete expense. Please try again.');
        }
    }

    public function receipt(Expense $expense): Response
    {
        abort_unless(Auth::user()->can('expenses.view'), 403);
        abort_if(! $expense->receipt_path || ! Storage::disk('local')->exists($expense->receipt_path), 404);

        return response(
            Storage::disk('local')->get($expense->receipt_path),
            200,
            ['Content-Type' => Storage::disk('local')->mimeType($expense->receipt_path)]
        );
    }

    public function storeCategory(StoreExpenseCategoryRequest $request): RedirectResponse
    {
        try {
            ExpenseCategory::create($request->validated());

            return redirect(request()->getSchemeAndHttpHost() . '/expenses')
                ->with('success', "Category '{$request->input('name')}' created.");
        } catch (\Throwable $e) {
            Log::error('[ExpenseController::storeCategory] ' . $e->getMessage());

            return back()->withInput()->with('error', 'Could not create category. Please try again.');
        }
    }
}
