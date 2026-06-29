@extends('layouts.tenant')

@section('title', 'Reports')
@section('page-title', 'Reports')

@push('head')
{{-- Chart.js loaded before Alpine so x-init can reference it --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')
@php
    $host = request()->getSchemeAndHttpHost();
    $examsForJs = $exams->map(fn($e) => [
        'id'      => $e->id,
        'name'    => $e->name,
        'term_id' => $e->term_id,
    ])->values()->toArray();
    $chartData = [
        'labels'        => $analyticsReport ? collect($analyticsReport['subjects'])->pluck('subject_name')->values()->toArray() : [],
        'avgScores'     => $analyticsReport ? collect($analyticsReport['subjects'])->pluck('avg_score')->values()->toArray() : [],
        'passRates'     => $analyticsReport ? collect($analyticsReport['subjects'])->pluck('pass_rate')->values()->toArray() : [],
        'trendLabels'   => collect($trendData)->pluck('exam_name')->values()->toArray(),
        'trendAverages' => collect($trendData)->pluck('average')->values()->toArray(),
    ];
    $financialChartData = [
        'labels'   => $financialReport ? collect($financialReport['monthly_trend'])->pluck('month')->values()->toArray() : [],
        'income'   => $financialReport ? collect($financialReport['monthly_trend'])->pluck('income')->values()->toArray() : [],
        'expenses' => $financialReport ? collect($financialReport['monthly_trend'])->pluck('expenses')->values()->toArray() : [],
    ];
    // Pass academic years with their terms for filter cascades
    $academicYearsForJs = $academicYears->map(fn($y) => [
        'id'    => $y->id,
        'name'  => $y->name,
        'terms' => $y->terms->map(fn($t) => ['id' => $t->id, 'name' => $t->name])->values(),
    ])->values()->toArray();
    $classesForJs = $classes->map(fn($c) => [
        'id'       => $c->id,
        'name'     => $c->name,
        'sections' => $c->sections->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->values(),
    ])->values()->toArray();
    // Derive the year of the currently selected term (used to pre-select year in fee/alerts filters)
    $selectedTermYearId = $selectedTermId
        ? ($terms->firstWhere('id', $selectedTermId)?->academicYear?->id ?? '')
        : '';
@endphp
<script>
window.__reportsPage = {
    classes:                 {!! Js::from($classesForJs) !!},
    selectedClassId:         {!! Js::from($selectedClassId) !!},
    selectedSection:         {!! Js::from($selectedSection) !!},
    activeTab:               {!! Js::from($activeTab) !!},
    examsForJs:              {!! Js::from($examsForJs) !!},
    chartData:               {!! Js::from($chartData) !!},
    financialChartData:      {!! Js::from($financialChartData) !!},
    academicYearsForJs:      {!! Js::from($academicYearsForJs) !!},
    selectedFinancialYearId: {!! Js::from($selectedFinancialYearId) !!},
    selectedFinancialTermId: {!! Js::from($selectedFinancialTermId) !!},
    selectedTermYearId:      {!! Js::from($selectedTermYearId) !!},
    selectedTermId:          {!! Js::from($selectedTermId) !!},
    selectedExamId:          {!! Js::from($selectedExamId) !!},
};
</script>
<div x-data="reportsPage()">

    {{-- Page header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-text-primary">Reports</h1>
            <p class="text-xs text-text-muted mt-0.5">Attendance summaries and fee collection overview</p>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="mb-5 bg-success-lightest border border-success-light text-success-foreground text-sm px-4 py-3 rounded-xl flex items-start gap-3">
            <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-5 bg-error-light border border-error text-error text-sm px-4 py-3 rounded-xl flex items-start gap-3">
            <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Tab bar --}}
    <div class="flex items-center gap-1 border-b border-border pb-0 mb-6 overflow-x-auto whitespace-nowrap">
        <button
            @click="activeTab = 'attendance'"
            :class="activeTab === 'attendance'
                ? 'px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors border-accent text-accent'
                : 'px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors border-transparent text-text-secondary hover:text-text-primary'"
            type="button"
        >
            Attendance Report
        </button>
        <button
            @click="activeTab = 'fees'"
            :class="activeTab === 'fees'
                ? 'px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors border-accent text-accent'
                : 'px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors border-transparent text-text-secondary hover:text-text-primary'"
            type="button"
        >
            Fee Collection
        </button>
        <button
            @click="activeTab = 'alerts'"
            :class="activeTab === 'alerts'
                ? 'px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors border-accent text-accent'
                : 'px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors border-transparent text-text-secondary hover:text-text-primary'"
            type="button"
        >
            Attendance Alerts
        </button>
        <button
            @click="activeTab = 'academic'"
            :class="activeTab === 'academic'
                ? 'px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors border-accent text-accent'
                : 'px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors border-transparent text-text-secondary hover:text-text-primary'"
            type="button"
        >
            Academic Analytics
        </button>
        <button
            @click="activeTab = 'financial'"
            :class="activeTab === 'financial'
                ? 'px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors border-accent text-accent'
                : 'px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors border-transparent text-text-secondary hover:text-text-primary'"
            type="button"
        >
            Financial Summary
        </button>
    </div>

    {{-- ============================================================ --}}
    {{-- ATTENDANCE REPORT TAB                                        --}}
    {{-- ============================================================ --}}
    <div x-show="activeTab === 'attendance'" x-cloak>

        {{-- Filter card --}}
        <div class="bg-surface border border-border rounded-2xl shadow-card p-5 mb-5">
            <form method="GET" action="{{ $host }}/reports" class="flex flex-wrap items-end gap-4">
                <input type="hidden" name="tab" value="attendance">

                <div class="flex-1 min-w-[160px]">
                    <label class="block text-xs font-medium text-text-secondary mb-1.5 uppercase tracking-wide">Class</label>
                    <select
                        name="class_id"
                        x-model="classId"
                        @change="onClassChange()"
                        class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent"
                    >
                        <option value="">Select class&hellip;</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ $selectedClassId === $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div x-show="hasSections" x-cloak class="flex-1 min-w-[140px]">
                    <label class="block text-xs font-medium text-text-secondary mb-1.5 uppercase tracking-wide">Section</label>
                    <select
                        name="section_id"
                        x-model="sectionId"
                        class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent"
                    >
                        <option value="">All sections</option>
                        <template x-for="s in currentSections" :key="s.id">
                            <option :value="s.id" :selected="s.id === sectionId" x-text="s.name"></option>
                        </template>
                    </select>
                </div>

                <div class="flex-1 min-w-[140px]">
                    <label class="block text-xs font-medium text-text-secondary mb-1.5 uppercase tracking-wide">Date From</label>
                    <input
                        type="date"
                        name="date_from"
                        value="{{ $dateFrom }}"
                        class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent"
                    >
                </div>

                <div class="flex-1 min-w-[140px]">
                    <label class="block text-xs font-medium text-text-secondary mb-1.5 uppercase tracking-wide">Date To</label>
                    <input
                        type="date"
                        name="date_to"
                        value="{{ $dateTo }}"
                        class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent"
                    >
                </div>

                <button type="submit" class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors shrink-0">
                    Load Report
                </button>
            </form>
        </div>

        @if($activeTab === 'attendance' && $attendanceReport !== null)
            {{-- Results card --}}
            <div class="bg-surface border border-border rounded-2xl shadow-card">

                {{-- Card header --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-6 py-4 border-b border-border">
                    <div>
                        <h2 class="text-base font-semibold text-text-primary">
                            {{ $attendanceReport['class']->name }}
                            @if($attendanceReport['section'])
                                &mdash; {{ $attendanceReport['section']->name }}
                            @endif
                        </h2>
                        <p class="text-xs text-text-muted mt-0.5">
                            {{ $attendanceReport['date_from']->format('d M Y') }} &mdash; {{ $attendanceReport['date_to']->format('d M Y') }}
                            &middot; {{ count($attendanceReport['rows']) }} student(s)
                        </p>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <a
                            href="{{ $host }}/reports/attendance/pdf?{{ http_build_query(['class_id' => $selectedClassId, 'section_id' => $selectedSection, 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-surface border border-border text-text-primary rounded-md hover:bg-surface-secondary transition-colors"
                        >
                            <svg class="w-3.5 h-3.5 text-error" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                            Export PDF
                        </a>
                        <a
                            href="{{ $host }}/reports/attendance/excel?{{ http_build_query(['class_id' => $selectedClassId, 'section_id' => $selectedSection, 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-surface border border-border text-text-primary rounded-md hover:bg-surface-secondary transition-colors"
                        >
                            <svg class="w-3.5 h-3.5 text-success-foreground" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h1.5C5.496 19.5 6 18.996 6 18.375m-3.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-1.5A1.125 1.125 0 0118 18.375M20.625 4.5H3.375m17.25 0c.621 0 1.125.504 1.125 1.125M20.625 4.5h-1.5C18.504 4.5 18 5.004 18 5.625m3.75 0v1.5c0 .621-.504 1.125-1.125 1.125M3.375 4.5c-.621 0-1.125.504-1.125 1.125M3.375 4.5h1.5C5.496 4.5 6 5.004 6 5.625m-3.75 0v1.5c0 .621.504 1.125 1.125 1.125m0 0h1.5m-1.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m1.5-3.75C5.496 8.25 6 7.746 6 7.125v-1.5M4.875 8.25C5.496 8.25 6 8.754 6 9.375v1.5m0-5.25v5.25m0-5.25C6 5.004 6.504 4.5 7.125 4.5h9.75c.621 0 1.125.504 1.125 1.125m1.125 2.625h1.5m-1.5 0A1.125 1.125 0 0118 7.125v-1.5m1.125 2.625c-.621 0-1.125.504-1.125 1.125v1.5m2.625-2.625c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125M18 5.625v5.25M7.125 12h9.75m-9.75 0A1.125 1.125 0 016 10.875M7.125 12C6.504 12 6 12.504 6 13.125m0-2.25C6 11.496 5.496 12 4.875 12M18 10.875c0 .621-.504 1.125-1.125 1.125M18 10.875c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125m-12 5.25v-5.25m0 5.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125m-12 0v-1.5c0-.621-.504-1.125-1.125-1.125M19.875 19.5c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m-17.25 0h1.5m14.25 0h1.5"/></svg>
                            Export Excel
                        </a>
                    </div>
                </div>

                @if(count($attendanceReport['rows']) === 0)
                    <div class="flex flex-col items-center justify-center py-16 text-center">
                        <div class="w-12 h-12 rounded-xl bg-surface-secondary flex items-center justify-center mb-3">
                            <svg class="w-6 h-6 text-text-muted" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                        </div>
                        <p class="text-sm text-text-muted">No students found for the selected class/section.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full" style="min-width: 600px">
                            <thead>
                                <tr class="border-b border-border bg-surface-secondary">
                                    <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">#</th>
                                    <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Student</th>
                                    <th class="text-left px-4 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary hidden sm:table-cell">Adm. No.</th>
                                    <th class="text-center px-4 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Present</th>
                                    <th class="text-center px-4 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Absent</th>
                                    <th class="text-center px-4 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary hidden md:table-cell">Late</th>
                                    <th class="text-center px-4 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary hidden md:table-cell">Days Marked</th>
                                    <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">% Present</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($attendanceReport['rows'] as $i => $row)
                                    @php
                                        $pct = $row['percent_present'];
                                        $barColor = $pct >= 80 ? '#10B981' : ($pct >= 60 ? '#FF8904' : '#EF4444');
                                    @endphp
                                    <tr class="border-b border-border last:border-b-0 hover:bg-surface-secondary transition-colors">
                                        <td class="px-6 py-4 text-sm text-text-muted">{{ $i + 1 }}</td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-lg bg-accent-muted flex items-center justify-center text-xs font-semibold text-accent shrink-0">
                                                    {{ strtoupper(substr($row['student']->full_name, 0, 1)) }}
                                                </div>
                                                <span class="text-sm font-medium text-text-primary">{{ $row['student']->full_name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 text-sm text-text-muted hidden sm:table-cell">{{ $row['student']->admission_no }}</td>
                                        <td class="px-4 py-4 text-center">
                                            <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs font-medium bg-success-lightest text-success-foreground">{{ $row['present'] }}</span>
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            @if($row['absent'] > 0)
                                                <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs font-medium bg-error-light text-error">{{ $row['absent'] }}</span>
                                            @else
                                                <span class="text-sm text-text-muted">0</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 text-center hidden md:table-cell">
                                            @if($row['late'] > 0)
                                                <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs font-medium bg-warning-light text-warning">{{ $row['late'] }}</span>
                                            @else
                                                <span class="text-sm text-text-muted">0</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 text-center text-sm text-text-muted hidden md:table-cell">{{ $row['total_marked'] }}</td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-2">
                                                <div class="flex-1 h-1.5 rounded-full bg-border" style="min-width:60px; max-width:80px">
                                                    <div class="h-1.5 rounded-full" style="width: {{ min(100, $pct) }}%; background-color: {{ $barColor }}"></div>
                                                </div>
                                                <span class="text-sm font-medium text-text-primary shrink-0">{{ number_format($pct, 1) }}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        @elseif($activeTab === 'attendance')
            {{-- No report loaded yet --}}
            <div class="bg-surface border border-border rounded-2xl shadow-card flex flex-col items-center justify-center py-20 text-center">
                <div class="w-14 h-14 rounded-xl bg-surface-secondary flex items-center justify-center mb-4">
                    <svg class="w-7 h-7 text-text-muted" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/></svg>
                </div>
                <p class="text-sm font-medium text-text-primary mb-1">No report loaded</p>
                <p class="text-xs text-text-muted">Select a class and date range above, then click Load Report.</p>
            </div>
        @endif

    </div>{{-- /attendance tab --}}

    {{-- ============================================================ --}}
    {{-- FEE COLLECTION TAB                                           --}}
    {{-- ============================================================ --}}
    <div x-show="activeTab === 'fees'" x-cloak>

        {{-- Filter card --}}
        <div class="bg-surface border border-border rounded-2xl shadow-card p-5 mb-5">
            <form method="GET" action="{{ $host }}/reports" class="flex flex-wrap items-end gap-4">
                <input type="hidden" name="tab" value="fees">

                <div class=”flex-1 min-w-[180px]”>
                    <label class=”block text-xs font-medium text-text-secondary mb-1.5 uppercase tracking-wide”>Academic Year</label>
                    <select x-model=”feeYearId” @change=”feeTermId = ''”
                            class=”w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent”>
                        <option value=””>Select year&hellip;</option>
                        <template x-for=”y in academicYearsData” :key=”y.id”>
                            <option :value=”y.id” :selected=”y.id === feeYearId” x-text=”y.name”></option>
                        </template>
                    </select>
                </div>

                <div class=”flex-1 min-w-[180px]”>
                    <label class=”block text-xs font-medium text-text-secondary mb-1.5 uppercase tracking-wide”>Term</label>
                    <select name=”term_id” x-model=”feeTermId”
                            class=”w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent”
                            :disabled=”!feeYearId”>
                        <option value=””>Select term&hellip;</option>
                        <template x-for=”t in feeTermsForYear” :key=”t.id”>
                            <option :value=”t.id” :selected=”t.id === feeTermId” x-text=”t.name”></option>
                        </template>
                    </select>
                </div>

                <button type="submit" class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors shrink-0">
                    Load Report
                </button>
            </form>
        </div>

        @if($activeTab === 'fees' && $feeReport !== null)
            {{-- Results card --}}
            <div class="bg-surface border border-border rounded-2xl shadow-card">

                {{-- Card header --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-6 py-4 border-b border-border">
                    <div>
                        <h2 class="text-base font-semibold text-text-primary">
                            {{ $feeReport['term']->name }}
                            @if($feeReport['term']->academicYear)
                                &mdash; {{ $feeReport['term']->academicYear->name }}
                            @endif
                        </h2>
                        <p class="text-xs text-text-muted mt-0.5">{{ count($feeReport['rows']) }} fee structure(s)</p>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <a
                            href="{{ $host }}/reports/fees/pdf?{{ http_build_query(['term_id' => $selectedTermId]) }}"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-surface border border-border text-text-primary rounded-md hover:bg-surface-secondary transition-colors"
                        >
                            <svg class="w-3.5 h-3.5 text-error" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                            Export PDF
                        </a>
                        <a
                            href="{{ $host }}/reports/fees/excel?{{ http_build_query(['term_id' => $selectedTermId]) }}"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-surface border border-border text-text-primary rounded-md hover:bg-surface-secondary transition-colors"
                        >
                            <svg class="w-3.5 h-3.5 text-success-foreground" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h1.5C5.496 19.5 6 18.996 6 18.375m-3.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-1.5A1.125 1.125 0 0118 18.375M20.625 4.5H3.375m17.25 0c.621 0 1.125.504 1.125 1.125M20.625 4.5h-1.5C18.504 4.5 18 5.004 18 5.625m3.75 0v1.5c0 .621-.504 1.125-1.125 1.125M3.375 4.5c-.621 0-1.125.504-1.125 1.125M3.375 4.5h1.5C5.496 4.5 6 5.004 6 5.625m-3.75 0v1.5c0 .621.504 1.125 1.125 1.125m0 0h1.5m-1.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m1.5-3.75C5.496 8.25 6 7.746 6 7.125v-1.5M4.875 8.25C5.496 8.25 6 8.754 6 9.375v1.5m0-5.25v5.25m0-5.25C6 5.004 6.504 4.5 7.125 4.5h9.75c.621 0 1.125.504 1.125 1.125m1.125 2.625h1.5m-1.5 0A1.125 1.125 0 0118 7.125v-1.5m1.125 2.625c-.621 0-1.125.504-1.125 1.125v1.5m2.625-2.625c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125M18 5.625v5.25M7.125 12h9.75m-9.75 0A1.125 1.125 0 016 10.875M7.125 12C6.504 12 6 12.504 6 13.125m0-2.25C6 11.496 5.496 12 4.875 12M18 10.875c0 .621-.504 1.125-1.125 1.125M18 10.875c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125m-12 5.25v-5.25m0 5.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125m-12 0v-1.5c0-.621-.504-1.125-1.125-1.125M19.875 19.5c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m-17.25 0h1.5m14.25 0h1.5"/></svg>
                            Export Excel
                        </a>
                    </div>
                </div>

                {{-- Summary strip --}}
                <div class="grid grid-cols-3 gap-0 border-b border-border">
                    <div class="px-6 py-4 border-r border-border">
                        <p class="text-xs text-text-muted uppercase tracking-wide font-medium mb-1">Total Expected</p>
                        <p class="text-lg font-semibold text-text-primary">{{ format_money($feeReport['total_expected'], $currencySymbol) }}</p>
                    </div>
                    <div class="px-6 py-4 border-r border-border">
                        <p class="text-xs text-text-muted uppercase tracking-wide font-medium mb-1">Total Collected</p>
                        <p class="text-lg font-semibold text-success-foreground">{{ format_money($feeReport['total_collected'], $currencySymbol) }}</p>
                    </div>
                    <div class="px-6 py-4">
                        <p class="text-xs text-text-muted uppercase tracking-wide font-medium mb-1">Outstanding</p>
                        <p class="text-lg font-semibold {{ $feeReport['total_outstanding'] > 0 ? 'text-error' : 'text-success-foreground' }}">
                            {{ format_money($feeReport['total_outstanding'], $currencySymbol) }}
                        </p>
                    </div>
                </div>

                @if(count($feeReport['rows']) === 0)
                    <div class="flex flex-col items-center justify-center py-16 text-center">
                        <div class="w-12 h-12 rounded-xl bg-surface-secondary flex items-center justify-center mb-3">
                            <svg class="w-6 h-6 text-text-muted" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/></svg>
                        </div>
                        <p class="text-sm text-text-muted">No fee structures defined for this term.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full" style="min-width: 700px">
                            <thead>
                                <tr class="border-b border-border bg-surface-secondary">
                                    <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Class</th>
                                    <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Fee Item</th>
                                    <th class="text-right px-4 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary hidden md:table-cell">Amount / Student</th>
                                    <th class="text-center px-4 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary hidden md:table-cell">Students</th>
                                    <th class="text-right px-4 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Expected</th>
                                    <th class="text-right px-4 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Collected</th>
                                    <th class="text-right px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Outstanding</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($feeReport['rows'] as $row)
                                    <tr class="border-b border-border last:border-b-0 hover:bg-surface-secondary transition-colors">
                                        <td class="px-6 py-4 text-sm font-medium text-text-primary">{{ $row['class_label'] }}</td>
                                        <td class="px-6 py-4 text-sm text-text-primary">{{ $row['fee_structure']->fee_item }}</td>
                                        <td class="px-4 py-4 text-sm text-text-muted text-right hidden md:table-cell">{{ format_money((float)$row['fee_structure']->amount, $currencySymbol) }}</td>
                                        <td class="px-4 py-4 text-sm text-text-muted text-center hidden md:table-cell">{{ $row['student_count'] }}</td>
                                        <td class="px-4 py-4 text-sm text-text-primary text-right">{{ format_money($row['expected'], $currencySymbol) }}</td>
                                        <td class="px-4 py-4 text-sm text-right">
                                            <span class="{{ $row['collected'] > 0 ? 'text-success-foreground font-medium' : 'text-text-muted' }}">
                                                {{ format_money($row['collected'], $currencySymbol) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-right">
                                            @if($row['outstanding'] > 0)
                                                <span class="text-error font-medium">{{ format_money($row['outstanding'], $currencySymbol) }}</span>
                                            @else
                                                <span class="inline-flex items-center gap-1 text-success-foreground font-medium">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                                    Cleared
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="border-t-2 border-border bg-surface-secondary">
                                    <td class="px-6 py-4 text-sm font-semibold text-text-primary" colspan="2">Total</td>
                                    <td class="hidden md:table-cell"></td>
                                    <td class="hidden md:table-cell"></td>
                                    <td class="px-4 py-4 text-sm font-semibold text-text-primary text-right">{{ format_money($feeReport['total_expected'], $currencySymbol) }}</td>
                                    <td class="px-4 py-4 text-sm font-semibold text-success-foreground text-right">{{ format_money($feeReport['total_collected'], $currencySymbol) }}</td>
                                    <td class="px-6 py-4 text-sm font-semibold text-right {{ $feeReport['total_outstanding'] > 0 ? 'text-error' : 'text-success-foreground' }}">
                                        {{ format_money($feeReport['total_outstanding'], $currencySymbol) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif
            </div>

        @elseif($activeTab === 'fees')
            {{-- No report loaded yet --}}
            <div class="bg-surface border border-border rounded-2xl shadow-card flex flex-col items-center justify-center py-20 text-center">
                <div class="w-14 h-14 rounded-xl bg-surface-secondary flex items-center justify-center mb-4">
                    <svg class="w-7 h-7 text-text-muted" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/></svg>
                </div>
                <p class="text-sm font-medium text-text-primary mb-1">No report loaded</p>
                <p class="text-xs text-text-muted">Select an academic year and term above, then click Load Report.</p>
            </div>
        @endif

    </div>{{-- /fees tab --}}

    {{-- ============================================================ --}}
    {{-- ATTENDANCE ALERTS TAB                                        --}}
    {{-- ============================================================ --}}
    <div x-show="activeTab === 'alerts'" x-cloak>

        {{-- Filter card --}}
        <div class="bg-surface border border-border rounded-2xl shadow-card p-5 mb-5">
            <form method="GET" action="{{ $host }}/reports" class="flex flex-wrap items-end gap-4">
                <input type="hidden" name="tab" value="alerts">

                {{-- Class --}}
                <div class="flex-1 min-w-40">
                    <label class="block text-xs font-medium text-text-secondary mb-1.5 uppercase tracking-wide">Class</label>
                    <select name="class_id" x-model="classId" @change="onClassChange()"
                            class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent">
                        <option value="">Select class&hellip;</option>
                        @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ $selectedClassId === $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Section (conditional) --}}
                <div x-show="hasSections" x-cloak class="flex-1 min-w-35">
                    <label class="block text-xs font-medium text-text-secondary mb-1.5 uppercase tracking-wide">Section</label>
                    <select name="section_id" x-model="sectionId"
                            class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent">
                        <option value="">All sections</option>
                        <template x-for="s in currentSections" :key="s.id">
                            <option :value="s.id" :selected="s.id === sectionId" x-text="s.name"></option>
                        </template>
                    </select>
                </div>

                {{-- Year (alerts) --}}
                <div class="flex-1 min-w-40">
                    <label class="block text-xs font-medium text-text-secondary mb-1.5 uppercase tracking-wide">Academic Year</label>
                    <select x-model="alertYearId" @change="alertTermId = ''"
                            class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent">
                        <option value="">Select year&hellip;</option>
                        <template x-for="y in academicYearsData" :key="y.id">
                            <option :value="y.id" :selected="y.id === alertYearId" x-text="y.name"></option>
                        </template>
                    </select>
                </div>

                {{-- Term (alerts) --}}
                <div class="flex-1 min-w-40">
                    <label class="block text-xs font-medium text-text-secondary mb-1.5 uppercase tracking-wide">Term</label>
                    <select name="term_id" x-model="alertTermId"
                            class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent"
                            :disabled="!alertYearId">
                        <option value="">Select term&hellip;</option>
                        <template x-for="t in alertTermsForYear" :key="t.id">
                            <option :value="t.id" :selected="t.id === alertTermId" x-text="t.name"></option>
                        </template>
                    </select>
                </div>

                {{-- Threshold --}}
                <div class="flex-1 min-w-40" x-data="{ thresh: {{ $selectedThreshold }} }">
                    <label class="block text-xs font-medium text-text-secondary mb-1.5 uppercase tracking-wide">
                        Absence threshold &mdash; <span class="text-error font-semibold" x-text="thresh + '%'"></span>
                    </label>
                    <input type="range" name="threshold" min="1" max="99" step="1"
                           x-model.number="thresh"
                           value="{{ $selectedThreshold }}"
                           class="w-full h-1.5 bg-border rounded-full appearance-none cursor-pointer accent-accent">
                    <div class="flex justify-between text-[10px] text-text-muted mt-0.5">
                        <span>1%</span><span>Below this % present = alert</span><span>99%</span>
                    </div>
                </div>

                <button type="submit"
                        class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors shrink-0">
                    Load Alerts
                </button>
            </form>
        </div>

        @if($absenteesReport !== null)

        {{-- Bulk notify form --}}
        @if(count($absenteesReport['rows']) > 0)
        <form method="POST" action="{{ $host }}/attendance/notify-bulk" class="mb-4">
            @csrf
            <input type="hidden" name="term_id" value="{{ $absenteesReport['term']->id }}">
            <input type="hidden" name="class_id" value="{{ $selectedClassId }}">
            <input type="hidden" name="section_id" value="{{ $selectedSection }}">
            <input type="hidden" name="threshold" value="{{ $absenteesReport['threshold'] }}">
            <div class="flex items-center justify-between gap-4 bg-error-light border border-error rounded-xl px-5 py-3">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 text-error shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                    </svg>
                    <p class="text-sm font-medium text-error">
                        {{ count($absenteesReport['rows']) }} student(s) below {{ $absenteesReport['threshold'] }}% attendance
                        &mdash; {{ $absenteesReport['term']->name }}
                    </p>
                </div>
                <button type="submit"
                        class="shrink-0 px-4 py-2 bg-error text-white text-sm font-medium rounded-md hover:opacity-90 transition-opacity">
                    Notify All Guardians
                </button>
            </div>
        </form>
        @endif

        {{-- Results table --}}
        <div class="bg-surface border border-border rounded-2xl shadow-card overflow-hidden">
            {{-- Card header --}}
            <div class="px-6 py-4 border-b border-border">
                <h3 class="text-base font-semibold text-text-primary">
                    Attendance Alerts &mdash; {{ $absenteesReport['class']->name }}{{ $absenteesReport['section'] ? ' / ' . $absenteesReport['section']->name : '' }}
                </h3>
                <p class="text-xs text-text-muted mt-0.5">
                    {{ $absenteesReport['term']->name }} &middot;
                    Students below {{ $absenteesReport['threshold'] }}% attendance
                    @if(count($absenteesReport['rows']) === 0)
                        &middot; <span class=”text-success-foreground”>No alerts &mdash; all students meet the threshold</span>
                    @endif
                </p>
            </div>

            @if(count($absenteesReport['rows']) > 0)
            <div class="overflow-x-auto">
                <table class="w-full" style="min-width:640px">
                    <thead>
                        <tr class="border-b border-border bg-surface-secondary">
                            <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Student</th>
                            <th class="text-center px-4 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Absences</th>
                            <th class="text-center px-4 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Days Marked</th>
                            <th class="text-center px-4 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">% Present</th>
                            <th class="text-left px-4 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary hidden md:table-cell">Guardian</th>
                            <th class="px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($absenteesReport['rows'] as $row)
                        @php
                            $pct    = $row['percent_present'];
                            $color  = $pct < 50 ? 'text-error' : ($pct < 70 ? 'text-warning' : 'text-text-secondary');
                        @endphp
                        <tr class="border-b border-border last:border-b-0 hover:bg-surface-secondary transition-colors">
                            <td class="px-6 py-4">
                                <a href="{{ $host }}/students/{{ $row['student']->id }}"
                                   class="text-sm font-medium text-text-primary hover:text-accent transition-colors">
                                    {{ $row['student']->full_name }}
                                </a>
                                @if($row['student']->admission_no)
                                <p class="text-xs text-text-muted">{{ $row['student']->admission_no }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-sm font-semibold text-error text-center">{{ $row['absent'] }}</td>
                            <td class="px-4 py-4 text-sm text-text-secondary text-center">{{ $row['days_marked'] }}</td>
                            <td class="px-4 py-4 text-sm font-semibold text-center {{ $color }}">{{ $pct }}%</td>
                            <td class="px-4 py-4 hidden md:table-cell">
                                @if($row['guardian_name'])
                                <p class="text-sm text-text-primary">{{ $row['guardian_name'] }}</p>
                                @endif
                                @if($row['guardian_contact'])
                                <p class="text-xs text-text-muted">{{ $row['guardian_contact'] }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($row['guardian_email'])
                                <form method="POST" action="{{ $host }}/attendance/notify/{{ $row['student']->id }}">
                                    @csrf
                                    <input type="hidden" name="term_id" value="{{ $absenteesReport['term']->id }}">
                                    <input type="hidden" name="percent_present" value="{{ $pct }}">
                                    <button type="submit"
                                            class="text-xs font-medium px-3 py-1.5 bg-surface border border-border text-text-secondary rounded-md hover:bg-surface-secondary hover:text-text-primary transition-colors">
                                        Notify Guardian
                                    </button>
                                </form>
                                @else
                                <span class="text-xs text-text-muted italic">No email</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <div class="w-10 h-10 rounded-xl bg-success-lightest flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-success-foreground" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-text-primary mb-1">All students meet the threshold</p>
                <p class="text-xs text-text-muted">No students are below {{ $absenteesReport['threshold'] }}% attendance for this term.</p>
            </div>
            @endif
        </div>

        @elseif($activeTab === 'alerts')
        {{-- Initial state --}}
        <div class="bg-surface border border-border rounded-2xl shadow-card flex flex-col items-center justify-center py-20 text-center">
            <div class="w-14 h-14 rounded-xl bg-surface-secondary flex items-center justify-center mb-4">
                <svg class="w-7 h-7 text-text-muted" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-text-primary mb-1">No report loaded</p>
            <p class="text-xs text-text-muted">Select a class and term above, then click Load Alerts.</p>
        </div>
        @endif

    </div>{{-- /alerts tab --}}

    {{-- ============================================================ --}}
    {{-- ACADEMIC ANALYTICS TAB                                       --}}
    {{-- ============================================================ --}}
    <div x-show="activeTab === 'academic'" x-cloak>

        {{-- Filter card --}}
        <div class="bg-surface border border-border rounded-2xl shadow-card p-5 mb-5">
            <form method="GET" action="{{ $host }}/reports" class="flex flex-wrap items-end gap-4">
                <input type="hidden" name="tab" value="academic">

                {{-- Year --}}
                <div class="flex-1 min-w-40">
                    <label class="block text-xs font-medium text-text-secondary mb-1.5 uppercase tracking-wide">Academic Year</label>
                    <select x-model="academicYearId" @change="academicTermId = ''"
                            class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent">
                        <option value="">All years</option>
                        <template x-for="y in academicYearsData" :key="y.id">
                            <option :value="y.id" :selected="y.id === academicYearId" x-text="y.name"></option>
                        </template>
                    </select>
                </div>

                {{-- Term (filtered by year via Alpine) --}}
                <div class="flex-1 min-w-40">
                    <label class="block text-xs font-medium text-text-secondary mb-1.5 uppercase tracking-wide">Term</label>
                    <select name="term_id" x-model="academicTermId"
                            class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent">
                        <option value="">All terms</option>
                        <template x-for="t in academicTermsForYear" :key="t.id">
                            <option :value="t.id" :selected="t.id === academicTermId" x-text="t.name"></option>
                        </template>
                    </select>
                </div>

                {{-- Exam (filtered by term via Alpine) --}}
                <div class="flex-1 min-w-40">
                    <label class="block text-xs font-medium text-text-secondary mb-1.5 uppercase tracking-wide">Exam</label>
                    <select name="exam_id" x-model="academicExamId"
                            class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent">
                        <option value=””>&mdash; Select exam &mdash;</option>
                        <template x-for="e in filteredExams" :key="e.id">
                            <option :value="e.id" :selected="e.id === academicExamId" x-text="e.name"></option>
                        </template>
                    </select>
                </div>

                {{-- Class --}}
                <div class="flex-1 min-w-40">
                    <label class="block text-xs font-medium text-text-secondary mb-1.5 uppercase tracking-wide">Class</label>
                    <select name="class_id" x-model="classId" @change="onClassChange()"
                            class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent">
                        <option value="">Select class&hellip;</option>
                        @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ $selectedClassId === $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Section (conditional) --}}
                <div x-show="hasSections" x-cloak class="flex-1 min-w-35">
                    <label class="block text-xs font-medium text-text-secondary mb-1.5 uppercase tracking-wide">Section</label>
                    <select name="section_id" x-model="sectionId"
                            class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent">
                        <option value="">All sections</option>
                        <template x-for="s in currentSections" :key="s.id">
                            <option :value="s.id" :selected="s.id === sectionId" x-text="s.name"></option>
                        </template>
                    </select>
                </div>

                <button type="submit"
                        class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors shrink-0">
                    Load Report
                </button>
            </form>
        </div>

        @if($analyticsReport !== null && count($analyticsReport['subjects']) > 0)
        {{-- Results card --}}
        <div class="bg-surface border border-border rounded-2xl shadow-card">

            {{-- Card header --}}
            <div class="px-6 py-4 border-b border-border flex items-center justify-between gap-4">
                <div>
                    <h3 class="text-base font-semibold text-text-primary">
                        {{ $analyticsReport['exam']?->name }}
                        &mdash; {{ $analyticsReport['class']?->name }}{{ $analyticsReport['section'] ? ' / ' . $analyticsReport['section']->name : '' }}
                    </h3>
                    <p class="text-xs text-text-muted mt-0.5">
                        Passing threshold: {{ $analyticsReport['pass_threshold'] }}%
                        @if($analyticsReport['exam']?->term)
                         &middot; {{ $analyticsReport['exam']->term->name }}
                        @endif
                    </p>
                </div>
                <a href="{{ $host }}/reports/academic/pdf?{{ http_build_query(['exam_id' => $selectedExamId, 'class_id' => $selectedClassId, 'section_id' => $selectedSection]) }}"
                   class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-surface border border-border text-text-primary rounded-md hover:bg-surface-secondary transition-colors">
                    <svg class="w-3.5 h-3.5 text-error" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                    </svg>
                    Export PDF
                </a>
            </div>

            {{-- Charts grid --}}
            <div class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-6 border-b border-border">

                {{-- Subject Averages --}}
                <div class="bg-surface-secondary rounded-xl p-4">
                    <p class="text-xs font-medium text-text-secondary uppercase tracking-wide mb-3">Subject Averages</p>
                    <div style="position:relative; height:{{ max(120, count($analyticsReport['subjects']) * 36) }}px">
                        <canvas id="avgChart"></canvas>
                    </div>
                </div>

                {{-- Pass Rate --}}
                <div class="bg-surface-secondary rounded-xl p-4">
                    <p class="text-xs font-medium text-text-secondary uppercase tracking-wide mb-3">Pass Rate (%)</p>
                    <div style="position:relative; height:{{ max(120, count($analyticsReport['subjects']) * 36) }}px">
                        <canvas id="passChart"></canvas>
                    </div>
                </div>
            </div>

            @if(count($trendData) > 1)
            {{-- Class Trend --}}
            <div class="p-6 border-b border-border">
                <p class="text-xs font-medium text-text-secondary uppercase tracking-wide mb-3">Class Performance Trend</p>
                <div style="position:relative; height:200px">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
            @endif

            {{-- Summary table --}}
            <div class="overflow-x-auto">
                <table class="w-full" style="min-width:600px">
                    <thead>
                        <tr class="border-b border-border bg-surface-secondary">
                            <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Subject</th>
                            <th class="text-center px-4 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Students</th>
                            <th class="text-center px-4 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Avg Score</th>
                            <th class="text-center px-4 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary hidden md:table-cell">Highest</th>
                            <th class="text-center px-4 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary hidden md:table-cell">Lowest</th>
                            <th class="text-center px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Pass Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($analyticsReport['subjects'] as $row)
                        @php
                            $passOk = $row['pass_rate'] >= 50;
                        @endphp
                        <tr class="border-b border-border last:border-b-0 hover:bg-surface-secondary transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-text-primary">{{ $row['subject_name'] }}</td>
                            <td class="px-4 py-4 text-sm text-text-secondary text-center">{{ $row['student_count'] }}</td>
                            <td class="px-4 py-4 text-sm font-semibold text-text-primary text-center">{{ $row['avg_score'] }}</td>
                            <td class="px-4 py-4 text-sm text-text-secondary text-center hidden md:table-cell">{{ $row['highest'] }}</td>
                            <td class="px-4 py-4 text-sm text-text-secondary text-center hidden md:table-cell">{{ $row['lowest'] }}</td>
                            <td class="px-6 py-4 text-sm font-semibold text-center">
                                <span class="{{ $passOk ? 'text-success-foreground' : 'text-error' }}">
                                    {{ $row['pass_rate'] }}%
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @elseif(count($trendData) > 0 && $analyticsReport === null)
        {{-- Trend-only view (no exam selected) --}}
        <div class="bg-surface border border-border rounded-2xl shadow-card p-6">
            <p class="text-xs font-medium text-text-secondary uppercase tracking-wide mb-3">Class Performance Trend</p>
            <div style="position:relative; height:220px">
                <canvas id="trendChart"></canvas>
            </div>
        </div>

        @elseif($activeTab === 'academic' && request()->filled('class_id'))
        {{-- Filters applied but no data --}}
        <div class="bg-surface border border-border rounded-2xl shadow-card flex flex-col items-center justify-center py-16 text-center">
            <div class="w-12 h-12 rounded-xl bg-surface-secondary flex items-center justify-center mb-3">
                <svg class="w-6 h-6 text-text-muted" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-text-primary mb-1">No results found</p>
            <p class="text-xs text-text-muted">No exam results exist for the selected filters.</p>
        </div>

        @elseif($activeTab === 'academic')
        {{-- Initial state --}}
        <div class="bg-surface border border-border rounded-2xl shadow-card flex flex-col items-center justify-center py-20 text-center">
            <div class="w-14 h-14 rounded-xl bg-surface-secondary flex items-center justify-center mb-4">
                <svg class="w-7 h-7 text-text-muted" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-text-primary mb-1">No report loaded</p>
            <p class="text-xs text-text-muted">Select a class and exam above, then click Load Report.</p>
        </div>
        @endif

    </div>{{-- /academic tab --}}

    {{-- ============================================================ --}}
    {{-- FINANCIAL SUMMARY TAB                                        --}}
    {{-- ============================================================ --}}
    <div x-show="activeTab === 'financial'" x-cloak>

        {{-- Filter card --}}
        <div class="bg-surface border border-border rounded-2xl shadow-card p-5 mb-5">
            <form method="GET" action="{{ $host }}/reports" class="flex flex-wrap items-end gap-4">
                <input type="hidden" name="tab" value="financial">

                <div class="flex-1 min-w-[180px]">
                    <label class="block text-xs font-medium text-text-secondary mb-1.5 uppercase tracking-wide">Academic Year</label>
                    <select name="financial_year_id" x-model="financialYearId" @change="financialTermId = ''"
                            class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent">
                        <option value="">Select year&hellip;</option>
                        <template x-for="y in academicYearsData" :key="y.id">
                            <option :value="y.id" :selected="y.id === financialYearId" x-text="y.name"></option>
                        </template>
                    </select>
                </div>

                <div class="flex-1 min-w-[160px]">
                    <label class="block text-xs font-medium text-text-secondary mb-1.5 uppercase tracking-wide">Term (optional)</label>
                    <select name="financial_term_id" x-model="financialTermId"
                            class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent">
                        <option value="">Full Year</option>
                        <template x-for="t in financialTermsForYear" :key="t.id">
                            <option :value="t.id" :selected="t.id === financialTermId" x-text="t.name"></option>
                        </template>
                    </select>
                </div>

                <button type="submit"
                        class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors shrink-0">
                    Load Summary
                </button>
            </form>
        </div>

        @if($activeTab === 'financial' && $financialReport !== null)

        {{-- Summary cards --}}
        <div class="grid grid-cols-3 gap-4 mb-5">
            <div class="bg-surface border border-border rounded-2xl shadow-card p-5">
                <p class="text-xs font-medium text-text-muted uppercase tracking-wide mb-1">Total Income</p>
                <p class="text-2xl font-semibold text-success-foreground">{{ format_money($financialReport['income_total'], $currencySymbol) }}</p>
                <p class="text-xs text-text-muted mt-1">Fee collections received</p>
            </div>
            <div class="bg-surface border border-border rounded-2xl shadow-card p-5">
                <p class="text-xs font-medium text-text-muted uppercase tracking-wide mb-1">Total Expenses</p>
                <p class="text-2xl font-semibold text-error">{{ format_money($financialReport['expense_total'], $currencySymbol) }}</p>
                <p class="text-xs text-text-muted mt-1">Recorded expenditure</p>
            </div>
            <div class="bg-surface border border-border rounded-2xl shadow-card p-5">
                <p class="text-xs font-medium text-text-muted uppercase tracking-wide mb-1">Net Balance</p>
                <p class="text-2xl font-semibold {{ $financialReport['net'] >= 0 ? 'text-success-foreground' : 'text-error' }}">
                    {{ format_money(abs($financialReport['net']), $currencySymbol) }}
                    @if($financialReport['net'] < 0)<span class="text-base font-normal">(deficit)</span>@endif
                </p>
                <p class="text-xs text-text-muted mt-1">Income minus expenses</p>
            </div>
        </div>

        {{-- Export + Period --}}
        <div class="flex items-center justify-between mb-4">
            <p class="text-xs text-text-muted">
                Period:
                <span class="font-medium text-text-primary">
                    @if($financialReport['term'])
                        {{ $financialReport['term']->name }} &mdash; {{ $financialReport['academic_year']->name }}
                    @else
                        {{ $financialReport['academic_year']->name }} (Full Year)
                    @endif
                </span>
                &middot; {{ $financialReport['date_from']->format('d M Y') }} &mdash; {{ $financialReport['date_to']->format('d M Y') }}
            </p>
            <a href="{{ $host }}/reports/financial/pdf?financial_year_id={{ $selectedFinancialYearId }}&financial_term_id={{ $selectedFinancialTermId }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-surface border border-border rounded-md hover:bg-surface-secondary transition-colors text-text-primary">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export PDF
            </a>
        </div>

        {{-- Monthly trend chart --}}
        @if(count($financialReport['monthly_trend']) > 0)
        <div class="bg-surface border border-border rounded-2xl shadow-card p-5 mb-5">
            <h3 class="text-sm font-semibold text-text-primary mb-4">Monthly Income vs Expenses</h3>
            <div style="height: 220px; position: relative;">
                <canvas id="financialTrendChart"></canvas>
            </div>
        </div>
        @endif

        {{-- Breakdown tables --}}
        <div class="grid grid-cols-2 gap-4">
            {{-- Income breakdown --}}
            <div class="bg-surface border border-border rounded-2xl shadow-card overflow-hidden">
                <div class="px-5 py-4 border-b border-border">
                    <h3 class="text-sm font-semibold text-text-primary">Income by Fee Item</h3>
                </div>
                @if(empty($financialReport['income_by_category']))
                <p class="px-5 py-8 text-sm text-text-muted text-center">No fee payments recorded.</p>
                @else
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-surface-secondary">
                            <th class="px-5 py-2.5 text-left text-xs font-semibold text-text-muted uppercase tracking-wide">Fee Item</th>
                            <th class="px-5 py-2.5 text-right text-xs font-semibold text-text-muted uppercase tracking-wide">Collected</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach($financialReport['income_by_category'] as $row)
                        <tr class="hover:bg-surface-secondary transition-colors">
                            <td class="px-5 py-2.5 text-text-primary">{{ $row['label'] }}</td>
                            <td class="px-5 py-2.5 text-right font-medium text-success-foreground">{{ format_money($row['amount'], $currencySymbol) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-border bg-surface-secondary">
                            <td class="px-5 py-2.5 text-xs font-semibold text-text-muted uppercase">Total</td>
                            <td class="px-5 py-2.5 text-right font-semibold text-success-foreground">{{ format_money($financialReport['income_total'], $currencySymbol) }}</td>
                        </tr>
                    </tfoot>
                </table>
                @endif
            </div>

            {{-- Expense breakdown --}}
            <div class="bg-surface border border-border rounded-2xl shadow-card overflow-hidden">
                <div class="px-5 py-4 border-b border-border">
                    <h3 class="text-sm font-semibold text-text-primary">Expenses by Category</h3>
                </div>
                @if(empty($financialReport['expense_by_category']))
                <p class="px-5 py-8 text-sm text-text-muted text-center">No expenses recorded for this period.</p>
                @else
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-surface-secondary">
                            <th class="px-5 py-2.5 text-left text-xs font-semibold text-text-muted uppercase tracking-wide">Category</th>
                            <th class="px-5 py-2.5 text-right text-xs font-semibold text-text-muted uppercase tracking-wide">Spent</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach($financialReport['expense_by_category'] as $row)
                        <tr class="hover:bg-surface-secondary transition-colors">
                            <td class="px-5 py-2.5 text-text-primary">{{ $row['label'] }}</td>
                            <td class="px-5 py-2.5 text-right font-medium text-error">{{ format_money($row['amount'], $currencySymbol) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-border bg-surface-secondary">
                            <td class="px-5 py-2.5 text-xs font-semibold text-text-muted uppercase">Total</td>
                            <td class="px-5 py-2.5 text-right font-semibold text-error">{{ format_money($financialReport['expense_total'], $currencySymbol) }}</td>
                        </tr>
                    </tfoot>
                </table>
                @endif
            </div>
        </div>

        @elseif($activeTab === 'financial')
        {{-- No data yet --}}
        <div class="bg-surface border border-border rounded-2xl shadow-card flex flex-col items-center justify-center py-20 text-center">
            <div class="w-14 h-14 rounded-xl bg-surface-secondary flex items-center justify-center mb-4">
                <svg class="w-7 h-7 text-text-muted" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-text-primary mb-1">No summary loaded</p>
            <p class="text-xs text-text-muted">Select an academic year above and click Load Summary.</p>
        </div>
        @endif

    </div>{{-- /financial tab --}}

</div>{{-- /x-data --}}
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
Alpine.data('reportsPage', () => {
const _d = window.__reportsPage;
const classes = _d.classes;
const examsData = _d.examsForJs;
const chartData = _d.chartData;
const financialChartData = _d.financialChartData;
const academicYearsData = _d.academicYearsForJs;
return ({
    activeTab: _d.activeTab,
    classId: _d.selectedClassId || '',
    sectionId: _d.selectedSection || '',
    academicYearId: _d.selectedTermYearId || '',
    academicTermId: _d.selectedTermId,
    academicExamId: _d.selectedExamId,
    financialYearId: _d.selectedFinancialYearId || '',
    financialTermId: _d.selectedFinancialTermId || '',
    feeYearId: _d.selectedTermYearId || '',
    feeTermId: _d.selectedTermId,
    alertYearId: _d.selectedTermYearId || '',
    alertTermId: _d.selectedTermId,
    get academicTermsForYear() {
        if (!this.academicYearId) return academicYearsData.flatMap(y => y.terms);
        const y = academicYearsData.find(y => y.id === this.academicYearId);
        return y ? y.terms : [];
    },
    get financialTermsForYear() {
        const y = academicYearsData.find(y => y.id === this.financialYearId);
        return y ? y.terms : [];
    },
    get feeTermsForYear() {
        const y = academicYearsData.find(y => y.id === this.feeYearId);
        return y ? y.terms : [];
    },
    get alertTermsForYear() {
        const y = academicYearsData.find(y => y.id === this.alertYearId);
        return y ? y.terms : [];
    },

    get currentClass() {
        return classes.find(c => c.id === this.classId) || null;
    },
    get currentSections() {
        return this.currentClass ? this.currentClass.sections : [];
    },
    get hasSections() {
        return this.currentSections.length > 0;
    },
    get filteredExams() {
        if (this.academicTermId) return examsData.filter(e => e.term_id === this.academicTermId);
        if (this.academicYearId) {
            const termIds = this.academicTermsForYear.map(t => t.id);
            return examsData.filter(e => termIds.includes(e.term_id));
        }
        return examsData;
    },
    onClassChange() {
        this.sectionId = '';
    },

    initAcademicCharts() {
        const accentColor = getComputedStyle(document.documentElement).getPropertyValue('--color-accent').trim() || '#2563eb';
        const successColor = getComputedStyle(document.documentElement).getPropertyValue('--color-success-foreground').trim() || '#16a34a';

        const commonBarOpts = (maxVal) => ({
            type: 'bar',
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { min: 0, max: maxVal, grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { font: { size: 11 } } },
                    y: { ticks: { font: { size: 11 } }, grid: { display: false } },
                },
            },
        });

        const avgCanvas = document.getElementById('avgChart');
        if (avgCanvas && chartData.labels.length) {
            const opts = commonBarOpts(100);
            new Chart(avgCanvas, {
                ...opts,
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        data: chartData.avgScores,
                        backgroundColor: accentColor,
                        borderRadius: 4,
                        barThickness: 14,
                    }],
                },
            });
        }

        const passCanvas = document.getElementById('passChart');
        if (passCanvas && chartData.labels.length) {
            const opts = commonBarOpts(100);
            new Chart(passCanvas, {
                ...opts,
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        data: chartData.passRates,
                        backgroundColor: successColor,
                        borderRadius: 4,
                        barThickness: 14,
                    }],
                },
            });
        }

        const trendCanvas = document.getElementById('trendChart');
        if (trendCanvas && chartData.trendLabels.length > 1) {
            new Chart(trendCanvas, {
                type: 'line',
                data: {
                    labels: chartData.trendLabels,
                    datasets: [{
                        data: chartData.trendAverages,
                        borderColor: accentColor,
                        backgroundColor: accentColor + '22',
                        tension: 0.3,
                        fill: true,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { font: { size: 11 } } },
                        y: { min: 0, max: 100, ticks: { font: { size: 11 } }, grid: { color: 'rgba(0,0,0,0.05)' } },
                    },
                },
            });
        }
    },

    initFinancialChart() {
        const canvas = document.getElementById('financialTrendChart');
        if (!canvas || !financialChartData.labels.length) return;

        new Chart(canvas, {
            type: 'bar',
            data: {
                labels: financialChartData.labels,
                datasets: [
                    {
                        label: 'Income',
                        data: financialChartData.income,
                        backgroundColor: 'rgba(22,163,74,0.6)',
                        borderColor: '#16a34a',
                        borderWidth: 1,
                        borderRadius: 4,
                    },
                    {
                        label: 'Expenses',
                        data: financialChartData.expenses,
                        backgroundColor: 'rgba(220,38,38,0.6)',
                        borderColor: '#dc2626',
                        borderWidth: 1,
                        borderRadius: 4,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: true, position: 'top', labels: { font: { size: 11 } } },
                },
                scales: {
                    x: { grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { font: { size: 10 } } },
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { font: { size: 11 } } },
                },
            },
        });
    },

    init() {
        if (this.activeTab === 'academic') {
            this.$nextTick(() => this.initAcademicCharts());
        }
        if (this.activeTab === 'financial') {
            this.$nextTick(() => this.initFinancialChart());
        }
    },
});
});
});
</script>
@endpush
