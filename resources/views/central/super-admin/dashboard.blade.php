<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Super Admin Dashboard — Skolet</title>
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

    {{-- Page header --}}
    <div class="mb-6 flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-darkest">Tenant & Subscription Management</h1>
            <p class="text-sm text-text-muted mt-1">Manage school accounts, billing rates, and payment status.</p>
        </div>
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
    <div class="bg-surface border border-border rounded-xl overflow-hidden" x-data="superAdminPage()">

        <div class="px-6 py-4 border-b border-border flex flex-wrap items-center gap-4">
            <div class="flex-1">
                <h2 class="font-semibold text-text-darkest">Schools</h2>
                <span class="text-sm text-text-muted">{{ $tenants->count() }} total</span>
            </div>
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" x-model="search" placeholder="Search schools…"
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
                                <span class="text-text-muted">—</span>
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

                                {{-- Impersonate — start a Super Admin support session as this school's admin --}}
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

                                {{-- Mark Paid --}}
                                @if (($plan?->payment_status ?? 'unpaid') !== 'paid')
                                    <button @click="openMarkPaid({{ json_encode(['id' => $tenant->id, 'name' => $tenant->name, 'cycle_start' => $plan?->cycle_start?->format('Y-m-d'), 'cycle_end' => $plan?->cycle_end?->format('Y-m-d')]) }})"
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
                        <p class="text-xs text-text-muted mt-1">Amount due = rate × current student count</p>
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
                    <div class="grid grid-cols-2 gap-3 mb-4">
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
                    <p class="text-xs text-text-muted mb-4">Status will be set to <strong>paid</strong> and subscription marked <strong>active</strong>.</p>
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

    <p class="mt-6 text-xs text-text-muted">
        Student counts sync daily via scheduler (<code class="bg-surface-secondary px-1 rounded">skolet:sync-student-counts</code>).
        Use the <strong>Sync Students</strong> button above to force an immediate sync.
    </p>

</main>

<script>
function superAdminPage() {
    return {
        search: '',
        rateModal: { open: false, tenantId: null, tenantName: '', rate: 0 },
        paidModal: { open: false, tenantId: null, tenantName: '', cycleStart: '', cycleEnd: '' },

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
            };
        },
    };
}
</script>

</body>
</html>
