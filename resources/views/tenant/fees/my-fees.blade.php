@extends('layouts.tenant')

@section('title', 'My Fees')
@section('page-title', 'My Fees')

@section('content')
@php $host = request()->getSchemeAndHttpHost(); @endphp
<div class="flex flex-col gap-6">

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="bg-success-lightest border border-success-light text-success-foreground text-sm px-4 py-3 rounded-xl flex items-start gap-3">
        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="bg-error-light border border-error text-error text-sm px-4 py-3 rounded-xl flex items-start gap-3">
        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif
    @if(session('info'))
    <div class="bg-info-lightest border border-info-light text-info-foreground text-sm px-4 py-3 rounded-xl flex items-start gap-3">
        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
        </svg>
        <span>{{ session('info') }}</span>
    </div>
    @endif

    @if(!$student)
    {{-- No student record linked to this user --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card">
        <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
            <div class="w-12 h-12 rounded-xl bg-surface-secondary flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-text-primary mb-1">No student record linked</p>
            <p class="text-xs text-text-muted">Your account is not linked to a student profile. Please contact your school administrator.</p>
        </div>
    </div>
    @else

    {{-- Student summary header --}}
    @php
        $totalOwed        = collect($feeItems)->sum(fn($i) => (float) $i['fee_structure']->amount);
        $totalPaid        = collect($feeItems)->sum('paid_amount');
        $totalOutstanding = collect($feeItems)->sum('outstanding');
    @endphp

    <div class="bg-surface border border-border rounded-2xl shadow-card p-6">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-accent-muted flex items-center justify-center shrink-0">
                <span class="text-lg font-semibold text-accent">{{ strtoupper(substr($student->full_name, 0, 1)) }}</span>
            </div>
            <div class="flex-1 min-w-0">
                <h2 class="text-base font-semibold text-text-primary">{{ $student->full_name }}</h2>
                <p class="text-xs text-text-muted mt-0.5">
                    {{ $student->admission_no }}
                    @if($student->schoolClass) &middot; {{ $student->schoolClass->name }}@endif
                    @if($student->section) &middot; {{ $student->section->name }}@endif
                    @if($currentTerm) &middot; {{ $currentTerm->name }}{{ $currentTerm->academicYear ? ' &middot; ' . $currentTerm->academicYear->name : '' }}@endif
                </p>
            </div>
        </div>

        {{-- Summary stats --}}
        <div class="grid grid-cols-3 gap-3 mt-5 pt-5 border-t border-border">
            <div class="text-center">
                <p class="text-xs text-text-muted mb-1">Total Owed</p>
                <p class="text-base font-semibold text-text-primary">{{ format_money($totalOwed, $currencySymbol) }}</p>
            </div>
            <div class="text-center border-x border-border">
                <p class="text-xs text-text-muted mb-1">Paid</p>
                <p class="text-base font-semibold text-success-foreground">{{ format_money($totalPaid, $currencySymbol) }}</p>
            </div>
            <div class="text-center">
                <p class="text-xs text-text-muted mb-1">Outstanding</p>
                <p class="text-base font-semibold {{ $totalOutstanding > 0 ? 'text-error' : 'text-text-muted' }}">{{ format_money($totalOutstanding, $currencySymbol) }}</p>
            </div>
        </div>
    </div>

    {{-- Fee items table --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card">
        <div class="px-6 py-4 border-b border-border">
            <h3 class="text-base font-semibold text-text-primary">Fee Items</h3>
            @if($currentTerm)
            <p class="text-xs text-text-muted mt-0.5">{{ $currentTerm->name }}{{ $currentTerm->academicYear ? ' &middot; ' . $currentTerm->academicYear->name : '' }}</p>
            @endif
        </div>

        @if(empty($feeItems))
        <div class="flex flex-col items-center justify-center py-12 px-6 text-center">
            <div class="w-10 h-10 rounded-xl bg-surface-secondary flex items-center justify-center mb-3">
                <svg class="w-5 h-5 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-text-primary mb-1">No fee items found</p>
            <p class="text-xs text-text-muted">No fee structures have been set up for your class yet.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full" style="min-width: 620px">
                <thead>
                    <tr class="border-b border-border">
                        <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Fee Item</th>
                        <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary hidden sm:table-cell">Term</th>
                        <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Amount</th>
                        <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary hidden md:table-cell">Paid</th>
                        <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary hidden md:table-cell">Outstanding</th>
                        <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Status</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($feeItems as $item)
                    @php
                        $fs           = $item['fee_structure'];
                        $status       = $item['status'];
                        $outstanding  = $item['outstanding'];
                        $paidAmount   = $item['paid_amount'];
                        $payments     = $item['payments'];
                        $isOverdue    = $status === 'overdue';
                        $canPayOnline = $outstanding > 0 && in_array($status, ['unpaid', 'partial', 'overdue']);
                        $badgeClass   = match($status) {
                            'paid'    => 'bg-success-lightest text-success-foreground',
                            'partial' => 'bg-warning-light text-warning',
                            default   => 'bg-error-light text-error',
                        };
                        $rowClass = $isOverdue ? 'border-l-2 border-error bg-error-light/30' : '';
                    @endphp
                    <tr class="border-b border-border last:border-b-0 hover:bg-surface-secondary transition-colors {{ $rowClass }}">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                @if($isOverdue)
                                <svg class="w-3.5 h-3.5 text-error shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                @endif
                                <div>
                                    <p class="text-sm font-medium text-text-primary">{{ $fs->fee_item }}</p>
                                    @if($fs->due_date)
                                    <p class="text-xs {{ $isOverdue ? 'text-error font-medium' : 'text-text-muted' }} mt-0.5">
                                        Due {{ $fs->due_date->format('M j, Y') }}
                                        @if($isOverdue) &middot; Overdue @endif
                                    </p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-text-primary hidden sm:table-cell">{{ $fs->term?->name ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm font-medium text-text-primary">{{ format_money((float) $fs->amount, $currencySymbol) }}</td>
                        <td class="px-6 py-4 text-sm text-success-foreground font-medium hidden md:table-cell">{{ format_money($paidAmount, $currencySymbol) }}</td>
                        <td class="px-6 py-4 text-sm font-medium hidden md:table-cell {{ $outstanding > 0 ? 'text-error' : 'text-text-muted' }}">
                            {{ format_money($outstanding, $currencySymbol) }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">
                                {{ ucfirst($status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col items-start gap-1.5">
                                @if($canPayOnline)
                                <form method="POST" action="{{ $host }}/paystack/checkout">
                                    @csrf
                                    <input type="hidden" name="student_id" value="{{ $student->id }}">
                                    <input type="hidden" name="fee_structure_id" value="{{ $fs->id }}">
                                    <button type="submit"
                                            class="flex items-center gap-1.5 text-xs font-medium text-accent hover:text-accent-dark transition-colors px-2 py-1 rounded hover:bg-accent-muted whitespace-nowrap">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                        </svg>
                                        Pay Now
                                    </button>
                                </form>
                                @endif
                                @foreach($payments as $pmt)
                                <a href="{{ $host }}/fees/receipt/{{ $pmt->id }}"
                                   class="inline-flex items-center gap-1 text-xs font-medium text-text-secondary hover:text-text-primary transition-colors px-2 py-1 rounded hover:bg-surface-secondary whitespace-nowrap">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Receipt
                                </a>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-border bg-surface-secondary">
                        <td class="px-6 py-3 text-xs font-medium text-text-secondary uppercase tracking-wide hidden sm:table-cell">Total</td>
                        <td class="px-6 py-3 text-xs font-medium text-text-secondary uppercase tracking-wide sm:hidden">Total</td>
                        <td class="hidden sm:table-cell"></td>
                        <td class="px-6 py-3 text-sm font-semibold text-text-primary">{{ format_money($totalOwed, $currencySymbol) }}</td>
                        <td class="px-6 py-3 text-sm font-semibold text-success-foreground hidden md:table-cell">{{ format_money($totalPaid, $currencySymbol) }}</td>
                        <td class="px-6 py-3 text-sm font-semibold hidden md:table-cell {{ $totalOutstanding > 0 ? 'text-error' : 'text-text-muted' }}">{{ format_money($totalOutstanding, $currencySymbol) }}</td>
                        <td class="px-6 py-3"></td>
                        <td class="px-6 py-3"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif
    </div>

    @php
        $overdueItems = collect($feeItems)->filter(fn($i) => $i['status'] === 'overdue');
    @endphp
    @if($overdueItems->isNotEmpty())
    {{-- Overdue warning banner --}}
    <div class="bg-error-light border border-error rounded-2xl px-5 py-4 flex items-start gap-3">
        <svg class="w-4 h-4 text-error shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        <p class="text-sm text-error">
            You have <strong>{{ $overdueItems->count() }} overdue {{ Str::plural('fee item', $overdueItems->count()) }}</strong> with a total outstanding of <strong>{{ format_money($overdueItems->sum('outstanding'), $currencySymbol) }}</strong>.
            Please pay immediately to avoid penalties.
        </p>
    </div>
    @elseif($totalOutstanding > 0)
    {{-- Outstanding balance banner with Pay Now instructions --}}
    <div class="bg-accent-muted border border-accent-light rounded-2xl px-5 py-4 flex items-start gap-3">
        <svg class="w-4 h-4 text-accent shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-sm text-accent">
            You have an outstanding balance of <strong>{{ format_money($totalOutstanding, $currencySymbol) }}</strong>.
            Click <strong>Pay Now</strong> on any outstanding item to pay online via Paystack.
        </p>
    </div>
    @endif

    @endif
</div>
@endsection
