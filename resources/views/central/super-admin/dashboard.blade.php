<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Super Admin Dashboard &mdash; Skolet</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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

    {{-- Page header --}}
    <div class="mb-6 flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-darkest">Tenant & Subscription Management</h1>
            <p class="text-sm text-text-muted mt-1">Manage school accounts, billing rates, and payment status.</p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('super-admin.audit-log') }}"
               class="flex items-center gap-2 px-4 py-2 text-sm font-medium bg-surface border border-border text-text-secondary rounded-lg hover:bg-surface-secondary hover:text-text-primary transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Audit Log
            </a>
            <a href="{{ route('super-admin.broadcasts') }}"
               class="flex items-center gap-2 px-4 py-2 text-sm font-medium bg-accent text-accent-foreground rounded-lg hover:bg-accent-dark transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                </svg>
                Broadcasts
            </a>
        <form method="POST" action="{{ route('super-admin.sync-students') }}">
            @csrf
            <button type="submit"
                    class="flex items-center gap-2 px-4 py-2 text-sm font-medium bg-surface border border-border text-text-secondary rounded-lg hover:bg-surface-secondary hover:text-text-primary transition-colors shrink-0"
                    onclick="return confirm('Sync student counts now? This may take a moment.')">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Sync Students
            </button>
        </form>
        </div>
    </div>

    <div x-data="superAdminPage()" x-init="init()">

    {{-- Tab switcher --}}
    <div class="flex gap-1 border-b border-border mb-6">
        <button @click="setTab('schools')"
                :class="tab === 'schools' ? 'border-b-2 border-accent text-accent' : 'border-b-2 border-transparent text-text-secondary hover:text-text-primary'"
                class="px-4 py-2.5 text-sm font-medium -mb-px transition-colors">
            Schools
        </button>
        <button @click="setTab('analytics')"
                :class="tab === 'analytics' ? 'border-b-2 border-accent text-accent' : 'border-b-2 border-transparent text-text-secondary hover:text-text-primary'"
                class="px-4 py-2.5 text-sm font-medium -mb-px transition-colors">
            Analytics
        </button>
    </div>

    {{-- Schools Tab --}}
    <div x-show="tab === 'schools'">

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-surface border border-border rounded-xl p-4">
            <p class="text-xs font-medium text-text-muted uppercase tracking-wide">Total Schools</p>
            <p class="text-2xl font-bold text-text-darkest mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-surface border border-border rounded-xl p-4">
            <p class="text-xs font-medium text-text-muted uppercase tracking-wide">Active Subscriptions</p>
            <p class="text-2xl font-bold text-success mt-1">{{ $stats['active'] }}</p>
        </div>
        <div class="bg-surface border border-border rounded-xl p-4">
            <p class="text-xs font-medium text-text-muted uppercase tracking-wide">On Trial</p>
            <p class="text-2xl font-bold text-text-darkest mt-1">{{ $stats['trial'] }}</p>
        </div>
        <div class="bg-surface border border-border rounded-xl p-4">
            <p class="text-xs font-medium text-text-muted uppercase tracking-wide">Total Amount Due (Unpaid)</p>
            <p class="text-2xl font-bold text-error mt-1">GHS {{ number_format((float) $stats['total_amount_due'], 2) }}</p>
        </div>
    </div>

    {{-- Tenants table --}}
    <div class="bg-surface border border-border rounded-xl overflow-hidden">

        <div class="px-6 py-4 border-b border-border flex flex-wrap items-center gap-4">
            <div class="flex-1">
                <h2 class="font-semibold text-text-darkest">Schools</h2>
                <span class="text-sm text-text-muted">{{ $tenants->count() }} total</span>
            </div>
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" x-model="search" placeholder="Search schools&hellip;"
                       class="pl-9 pr-3 py-2 text-sm bg-surface-secondary border border-border rounded-lg focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors w-56">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm" style="min-width: 1000px">
                <thead>
                    <tr class="border-b border-border bg-surface-secondary">
                        <th class="px-4 py-3 text-left font-medium text-text-muted text-xs uppercase tracking-wide">School</th>
                        <th class="px-4 py-3 text-left font-medium text-text-muted text-xs uppercase tracking-wide">Students</th>
                        <th class="px-4 py-3 text-left font-medium text-text-muted text-xs uppercase tracking-wide">Rate / Student</th>
                        <th class="px-4 py-3 text-left font-medium text-text-muted text-xs uppercase tracking-wide">Amount Due</th>
                        <th class="px-4 py-3 text-left font-medium text-text-muted text-xs uppercase tracking-wide">Payment</th>
                        <th class="px-4 py-3 text-left font-medium text-text-muted text-xs uppercase tracking-wide">Cycle End</th>
                        <th class="px-4 py-3 text-left font-medium text-text-muted text-xs uppercase tracking-wide">Sub Status</th>
                        <th class="px-4 py-3 text-left font-medium text-text-muted text-xs uppercase tracking-wide">Account</th>
                        <th class="px-4 py-3 text-left font-medium text-text-muted text-xs uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($tenants as $tenant)
                    @php
                        $plan    = $tenant->subscriptionPlan;
                        $domain  = $tenant->domains->first()?->domain ?? $tenant->subdomain . '.skolet.com';
                    @endphp
                    <tr class="hover:bg-surface-secondary/50 transition-colors"
                        x-show="matchSearch('{{ addslashes(strtolower($tenant->name)) }}', '{{ addslashes(strtolower($domain)) }}')"
                        x-cloak>

                        {{-- School --}}
                        <td class="px-4 py-3">
                            <div class="font-medium text-text-darkest">{{ $tenant->name }}</div>
                            <div class="text-xs text-text-muted mt-0.5">
                                <a href="http://{{ $domain }}" target="_blank"
                                   class="hover:text-accent transition-colors">{{ $domain }}</a>
                            </div>
                            <div class="text-xs text-text-muted">
                                Joined {{ $tenant->created_at->format('d M Y') }}
                            </div>
                        </td>

                        {{-- Student count --}}
                        <td class="px-4 py-3">
                            <div class="font-medium text-text-primary">{{ number_format($plan?->student_count ?? 0) }}</div>
                            @if ($plan?->student_count_synced_at)
                                <div class="text-xs text-text-muted mt-0.5" title="{{ $plan->student_count_synced_at->format('d M Y H:i') }}">
                                    Synced {{ $plan->student_count_synced_at->diffForHumans() }}
                                </div>
                            @else
                                <div class="text-xs text-text-muted mt-0.5">Not synced</div>
                            @endif
                        </td>

                        {{-- Rate per student (editable) --}}
                        <td class="px-4 py-3">
                            <button @click="openRate({{ json_encode(['id' => $tenant->id, 'name' => $tenant->name, 'rate' => $plan?->rate_per_student ?? 0]) }})"
                                    class="group flex items-center gap-1 text-text-primary hover:text-accent transition-colors">
                                <span class="font-medium">GHS {{ number_format((float) ($plan?->rate_per_student ?? 0), 2) }}</span>
                                <svg class="w-3.5 h-3.5 text-text-muted group-hover:text-accent transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828a2 2 0 01-2.828 0L9 13z"/>
                                </svg>
                            </button>
                        </td>

                        {{-- Amount due --}}
                        <td class="px-4 py-3">
                            <span class="font-medium {{ ($plan?->payment_status ?? 'unpaid') === 'unpaid' ? 'text-error' : 'text-text-primary' }}">
                                GHS {{ number_format((float) ($plan?->amount_due ?? 0), 2) }}
                            </span>
                        </td>

                        {{-- Payment status --}}
                        <td class="px-4 py-3">
                            @if (($plan?->payment_status ?? 'unpaid') === 'paid')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-success-lightest text-success-foreground border border-success-light">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414L8.414 15l-4.121-4.121a1 1 0 111.414-1.414L8.414 12.172l7.879-7.879a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    Paid
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-error-light text-error border border-error/20">
                                    Unpaid
                                </span>
                            @endif
                        </td>

                        {{-- Cycle end --}}
                        <td class="px-4 py-3">
                            @if ($plan?->cycle_end)
                                <span class="{{ $plan->cycle_end->isPast() && ($plan->payment_status === 'unpaid') ? 'text-error font-medium' : 'text-text-primary' }}">
                                    {{ $plan->cycle_end->format('d M Y') }}
                                </span>
                            @else
                                <span class="text-text-muted">&mdash;</span>
                            @endif
                        </td>

                        {{-- Subscription status --}}
                        <td class="px-4 py-3">
                            @php $subStatus = $plan?->status ?? 'trial' @endphp
                            @if ($subStatus === 'active')
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-success-lightest text-success-foreground">Active</span>
                            @elseif ($subStatus === 'expired')
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-error-light text-error">Expired</span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-surface-secondary text-text-muted">Trial</span>
                            @endif
                        </td>

                        {{-- Account status (tenant.status) --}}
                        <td class="px-4 py-3">
                            @if ($tenant->status === 'active')
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-success-lightest text-success-foreground">Enabled</span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-warning-light text-warning">Suspended</span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-1 flex-wrap">

                                {{-- Impersonate &mdash; start a Super Admin support session as this school's admin --}}
                                <form method="POST" action="{{ route('super-admin.tenants.impersonate', $tenant) }}">
                                    @csrf
                                    <button type="submit"
                                            class="px-2.5 py-1 text-xs font-medium rounded-md border border-accent/40 text-accent bg-accent-muted hover:bg-accent/20 transition-colors">
                                        Impersonate
                                    </button>
                                </form>

                                {{-- Enable / Disable toggle --}}
                                <form method="POST" action="{{ route('super-admin.tenants.toggle', $tenant) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit"
                                            class="px-2.5 py-1 text-xs font-medium rounded-md border transition-colors
                                                   {{ $tenant->status === 'active'
                                                        ? 'border-warning/40 text-warning bg-warning-light/40 hover:bg-warning-light'
                                                        : 'border-success-light text-success-foreground bg-success-lightest hover:bg-success-lightest/70' }}">
                                        {{ $tenant->status === 'active' ? 'Suspend' : 'Enable' }}
                                    </button>
                                </form>

                                {{-- Payment History --}}
                                <a href="{{ route('super-admin.tenants.detail', $tenant) }}"
                                   class="px-2.5 py-1 text-xs font-medium rounded-md border border-border text-text-secondary bg-surface hover:bg-surface-secondary transition-colors">
                                    Payments
                                </a>

                                {{-- Mark Paid --}}
                                @if (($plan?->payment_status ?? 'unpaid') !== 'paid')
                                    <button @click="openMarkPaid({{ json_encode(['id' => $tenant->id, 'name' => $tenant->name, 'cycle_start' => $plan?->cycle_start?->format('Y-m-d'), 'cycle_end' => $plan?->cycle_end?->format('Y-m-d'), 'amount' => $plan?->amount_due]) }})"
                                            class="px-2.5 py-1 text-xs font-medium rounded-md border border-success-light text-success-foreground bg-success-lightest hover:bg-success-lightest/70 transition-colors">
                                        Mark Paid
                                    </button>
                                @else
                                    {{-- Mark Unpaid --}}
                                    <form method="POST" action="{{ route('super-admin.tenants.mark-unpaid', $tenant) }}">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                                onclick="return confirm('Mark {{ addslashes($tenant->name) }} as unpaid?')"
                                                class="px-2.5 py-1 text-xs font-medium rounded-md border border-border text-text-secondary bg-surface hover:bg-surface-secondary transition-colors">
                                            Mark Unpaid
                                        </button>
                                    </form>
                                @endif

                                {{-- Delete --}}
                                <form method="POST" action="{{ route('super-admin.tenants.destroy', $tenant) }}">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            onclick="return confirm('⚠️ PERMANENTLY DELETE {{ addslashes($tenant->name) }}?\n\nThis will drop the school\'s database and remove all data. This cannot be undone.')"
                                            class="px-2.5 py-1 text-xs font-medium rounded-md border border-error/30 text-error bg-error-light/40 hover:bg-error-light transition-colors">
                                        Delete
                                    </button>
                                </form>

                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-16 text-center text-text-muted">
                            <svg class="w-10 h-10 mx-auto mb-3 text-border" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <p class="font-medium text-text-dark">No schools registered yet</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Edit Rate Modal --}}
        <div x-show="rateModal.open" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="bg-surface rounded-2xl shadow-xl border border-border w-full max-w-sm p-6"
                 @click.outside="rateModal.open = false">
                <h3 class="font-semibold text-text-darkest mb-1">Edit Rate per Student</h3>
                <p class="text-sm text-text-muted mb-5" x-text="rateModal.tenantName"></p>
                <form method="POST" :action="'/super-admin/tenants/' + rateModal.tenantId + '/rate'">
                    @csrf
                    <input type="hidden" name="_method" value="PATCH">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-text-dark mb-1.5">Rate per Student (GHS / year)</label>
                        <input type="number" name="rate_per_student" step="0.01" min="0"
                               x-model="rateModal.rate"
                               class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors"
                               placeholder="5.00" required>
                        <p class="text-xs text-text-muted mt-1">Amount due = rate &times; current student count</p>
                    </div>
                    <div class="flex gap-3 justify-end">
                        <button type="button" @click="rateModal.open = false"
                                class="px-4 py-2 text-sm font-medium text-text-secondary bg-surface border border-border rounded-md hover:bg-surface-secondary transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 text-sm font-medium bg-accent text-accent-foreground rounded-md hover:bg-accent-dark transition-colors">
                            Save Rate
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Mark Paid Modal --}}
        <div x-show="paidModal.open" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="bg-surface rounded-2xl shadow-xl border border-border w-full max-w-sm p-6"
                 @click.outside="paidModal.open = false">
                <h3 class="font-semibold text-text-darkest mb-1">Confirm Payment Received</h3>
                <p class="text-sm text-text-muted mb-5" x-text="paidModal.tenantName"></p>
                <form method="POST" :action="'/super-admin/tenants/' + paidModal.tenantId + '/mark-paid'">
                    @csrf
                    <input type="hidden" name="_method" value="PATCH">
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div>
                            <label class="block text-sm font-medium text-text-dark mb-1.5">Cycle Start</label>
                            <input type="date" name="cycle_start" x-model="paidModal.cycleStart" required
                                   class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-dark mb-1.5">Cycle End</label>
                            <input type="date" name="cycle_end" x-model="paidModal.cycleEnd" required
                                   class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-text-dark mb-1.5">
                            Reference <span class="text-text-muted font-normal">(optional)</span>
                        </label>
                        <input type="text" name="payment_reference" maxlength="255"
                               placeholder="e.g. bank transfer ref, receipt no."
                               class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-text-dark mb-1.5">
                            Notes <span class="text-text-muted font-normal">(optional)</span>
                        </label>
                        <textarea name="notes" rows="2" maxlength="1000"
                                  placeholder="Any additional notes&hellip;"
                                  class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors resize-none"></textarea>
                    </div>
                    <p class="text-xs text-text-muted mb-4">
                        Amount: <strong x-text="paidModal.amount ? 'GHS ' + Number(paidModal.amount).toFixed(2) : '&mdash;'"></strong> &middot;
                        Status will be set to <strong>paid</strong>.
                    </p>
                    <div class="flex gap-3 justify-end">
                        <button type="button" @click="paidModal.open = false"
                                class="px-4 py-2 text-sm font-medium text-text-secondary bg-surface border border-border rounded-md hover:bg-surface-secondary transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 text-sm font-medium bg-success text-white rounded-md hover:opacity-90 transition-opacity">
                            Confirm Paid
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>{{-- /card --}}

    </div>{{-- /schools tab --}}

    {{-- Analytics Tab --}}
    <div x-show="tab === 'analytics'" x-cloak>

        {{-- Last refreshed + Rebuild --}}
        <div class="mb-6 flex items-center justify-between gap-4 flex-wrap">
            <p class="text-xs text-text-muted">
                @if ($analyticsData['computed_at'])
                    Analytics last computed {{ $analyticsData['computed_at']->diffForHumans() }}
                    ({{ $analyticsData['computed_at']->format('d M Y H:i') }})
                @else
                    No analytics data yet. Click <strong>Rebuild</strong> to compute.
                @endif
            </p>
            <form method="POST" action="{{ route('super-admin.analytics.rebuild') }}">
                @csrf
                <button type="submit"
                        class="flex items-center gap-2 px-3 py-1.5 text-xs font-medium bg-surface border border-border text-text-secondary rounded-lg hover:bg-surface-secondary hover:text-text-primary transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Rebuild Analytics
                </button>
            </form>
        </div>

        {{-- KPI Cards --}}
        @php
            $kpi      = $analyticsData['kpi'] ?? [];
            $adoption = $analyticsData['adoption'] ?? [];
        @endphp
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-surface border border-border rounded-xl p-4">
                <p class="text-xs font-medium text-text-muted uppercase tracking-wide">Total Schools</p>
                <p class="text-2xl font-bold text-text-darkest mt-1">{{ $kpi['total_schools'] ?? '&mdash;' }}</p>
            </div>
            <div class="bg-surface border border-border rounded-xl p-4">
                <p class="text-xs font-medium text-text-muted uppercase tracking-wide">Total Students</p>
                <p class="text-2xl font-bold text-text-darkest mt-1">{{ isset($kpi['total_students']) ? number_format((int) $kpi['total_students']) : '&mdash;' }}</p>
            </div>
            <div class="bg-surface border border-border rounded-xl p-4">
                <p class="text-xs font-medium text-text-muted uppercase tracking-wide">MRR (Est.)</p>
                <p class="text-2xl font-bold text-text-darkest mt-1">{{ isset($kpi['mrr']) ? 'GHS ' . number_format((float) $kpi['mrr'], 2) : '&mdash;' }}</p>
                <p class="text-xs text-text-muted mt-0.5">Current month payments</p>
            </div>
            <div class="bg-surface border border-border rounded-xl p-4">
                <p class="text-xs font-medium text-text-muted uppercase tracking-wide">Avg Students / School</p>
                <p class="text-2xl font-bold text-text-darkest mt-1">{{ $kpi['avg_per_school'] ?? '&mdash;' }}</p>
            </div>
        </div>

        {{-- 4 Charts in 2&times;2 grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="bg-surface border border-border rounded-xl p-5">
                <h3 class="text-sm font-semibold text-text-darkest mb-4">New Schools / Month</h3>
                <div style="height:220px;position:relative">
                    <canvas id="chart-new-schools"></canvas>
                </div>
            </div>
            <div class="bg-surface border border-border rounded-xl p-5">
                <h3 class="text-sm font-semibold text-text-darkest mb-4">Platform Student Count</h3>
                <div style="height:220px;position:relative">
                    <canvas id="chart-students"></canvas>
                </div>
            </div>
            <div class="bg-surface border border-border rounded-xl p-5">
                <h3 class="text-sm font-semibold text-text-darkest mb-4">Monthly Revenue (GHS)</h3>
                <div style="height:220px;position:relative">
                    <canvas id="chart-revenue"></canvas>
                </div>
            </div>
            <div class="bg-surface border border-border rounded-xl p-5">
                <h3 class="text-sm font-semibold text-text-darkest mb-4">Subscription Status</h3>
                <div style="height:220px;position:relative">
                    <canvas id="chart-status"></canvas>
                </div>
            </div>
        </div>

        {{-- Feature Adoption Table --}}
        <div class="bg-surface border border-border rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-border">
                <h3 class="font-semibold text-text-darkest">Feature Adoption</h3>
                <p class="text-xs text-text-muted mt-0.5">How many schools actively use each platform feature</p>
            </div>
            @if (!empty($adoption))
            @php
                $total = max(1, (int) ($adoption['total_tenants'] ?? 1));
                $features = [
                    ['name' => 'Payroll',                    'key' => 'payroll'],
                    ['name' => 'REST API (API Tokens)',      'key' => 'api_tokens'],
                    ['name' => 'Online Payments (Paystack)', 'key' => 'paystack'],
                ];
            @endphp
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border bg-surface-secondary">
                        <th class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wide">Feature</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wide">Schools Using It</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wide">Adoption Rate</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach ($features as $f)
                    @php
                        $count = (int) ($adoption[$f['key']] ?? 0);
                        $pct   = round(($count / $total) * 100);
                    @endphp
                    <tr class="hover:bg-surface-secondary/50 transition-colors">
                        <td class="px-6 py-4 font-medium text-text-darkest">{{ $f['name'] }}</td>
                        <td class="px-6 py-4">
                            <span class="font-semibold text-text-darkest">{{ $count }}</span>
                            <span class="text-text-muted"> / {{ $total }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex-1 bg-surface-secondary rounded-full h-2" style="max-width:200px">
                                    <div class="bg-accent h-2 rounded-full" style="width:{{ $pct }}%"></div>
                                </div>
                                <span class="text-sm font-medium text-text-primary w-10">{{ $pct }}%</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <p class="text-sm text-text-muted">No analytics data yet.</p>
                <p class="text-xs text-text-muted mt-1">Click <strong>Rebuild Analytics</strong> above to compute.</p>
            </div>
            @endif
        </div>

    </div>{{-- /analytics tab --}}

    </div>{{-- /superAdminPage wrapper --}}

    <p class="mt-6 text-xs text-text-muted">
        Student counts sync daily via scheduler (<code class="bg-surface-secondary px-1 rounded">skolet:sync-student-counts</code>).
        Use the <strong>Sync Students</strong> button above to force an immediate sync.
    </p>

</main>

<script>
var _analyticsData = {
    newSchools:     @json($analyticsData['new_schools'] ?? []),
    revenue:        @json($analyticsData['revenue'] ?? []),
    status:         @json($analyticsData['status'] ?? []),
    studentHistory: @json($analyticsData['student_history'] ?? []),
};

function initAnalyticsCharts() {
    var gridColor  = '#E7EAF3';
    var tickColor  = '#9CA3AF';
    var gridDash   = [4, 4];
    var noBorder   = { display: false };
    var noLegend   = { display: false };
    var baseScales = function (yCallback) {
        return {
            x: { grid: { display: false }, ticks: { color: tickColor, font: { size: 11 } }, border: noBorder },
            y: { beginAtZero: true, grid: { color: gridColor, borderDash: gridDash }, ticks: { color: tickColor, font: { size: 11 }, callback: yCallback }, border: noBorder }
        };
    };

    // Chart 1: New schools / month (bar)
    var nsEl = document.getElementById('chart-new-schools');
    if (nsEl && _analyticsData.newSchools.length) {
        new Chart(nsEl, {
            type: 'bar',
            data: {
                labels: _analyticsData.newSchools.map(function(d) { return d.month; }),
                datasets: [{ data: _analyticsData.newSchools.map(function(d) { return d.count; }), backgroundColor: '#2563EB', borderRadius: 4, borderSkipped: false }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: noLegend }, scales: baseScales(undefined) }
        });
    }

    // Chart 2: Student count over time (line)
    var stEl = document.getElementById('chart-students');
    if (stEl && _analyticsData.studentHistory.length) {
        new Chart(stEl, {
            type: 'line',
            data: {
                labels: _analyticsData.studentHistory.map(function(d) { return d.month; }),
                datasets: [{
                    data: _analyticsData.studentHistory.map(function(d) { return d.count; }),
                    borderColor: '#06B6D4', borderWidth: 2.5,
                    backgroundColor: 'rgba(6,182,212,0.08)', fill: true,
                    tension: 0.4, pointRadius: 4, pointBackgroundColor: '#06B6D4',
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: noLegend }, scales: baseScales(undefined) }
        });
    }

    // Chart 3: Monthly revenue (bar)
    var revEl = document.getElementById('chart-revenue');
    if (revEl && _analyticsData.revenue.length) {
        new Chart(revEl, {
            type: 'bar',
            data: {
                labels: _analyticsData.revenue.map(function(d) { return d.month; }),
                datasets: [{ data: _analyticsData.revenue.map(function(d) { return d.total; }), backgroundColor: '#10B981', borderRadius: 4, borderSkipped: false }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: noLegend, tooltip: { callbacks: { label: function(c) { return 'GHS ' + Number(c.raw).toLocaleString(); } } } },
                scales: baseScales(function(v) { return 'GHS ' + Number(v).toLocaleString(); })
            }
        });
    }

    // Chart 4: Subscription status (doughnut)
    var stEl2 = document.getElementById('chart-status');
    var s = _analyticsData.status;
    if (stEl2 && s && Object.keys(s).length) {
        new Chart(stEl2, {
            type: 'doughnut',
            data: {
                labels: ['Trial', 'Active', 'Expired', 'Suspended'],
                datasets: [{
                    data: [s.trial || 0, s.active || 0, s.expired || 0, s.suspended || 0],
                    backgroundColor: ['#9CA3AF', '#10B981', '#EF4444', '#F59E0B'],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'right', labels: { color: '#6B7280', font: { size: 12 }, usePointStyle: true, pointStyleWidth: 10 } } }
            }
        });
    }
}

function superAdminPage() {
    return {
        tab: new URLSearchParams(window.location.search).get('tab') || 'schools',
        chartsReady: false,
        search: '',
        rateModal: { open: false, tenantId: null, tenantName: '', rate: 0 },
        paidModal: { open: false, tenantId: null, tenantName: '', cycleStart: '', cycleEnd: '', amount: '' },

        init() {
            if (this.tab === 'analytics') {
                this.chartsReady = true;
                this.$nextTick(function() { initAnalyticsCharts(); });
            }
        },
        setTab(t) {
            this.tab = t;
            if (t === 'analytics' && !this.chartsReady) {
                this.chartsReady = true;
                this.$nextTick(function() { initAnalyticsCharts(); });
            }
        },
        matchSearch(name, domain) {
            if (!this.search.trim()) return true;
            const s = this.search.toLowerCase().trim();
            return name.includes(s) || domain.includes(s);
        },
        openRate(data) {
            this.rateModal = { open: true, tenantId: data.id, tenantName: data.name, rate: data.rate };
        },
        openMarkPaid(data) {
            const today = new Date().toISOString().split('T')[0];
            const nextYear = new Date(new Date().setFullYear(new Date().getFullYear() + 1)).toISOString().split('T')[0];
            this.paidModal = {
                open:       true,
                tenantId:   data.id,
                tenantName: data.name,
                cycleStart: data.cycle_start || today,
                cycleEnd:   data.cycle_end   || nextYear,
                amount:     data.amount,
            };
        },
    };
}
</script>

</body>
</html>
