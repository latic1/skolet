@extends('layouts.tenant')

@section('title', 'Reports')

@section('page-title', 'Reports')

@section('content')
@php $host = request()->getSchemeAndHttpHost(); @endphp
<div x-data="reportsPage(
    {{ Js::from($classes->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'sections' => $c->sections->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->values()])->values()) }},
    '{{ $selectedClassId }}',
    '{{ $selectedSection }}',
    '{{ $activeTab }}'
)">

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
                        <option value="">Select class…</option>
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
                                — {{ $attendanceReport['section']->name }}
                            @endif
                        </h2>
                        <p class="text-xs text-text-muted mt-0.5">
                            {{ $attendanceReport['date_from']->format('d M Y') }} — {{ $attendanceReport['date_to']->format('d M Y') }}
                            · {{ count($attendanceReport['rows']) }} student(s)
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

                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-medium text-text-secondary mb-1.5 uppercase tracking-wide">Term</label>
                    <select
                        name="term_id"
                        class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent"
                    >
                        <option value="">Select term…</option>
                        @foreach($terms as $term)
                            <option value="{{ $term->id }}" {{ $selectedTermId === $term->id ? 'selected' : '' }}>
                                {{ $term->name }}
                                @if($term->academicYear)
                                    ({{ $term->academicYear->name }})
                                @endif
                                @if($term->is_current)
                                    — Current
                                @endif
                            </option>
                        @endforeach
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
                                — {{ $feeReport['term']->academicYear->name }}
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
                        <p class="text-lg font-semibold text-text-primary">{{ number_format($feeReport['total_expected'], 2) }}</p>
                    </div>
                    <div class="px-6 py-4 border-r border-border">
                        <p class="text-xs text-text-muted uppercase tracking-wide font-medium mb-1">Total Collected</p>
                        <p class="text-lg font-semibold text-success-foreground">{{ number_format($feeReport['total_collected'], 2) }}</p>
                    </div>
                    <div class="px-6 py-4">
                        <p class="text-xs text-text-muted uppercase tracking-wide font-medium mb-1">Outstanding</p>
                        <p class="text-lg font-semibold {{ $feeReport['total_outstanding'] > 0 ? 'text-error' : 'text-success-foreground' }}">
                            {{ number_format($feeReport['total_outstanding'], 2) }}
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
                                        <td class="px-6 py-4 text-sm font-medium text-text-primary">{{ $row['class']->name }}</td>
                                        <td class="px-6 py-4 text-sm text-text-primary">{{ $row['fee_structure']->fee_item }}</td>
                                        <td class="px-4 py-4 text-sm text-text-muted text-right hidden md:table-cell">{{ number_format((float)$row['fee_structure']->amount, 2) }}</td>
                                        <td class="px-4 py-4 text-sm text-text-muted text-center hidden md:table-cell">{{ $row['student_count'] }}</td>
                                        <td class="px-4 py-4 text-sm text-text-primary text-right">{{ number_format($row['expected'], 2) }}</td>
                                        <td class="px-4 py-4 text-sm text-right">
                                            <span class="{{ $row['collected'] > 0 ? 'text-success-foreground font-medium' : 'text-text-muted' }}">
                                                {{ number_format($row['collected'], 2) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-right">
                                            @if($row['outstanding'] > 0)
                                                <span class="text-error font-medium">{{ number_format($row['outstanding'], 2) }}</span>
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
                                    <td class="px-4 py-4 text-sm font-semibold text-text-primary text-right">{{ number_format($feeReport['total_expected'], 2) }}</td>
                                    <td class="px-4 py-4 text-sm font-semibold text-success-foreground text-right">{{ number_format($feeReport['total_collected'], 2) }}</td>
                                    <td class="px-6 py-4 text-sm font-semibold text-right {{ $feeReport['total_outstanding'] > 0 ? 'text-error' : 'text-success-foreground' }}">
                                        {{ number_format($feeReport['total_outstanding'], 2) }}
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
                <p class="text-xs text-text-muted">Select a term above, then click Load Report.</p>
            </div>
        @endif

    </div>{{-- /fees tab --}}

</div>{{-- /x-data --}}
@endsection

@push('scripts')
<script>
Alpine.data('reportsPage', (classes, selectedClassId, selectedSectionId, activeTab) => ({
    activeTab,
    classId: selectedClassId || '',
    sectionId: selectedSectionId || '',

    get currentClass() {
        return classes.find(c => c.id === this.classId) || null;
    },
    get currentSections() {
        return this.currentClass ? this.currentClass.sections : [];
    },
    get hasSections() {
        return this.currentSections.length > 0;
    },
    onClassChange() {
        this.sectionId = '';
    },
}));
</script>
@endpush
