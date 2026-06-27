@extends('layouts.tenant')

@section('title', 'Payroll')

@section('content')
@php $host = request()->getSchemeAndHttpHost(); @endphp

<div class="flex flex-col gap-6"
     x-data="{
         tab: '{{ request()->get('tab', 'salary') }}',

         editModal: false,
         editStaffId: null,
         editStaffName: '',
         editForm: {
             gross: 0,
             effective_from: '',
             allowances: { housing: 0, transport: 0, medical: 0, other: 0 },
             deductions: { tax: 0, pension: 0, loan: 0, other: 0 }
         },
         openEdit(data) {
             this.editStaffId   = data.staff_id;
             this.editStaffName = data.staff_name;
             this.editForm = {
                 gross:          data.gross ?? 0,
                 effective_from: data.effective_from ?? '',
                 allowances:     data.allowances ?? { housing: 0, transport: 0, medical: 0, other: 0 },
                 deductions:     data.deductions ?? { tax: 0, pension: 0, loan: 0, other: 0 }
             };
             this.editModal = true;
         },

         runModal: false,
         runMonth: {{ now()->month }},
         runYear:  {{ now()->year }},

         expandedRun: null,
         toggleRun(id) { this.expandedRun = this.expandedRun === id ? null : id; }
     }">

    {{-- Page header --}}
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-text-primary">Payroll</h1>
            <p class="text-xs text-text-muted mt-0.5">Staff salary structures and payroll runs</p>
        </div>
        @can('payroll.create')
        <button type="button" @click="runModal = true"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium bg-accent text-accent-foreground rounded-md hover:bg-accent-dark transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Run Payroll
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

    {{-- Tabs --}}
    <div class="border-b border-border">
        <nav class="-mb-px flex gap-6">
            <button type="button" @click="tab = 'salary'"
                    :class="tab === 'salary' ? 'border-accent text-accent' : 'border-transparent text-text-muted hover:text-text-primary hover:border-border'"
                    class="border-b-2 pb-3 text-sm font-medium transition-colors">
                Salary Structures
            </button>
            <button type="button" @click="tab = 'runs'"
                    :class="tab === 'runs' ? 'border-accent text-accent' : 'border-transparent text-text-muted hover:text-text-primary hover:border-border'"
                    class="border-b-2 pb-3 text-sm font-medium transition-colors">
                Payroll Runs
            </button>
        </nav>
    </div>

    {{-- ====================== Salary Structures Tab ====================== --}}
    <div x-show="tab === 'salary'" x-cloak>
        @if($staffWithStructures->isEmpty())
        <div class="bg-surface border border-border rounded-2xl p-12 flex flex-col items-center text-center shadow-card">
            <svg class="w-10 h-10 text-text-muted mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <p class="text-sm font-medium text-text-primary">No active staff found</p>
            <p class="text-xs text-text-muted mt-1">Add staff members first, then configure salary structures here.</p>
        </div>
        @else
        <div class="bg-surface border border-border rounded-2xl shadow-card overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border bg-surface-secondary">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-text-muted uppercase tracking-wide">Staff Member</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-text-muted uppercase tracking-wide">Role</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-text-muted uppercase tracking-wide">Gross</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-text-muted uppercase tracking-wide">+ Allowances</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-text-muted uppercase tracking-wide">− Deductions</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-text-muted uppercase tracking-wide">Net Pay</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-text-muted uppercase tracking-wide">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($staffWithStructures as $staff)
                    @php
                        $s           = $staff->salaryStructure;
                        $allowTotal  = $s ? (float) array_sum($s->allowances ?? []) : 0;
                        $deductTotal = $s ? (float) array_sum($s->deductions ?? []) : 0;
                        $net         = $s ? max(0, (float) $s->gross + $allowTotal - $deductTotal) : 0;
                    @endphp
                    <tr class="hover:bg-surface-secondary transition-colors">
                        <td class="px-4 py-3 font-medium text-text-primary">{{ $staff->full_name }}</td>
                        <td class="px-4 py-3 text-text-muted">{{ $staff->role_title ?? '—' }}</td>
                        <td class="px-4 py-3 text-right text-text-primary">
                            {{ $s ? format_money($s->gross, $currencySymbol) : '—' }}
                        </td>
                        <td class="px-4 py-3 text-right text-success">
                            {{ $s ? format_money($allowTotal, $currencySymbol) : '—' }}
                        </td>
                        <td class="px-4 py-3 text-right text-error">
                            {{ $s ? format_money($deductTotal, $currencySymbol) : '—' }}
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-text-primary">
                            {{ $s ? format_money($net, $currencySymbol) : '—' }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            @can('payroll.edit')
                            <button type="button"
                                    @click="openEdit({
                                        staff_id:       '{{ $staff->id }}',
                                        staff_name:     @js($staff->full_name),
                                        gross:          {{ $s?->gross ?? 0 }},
                                        effective_from: '{{ $s?->effective_from?->format('Y-m-d') ?? '' }}',
                                        allowances:     @js($s?->allowances ?? ['housing' => 0, 'transport' => 0, 'medical' => 0, 'other' => 0]),
                                        deductions:     @js($s?->deductions ?? ['tax' => 0, 'pension' => 0, 'loan' => 0, 'other' => 0])
                                    })"
                                    class="text-xs font-medium text-accent hover:underline">
                                {{ $s ? 'Edit' : 'Set Up' }}
                            </button>
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- ====================== Payroll Runs Tab ====================== --}}
    <div x-show="tab === 'runs'" x-cloak>
        @if($runs->isEmpty())
        <div class="bg-surface border border-border rounded-2xl p-12 flex flex-col items-center text-center shadow-card">
            <svg class="w-10 h-10 text-text-muted mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <p class="text-sm font-medium text-text-primary">No payroll runs yet</p>
            <p class="text-xs text-text-muted mt-1">Click "Run Payroll" to process the first payroll.</p>
        </div>
        @else
        <div class="bg-surface border border-border rounded-2xl shadow-card overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border bg-surface-secondary">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-text-muted uppercase tracking-wide w-4"></th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-text-muted uppercase tracking-wide">Period</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-text-muted uppercase tracking-wide">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-text-muted uppercase tracking-wide">Staff</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-text-muted uppercase tracking-wide">Total Net Pay</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-text-muted uppercase tracking-wide">Processed By</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-text-muted uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($runs as $run)
                    <tr class="group">
                        <td colspan="7" class="p-0">
                            {{-- Collapsible header row --}}
                            <div class="flex items-center gap-3 px-4 py-3 hover:bg-surface-secondary transition-colors cursor-pointer"
                                 @click="toggleRun('{{ $run->id }}')">
                                <svg class="w-4 h-4 text-text-muted shrink-0 transition-transform duration-150"
                                     :class="expandedRun === '{{ $run->id }}' ? 'rotate-90' : ''"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                                <span class="font-medium text-text-primary min-w-[100px]">{{ $run->period_label }}</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ $run->status === 'processed' ? 'bg-success-lightest text-success-foreground' : 'bg-surface-secondary text-text-muted' }}">
                                    {{ ucfirst($run->status) }}
                                </span>
                                <span class="text-xs text-text-muted ml-auto">{{ $run->items->count() }} staff</span>
                                <span class="font-semibold text-text-primary min-w-[100px] text-right">
                                    {{ format_money($run->total_net, $currencySymbol) }}
                                </span>
                                <span class="text-xs text-text-muted min-w-[120px]">
                                    {{ $run->processedBy?->name ?? '—' }}
                                </span>
                                @can('payroll.create')
                                @if($run->status === 'processed')
                                <form action="{{ $host }}/payroll/{{ $run->id }}/expense" method="POST"
                                      @click.stop
                                      onsubmit="return confirm('Log {{ $run->period_label }} payroll as a Salaries expense?')">
                                    @csrf
                                    <button type="submit"
                                            class="text-xs font-medium text-text-muted hover:text-accent transition-colors whitespace-nowrap">
                                        Log Expense
                                    </button>
                                </form>
                                @endif
                                @endcan
                            </div>

                            {{-- Expanded staff items --}}
                            <div x-show="expandedRun === '{{ $run->id }}'" x-cloak
                                 class="border-t border-border bg-surface-secondary">
                                @if($run->items->isEmpty())
                                <p class="px-10 py-4 text-xs text-text-muted">No payroll items recorded for this run.</p>
                                @else
                                <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="border-b border-border">
                                            <th class="pl-12 pr-4 py-2 text-left text-xs text-text-muted font-medium">Staff</th>
                                            <th class="px-4 py-2 text-right text-xs text-text-muted font-medium">Gross</th>
                                            <th class="px-4 py-2 text-right text-xs text-text-muted font-medium">+ Allow.</th>
                                            <th class="px-4 py-2 text-right text-xs text-text-muted font-medium">− Deduct.</th>
                                            <th class="px-4 py-2 text-right text-xs text-text-muted font-medium">− Statutory</th>
                                            <th class="px-4 py-2 text-right text-xs text-text-muted font-medium">Net Pay</th>
                                            <th class="px-4 py-2 text-center text-xs text-text-muted font-medium">Status</th>
                                            <th class="px-4 py-2 text-right text-xs text-text-muted font-medium">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-border">
                                        @foreach($run->items as $item)
                                        @php
                                            $statutory = (float) $item->ssnit_employee + (float) $item->tier2_employee + (float) $item->paye;
                                        @endphp
                                        <tr x-data="{ paying: false, payMethod: 'bank_transfer', payDate: '{{ date('Y-m-d') }}' }"
                                            class="hover:bg-surface transition-colors">
                                            <td class="pl-12 pr-4 py-2 text-text-primary">{{ $item->staff?->full_name ?? '—' }}</td>
                                            <td class="px-4 py-2 text-right text-text-muted">{{ format_money($item->gross, $currencySymbol) }}</td>
                                            <td class="px-4 py-2 text-right text-success text-xs">+{{ format_money($item->allowances_total, $currencySymbol) }}</td>
                                            <td class="px-4 py-2 text-right text-error text-xs">−{{ format_money($item->deductions_total, $currencySymbol) }}</td>
                                            <td class="px-4 py-2 text-right text-error text-xs">−{{ format_money($statutory, $currencySymbol) }}</td>
                                            <td class="px-4 py-2 text-right font-semibold text-text-primary">{{ format_money($item->net, $currencySymbol) }}</td>
                                            <td class="px-4 py-2 text-center">
                                                @if($item->payment_status === 'paid')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-success-lightest text-success-foreground">Paid</span>
                                                @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-warning-light text-warning">Pending</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-2 text-right">
                                                <div class="flex flex-col items-end gap-1">
                                                    @can('payroll.view')
                                                    <a href="{{ $host }}/payroll/{{ $run->id }}/{{ $item->id }}/payslip"
                                                       class="text-xs font-medium text-accent hover:underline">Payslip</a>
                                                    @endcan
                                                    @if($item->payment_status !== 'paid')
                                                    @can('payroll.create')
                                                    <button type="button" x-show="!paying" @click="paying = true"
                                                            class="text-xs font-medium text-text-muted hover:text-accent transition-colors">
                                                        Mark Paid
                                                    </button>
                                                    <div x-show="paying" class="w-52 bg-surface border border-border rounded-xl p-3 shadow-card text-left mt-1">
                                                        <form action="{{ $host }}/payroll/{{ $run->id }}/items/{{ $item->id }}/pay"
                                                              method="POST" class="flex flex-col gap-2">
                                                            @csrf
                                                            @method('PATCH')
                                                            <div>
                                                                <label class="block text-xs text-text-muted mb-1">Method</label>
                                                                <select name="payment_method" x-model="payMethod"
                                                                        class="w-full px-2 py-1.5 text-xs bg-surface border border-border rounded-lg focus:outline-none focus:ring-1 focus:ring-accent/30 focus:border-accent">
                                                                    <option value="bank_transfer">Bank Transfer</option>
                                                                    <option value="mobile_money">Mobile Money</option>
                                                                    <option value="cash">Cash</option>
                                                                </select>
                                                            </div>
                                                            <div>
                                                                <label class="block text-xs text-text-muted mb-1">Date Paid</label>
                                                                <input type="date" name="paid_at" x-model="payDate"
                                                                       class="w-full px-2 py-1.5 text-xs bg-surface border border-border rounded-lg focus:outline-none focus:ring-1 focus:ring-accent/30 focus:border-accent">
                                                            </div>
                                                            <div class="flex gap-2 pt-1">
                                                                <button type="submit"
                                                                        class="flex-1 px-2 py-1 text-xs font-medium bg-accent text-accent-foreground rounded-md hover:bg-accent-dark transition-colors">
                                                                    Confirm
                                                                </button>
                                                                <button type="button" @click="paying = false"
                                                                        class="px-2 py-1 text-xs font-medium text-text-muted hover:text-text-primary transition-colors">
                                                                    Cancel
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                    @endcan
                                                    @else
                                                    <span class="text-xs text-text-muted">
                                                        {{ $item->paid_at?->format('d M Y') }}
                                                    </span>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                </div>

                                {{-- Remittance Summary --}}
                                @php
                                    $run->items->loadMissing('staff');
                                @endphp
                                <div class="mx-4 my-4 bg-surface border border-border rounded-xl p-4">
                                    <p class="text-xs font-semibold text-text-muted uppercase tracking-wide mb-3">Remittance Summary</p>
                                    <div class="grid grid-cols-2 gap-x-8 gap-y-2 text-sm md:grid-cols-3 lg:grid-cols-5">
                                        <div>
                                            <p class="text-xs text-text-muted">Net Disbursement</p>
                                            <p class="font-semibold text-text-primary">{{ format_money($run->total_net, $currencySymbol) }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-text-muted">SSNIT (Emp. 5.5%)</p>
                                            <p class="font-semibold text-error">{{ format_money($run->total_ssnit_employee, $currencySymbol) }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-text-muted">Tier 2 (Emp. 5%)</p>
                                            <p class="font-semibold text-error">{{ format_money($run->total_tier2_employee, $currencySymbol) }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-text-muted">PAYE → GRA</p>
                                            <p class="font-semibold text-error">{{ format_money($run->total_paye, $currencySymbol) }}</p>
                                        </div>
                                        <div class="col-span-2 md:col-span-1">
                                            <p class="text-xs text-text-muted">Employer Liability</p>
                                            <p class="font-semibold text-text-muted">
                                                {{ format_money($run->total_ssnit_employer + $run->total_tier2_employer, $currencySymbol) }}
                                                <span class="font-normal text-xs">(SSNIT 13% + Tier2 5%)</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($runs->hasPages())
        <div class="mt-4">{{ $runs->links() }}</div>
        @endif
        @endif
    </div>

    {{-- ====================== Edit Salary Structure Modal ====================== --}}
    <div x-show="editModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         @keydown.escape.window="editModal = false">
        <div class="absolute inset-0 bg-black/50" @click="editModal = false"></div>
        <div class="relative bg-surface rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto" @click.stop>

            <div class="px-6 py-5 border-b border-border flex items-center justify-between">
                <h2 class="text-base font-semibold text-text-primary"
                    x-text="'Salary Structure — ' + editStaffName"></h2>
                <button type="button" @click="editModal = false" class="text-text-muted hover:text-text-primary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form :action="`{{ $host }}/payroll/salary/${editStaffId}`" method="POST"
                  class="px-6 py-5 flex flex-col gap-5">
                @csrf
                @method('PATCH')

                <div>
                    <label class="block text-xs font-medium text-text-muted mb-1.5">Gross Basic Salary</label>
                    <input type="number" name="gross" x-model="editForm.gross" min="0" step="0.01"
                           class="w-full px-3 py-2 text-sm bg-surface border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent">
                </div>

                <div>
                    <label class="block text-xs font-medium text-text-muted mb-1.5">Effective From <span class="text-text-muted font-normal">(optional)</span></label>
                    <input type="date" name="effective_from" x-model="editForm.effective_from"
                           class="w-full px-3 py-2 text-sm bg-surface border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent">
                </div>

                <div>
                    <p class="text-xs font-semibold text-success uppercase tracking-wide mb-2">Allowances (+)</p>
                    <div class="grid grid-cols-2 gap-3">
                        @foreach(['housing' => 'Housing', 'transport' => 'Transport', 'medical' => 'Medical', 'other' => 'Other'] as $key => $label)
                        <div>
                            <label class="block text-xs text-text-muted mb-1">{{ $label }}</label>
                            <input type="number" name="allowances[{{ $key }}]"
                                   x-model="editForm.allowances.{{ $key }}" min="0" step="0.01"
                                   class="w-full px-3 py-2 text-sm bg-surface border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent">
                        </div>
                        @endforeach
                    </div>
                </div>

                <div>
                    <p class="text-xs font-semibold text-error uppercase tracking-wide mb-2">Deductions (−)</p>
                    <div class="grid grid-cols-2 gap-3">
                        @foreach(['tax' => 'Tax', 'pension' => 'Pension', 'loan' => 'Loan', 'other' => 'Other'] as $key => $label)
                        <div>
                            <label class="block text-xs text-text-muted mb-1">{{ $label }}</label>
                            <input type="number" name="deductions[{{ $key }}]"
                                   x-model="editForm.deductions.{{ $key }}" min="0" step="0.01"
                                   class="w-full px-3 py-2 text-sm bg-surface border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent">
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2 border-t border-border">
                    <button type="button" @click="editModal = false"
                            class="px-4 py-2 text-sm font-medium text-text-muted hover:text-text-primary transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium bg-accent text-accent-foreground rounded-md hover:bg-accent-dark transition-colors">
                        Save Structure
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ====================== Run Payroll Modal ====================== --}}
    <div x-show="runModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         @keydown.escape.window="runModal = false">
        <div class="absolute inset-0 bg-black/50" @click="runModal = false"></div>
        <div class="relative bg-surface rounded-2xl shadow-xl w-full max-w-sm" @click.stop>

            <div class="px-6 py-5 border-b border-border flex items-center justify-between">
                <h2 class="text-base font-semibold text-text-primary">Run Payroll</h2>
                <button type="button" @click="runModal = false" class="text-text-muted hover:text-text-primary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form action="{{ $host }}/payroll/run" method="POST" class="px-6 py-5 flex flex-col gap-4">
                @csrf
                <p class="text-sm text-text-muted">
                    All active staff with configured salary structures will be included.
                </p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-text-muted mb-1.5">Month</label>
                        <select name="month" x-model="runMonth"
                                class="w-full px-3 py-2 text-sm bg-surface border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent">
                            @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}">{{ \Carbon\Carbon::createFromDate(null, $m, 1)->format('F') }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-text-muted mb-1.5">Year</label>
                        <select name="year" x-model="runYear"
                                class="w-full px-3 py-2 text-sm bg-surface border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent">
                            @foreach(range(now()->year - 2, now()->year + 1) as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2 border-t border-border">
                    <button type="button" @click="runModal = false"
                            class="px-4 py-2 text-sm font-medium text-text-muted hover:text-text-primary transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium bg-accent text-accent-foreground rounded-md hover:bg-accent-dark transition-colors">
                        Process Payroll
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
