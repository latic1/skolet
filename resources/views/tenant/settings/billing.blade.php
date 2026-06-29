@extends('layouts.tenant')

@section('title', 'Settings &mdash; Billing')
@section('page-title', 'Settings')

@section('content')
@php $host = request()->getSchemeAndHttpHost(); @endphp

<div class="flex flex-col gap-6">

    @include('partials.settings-tabs')

    {{-- Subscription Status --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card p-6">
        <h2 class="font-semibold text-text-darkest mb-4">Subscription Status</h2>
        @if ($plan)
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-surface-secondary rounded-xl p-4">
                    <p class="text-xs font-medium text-text-muted uppercase tracking-wide mb-2">Plan Status</p>
                    @if ($plan->status === 'active')
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-success-lightest text-success-foreground">Active</span>
                    @elseif ($plan->status === 'expired')
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-error-light text-error">Expired</span>
                    @else
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-surface-secondary text-text-muted border border-border">Trial</span>
                    @endif
                </div>
                <div class="bg-surface-secondary rounded-xl p-4">
                    <p class="text-xs font-medium text-text-muted uppercase tracking-wide mb-2">Payment</p>
                    @if ($plan->payment_status === 'paid')
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-success-lightest text-success-foreground">Paid</span>
                    @else
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-error-light text-error">Unpaid</span>
                    @endif
                </div>
                <div class="bg-surface-secondary rounded-xl p-4">
                    <p class="text-xs font-medium text-text-muted uppercase tracking-wide mb-1">Cycle Period</p>
                    <p class="text-sm font-medium text-text-primary">
                        {{ $plan->cycle_start?->format('d M Y') ?? '&mdash;' }}
                    </p>
                    <p class="text-xs text-text-muted">
                        to {{ $plan->cycle_end?->format('d M Y') ?? '&mdash;' }}
                    </p>
                </div>
                <div class="bg-surface-secondary rounded-xl p-4">
                    <p class="text-xs font-medium text-text-muted uppercase tracking-wide mb-1">Amount Due</p>
                    <p class="text-lg font-bold {{ $plan->payment_status === 'unpaid' ? 'text-error' : 'text-text-darkest' }}">
                        GHS {{ number_format((float) $plan->amount_due, 2) }}
                    </p>
                    <p class="text-xs text-text-muted mt-0.5">
                        GHS {{ number_format((float) $plan->rate_per_student, 2) }} &times; {{ number_format($plan->student_count ?? 0) }} active students
                    </p>
                    @if($plan->student_count_synced_at)
                    <p class="text-[10px] text-text-muted mt-1">Updated {{ $plan->student_count_synced_at->diffForHumans() }}</p>
                    @endif
                </div>
            </div>

            @if ($plan->payment_status === 'unpaid')
                <div class="mt-4 bg-warning-light border border-warning/20 rounded-xl px-4 py-3 text-sm text-warning">
                    Your subscription payment is outstanding. Please contact Skolet support to arrange payment.
                </div>
            @endif
        @else
            <p class="text-sm text-text-muted">No subscription plan on file. Contact Skolet support for assistance.</p>
        @endif
    </div>

    {{-- Payment History --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card overflow-hidden">
        <div class="px-6 py-4 border-b border-border">
            <h2 class="font-semibold text-text-darkest">Payment History</h2>
            <p class="text-xs text-text-muted mt-0.5">{{ $payments->count() }} {{ Str::plural('payment', $payments->count()) }} on record</p>
        </div>

        @if ($payments->isEmpty())
            <div class="flex flex-col items-center justify-center py-16">
                <div class="w-12 h-12 rounded-xl bg-accent-muted flex items-center justify-center mb-3">
                    <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-text-dark">No payments recorded</p>
                <p class="text-xs text-text-muted mt-1">Payment records will appear here once processed by Skolet.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm" style="min-width: 600px">
                    <thead>
                        <tr class="border-b border-border bg-surface-secondary">
                            <th class="px-5 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wide">Date</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wide">Amount</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wide">Cycle Period</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wide">Reference</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wide">Invoice</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach ($payments as $payment)
                        <tr class="hover:bg-surface-secondary/50 transition-colors">
                            <td class="px-5 py-4 text-text-secondary">
                                {{ $payment->created_at->format('d M Y') }}
                            </td>
                            <td class="px-5 py-4 font-semibold text-text-darkest">
                                GHS {{ number_format((float) $payment->amount, 2) }}
                            </td>
                            <td class="px-5 py-4 text-text-secondary">
                                {{ $payment->cycle_start->format('d M Y') }} &ndash; {{ $payment->cycle_end->format('d M Y') }}
                            </td>
                            <td class="px-5 py-4">
                                @if ($payment->payment_reference)
                                    <span class="font-mono text-xs text-text-primary">{{ $payment->payment_reference }}</span>
                                @else
                                    <span class="text-text-muted">&mdash;</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <a href="{{ $host }}/settings/billing/invoices/{{ $payment->id }}"
                                   class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md border border-accent/40 text-accent bg-accent-muted hover:bg-accent/20 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Download PDF
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection
