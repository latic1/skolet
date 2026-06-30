@extends('layouts.tenant')

@section('title', 'Fees')
@section('page-title', 'Fees')

@section('content')
@php $host = request()->getSchemeAndHttpHost(); @endphp
<script>
window.__feesPage = {
    classes:       {!! Js::from($classes->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->values()) !!},
    terms:         {!! Js::from($terms->map(fn($t) => ['id' => $t->id, 'name' => $t->name, 'academic_year_id' => $t->academic_year_id, 'is_current' => $t->is_current, 'academic_year' => $t->academicYear ? ['id' => $t->academicYear->id, 'name' => $t->academicYear->name] : null])->values()) !!},
    academicYears: {!! Js::from($academicYears->map(fn($y) => ['id' => $y->id, 'name' => $y->name, 'terms' => $y->terms->map(fn($t) => ['id' => $t->id, 'name' => $t->name])->values()])->values()) !!},
    currentYearId: {!! Js::from($currentYear?->id) !!},
    activeTab:     {!! Js::from($activeTab) !!},
    filterTermId:  {!! Js::from($filterTermId ?? '') !!},
    bundles:       {!! Js::from($feeBundles->map(fn($b) => ['id' => $b->id, 'name' => $b->name, 'target_class' => $b->target_class, 'billing_cycle' => $b->billing_cycle, 'term_id' => $b->term_id ?? '', 'academic_year_id' => $b->academic_year_id ?? '', 'due_date' => $b->due_date?->format('Y-m-d') ?? ''])->values()) !!},
};
</script>
<div class="flex flex-col gap-6" x-data="feesAdminPage()" x-init="initTab()">

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

    {{-- Tab bar --}}
    <div class="flex items-center gap-1 border-b border-border pb-0">
        <button @click="activeTab = 'collection'"
                :class="activeTab === 'collection'
                    ? 'border-accent text-accent'
                    : 'border-transparent text-text-secondary hover:text-text-primary'"
                class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors">
            Fee Collection
        </button>
        <button @click="activeTab = 'structure'"
                :class="activeTab === 'structure'
                    ? 'border-accent text-accent'
                    : 'border-transparent text-text-secondary hover:text-text-primary'"
                class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors">
            Fee Structure
        </button>
    </div>

    {{-- ====================================================================
         TAB 1 &mdash; FEE COLLECTION
         ==================================================================== --}}
    <div x-show="activeTab === 'collection'" x-cloak>
        <div class="flex flex-col gap-6">

            {{-- Search / filter card --}}
            <div class="bg-surface border border-border rounded-2xl shadow-card p-5">
                <form method="GET" action="{{ $host }}/fees" class="flex flex-col gap-4">
                    <input type="hidden" name="tab" value="collection">

                    <div class="flex flex-wrap items-end gap-4">
                        {{-- Student search --}}
                        <div class="flex-1 min-w-50">
                            <label class="block text-xs font-medium text-text-secondary mb-1.5 uppercase tracking-wide">Search Student</label>
                            <div class="relative">
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <input type="text" name="search" value="{{ $searchQuery }}"
                                       placeholder="Name or admission no."
                                       class="w-full pl-9 pr-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                            </div>
                        </div>

                        {{-- Year filter --}}
                        <div class="min-w-40">
                            <label class="block text-xs font-medium text-text-secondary mb-1.5 uppercase tracking-wide">Academic Year</label>
                            <select x-model="collectionYearId" @change="collectionTermId = ''"
                                    class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                                <option value="">All years</option>
                                <template x-for="y in collectionYearsData" :key="y.id">
                                    <option :value="y.id" :selected="y.id === collectionYearId" x-text="y.name"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Term --}}
                        <div class="min-w-40">
                            <label class="block text-xs font-medium text-text-secondary mb-1.5 uppercase tracking-wide">Term</label>
                            <select name="term_id" x-model="collectionTermId"
                                    class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                                <option value="">All terms</option>
                                <template x-if="collectionYearId">
                                    <template x-for="t in collectionTermsForYear" :key="t.id">
                                        <option :value="t.id" :selected="t.id === collectionTermId" x-text="t.name"></option>
                                    </template>
                                </template>
                                <template x-if="!collectionYearId">
                                    <template x-for="t in allCollectionTerms" :key="t.id">
                                        <option :value="t.id" :selected="t.id === collectionTermId" x-text="t.name + (t.year_name ? ' (' + t.year_name + ')' : '')"></option>
                                    </template>
                                </template>
                            </select>
                        </div>

                        <button type="submit"
                                class="flex items-center gap-2 px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            Search
                        </button>

                        @if($searchQuery || $selectedStudent)
                        <a href="{{ $host }}/fees?tab=collection"
                           class="px-4 py-2 text-sm font-medium text-text-secondary hover:text-text-primary border border-border rounded-md hover:bg-surface-secondary transition-colors shrink-0">
                            Clear
                        </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Search results list --}}
            @if($searchResults->isNotEmpty())
            <div class="bg-surface border border-border rounded-2xl shadow-card">
                <div class="px-6 py-4 border-b border-border">
                    <p class="text-sm font-medium text-text-primary">{{ $searchResults->count() }} {{ Str::plural('student', $searchResults->count()) }} found</p>
                </div>
                <ul class="divide-y divide-border">
                    @foreach($searchResults as $result)
                    <li>
                        <a href="{{ $host }}/fees?student_id={{ $result->id }}&tab=collection"
                           class="flex items-center gap-4 px-6 py-3.5 hover:bg-surface-secondary transition-colors">
                            <div class="w-9 h-9 rounded-xl bg-accent-muted flex items-center justify-center shrink-0">
                                <span class="text-sm font-semibold text-accent">{{ strtoupper(substr($result->full_name, 0, 1)) }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-text-primary truncate">{{ $result->full_name }}</p>
                                <p class="text-xs text-text-muted">{{ $result->admission_no }} &middot; {{ $result->schoolClass?->name ?? '—' }}</p>
                            </div>
                            <svg class="w-4 h-4 text-text-muted shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            @elseif($searchQuery && $searchResults->isEmpty())
            <div class="bg-surface border border-border rounded-2xl shadow-card">
                <div class="flex flex-col items-center justify-center py-12 px-6 text-center">
                    <div class="w-10 h-10 rounded-xl bg-surface-secondary flex items-center justify-center mb-3">
                        <svg class="w-5 h-5 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-text-primary mb-1">No students found</p>
                    <p class="text-xs text-text-muted">Try a different name or admission number</p>
                </div>
            </div>
            @endif

            {{-- Selected student: fee items --}}
            @if($selectedStudent)
            {{-- Student info bar --}}
            <div class="bg-surface border border-border rounded-2xl shadow-card p-5 flex items-center gap-4">
                <div class="w-11 h-11 rounded-xl bg-accent-muted flex items-center justify-center shrink-0">
                    <span class="text-base font-semibold text-accent">{{ strtoupper(substr($selectedStudent->full_name, 0, 1)) }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-text-primary">{{ $selectedStudent->full_name }}</p>
                    <p class="text-xs text-text-muted">{{ $selectedStudent->admission_no }} &middot; {{ $selectedStudent->schoolClass?->name ?? '—' }}{{ $selectedStudent->section ? ' &middot; ' . $selectedStudent->section->name : '' }}</p>
                </div>
                @php
                    $totalOwed  = collect($feeItems)->sum('effective_amount');
                    $totalPaid  = collect($feeItems)->sum('paid_amount');
                    $totalOutstanding = collect($feeItems)->sum('outstanding');
                @endphp
                <div class="hidden sm:flex items-center gap-6 text-right shrink-0">
                    <div>
                        <p class="text-xs text-text-muted">Total Owed</p>
                        <p class="text-sm font-semibold text-text-primary">{{ format_money($totalOwed, $currencySymbol) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-text-muted">Paid</p>
                        <p class="text-sm font-semibold text-success-foreground">{{ format_money($totalPaid, $currencySymbol) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-text-muted">Outstanding</p>
                        <p class="text-sm font-semibold text-error">{{ format_money($totalOutstanding, $currencySymbol) }}</p>
                    </div>
                </div>
                @can('fees.view')
                <a href="{{ $host }}/fees/bill/{{ $selectedStudent->id }}{{ isset($currentTerm) && $currentTerm ? '?term_id=' . $currentTerm->id : '' }}"
                   target="_blank"
                   class="shrink-0 flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-text-secondary hover:text-text-primary border border-border rounded-md hover:bg-surface-secondary transition-colors"
                   title="Print term bill">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Print Bill
                </a>
                @endcan
                <a href="{{ $host }}/fees?tab=collection"
                   class="shrink-0 p-1.5 rounded-md text-text-muted hover:bg-surface-secondary hover:text-text-primary transition-colors"
                   title="Clear student">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </a>
            </div>

            {{-- Mobile summary --}}
            <div class="sm:hidden grid grid-cols-3 gap-3">
                <div class="bg-surface border border-border rounded-xl p-3 text-center shadow-card">
                    <p class="text-xs text-text-muted mb-1">Total Owed</p>
                    <p class="text-sm font-semibold text-text-primary">{{ format_money($totalOwed, $currencySymbol) }}</p>
                </div>
                <div class="bg-surface border border-border rounded-xl p-3 text-center shadow-card">
                    <p class="text-xs text-text-muted mb-1">Paid</p>
                    <p class="text-sm font-semibold text-success-foreground">{{ format_money($totalPaid, $currencySymbol) }}</p>
                </div>
                <div class="bg-surface border border-border rounded-xl p-3 text-center shadow-card">
                    <p class="text-xs text-text-muted mb-1">Outstanding</p>
                    <p class="text-sm font-semibold text-error">{{ format_money($totalOutstanding, $currencySymbol) }}</p>
                </div>
            </div>

            {{-- Fee items — grouped by bundle then standalone --}}
            @php
                $feeItemsByGroup = collect($feeItems)->groupBy(
                    fn($i) => $i['fee_structure']->fee_bundle_id ?? '__standalone__'
                );
                $bundleGroups    = $feeItemsByGroup->filter(fn($g, $k) => $k !== '__standalone__');
                $standaloneItems = $feeItemsByGroup->get('__standalone__', collect());
            @endphp

            <div x-data="paymentModal()">

            {{-- BUNDLE GROUPS --}}
            @foreach($bundleGroups as $bundleId => $bundleItems)
            @php
                $bundleFs    = $bundleItems->first()['fee_structure']->bundle;
                $bundleTotal = $bundleItems->sum('effective_amount');
                $bundlePaid  = $bundleItems->sum('paid_amount');
                $bundleOut   = $bundleItems->sum('outstanding');
                $bundleStatus = match(true) {
                    $bundlePaid >= $bundleTotal => 'paid',
                    $bundlePaid > 0             => 'partial',
                    default                     => $bundleItems->contains(fn($i) => $i['status'] === 'overdue') ? 'overdue' : 'unpaid',
                };
                $bundleBadge = match($bundleStatus) {
                    'paid'    => 'bg-success-lightest text-success-foreground',
                    'partial' => 'bg-warning-light text-warning',
                    default   => 'bg-error-light text-error',
                };
                // Collect unique receipt numbers for this bundle
                $bundleReceipts = collect();
                foreach ($bundleItems as $bi) {
                    foreach ($bi['payments'] as $pmt) {
                        $rKey = $pmt->receipt_number ?? $pmt->id;
                        if (!$bundleReceipts->has($rKey)) {
                            $bundleReceipts->put($rKey, ['key' => $rKey, 'amount' => 0, 'paid_at' => $pmt->paid_at]);
                        }
                        $entry = $bundleReceipts->get($rKey);
                        $entry['amount'] += (float) $pmt->amount;
                        $bundleReceipts->put($rKey, $entry);
                    }
                }
            @endphp
            <div class="bg-surface border border-border rounded-2xl shadow-card mb-4">
                <div class="px-6 py-4 border-b border-border flex items-center gap-3">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold text-text-primary">{{ $bundleFs?->name ?? 'Fee Bundle' }}</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $bundleBadge }}">
                                {{ ucfirst($bundleStatus) }}
                            </span>
                        </div>
                        <p class="text-xs text-text-muted mt-0.5">
                            Bundle · {{ $bundleItems->count() }} {{ Str::plural('item', $bundleItems->count()) }} ·
                            <span class="font-medium">{{ format_money($bundleOut, $currencySymbol) }} outstanding</span>
                            of {{ format_money($bundleTotal, $currencySymbol) }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        {{-- Bundle receipts --}}
                        @foreach($bundleReceipts as $receipt)
                        <a href="{{ $host }}/fees/receipt/{{ $receipt['key'] }}"
                           class="inline-flex items-center gap-1 text-xs font-medium text-text-secondary hover:text-text-primary transition-colors px-2 py-1 rounded hover:bg-surface-secondary whitespace-nowrap">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Receipt ({{ format_money($receipt['amount'], $currencySymbol) }})
                        </a>
                        @endforeach
                        @can('fees.create')
                        @if($bundleOut > 0)
                        <button @click="open({
                                    fee_bundle_id: '{{ $bundleId }}',
                                    fee_structure_id: '',
                                    fee_item: '{{ addslashes($bundleFs?->name ?? 'Fee Bundle') }}',
                                    outstanding: {{ $bundleOut }},
                                    student_id: '{{ $selectedStudent->id }}',
                                    student_name: '{{ addslashes($selectedStudent->full_name) }}'
                                })"
                                class="text-xs font-medium text-accent hover:text-accent-dark transition-colors px-3 py-1.5 rounded-md border border-accent hover:bg-accent-muted whitespace-nowrap">
                            Pay Bundle
                        </button>
                        @endif
                        @endcan
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full" style="min-width: 560px">
                        <tbody>
                            @foreach($bundleItems as $item)
                            @php
                                $fs          = $item['fee_structure'];
                                $status      = $item['status'];
                                $outstanding = $item['outstanding'];
                                $isOverdue   = $status === 'overdue';
                                $badgeClass  = match($status) {
                                    'paid'    => 'bg-success-lightest text-success-foreground',
                                    'partial' => 'bg-warning-light text-warning',
                                    default   => 'bg-error-light text-error',
                                };
                            @endphp
                            <tr class="border-b border-border last:border-b-0 hover:bg-surface-secondary transition-colors {{ $isOverdue ? 'bg-error-light/20' : '' }}">
                                <td class="px-6 py-3">
                                    <p class="text-sm text-text-primary">{{ $fs->fee_item }}</p>
                                    @if($fs->due_date)
                                    <p class="text-xs {{ $isOverdue ? 'text-error' : 'text-text-muted' }} mt-0.5">Due {{ $fs->due_date->format('M j, Y') }}{{ $isOverdue ? ' · Overdue' : '' }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-sm text-text-primary text-right">{{ format_money((float) $item['effective_amount'], $currencySymbol) }}</td>
                                <td class="px-6 py-3 text-sm text-success-foreground text-right">{{ format_money((float) $item['paid_amount'], $currencySymbol) }}</td>
                                <td class="px-6 py-3 text-sm font-medium text-right {{ $outstanding > 0 ? 'text-error' : 'text-text-muted' }}">{{ format_money((float) $outstanding, $currencySymbol) }}</td>
                                <td class="px-6 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">{{ ucfirst($status) }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endforeach

            {{-- STANDALONE ITEMS --}}
            @if($standaloneItems->isNotEmpty() || $bundleGroups->isEmpty())
            <div class="bg-surface border border-border rounded-2xl shadow-card">
                @if($bundleGroups->isNotEmpty())
                <div class="px-6 py-3 border-b border-border">
                    <h3 class="text-sm font-semibold text-text-primary">Standalone Fees</h3>
                </div>
                @else
                <div class="px-6 py-4 border-b border-border">
                    <h3 class="text-base font-semibold text-text-primary">Fee Items</h3>
                    @if($filterTermId)
                    @php $filteredTerm = $terms->firstWhere('id', $filterTermId); @endphp
                    <p class="text-xs text-text-muted mt-0.5">
                        Filtered by {{ $filteredTerm?->name }}{{ $filteredTerm?->academicYear ? ' · ' . $filteredTerm->academicYear->name : '' }}
                    </p>
                    @endif
                </div>
                @endif

                @if($standaloneItems->isEmpty())
                <div class="flex flex-col items-center justify-center py-10 px-6 text-center">
                    <p class="text-sm font-medium text-text-primary mb-1">No standalone fee items found</p>
                    <p class="text-xs text-text-muted">All fees for this student are assigned to bundles</p>
                </div>
                @elseif(empty($feeItems))
                <div class="flex flex-col items-center justify-center py-12 px-6 text-center">
                    <p class="text-sm font-medium text-text-primary mb-1">No fee items found</p>
                    <p class="text-xs text-text-muted">No fee structures are defined for this student's class{{ $filterTermId ? ' with the selected filters' : '' }}</p>
                </div>
                @else
                <div class="overflow-x-auto">
                    <table class="w-full" style="min-width: 640px">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Fee Item</th>
                                <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary hidden md:table-cell">Term / Year</th>
                                <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Amount</th>
                                <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Paid</th>
                                <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Outstanding</th>
                                <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Status</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($standaloneItems as $item)
                            @php
                                $fs              = $item['fee_structure'];
                                $status          = $item['status'];
                                $outstanding     = $item['outstanding'];
                                $paidAmount      = $item['paid_amount'];
                                $payments        = $item['payments'];
                                $hasDiscount     = $item['has_discount'] ?? false;
                                $originalAmount  = $item['original_amount'] ?? (float) $fs->amount;
                                $effectiveAmount = $item['effective_amount'] ?? $originalAmount;
                                $isOverdue       = $status === 'overdue';
                                $badgeClass      = match($status) {
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
                                                Due {{ $fs->due_date->format('M j, Y') }}@if($isOverdue) · Overdue @endif
                                            </p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-text-primary hidden md:table-cell">
                                    {{ $fs->term?->name ?? '—' }}{{ $fs->term?->academicYear ? ' · ' . $fs->term->academicYear->name : '' }}
                                </td>
                                <td class="px-6 py-4">
                                    @if($hasDiscount)
                                    <div class="flex flex-col gap-0.5">
                                        <span class="text-xs text-text-muted line-through">{{ format_money($originalAmount, $currencySymbol) }}</span>
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-sm font-medium text-text-primary">{{ format_money($effectiveAmount, $currencySymbol) }}</span>
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-accent-muted text-accent">Discounted</span>
                                        </div>
                                    </div>
                                    @else
                                    <span class="text-sm text-text-primary font-medium">{{ format_money($originalAmount, $currencySymbol) }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-success-foreground font-medium">{{ format_money($paidAmount, $currencySymbol) }}</td>
                                <td class="px-6 py-4 text-sm font-medium {{ $outstanding > 0 ? 'text-error' : 'text-text-muted' }}">{{ format_money($outstanding, $currencySymbol) }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">
                                        {{ ucfirst($status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col items-start gap-1.5">
                                        @can('fees.create')
                                        @if($outstanding > 0)
                                        <button @click="open({
                                                    fee_bundle_id: '',
                                                    fee_structure_id: '{{ $fs->id }}',
                                                    fee_item: '{{ addslashes($fs->fee_item) }}',
                                                    outstanding: {{ $outstanding }},
                                                    student_id: '{{ $selectedStudent->id }}',
                                                    student_name: '{{ addslashes($selectedStudent->full_name) }}'
                                                })"
                                                class="text-xs font-medium text-accent hover:text-accent-dark transition-colors px-2 py-1 rounded hover:bg-accent-muted whitespace-nowrap">
                                            Record Payment
                                        </button>
                                        @endif
                                        @endcan
                                        @foreach($payments as $pmt)
                                        <a href="{{ $host }}/fees/receipt/{{ $pmt->receipt_number ?? $pmt->id }}"
                                           class="inline-flex items-center gap-1 text-xs font-medium text-text-secondary hover:text-text-primary transition-colors px-2 py-1 rounded hover:bg-surface-secondary whitespace-nowrap">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            Receipt ({{ format_money((float)$pmt->amount, $currencySymbol) }})
                                        </a>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
            @endif

            {{-- Record Cash Payment Modal (shared for bundle + standalone) --}}
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

                <div class="relative w-full max-w-md bg-surface rounded-2xl shadow-xl border border-border p-6"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100">

                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-base font-semibold text-text-primary">Record Cash Payment</h3>
                        <button @click="close()" class="p-1.5 rounded-md text-text-muted hover:bg-surface-secondary transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="bg-surface-secondary rounded-xl px-4 py-3 mb-5 flex flex-col gap-1">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-text-muted">Student</span>
                            <span class="font-medium text-text-primary" x-text="form.student_name"></span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-text-muted" x-text="form.fee_bundle_id ? 'Bundle' : 'Fee Item'"></span>
                            <span class="font-medium text-text-primary" x-text="form.fee_item"></span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-text-muted">Outstanding</span>
                            <span class="font-semibold text-error" x-text="Number(form.outstanding).toFixed(2)"></span>
                        </div>
                    </div>

                    <form method="POST" action="{{ $host }}/fees/pay" @submit="submitting = true">
                        @csrf
                        <input type="hidden" name="student_id" :value="form.student_id">
                        <input type="hidden" name="fee_structure_id" :value="form.fee_structure_id">
                        <input type="hidden" name="fee_bundle_id" :value="form.fee_bundle_id">

                        <div class="mb-5">
                            <label class="block text-sm font-medium text-text-dark mb-1.5">
                                Amount Paid <span class="text-error">*</span>
                            </label>
                            <input type="number" name="amount" x-model="form.amount"
                                   :max="form.outstanding" min="0.01" step="0.01"
                                   placeholder="0.00"
                                   class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors"
                                   required>
                            <p class="mt-1 text-xs text-text-muted">Max: <span x-text="Number(form.outstanding).toFixed(2)"></span></p>
                        </div>

                        <div class="flex justify-end gap-3">
                            <button type="button" @click="close()"
                                    class="px-4 py-2 bg-surface border border-border text-sm font-medium text-text-primary rounded-md hover:bg-surface-secondary transition-colors">
                                Cancel
                            </button>
                            <button type="submit"
                                    :disabled="submitting"
                                    :class="submitting ? 'opacity-60 cursor-not-allowed' : 'hover:bg-accent-dark'"
                                    class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md transition-colors">
                                <span x-show="!submitting">Record Cash Payment</span>
                                <span x-show="submitting">Saving&hellip;</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            </div>{{-- /x-data paymentModal --}}
            @elseif(!$searchQuery)
            {{-- Recent payments --}}
            <div class="bg-surface border border-border rounded-2xl shadow-card">
                <div class="px-6 py-4 border-b border-border flex items-center justify-between">
                    <p class="text-sm font-semibold text-text-primary">Recent Payments</p>
                    @if($recentPayments->isNotEmpty())
                    <span class="text-xs text-text-muted">Last {{ $recentPayments->count() }} transactions</span>
                    @endif
                </div>
                @if($recentPayments->isEmpty())
                <div class="flex flex-col items-center justify-center py-12 px-6 text-center">
                    <p class="text-sm font-medium text-text-primary mb-1">No payments recorded yet</p>
                    <p class="text-xs text-text-muted">Payments will appear here once fees are collected</p>
                </div>
                @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-border bg-surface-secondary">
                                <th class="text-left px-5 py-3 text-xs font-semibold text-text-secondary uppercase tracking-wide">Student</th>
                                <th class="text-left px-5 py-3 text-xs font-semibold text-text-secondary uppercase tracking-wide">Fee Item</th>
                                <th class="text-right px-5 py-3 text-xs font-semibold text-text-secondary uppercase tracking-wide">Amount</th>
                                <th class="text-left px-5 py-3 text-xs font-semibold text-text-secondary uppercase tracking-wide">Method</th>
                                <th class="text-left px-5 py-3 text-xs font-semibold text-text-secondary uppercase tracking-wide">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @foreach($recentPayments as $payment)
                            <tr class="hover:bg-surface-secondary transition-colors">
                                <td class="px-5 py-3">
                                    <a href="{{ $host }}/fees?student_id={{ $payment->student_id }}&tab=collection"
                                       class="flex items-center gap-2.5 group">
                                        <div class="w-7 h-7 rounded-lg bg-accent-muted flex items-center justify-center shrink-0">
                                            <span class="text-xs font-semibold text-accent">{{ strtoupper(substr($payment->student?->full_name ?? '?', 0, 1)) }}</span>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-text-primary group-hover:text-accent truncate">{{ $payment->student?->full_name ?? '—' }}</p>
                                            <p class="text-xs text-text-muted">{{ $payment->student?->schoolClass?->name ?? '—' }}</p>
                                        </div>
                                    </a>
                                </td>
                                <td class="px-5 py-3 text-sm text-text-secondary">{{ $payment->feeStructure?->fee_item ?? '—' }}</td>
                                <td class="px-5 py-3 text-sm font-semibold text-success-foreground text-right">{{ format_money((float)$payment->amount, $currencySymbol) }}</td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-surface-secondary text-text-secondary capitalize">
                                        {{ $payment->payment_method ?? 'cash' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-sm text-text-muted whitespace-nowrap">{{ $payment->paid_at?->format('d M Y, H:i') ?? $payment->created_at->format('d M Y, H:i') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
            @endif

        </div>
    </div>

    {{-- ====================================================================
         TAB 2 &mdash; FEE STRUCTURE
         ==================================================================== --}}
    <div x-show="activeTab === 'structure'" x-cloak x-data="feeStructureTab()" x-init="init()">

        <div class="flex flex-col gap-6">

            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold text-text-primary">Fee Structure</h2>
                    <p class="text-xs text-text-muted mt-0.5">{{ $feeBundles->count() }} {{ Str::plural('bundle', $feeBundles->count()) }} · {{ $feeStructures->count() }} standalone {{ Str::plural('item', $feeStructures->count()) }}{{ $structureYearId ? ' for selected year' : '' }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <form method="GET" action="{{ $host }}/fees" class="flex items-center gap-2">
                        <input type="hidden" name="tab" value="structure">
                        <select name="structure_year_id" onchange="this.form.submit()"
                                class="px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                            <option value="">All Years</option>
                            @foreach($academicYears as $y)
                            <option value="{{ $y->id }}" {{ $structureYearId === $y->id ? 'selected' : '' }}>{{ $y->name }}</option>
                            @endforeach
                        </select>
                    </form>
                    @can('fees.create')
                    <button @click="openAddBundle()"
                            class="flex items-center gap-2 px-3 py-2 bg-surface border border-border text-text-secondary text-sm font-medium rounded-md hover:bg-surface-secondary transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        New Bundle
                    </button>
                    <button @click="openAdd()"
                            class="flex items-center gap-2 px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        New Fee Item
                    </button>
                    @endcan
                </div>
            </div>

            {{-- ── Fee Bundles section ── --}}
            @if($feeBundles->isNotEmpty())
            <div class="flex flex-col gap-3">
                <h3 class="text-sm font-semibold text-text-secondary uppercase tracking-wide">Fee Bundles</h3>
                @foreach($feeBundles as $bundle)
                <div class="bg-surface border border-border rounded-2xl shadow-card">
                    <div class="px-6 py-4 flex items-center gap-3">
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-text-primary">{{ $bundle->name }}</p>
                            <p class="text-xs text-text-muted mt-0.5">
                                {{ $bundle->billing_cycle === 'annual' ? 'Annual' : 'Per Term' }}
                                @if($bundle->term)· {{ $bundle->term->name }}{{ $bundle->term->academicYear ? ' ' . $bundle->term->academicYear->name : '' }}@endif
                                @if($bundle->target_class !== 'all') · {{ $classes->firstWhere('id', $bundle->target_class)?->name ?? $bundle->target_class }} @else · All Classes @endif
                                · {{ $bundle->items->count() }} {{ Str::plural('item', $bundle->items->count()) }}
                                · Total: {{ format_money((float) $bundle->items->sum('amount'), $currencySymbol) }}
                                @if($bundle->due_date) · Due {{ $bundle->due_date->format('M j, Y') }} @endif
                            </p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            @can('fees.create')
                            <button @click="openAddBundleItem({ id: '{{ $bundle->id }}', name: '{{ addslashes($bundle->name) }}' })"
                                    class="text-xs font-medium text-accent hover:text-accent-dark px-2 py-1 rounded hover:bg-accent-muted transition-colors">
                                Add Item
                            </button>
                            @endcan
                            @can('fees.edit')
                            <button @click="openEditBundle({
                                        id: '{{ $bundle->id }}',
                                        name: '{{ addslashes($bundle->name) }}',
                                        target_class: '{{ $bundle->target_class }}',
                                        billing_cycle: '{{ $bundle->billing_cycle }}',
                                        term_id: '{{ $bundle->term_id ?? '' }}',
                                        academic_year_id: '{{ $bundle->academic_year_id ?? '' }}',
                                        due_date: '{{ $bundle->due_date?->format('Y-m-d') ?? '' }}'
                                    })"
                                    class="text-xs font-medium text-text-secondary hover:text-text-primary px-2 py-1 rounded hover:bg-surface-secondary transition-colors">
                                Edit
                            </button>
                            @endcan
                            @can('fees.delete')
                            <form method="POST" action="{{ $host }}/fees/bundles/{{ $bundle->id }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        onclick="return confirm('Delete bundle \'{{ addslashes($bundle->name) }}\'? Its fee items will become standalone.')"
                                        class="text-xs font-medium text-error hover:text-red-700 px-2 py-1 rounded hover:bg-error-light transition-colors">
                                    Delete
                                </button>
                            </form>
                            @endcan
                        </div>
                    </div>
                    @if($bundle->items->isNotEmpty())
                    <div class="border-t border-border overflow-x-auto">
                        <table class="w-full" style="min-width: 500px">
                            <tbody>
                                @foreach($bundle->items as $item)
                                <tr class="border-b border-border last:border-b-0 hover:bg-surface-secondary transition-colors">
                                    <td class="px-6 py-2.5 text-sm text-text-primary pl-10">{{ $item->fee_item }}</td>
                                    <td class="px-6 py-2.5 text-sm text-text-primary">{{ format_money((float) $item->amount, $currencySymbol) }}</td>
                                    <td class="px-6 py-2.5 text-xs text-text-muted">{{ $item->due_date ? 'Due ' . $item->due_date->format('M j, Y') : '' }}</td>
                                    <td class="px-6 py-2.5">
                                        @can('fees.delete')
                                        <form method="POST" action="{{ $host }}/fees/{{ $item->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    onclick="return confirm('Remove \'{{ addslashes($item->fee_item) }}\' from this bundle?')"
                                                    class="text-xs text-error hover:text-red-700 px-1.5 py-0.5 rounded hover:bg-error-light transition-colors">
                                                Remove
                                            </button>
                                        </form>
                                        @endcan
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            @endif

            {{-- ── Standalone Fee Structure CRUD table ── --}}
            @if($feeBundles->isNotEmpty())
            <h3 class="text-sm font-semibold text-text-secondary uppercase tracking-wide -mb-3">Standalone Fees</h3>
            @endif
            <div class="bg-surface border border-border rounded-2xl shadow-card">
                @if($feeStructures->isEmpty())
                <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
                    <div class="w-12 h-12 rounded-xl bg-accent-muted flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-text-primary mb-1">No standalone fee items yet</p>
                    <p class="text-xs text-text-muted mb-4">Add individual fee items or group them into a bundle above</p>
                    @can('fees.create')
                    <button @click="openAdd()"
                            class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors">
                        Configure New Fee
                    </button>
                    @endcan
                </div>
                @else
                <div class="overflow-x-auto">
                    <table class="w-full" style="min-width: 700px">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Fee Name</th>
                                <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Amount</th>
                                <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Target Classes</th>
                                <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary hidden md:table-cell">Period</th>
                                <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary hidden lg:table-cell">Mandatory</th>
                                <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary hidden lg:table-cell">Due Date</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($feeStructures as $fee)
                            <tr class="border-b border-border last:border-b-0 hover:bg-surface-secondary transition-colors">
                                <td class="px-6 py-4 text-sm font-medium text-text-primary">{{ $fee->fee_item }}</td>
                                <td class="px-6 py-4 text-sm font-medium text-text-primary">{{ format_money((float) $fee->amount, $currencySymbol) }}</td>
                                <td class="px-6 py-4 text-sm text-text-primary">
                                    @if($fee->target_class === 'all')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-surface-secondary text-text-secondary">All Classes</span>
                                    @else
                                        {{ $classes->firstWhere('id', $fee->target_class)?->name ?? '—' }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-text-primary hidden md:table-cell">
                                    @if(($fee->billing_cycle ?? 'term') === 'annual')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-accent-muted text-accent">Annual</span>
                                        <span class="text-xs text-text-muted ml-1">{{ $fee->academicYear?->name ?? '' }}</span>
                                    @else
                                        {{ $fee->term?->name ?? '—' }}{{ $fee->term?->academicYear ? ' &middot; ' . $fee->term->academicYear->name : '' }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 hidden lg:table-cell">
                                    @if($fee->is_mandatory)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-error-light text-error">Yes</span>
                                    @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-surface-secondary text-text-muted">No</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-text-muted hidden lg:table-cell">
                                    {{ $fee->due_date ? $fee->due_date->format('M j, Y') : '—' }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        @can('fees.edit')
                                        <button @click="openEdit({
                                                    id: '{{ $fee->id }}',
                                                    billing_cycle: '{{ $fee->billing_cycle ?? 'term' }}',
                                                    target_class: '{{ $fee->target_class }}',
                                                    term_id: '{{ $fee->term_id ?? '' }}',
                                                    academic_year_id: '{{ $fee->academic_year_id ?? '' }}',
                                                    fee_item: '{{ addslashes($fee->fee_item) }}',
                                                    amount: '{{ $fee->amount }}',
                                                    is_mandatory: {{ $fee->is_mandatory ? 'true' : 'false' }},
                                                    due_date: '{{ $fee->due_date?->format('Y-m-d') ?? '' }}'
                                                })"
                                                class="text-xs font-medium text-text-secondary hover:text-text-primary transition-colors px-2 py-1 rounded hover:bg-surface-secondary">
                                            Edit
                                        </button>
                                        @endcan
                                        @can('fees.delete')
                                        <form method="POST" action="{{ $host }}/fees/{{ $fee->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    onclick="return confirm('Delete this fee item? This cannot be undone.')"
                                                    class="text-xs font-medium text-error hover:text-red-700 transition-colors px-2 py-1 rounded hover:bg-error-light">
                                                Delete
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

            {{-- Create / Edit Bundle Modal --}}
            <div x-show="showBundleModal"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4"
                 style="display: none;">
                <div class="absolute inset-0 bg-overlay/40" @click="closeBundle()"></div>
                <div class="relative w-full max-w-lg bg-surface rounded-2xl shadow-xl border border-border p-6"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-base font-semibold text-text-primary" x-text="bundleMode === 'add' ? 'Create Fee Bundle' : 'Edit Bundle'"></h3>
                        <button @click="closeBundle()" class="p-1.5 rounded-md text-text-muted hover:bg-surface-secondary transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- Add Bundle Form --}}
                    <form x-show="bundleMode === 'add'" method="POST" action="{{ $host }}/fees/bundles" class="flex flex-col gap-4" @submit="submitting = true">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-text-dark mb-1.5">Bundle Name <span class="text-error">*</span></label>
                            <input type="text" name="name" x-model="bundleForm.name" placeholder="e.g. First Semester Fees"
                                   class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-dark mb-1.5">Target Class <span class="text-error">*</span></label>
                            <select name="target_class" x-model="bundleForm.target_class" required
                                    class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                                <option value="all">All Classes</option>
                                <template x-for="cls in classes" :key="cls.id">
                                    <option :value="cls.id" :selected="bundleForm.target_class === cls.id" x-text="cls.name"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-dark mb-1.5">Billing Cycle <span class="text-error">*</span></label>
                            <div class="flex gap-2">
                                <button type="button" @click="bundleForm.billing_cycle = 'term'"
                                        :class="bundleForm.billing_cycle === 'term' ? 'bg-accent text-accent-foreground border-accent' : 'bg-surface text-text-secondary border-border hover:bg-surface-secondary'"
                                        class="flex-1 py-2 text-sm font-medium border rounded-md transition-colors">Per Term</button>
                                <button type="button" @click="bundleForm.billing_cycle = 'annual'; bundleForm.term_id = ''"
                                        :class="bundleForm.billing_cycle === 'annual' ? 'bg-accent text-accent-foreground border-accent' : 'bg-surface text-text-secondary border-border hover:bg-surface-secondary'"
                                        class="flex-1 py-2 text-sm font-medium border rounded-md transition-colors">Annual</button>
                            </div>
                            <input type="hidden" name="billing_cycle" :value="bundleForm.billing_cycle">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-text-dark mb-1.5">Academic Year</label>
                                <select name="academic_year_id" x-model="bundleForm.academic_year_id" @change="bundleForm.term_id = ''"
                                        class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                                    <option value="">Select year&hellip;</option>
                                    <template x-for="y in allYears" :key="y.id">
                                        <option :value="y.id" :selected="bundleForm.academic_year_id === y.id" x-text="y.name"></option>
                                    </template>
                                </select>
                            </div>
                            <div x-show="bundleForm.billing_cycle === 'term'">
                                <label class="block text-sm font-medium text-text-dark mb-1.5">Term</label>
                                <select name="term_id" x-model="bundleForm.term_id" :disabled="!bundleForm.academic_year_id"
                                        class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                    <option value="">Select term&hellip;</option>
                                    <template x-for="t in bundleTermsForYear" :key="t.id">
                                        <option :value="t.id" :selected="bundleForm.term_id === t.id" x-text="t.name"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-dark mb-1.5">Due Date <span class="text-text-muted font-normal">(optional)</span></label>
                            <input type="date" name="due_date" x-model="bundleForm.due_date"
                                   class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                        </div>
                        <div class="flex justify-end gap-3 mt-2">
                            <button type="button" @click="closeBundle()" class="px-4 py-2 bg-surface border border-border text-sm font-medium text-text-primary rounded-md hover:bg-surface-secondary transition-colors">Cancel</button>
                            <button type="submit" :disabled="submitting" :class="submitting ? 'opacity-60 cursor-not-allowed' : 'hover:bg-accent-dark'" class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md transition-colors">
                                <span x-show="!submitting">Create Bundle</span>
                                <span x-show="submitting">Saving&hellip;</span>
                            </button>
                        </div>
                    </form>

                    {{-- Edit Bundle Form --}}
                    <form x-show="bundleMode === 'edit'" method="POST" :action="`{{ $host }}/fees/bundles/${bundleForm.id}`" class="flex flex-col gap-4" @submit="submitting = true">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="block text-sm font-medium text-text-dark mb-1.5">Bundle Name <span class="text-error">*</span></label>
                            <input type="text" name="name" x-model="bundleForm.name"
                                   class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-dark mb-1.5">Target Class</label>
                            <select name="target_class" x-model="bundleForm.target_class"
                                    class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                                <option value="all">All Classes</option>
                                <template x-for="cls in classes" :key="cls.id">
                                    <option :value="cls.id" :selected="bundleForm.target_class === cls.id" x-text="cls.name"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-dark mb-1.5">Billing Cycle</label>
                            <div class="flex gap-2">
                                <button type="button" @click="bundleForm.billing_cycle = 'term'"
                                        :class="bundleForm.billing_cycle === 'term' ? 'bg-accent text-accent-foreground border-accent' : 'bg-surface text-text-secondary border-border hover:bg-surface-secondary'"
                                        class="flex-1 py-2 text-sm font-medium border rounded-md transition-colors">Per Term</button>
                                <button type="button" @click="bundleForm.billing_cycle = 'annual'; bundleForm.term_id = ''"
                                        :class="bundleForm.billing_cycle === 'annual' ? 'bg-accent text-accent-foreground border-accent' : 'bg-surface text-text-secondary border-border hover:bg-surface-secondary'"
                                        class="flex-1 py-2 text-sm font-medium border rounded-md transition-colors">Annual</button>
                            </div>
                            <input type="hidden" name="billing_cycle" :value="bundleForm.billing_cycle">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-text-dark mb-1.5">Academic Year</label>
                                <select name="academic_year_id" x-model="bundleForm.academic_year_id" @change="bundleForm.term_id = ''"
                                        class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                                    <option value="">Select year&hellip;</option>
                                    <template x-for="y in allYears" :key="y.id">
                                        <option :value="y.id" :selected="bundleForm.academic_year_id === y.id" x-text="y.name"></option>
                                    </template>
                                </select>
                            </div>
                            <div x-show="bundleForm.billing_cycle === 'term'">
                                <label class="block text-sm font-medium text-text-dark mb-1.5">Term</label>
                                <select name="term_id" x-model="bundleForm.term_id" :disabled="!bundleForm.academic_year_id"
                                        class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                    <option value="">Select term&hellip;</option>
                                    <template x-for="t in bundleTermsForYear" :key="t.id">
                                        <option :value="t.id" :selected="bundleForm.term_id === t.id" x-text="t.name"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-dark mb-1.5">Due Date</label>
                            <input type="date" name="due_date" x-model="bundleForm.due_date"
                                   class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                        </div>
                        <div class="flex justify-end gap-3 mt-2">
                            <button type="button" @click="closeBundle()" class="px-4 py-2 bg-surface border border-border text-sm font-medium text-text-primary rounded-md hover:bg-surface-secondary transition-colors">Cancel</button>
                            <button type="submit" :disabled="submitting" :class="submitting ? 'opacity-60 cursor-not-allowed' : 'hover:bg-accent-dark'" class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md transition-colors">
                                <span x-show="!submitting">Save Changes</span>
                                <span x-show="submitting">Saving&hellip;</span>
                            </button>
                        </div>
                    </form>

                    {{-- Add Bundle Item Form --}}
                    <form x-show="bundleMode === 'addItem'" method="POST" :action="`{{ $host }}/fees/bundles/${bundleForm.id}/items`" class="flex flex-col gap-4" @submit="submitting = true">
                        @csrf
                        <p class="text-sm text-text-muted -mt-2">Adding fee item to: <strong x-text="bundleForm.name"></strong></p>
                        <div>
                            <label class="block text-sm font-medium text-text-dark mb-1.5">Fee Name <span class="text-error">*</span></label>
                            <input type="text" name="fee_item" x-model="bundleForm.fee_item" placeholder="e.g. Tuition, PTA Dues"
                                   class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-dark mb-1.5">Amount <span class="text-error">*</span></label>
                            <input type="number" name="amount" x-model="bundleForm.amount" placeholder="0.00" step="0.01" min="0"
                                   class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors" required>
                        </div>
                        <div class="grid grid-cols-2 gap-4 items-start">
                            <label class="flex items-center gap-3 cursor-pointer pt-6">
                                <input type="hidden" name="is_mandatory" value="0">
                                <input type="checkbox" name="is_mandatory" value="1" x-model="bundleForm.is_mandatory"
                                       class="w-4 h-4 rounded border-border text-accent focus:ring-accent">
                                <span class="text-sm text-text-secondary">Mandatory</span>
                            </label>
                            <div>
                                <label class="block text-sm font-medium text-text-dark mb-1.5">Due Date</label>
                                <input type="date" name="due_date" x-model="bundleForm.due_date"
                                       class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 mt-2">
                            <button type="button" @click="closeBundle()" class="px-4 py-2 bg-surface border border-border text-sm font-medium text-text-primary rounded-md hover:bg-surface-secondary transition-colors">Cancel</button>
                            <button type="submit" :disabled="submitting" :class="submitting ? 'opacity-60 cursor-not-allowed' : 'hover:bg-accent-dark'" class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md transition-colors">
                                <span x-show="!submitting">Add to Bundle</span>
                                <span x-show="submitting">Saving&hellip;</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Configure New Fee / Edit Fee Modal --}}
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

                <div class="relative w-full max-w-lg bg-surface rounded-2xl shadow-xl border border-border p-6"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100">

                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-base font-semibold text-text-primary"
                            x-text="mode === 'add' ? 'Configure New Fee' : 'Edit Fee'"></h3>
                        <button @click="close()" class="p-1.5 rounded-md text-text-muted hover:bg-surface-secondary transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Add Form --}}
                    <form x-show="mode === 'add'" method="POST" action="{{ $host }}/fees" class="flex flex-col gap-4"
                          @submit="submitting = true">
                        @csrf
                        <input type="hidden" name="_fee_mode" value="add">

                        {{-- Fee Name --}}
                        <div>
                            <label class="block text-sm font-medium text-text-dark mb-1.5">Fee Name <span class="text-error">*</span></label>
                            <input type="text" name="fee_item" x-model="form.fee_item"
                                   placeholder="e.g. Tuition, Exam Fee, PTA Levy"
                                   class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors"
                                   required>
                            @error('fee_item')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                        </div>

                        {{-- Amount --}}
                        <div>
                            <label class="block text-sm font-medium text-text-dark mb-1.5">Amount <span class="text-error">*</span></label>
                            <input type="number" name="amount" x-model="form.amount"
                                   placeholder="0.00" step="0.01" min="0"
                                   class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors"
                                   required>
                            @error('amount')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                        </div>

                        {{-- Target Classes (multi-select) --}}
                        <div>
                            <label class="block text-sm font-medium text-text-dark mb-1.5">Target Classes <span class="text-error">*</span></label>
                            <div class="border border-border rounded-md divide-y divide-border max-h-44 overflow-y-auto">
                                {{-- All Classes --}}
                                <label class="flex items-center gap-3 px-3 py-2.5 cursor-pointer hover:bg-surface-secondary select-none">
                                    <input type="checkbox" value="all"
                                           :checked="form.target_classes.includes('all')"
                                           @change="form.target_classes = ['all']"
                                           class="w-4 h-4 rounded border-border text-accent focus:ring-accent">
                                    <span class="text-sm font-medium text-text-primary">All Classes</span>
                                </label>
                                {{-- Individual classes --}}
                                <template x-for="cls in classes" :key="cls.id">
                                    <label class="flex items-center gap-3 px-3 py-2.5 cursor-pointer hover:bg-surface-secondary select-none">
                                        <input type="checkbox" :value="cls.id"
                                               :checked="form.target_classes.includes(cls.id)"
                                               @change="
                                                   if ($el.checked) {
                                                       form.target_classes = form.target_classes.filter(v => v !== 'all').concat(cls.id);
                                                   } else {
                                                       const next = form.target_classes.filter(v => v !== cls.id);
                                                       form.target_classes = next.length ? next : ['all'];
                                                   }
                                               "
                                               class="w-4 h-4 rounded border-border text-accent focus:ring-accent">
                                        <span class="text-sm text-text-primary" x-text="cls.name"></span>
                                    </label>
                                </template>
                            </div>
                            {{-- Submit as array --}}
                            <template x-for="tc in form.target_classes" :key="tc">
                                <input type="hidden" name="target_classes[]" :value="tc">
                            </template>
                            @error('target_classes')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                        </div>

                        {{-- Billing Cycle --}}
                        <div>
                            <label class="block text-sm font-medium text-text-dark mb-1.5">Billing Cycle <span class="text-error">*</span></label>
                            <div class="flex gap-2">
                                <button type="button"
                                        @click="form.billing_cycle = 'term'"
                                        :class="form.billing_cycle === 'term' ? 'bg-accent text-accent-foreground border-accent' : 'bg-surface text-text-secondary border-border hover:bg-surface-secondary'"
                                        class="flex-1 py-2 text-sm font-medium border rounded-md transition-colors">
                                    Per Term
                                </button>
                                <button type="button"
                                        @click="form.billing_cycle = 'annual'; form.term_id = ''"
                                        :class="form.billing_cycle === 'annual' ? 'bg-accent text-accent-foreground border-accent' : 'bg-surface text-text-secondary border-border hover:bg-surface-secondary'"
                                        class="flex-1 py-2 text-sm font-medium border rounded-md transition-colors">
                                    Annual
                                </button>
                            </div>
                            <input type="hidden" name="billing_cycle" :value="form.billing_cycle">
                            @error('billing_cycle')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                        </div>

                        {{-- Academic Year + Academic Term --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-text-dark mb-1.5">Academic Year <span class="text-error">*</span></label>
                                <select name="academic_year_id" x-model="form.academic_year_id"
                                        @change="onYearChange()"
                                        required
                                        class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                                    <option value="">Select year&hellip;</option>
                                    <template x-for="y in allYears" :key="y.id">
                                        <option :value="y.id" :selected="form.academic_year_id === y.id" x-text="y.name"></option>
                                    </template>
                                </select>
                                @error('academic_year_id')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                            </div>
                            <div x-show="form.billing_cycle === 'term'">
                                <label class="block text-sm font-medium text-text-dark mb-1.5">Academic Term <span class="text-error">*</span></label>
                                <select name="term_id" x-model="form.term_id"
                                        :required="form.billing_cycle === 'term'"
                                        :disabled="!form.academic_year_id"
                                        class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                    <option value="" x-text="form.academic_year_id ? 'Select a term' : 'Select a year first'"></option>
                                    <template x-for="t in termsForSelectedYear" :key="t.id">
                                        <option :value="t.id" :selected="form.term_id === t.id" x-text="t.name"></option>
                                    </template>
                                </select>
                                @error('term_id')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        {{-- Mandatory Fee toggle + Due Date --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start">
                            <div>
                                <label class="block text-sm font-medium text-text-dark mb-2">Mandatory Fee</label>
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="hidden" name="is_mandatory" value="0">
                                    <input type="checkbox" name="is_mandatory" value="1"
                                           x-model="form.is_mandatory"
                                           class="w-4 h-4 rounded border-border text-accent focus:ring-accent">
                                    <span class="text-sm text-text-secondary" x-text="form.is_mandatory ? 'Required for all' : 'Optional'"></span>
                                </label>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-text-dark mb-1.5">Due Date <span class="text-text-muted font-normal">(optional)</span></label>
                                <input type="date" name="due_date" x-model="form.due_date"
                                       class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                                @error('due_date')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 mt-2">
                            <button type="button" @click="close()"
                                    class="px-4 py-2 bg-surface border border-border text-sm font-medium text-text-primary rounded-md hover:bg-surface-secondary transition-colors">
                                Cancel
                            </button>
                            <button type="submit"
                                    :disabled="submitting"
                                    :class="submitting ? 'opacity-60 cursor-not-allowed' : 'hover:bg-accent-dark'"
                                    class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md transition-colors">
                                <span x-show="!submitting">Save Fee</span>
                                <span x-show="submitting">Saving&hellip;</span>
                            </button>
                        </div>
                    </form>

                    {{-- Edit Form --}}
                    <form x-show="mode === 'edit'" method="POST"
                          :action="`{{ $host }}/fees/${form.id}`"
                          class="flex flex-col gap-4"
                          @submit="submitting = true">
                        @csrf
                        <input type="hidden" name="_method" value="PUT">
                        <input type="hidden" name="_fee_mode" value="edit">
                        <input type="hidden" name="_fee_id" :value="form.id">

                        {{-- Fee Name --}}
                        <div>
                            <label class="block text-sm font-medium text-text-dark mb-1.5">Fee Name <span class="text-error">*</span></label>
                            <input type="text" name="fee_item" x-model="form.fee_item"
                                   placeholder="e.g. Tuition, Exam Fee, PTA Levy"
                                   class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors"
                                   required>
                        </div>

                        {{-- Amount --}}
                        <div>
                            <label class="block text-sm font-medium text-text-dark mb-1.5">Amount <span class="text-error">*</span></label>
                            <input type="number" name="amount" x-model="form.amount"
                                   placeholder="0.00" step="0.01" min="0"
                                   class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors"
                                   required>
                        </div>

                        {{-- Target Classes --}}
                        <div>
                            <label class="block text-sm font-medium text-text-dark mb-1.5">Target Classes <span class="text-error">*</span></label>
                            <select name="target_class" x-model="form.target_class" required
                                    class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                                <option value="all">All Classes</option>
                                <template x-for="cls in classes" :key="cls.id">
                                    <option :value="cls.id" :selected="form.target_class === cls.id" x-text="cls.name"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Billing Cycle --}}
                        <div>
                            <label class="block text-sm font-medium text-text-dark mb-1.5">Billing Cycle <span class="text-error">*</span></label>
                            <div class="flex gap-2">
                                <button type="button"
                                        @click="form.billing_cycle = 'term'"
                                        :class="form.billing_cycle === 'term' ? 'bg-accent text-accent-foreground border-accent' : 'bg-surface text-text-secondary border-border hover:bg-surface-secondary'"
                                        class="flex-1 py-2 text-sm font-medium border rounded-md transition-colors">
                                    Per Term
                                </button>
                                <button type="button"
                                        @click="form.billing_cycle = 'annual'; form.term_id = ''"
                                        :class="form.billing_cycle === 'annual' ? 'bg-accent text-accent-foreground border-accent' : 'bg-surface text-text-secondary border-border hover:bg-surface-secondary'"
                                        class="flex-1 py-2 text-sm font-medium border rounded-md transition-colors">
                                    Annual
                                </button>
                            </div>
                            <input type="hidden" name="billing_cycle" :value="form.billing_cycle">
                        </div>

                        {{-- Academic Year + Academic Term --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-text-dark mb-1.5">Academic Year <span class="text-error">*</span></label>
                                <select name="academic_year_id" x-model="form.academic_year_id"
                                        @change="onYearChange()"
                                        required
                                        class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                                    <option value="">Select year&hellip;</option>
                                    <template x-for="y in allYears" :key="y.id">
                                        <option :value="y.id" :selected="form.academic_year_id === y.id" x-text="y.name"></option>
                                    </template>
                                </select>
                            </div>
                            <div x-show="form.billing_cycle === 'term'">
                                <label class="block text-sm font-medium text-text-dark mb-1.5">Academic Term <span class="text-error">*</span></label>
                                <select name="term_id" x-model="form.term_id"
                                        :required="form.billing_cycle === 'term'"
                                        :disabled="!form.academic_year_id"
                                        class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                    <option value="" x-text="form.academic_year_id ? 'Select a term' : 'Select a year first'"></option>
                                    <template x-for="t in termsForSelectedYear" :key="t.id">
                                        <option :value="t.id" :selected="form.term_id === t.id" x-text="t.name"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        {{-- Mandatory Fee toggle + Due Date --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start">
                            <div>
                                <label class="block text-sm font-medium text-text-dark mb-2">Mandatory Fee</label>
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="hidden" name="is_mandatory" value="0">
                                    <input type="checkbox" name="is_mandatory" value="1"
                                           x-model="form.is_mandatory"
                                           class="w-4 h-4 rounded border-border text-accent focus:ring-accent">
                                    <span class="text-sm text-text-secondary" x-text="form.is_mandatory ? 'Required for all' : 'Optional'"></span>
                                </label>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-text-dark mb-1.5">Due Date <span class="text-text-muted font-normal">(optional)</span></label>
                                <input type="date" name="due_date" x-model="form.due_date"
                                       class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 mt-2">
                            <button type="button" @click="close()"
                                    class="px-4 py-2 bg-surface border border-border text-sm font-medium text-text-primary rounded-md hover:bg-surface-secondary transition-colors">
                                Cancel
                            </button>
                            <button type="submit"
                                    :disabled="submitting"
                                    :class="submitting ? 'opacity-60 cursor-not-allowed' : 'hover:bg-accent-dark'"
                                    class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md transition-colors">
                                <span x-show="!submitting">Save Changes</span>
                                <span x-show="submitting">Saving&hellip;</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function feesAdminPage() {
    const _d = window.__feesPage;
    return {
        activeTab: 'collection',
        classes: _d.classes,
        terms: _d.terms,
        collectionYearId: '',
        collectionTermId: _d.filterTermId || '',

        get collectionYearsData() {
            const map = {};
            this.terms.forEach(t => {
                if (t.academic_year && !map[t.academic_year.id]) {
                    map[t.academic_year.id] = { id: t.academic_year.id, name: t.academic_year.name, terms: [] };
                }
                if (t.academic_year) {
                    map[t.academic_year.id].terms.push({ id: t.id, name: t.name });
                }
            });
            return Object.values(map);
        },

        get collectionTermsForYear() {
            const y = this.collectionYearsData.find(y => y.id === this.collectionYearId);
            return y ? y.terms : [];
        },

        get allCollectionTerms() {
            return this.terms.map(t => ({
                id: t.id,
                name: t.name,
                year_name: t.academic_year ? t.academic_year.name : '',
            }));
        },

        initTab() {
            this.activeTab = _d.activeTab === 'structure' ? 'structure' : 'collection';
            // Pre-select the year that matches the currently filtered term
            if (this.collectionTermId) {
                const term = this.terms.find(t => t.id === this.collectionTermId);
                if (term?.academic_year) {
                    this.collectionYearId = term.academic_year.id;
                }
            }
        },
    };
}

function feeStructureTab() {
    const _d = window.__feesPage;
    return {
        // Standalone fee modal
        showModal: false,
        submitting: false,
        mode: 'add',
        classes: _d.classes,
        allYears: _d.academicYears,
        currentYearId: _d.currentYearId,
        form: {
            id: '', billing_cycle: 'term', target_classes: ['all'], target_class: 'all',
            term_id: '', academic_year_id: _d.currentYearId || '',
            fee_item: '', amount: '', is_mandatory: true, due_date: '',
        },

        // Bundle modal
        showBundleModal: false,
        bundleMode: 'add', // 'add' | 'edit' | 'addItem'
        bundleForm: {
            id: '', name: '', target_class: 'all', billing_cycle: 'term',
            term_id: '', academic_year_id: _d.currentYearId || '',
            due_date: '', fee_item: '', amount: '', is_mandatory: true,
        },

        get termsForSelectedYear() {
            const y = this.allYears.find(y => y.id === this.form.academic_year_id);
            return y ? y.terms : [];
        },

        get bundleTermsForYear() {
            const y = this.allYears.find(y => y.id === this.bundleForm.academic_year_id);
            return y ? y.terms : [];
        },

        onYearChange() { this.form.term_id = ''; },

        init() {
            const feeMode = @json(old('_fee_mode'));
            if (feeMode === 'add' || feeMode === 'edit') {
                this.$nextTick(() => {
                    this.mode = feeMode;
                    const base = {
                        id:               @json(old('_fee_id', '')),
                        billing_cycle:    @json(old('billing_cycle', 'term')),
                        term_id:          @json(old('term_id', '')),
                        academic_year_id: @json(old('academic_year_id', $currentYear?->id ?? '')),
                        fee_item:         @json(old('fee_item', '')),
                        amount:           @json(old('amount', '')),
                        is_mandatory:     @json(old('is_mandatory', '1')) == '1',
                        due_date:         @json(old('due_date', '')),
                    };
                    if (feeMode === 'add') {
                        this.form = { ...base, target_classes: @json(old('target_classes', ['all'])) };
                    } else {
                        this.form = { ...base, target_class: @json(old('target_class', 'all')) };
                    }
                    this.showModal = true;
                });
            }
        },

        openAdd() {
            this.mode = 'add';
            this.form = {
                id: '', billing_cycle: 'term', target_classes: ['all'],
                term_id: '', academic_year_id: _d.currentYearId || '',
                fee_item: '', amount: '', is_mandatory: true, due_date: '',
            };
            this.showModal = true;
        },
        openEdit(data) {
            this.mode = 'edit';
            this.form = { ...data, academic_year_id: data.academic_year_id || _d.currentYearId || '' };
            this.showModal = true;
        },
        close() { this.showModal = false; this.submitting = false; },

        openAddBundle() {
            this.bundleMode = 'add';
            this.bundleForm = {
                id: '', name: '', target_class: 'all', billing_cycle: 'term',
                term_id: '', academic_year_id: _d.currentYearId || '',
                due_date: '', fee_item: '', amount: '', is_mandatory: true,
            };
            this.showBundleModal = true;
        },
        openEditBundle(data) {
            this.bundleMode = 'edit';
            this.bundleForm = { ...data, fee_item: '', amount: '', is_mandatory: true };
            this.showBundleModal = true;
        },
        openAddBundleItem(bundle) {
            this.bundleMode = 'addItem';
            this.bundleForm = { id: bundle.id, name: bundle.name, fee_item: '', amount: '', is_mandatory: true, due_date: '' };
            this.showBundleModal = true;
        },
        closeBundle() { this.showBundleModal = false; this.submitting = false; },
    };
}

function paymentModal() {
    return {
        showModal: false,
        submitting: false,
        form: { student_id: '', fee_structure_id: '', fee_bundle_id: '', fee_item: '', outstanding: 0, student_name: '', amount: '' },
        open(data) {
            this.form = { ...data, amount: data.outstanding };
            this.showModal = true;
        },
        close() { this.showModal = false; this.submitting = false; },
    };
}
</script>
@endpush
