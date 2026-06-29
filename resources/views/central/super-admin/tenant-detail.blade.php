<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $tenant->name }} &mdash; School Detail &mdash; Skolet Super Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-background text-text-primary">

{{-- Top bar --}}
<header class="bg-surface border-b border-border sticky top-0 z-50">
    <div class="max-w-[1400px] mx-auto px-6 h-14 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
                 style="background: linear-gradient(45deg, #2563eb 0%, #1e3a8a 100%)">
                <svg width="16" height="16" viewBox="0 0 20 20" fill="none">
                    <path d="M10 2L3 6V10C3 13.866 6.134 17 10 17C13.866 17 17 13.866 17 10V6L10 2Z" fill="white" fill-opacity="0.9"/>
                    <path d="M7 10L9 12L13 8" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <span class="font-bold text-text-darkest">Skolet</span>
            <span class="text-border">|</span>
            <span class="text-sm text-text-muted">Super Admin</span>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-sm text-text-secondary hidden sm:inline">{{ Auth::guard('super_admin')->user()->name }}</span>
            <form method="POST" action="{{ route('super-admin.logout') }}">
                @csrf
                <button type="submit" class="text-sm text-text-muted hover:text-error transition-colors">Sign out</button>
            </form>
        </div>
    </div>
</header>

<main class="max-w-[1400px] mx-auto px-6 py-8">

    {{-- Flash --}}
    @if (session('success'))
        <div class="mb-6 bg-success-lightest border border-success-light text-success-foreground text-sm px-4 py-3 rounded-xl flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-6 bg-error-light border border-error text-error text-sm px-4 py-3 rounded-xl flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Nav --}}
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('super-admin.dashboard') }}"
           class="text-sm text-text-muted hover:text-accent transition-colors">← Back to Dashboard</a>
    </div>

    {{-- Page header --}}
    <div class="mb-8 flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-darkest">{{ $tenant->name }}</h1>
            <p class="text-sm text-text-muted mt-1">School detail &mdash; subscription and payment history.</p>
        </div>
    </div>

    <div class="flex flex-col gap-6">

        {{-- Overview --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- School info --}}
            <div class="bg-surface border border-border rounded-2xl shadow-card p-6">
                <h2 class="font-semibold text-text-darkest mb-4">School Info</h2>
                @php $domain = $tenant->domains->first()?->domain ?? $tenant->subdomain . '.skolet.com'; @endphp
                <dl class="space-y-3 text-sm">
                    <div class="flex items-start gap-2">
                        <dt class="w-28 text-text-muted shrink-0">Name</dt>
                        <dd class="font-medium text-text-darkest">{{ $tenant->name }}</dd>
                    </div>
                    <div class="flex items-start gap-2">
                        <dt class="w-28 text-text-muted shrink-0">Domain</dt>
                        <dd>
                            <a href="http://{{ $domain }}" target="_blank"
                               class="text-accent hover:underline">{{ $domain }}</a>
                        </dd>
                    </div>
                    <div class="flex items-start gap-2">
                        <dt class="w-28 text-text-muted shrink-0">Account</dt>
                        <dd>
                            @if ($tenant->status === 'active')
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-success-lightest text-success-foreground">Enabled</span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-warning-light text-warning">Suspended</span>
                            @endif
                        </dd>
                    </div>
                    <div class="flex items-start gap-2">
                        <dt class="w-28 text-text-muted shrink-0">Joined</dt>
                        <dd class="text-text-secondary">{{ $tenant->created_at->format('d M Y') }}</dd>
                    </div>
                    <div class="flex items-start gap-2">
                        <dt class="w-28 text-text-muted shrink-0">Students</dt>
                        <dd class="font-medium text-text-primary">{{ number_format($plan?->student_count ?? 0) }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Subscription info --}}
            <div class="bg-surface border border-border rounded-2xl shadow-card p-6">
                <h2 class="font-semibold text-text-darkest mb-4">Subscription</h2>
                @if ($plan)
                <dl class="space-y-3 text-sm">
                    <div class="flex items-start gap-2">
                        <dt class="w-28 text-text-muted shrink-0">Status</dt>
                        <dd>
                            @if ($plan->status === 'active')
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-success-lightest text-success-foreground">Active</span>
                            @elseif ($plan->status === 'expired')
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-error-light text-error">Expired</span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-surface-secondary text-text-muted">Trial</span>
                            @endif
                        </dd>
                    </div>
                    <div class="flex items-start gap-2">
                        <dt class="w-28 text-text-muted shrink-0">Payment</dt>
                        <dd>
                            @if ($plan->payment_status === 'paid')
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-success-lightest text-success-foreground">Paid</span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-error-light text-error">Unpaid</span>
                            @endif
                        </dd>
                    </div>
                    <div class="flex items-start gap-2">
                        <dt class="w-28 text-text-muted shrink-0">Cycle</dt>
                        <dd class="text-text-secondary">
                            {{ $plan->cycle_start?->format('d M Y') ?? '&mdash;' }} → {{ $plan->cycle_end?->format('d M Y') ?? '&mdash;' }}
                        </dd>
                    </div>
                    <div class="flex items-start gap-2">
                        <dt class="w-28 text-text-muted shrink-0">Rate / Student</dt>
                        <dd class="font-medium text-text-primary">GHS {{ number_format((float) $plan->rate_per_student, 2) }}</dd>
                    </div>
                    <div class="flex items-start gap-2">
                        <dt class="w-28 text-text-muted shrink-0">Amount Due</dt>
                        <dd class="font-semibold {{ $plan->payment_status === 'unpaid' ? 'text-error' : 'text-text-primary' }}">
                            GHS {{ number_format((float) $plan->amount_due, 2) }}
                        </dd>
                    </div>
                </dl>
                @else
                    <p class="text-sm text-text-muted">No subscription plan on file.</p>
                @endif
            </div>
        </div>

        {{-- Mark Paid form (only if currently unpaid) --}}
        @if ($plan && $plan->payment_status !== 'paid')
        <div class="bg-surface border border-border rounded-2xl shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-border">
                <h2 class="font-semibold text-text-darkest">Mark as Paid</h2>
                <p class="text-xs text-text-muted mt-0.5">Record a payment received for this school.</p>
            </div>
            <form method="POST" action="{{ route('super-admin.tenants.mark-paid', $tenant) }}"
                  class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                @csrf
                <input type="hidden" name="_method" value="PATCH">

                <div>
                    <label class="block text-sm font-medium text-text-dark mb-1.5">Cycle Start</label>
                    <input type="date" name="cycle_start"
                           value="{{ old('cycle_start', $plan->cycle_start?->format('Y-m-d')) }}" required
                           class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                </div>
                <div>
                    <label class="block text-sm font-medium text-text-dark mb-1.5">Cycle End</label>
                    <input type="date" name="cycle_end"
                           value="{{ old('cycle_end', $plan->cycle_end?->format('Y-m-d')) }}" required
                           class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                </div>
                <div>
                    <label class="block text-sm font-medium text-text-dark mb-1.5">Reference <span class="text-text-muted font-normal">(optional)</span></label>
                    <input type="text" name="payment_reference" maxlength="255"
                           value="{{ old('payment_reference') }}"
                           placeholder="e.g. bank transfer ref"
                           class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                </div>
                <div>
                    <button type="submit"
                            class="w-full px-4 py-2 text-sm font-medium bg-success text-white rounded-md hover:opacity-90 transition-opacity">
                        Confirm Paid
                    </button>
                </div>

                @if (old('notes'))
                <div class="sm:col-span-2 lg:col-span-4">
                @else
                <div class="sm:col-span-2 lg:col-span-4 hidden" x-data x-init="$el.classList.remove('hidden')">
                @endif
                    <label class="block text-sm font-medium text-text-dark mb-1.5">Notes <span class="text-text-muted font-normal">(optional)</span></label>
                    <textarea name="notes" rows="2" maxlength="1000"
                              placeholder="Any additional notes&hellip;"
                              class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors resize-none">{{ old('notes') }}</textarea>
                </div>
            </form>
        </div>
        @endif

        {{-- Payment History --}}
        <div class="bg-surface border border-border rounded-2xl shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-border flex items-center justify-between">
                <div>
                    <h2 class="font-semibold text-text-darkest">Payment History</h2>
                    <p class="text-xs text-text-muted mt-0.5">{{ $payments->count() }} {{ Str::plural('payment', $payments->count()) }} recorded</p>
                </div>
            </div>

            @if ($payments->isEmpty())
                <div class="flex flex-col items-center justify-center py-16">
                    <div class="w-12 h-12 rounded-xl bg-accent-muted flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-text-dark">No payments recorded yet</p>
                    <p class="text-xs text-text-muted mt-1">Mark this school as paid to create the first payment record.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm" style="min-width: 700px">
                        <thead>
                            <tr class="border-b border-border bg-surface-secondary">
                                <th class="px-5 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wide">Date</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wide">Amount</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wide">Cycle Period</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wide">Reference</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wide">Marked By</th>
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
                                    @if ($payment->notes)
                                        <div class="text-xs text-text-muted mt-0.5 line-clamp-1" title="{{ $payment->notes }}">{{ $payment->notes }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-text-secondary">
                                    {{ $payment->recordedBy?->name ?? '&mdash;' }}
                                </td>
                                <td class="px-5 py-4">
                                    <a href="{{ route('super-admin.tenants.invoices.download', [$tenant, $payment]) }}"
                                       class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md border border-accent/40 text-accent bg-accent-muted hover:bg-accent/20 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        PDF
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

</main>

</body>
</html>
