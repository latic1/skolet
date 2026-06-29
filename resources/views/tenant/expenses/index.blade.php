@extends('layouts.tenant')

@section('title', 'Expenses')

@section('content')
<div class="flex flex-col gap-6">

    {{-- Page header --}}
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-text-primary">Expenses</h1>
            <p class="text-xs text-text-muted mt-0.5">{{ $expenses->total() }} {{ Str::plural('record', $expenses->total()) }}</p>
        </div>
        @can('expenses.create')
        <button type="button"
                @click="$dispatch('open-expense-modal')"
                x-data
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium bg-accent text-accent-foreground rounded-md hover:bg-accent-dark transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Log Expense
        </button>
        @endcan
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="bg-success-lightest border border-success-light text-success-foreground text-sm px-4 py-3 rounded-xl flex items-start gap-3">
        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="bg-error-light border border-error text-error text-sm px-4 py-3 rounded-xl flex items-start gap-3">
        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- Summary strip --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-surface border border-border rounded-2xl p-5 shadow-card">
            <p class="text-xs font-medium text-text-muted uppercase tracking-wide mb-1">This Month</p>
            <p class="text-[26px] font-semibold text-text-primary leading-none">{{ format_money($totalThisMonth, $currencySymbol) }}</p>
        </div>
        <div class="bg-surface border border-border rounded-2xl p-5 shadow-card">
            <p class="text-xs font-medium text-text-muted uppercase tracking-wide mb-1">This Term</p>
            <p class="text-[26px] font-semibold text-text-primary leading-none">{{ format_money($totalThisTerm, $currencySymbol) }}</p>
        </div>
        <div class="bg-surface border border-border rounded-2xl p-5 shadow-card">
            <p class="text-xs font-medium text-text-muted uppercase tracking-wide mb-1">YTD Total</p>
            <p class="text-[26px] font-semibold text-text-primary leading-none">{{ format_money($totalYtd, $currencySymbol) }}</p>
        </div>
    </div>

    {{-- Filter bar --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card p-5">
        <form method="GET" action="{{ request()->getSchemeAndHttpHost() }}/expenses" class="flex flex-wrap items-end gap-4">
            <div class="flex flex-col gap-1.5 min-w-[180px]">
                <label class="block text-xs font-medium text-text-dark">Category</label>
                <select name="category_id"
                        class="px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ $categoryId === $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-col gap-1.5">
                <label class="block text-xs font-medium text-text-dark">From</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}"
                       class="px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent">
            </div>
            <div class="flex flex-col gap-1.5">
                <label class="block text-xs font-medium text-text-dark">To</label>
                <input type="date" name="date_to" value="{{ $dateTo }}"
                       class="px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit"
                        class="px-4 py-2 text-sm font-medium bg-accent text-accent-foreground rounded-md hover:bg-accent-dark transition-colors">
                    Filter
                </button>
                @if($categoryId || $dateFrom || $dateTo)
                <a href="{{ request()->getSchemeAndHttpHost() }}/expenses"
                   class="px-4 py-2 text-sm font-medium bg-surface border border-border text-text-primary rounded-md hover:bg-surface-secondary transition-colors">
                    Clear
                </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Expenses table --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card"
         x-data="expensesPage(@js($categories), @js($expenses->items()))"
         @open-expense-modal.window="openAdd()">

        {{-- Table header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-border">
            <h2 class="text-sm font-semibold text-text-primary">Expense Records</h2>
            @can('expenses.create')
            <button type="button" @click="openAdd()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-accent text-accent-foreground rounded-md hover:bg-accent-dark transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Log Expense
            </button>
            @endcan
        </div>

        <div class="overflow-x-auto">
            <table class="w-full" style="min-width: 700px">
                <thead>
                    <tr class="border-b border-border bg-surface-secondary">
                        <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Date</th>
                        <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Category</th>
                        <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Description</th>
                        <th class="text-right px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Amount</th>
                        <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary hidden md:table-cell">Recorded By</th>
                        <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary hidden sm:table-cell">Receipt</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $expense)
                    <tr class="border-b border-border last:border-b-0 hover:bg-surface-secondary transition-colors">
                        <td class="px-6 py-4 text-sm text-text-primary whitespace-nowrap">
                            {{ $expense->date->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-accent-muted text-accent">
                                {{ $expense->category->name }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-text-primary max-w-xs">
                            <span class="line-clamp-2">{{ $expense->description }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm font-semibold text-text-primary text-right whitespace-nowrap">
                            {{ format_money($expense->amount, $currencySymbol) }}
                        </td>
                        <td class="px-6 py-4 text-sm text-text-secondary hidden md:table-cell">
                            {{ $expense->recordedBy?->name ?? '&mdash;' }}
                        </td>
                        <td class="px-6 py-4 hidden sm:table-cell">
                            @if($expense->receipt_path)
                            <a href="{{ request()->getSchemeAndHttpHost() }}/expenses/receipt/{{ $expense->id }}"
                               target="_blank"
                               class="inline-flex items-center gap-1 text-xs font-medium text-accent hover:text-accent-dark transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                </svg>
                                View
                            </a>
                            @else
                            <span class="text-xs text-text-muted">&mdash;</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                @can('expenses.edit')
                                <button type="button"
                                        @click="openEdit(@js(['id' => $expense->id, 'category_id' => $expense->category_id, 'amount' => $expense->amount, 'date' => $expense->date->format('Y-m-d'), 'description' => $expense->description]))"
                                        class="text-xs font-medium text-text-secondary hover:text-text-primary transition-colors px-2 py-1 rounded hover:bg-surface-secondary">
                                    Edit
                                </button>
                                @endcan
                                @can('expenses.delete')
                                <form method="POST"
                                      action="{{ request()->getSchemeAndHttpHost() }}/expenses/{{ $expense->id }}"
                                      onsubmit="return confirm('Delete this expense record?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-xs font-medium text-error hover:text-red-700 transition-colors px-2 py-1 rounded hover:bg-error-light">
                                        Delete
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-surface-secondary flex items-center justify-center">
                                    <svg class="w-6 h-6 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-text-primary">No expenses recorded</p>
                                    <p class="text-xs text-text-muted mt-1">Log your first expense to start tracking school spending.</p>
                                </div>
                                @can('expenses.create')
                                <button type="button" @click="openAdd()"
                                        class="mt-1 px-4 py-2 text-sm font-medium bg-accent text-accent-foreground rounded-md hover:bg-accent-dark transition-colors">
                                    Log Expense
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($expenses->hasPages())
        <div class="px-6 py-4 border-t border-border">
            {{ $expenses->links() }}
        </div>
        @endif

        {{-- Log Expense / Edit modal --}}
        <div x-show="showModal"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="display: none;">
            <div class="absolute inset-0 bg-overlay/40" @click="close()"></div>
            <div class="relative w-full max-w-lg bg-surface rounded-2xl shadow-xl border border-border"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">

                <div class="flex items-center justify-between px-6 py-4 border-b border-border">
                    <h3 class="text-base font-semibold text-text-primary" x-text="mode === 'add' ? 'Log Expense' : 'Edit Expense'"></h3>
                    <button type="button" @click="close()" class="p-1.5 rounded-md text-text-muted hover:bg-surface-secondary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="p-6 overflow-y-auto" style="max-height: calc(90vh - 120px)">

                    {{-- Add form --}}
                    <form x-show="mode === 'add'"
                          method="POST"
                          action="{{ request()->getSchemeAndHttpHost() }}/expenses"
                          enctype="multipart/form-data"
                          @submit="submitting = true">
                        @csrf
                        @include('tenant.expenses._form')
                        <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-border">
                            <button type="button" @click="close()"
                                    class="px-4 py-2 text-sm font-medium bg-surface border border-border text-text-primary rounded-md hover:bg-surface-secondary transition-colors">
                                Cancel
                            </button>
                            <button type="submit" :disabled="submitting"
                                    class="px-4 py-2 text-sm font-medium bg-accent text-accent-foreground rounded-md hover:bg-accent-dark transition-colors disabled:opacity-60">
                                <span x-show="!submitting">Log Expense</span>
                                <span x-show="submitting">Saving&hellip;</span>
                            </button>
                        </div>
                    </form>

                    {{-- Edit form --}}
                    <form x-show="mode === 'edit'"
                          method="POST"
                          :action="editAction"
                          enctype="multipart/form-data"
                          @submit="submitting = true">
                        @csrf
                        @method('PUT')
                        @include('tenant.expenses._form')
                        <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-border">
                            <button type="button" @click="close()"
                                    class="px-4 py-2 text-sm font-medium bg-surface border border-border text-text-primary rounded-md hover:bg-surface-secondary transition-colors">
                                Cancel
                            </button>
                            <button type="submit" :disabled="submitting"
                                    class="px-4 py-2 text-sm font-medium bg-accent text-accent-foreground rounded-md hover:bg-accent-dark transition-colors disabled:opacity-60">
                                <span x-show="!submitting">Save Changes</span>
                                <span x-show="submitting">Saving&hellip;</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Add Category modal --}}
        <div x-show="showCategoryModal"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="display: none;">
            <div class="absolute inset-0 bg-overlay/40" @click="showCategoryModal = false"></div>
            <div class="relative w-full max-w-sm bg-surface rounded-2xl shadow-xl border border-border p-6"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-base font-semibold text-text-primary">Add Category</h3>
                    <button type="button" @click="showCategoryModal = false" class="p-1.5 rounded-md text-text-muted hover:bg-surface-secondary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <form method="POST" action="{{ request()->getSchemeAndHttpHost() }}/expenses/categories"
                      @submit="categorySubmitting = true">
                    @csrf
                    <div class="flex flex-col gap-1.5 mb-5">
                        <label class="block text-sm font-medium text-text-dark">Category Name <span class="text-error">*</span></label>
                        <input type="text" name="name" required maxlength="100"
                               class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent"
                               placeholder="e.g. Transport">
                        @error('name')
                        <p class="text-xs text-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex items-center justify-end gap-3">
                        <button type="button" @click="showCategoryModal = false"
                                class="px-4 py-2 text-sm font-medium bg-surface border border-border text-text-primary rounded-md hover:bg-surface-secondary transition-colors">
                            Cancel
                        </button>
                        <button type="submit" :disabled="categorySubmitting"
                                class="px-4 py-2 text-sm font-medium bg-accent text-accent-foreground rounded-md hover:bg-accent-dark transition-colors disabled:opacity-60">
                            <span x-show="!categorySubmitting">Add Category</span>
                            <span x-show="categorySubmitting">Saving&hellip;</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>{{-- /expenses table card --}}

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
Alpine.data('expensesPage', (categories, expenses) => ({
    showModal: false,
    showCategoryModal: false,
    mode: 'add',
    submitting: false,
    categorySubmitting: false,
    editAction: '',
    form: {
        category_id: '',
        amount: '',
        date: '{{ now()->format('Y-m-d') }}',
        description: '',
    },

    init() {
        // Re-open modal on validation error (add mode)
        @if($errors->any() && old('_expense_mode') === 'add')
        this.showModal = true;
        this.mode = 'add';
        this.form.category_id = '{{ old('category_id', '') }}';
        this.form.amount      = '{{ old('amount', '') }}';
        this.form.date        = '{{ old('date', now()->format('Y-m-d')) }}';
        this.form.description = '{{ old('description', '') }}';
        @elseif($errors->any() && old('_expense_mode') === 'edit')
        this.showModal = true;
        this.mode = 'edit';
        this.editAction = window.location.origin + '/expenses/' + '{{ old('_expense_id', '') }}';
        this.form.category_id = '{{ old('category_id', '') }}';
        this.form.amount      = '{{ old('amount', '') }}';
        this.form.date        = '{{ old('date', now()->format('Y-m-d')) }}';
        this.form.description = '{{ old('description', '') }}';
        @endif
    },

    openAdd() {
        this.mode = 'add';
        this.editAction = '';
        this.form = {
            category_id: '',
            amount: '',
            date: '{{ now()->format('Y-m-d') }}',
            description: '',
        };
        this.submitting = false;
        this.showModal = true;
    },

    openEdit(data) {
        this.mode = 'edit';
        this.editAction = window.location.origin + '/expenses/' + data.id;
        this.form = {
            category_id: data.category_id,
            amount: data.amount,
            date: data.date,
            description: data.description,
        };
        this.submitting = false;
        this.showModal = true;
    },

    close() {
        this.showModal = false;
        this.submitting = false;
    },
}));
});
</script>
@endpush
