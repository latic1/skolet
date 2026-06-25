@extends('layouts.tenant')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@push('head')
{{-- Chart.js loaded before Alpine so x-init can reference it --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')
<div class="flex flex-col gap-6">

    {{-- ================================================================
         SCHOOL ADMIN VIEW
         Shown when user has students.view + staff.view (i.e. settings.manage)
    ================================================================ --}}
    @if($can['settings'])

        {{-- Onboarding Resume Banner --}}
        @if(! ($schoolProfile?->onboarding_completed) && ! session('onboarding_skipped'))
        <div class="bg-accent-muted border border-accent rounded-2xl p-5 flex items-center justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-lg bg-accent flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-accent">Finish setting up your school</p>
                    <p class="text-xs text-text-muted mt-0.5">Complete the quick setup wizard to unlock all features — takes under 3 minutes.</p>
                </div>
            </div>
            <a href="{{ request()->getSchemeAndHttpHost() }}/onboarding/{{ $schoolProfile?->onboarding_step ?? 1 }}"
               class="shrink-0 inline-flex items-center gap-1.5 px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors">
                Continue Setup
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
        @endif

        {{-- Setup Checklist --}}
        @php $doneCount = collect($checklist)->where('done', true)->count(); $total = count($checklist); @endphp
        @if($doneCount < $total)
        <div class="bg-surface border border-border rounded-2xl p-6 shadow-card">
            <div class="flex items-start justify-between gap-4 mb-5">
                <div>
                    <h3 class="text-base font-semibold text-text-primary">School Setup Checklist</h3>
                    <p class="text-sm text-text-muted mt-0.5">Complete these steps before adding students and staff</p>
                </div>
                <span class="shrink-0 text-xs font-medium bg-surface-secondary text-text-secondary px-2.5 py-1 rounded-md">
                    {{ $doneCount }} of {{ $total }} complete
                </span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach($checklist as $i => $item)
                @php
                    $tag  = (!$item['done'] && !empty($item['link'])) ? 'a' : 'div';
                    $href = (!$item['done'] && !empty($item['link'])) ? "href=\"{$item['link']}\"" : '';
                @endphp
                <{{ $tag }} {{ $href }} class="flex items-center gap-3 p-3 rounded-xl border transition-colors
                    {{ $item['done'] ? 'border-success-light bg-success-lightest' : 'border-border bg-surface-secondary' }}
                    {{ (!$item['done'] && !empty($item['link'])) ? 'hover:border-accent hover:bg-accent-muted cursor-pointer' : '' }}">
                    @if($item['done'])
                        <div class="w-5 h-5 rounded-full bg-success flex items-center justify-center shrink-0">
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-success-foreground">{{ $item['label'] }}</span>
                    @else
                        <div class="w-5 h-5 rounded-full border-2 border-border-muted bg-surface shrink-0 flex items-center justify-center">
                            <span class="text-[10px] font-semibold text-text-muted">{{ $i + 1 }}</span>
                        </div>
                        <span class="text-sm font-medium text-text-secondary">{{ $item['label'] }}</span>
                        @if(!empty($item['link']))
                        <svg class="w-3.5 h-3.5 text-text-muted ml-auto shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        @endif
                    @endif
                </{{ $tag }}>
                @endforeach
            </div>
        </div>
        @endif

        {{-- 5 Stat Cards --}}
        <div class="grid grid-cols-2 xl:grid-cols-5 gap-4">

            {{-- Total Students --}}
            <div class="bg-surface border border-border rounded-2xl p-5 shadow-card">
                <div class="flex items-start justify-between gap-2 mb-3">
                    <p class="text-sm font-medium text-text-secondary">Total Students</p>
                    <div class="w-8 h-8 rounded-lg bg-accent-muted flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-[30px] font-semibold text-text-primary leading-none mb-1">{{ number_format($stats['total_students']) }}</p>
                <span class="text-xs text-text-muted">enrolled</span>
            </div>

            {{-- Total Staff --}}
            <div class="bg-surface border border-border rounded-2xl p-5 shadow-card">
                <div class="flex items-start justify-between gap-2 mb-3">
                    <p class="text-sm font-medium text-text-secondary">Total Staff</p>
                    <div class="w-8 h-8 rounded-lg bg-accent-muted flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-[30px] font-semibold text-text-primary leading-none mb-1">{{ $stats['total_staff'] }}</p>
                <span class="text-xs text-text-muted">active</span>
            </div>

            {{-- Attendance Rate Today --}}
            <div class="bg-surface border border-border rounded-2xl p-5 shadow-card">
                <div class="flex items-start justify-between gap-2 mb-3">
                    <p class="text-sm font-medium text-text-secondary">Attendance Today</p>
                    <div class="w-8 h-8 rounded-lg bg-info-lightest flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                </div>
                <p class="text-[30px] font-semibold text-text-primary leading-none mb-1">{{ $stats['attendance_today'] }}<span class="text-lg font-medium text-text-muted">%</span></p>
                <div class="mt-2 h-1.5 bg-border-light rounded-full overflow-hidden">
                    <div class="h-full bg-info rounded-full" style="width: {{ $stats['attendance_today'] }}%"></div>
                </div>
            </div>

            {{-- Fees Collected This Term --}}
            <div class="bg-surface border border-border rounded-2xl p-5 shadow-card">
                <div class="flex items-start justify-between gap-2 mb-3">
                    <p class="text-sm font-medium text-text-secondary">Fees This Term</p>
                    <div class="w-8 h-8 rounded-lg bg-success-lightest flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-[30px] font-semibold text-text-primary leading-none mb-1"><span class="text-lg font-medium text-text-muted">GH₵</span> {{ number_format($stats['fees_this_term'], 2) }}</p>
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-xs text-text-muted">GH₵ {{ number_format($stats['fees_outstanding'], 2) }} outstanding</span>
                    @if(($stats['overdue_count'] ?? 0) > 0)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-error-light text-error">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        {{ $stats['overdue_count'] }} overdue
                    </span>
                    @endif
                </div>
            </div>

            {{-- Chronic Absentees --}}
            @php $absLink = request()->getSchemeAndHttpHost() . '/reports?tab=alerts'; @endphp
            <a href="{{ $absLink }}" class="bg-surface border border-border rounded-2xl p-5 shadow-card hover:border-error hover:bg-error-light transition-colors block">
                <div class="flex items-start justify-between gap-2 mb-3">
                    <p class="text-sm font-medium text-text-secondary">Chronic Absentees</p>
                    <div class="w-8 h-8 rounded-lg bg-error-light flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-error" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-[30px] font-semibold {{ $stats['chronic_absentees'] > 0 ? 'text-error' : 'text-text-primary' }} leading-none mb-1">{{ $stats['chronic_absentees'] }}</p>
                <span class="text-xs text-text-muted">below 80% this term</span>
            </a>

        </div>

        {{-- Recent Activity + Fee Chart --}}
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

            {{-- Recent Activity --}}
            <div class="bg-surface border border-border rounded-2xl p-6 shadow-card">
                <h3 class="text-base font-semibold text-text-primary mb-4">Recent Activity</h3>
                <div class="flex flex-col gap-3">
                    @foreach($activity as $entry)
                    @php
                        $dotColors = [
                            'attendance' => ['outer' => '#DBEAFE', 'inner' => '#2563EB'],
                            'fee'        => ['outer' => '#D0FAE5', 'inner' => '#00BC7D'],
                            'announce'   => ['outer' => '#FFF7ED', 'inner' => '#FF8904'],
                            'student'    => ['outer' => '#DBEAFE', 'inner' => '#2563EB'],
                            'exam'       => ['outer' => '#CFFAFE', 'inner' => '#06B6D4'],
                        ];
                        $colors = $dotColors[$entry['type']] ?? $dotColors['student'];
                    @endphp
                    <div class="flex items-start gap-3">
                        <div class="w-4 h-4 rounded-full flex items-center justify-center shrink-0 mt-0.5"
                             style="background: {{ $colors['outer'] }}">
                            <div class="w-2 h-2 rounded-full" style="background: {{ $colors['inner'] }}"></div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-text-primary leading-snug">{{ $entry['text'] }}</p>
                            <p class="text-xs text-text-muted mt-0.5">{{ $entry['time'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Fee Collection Line Chart --}}
            <div class="bg-surface border border-border rounded-2xl p-6 shadow-card">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-semibold text-text-primary">Fee Collection</h3>
                    <span class="text-xs text-text-muted">Last 6 months</span>
                </div>
                <div>
                    <div style="height: 180px; position: relative;">
                        <canvas id="chart-fee"></canvas>
                    </div>
                </div>
            </div>

        </div>

        {{-- Attendance Chart + Grade Distribution --}}
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

            {{-- Attendance Rate Bar Chart --}}
            <div class="bg-surface border border-border rounded-2xl p-6 shadow-card">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-semibold text-text-primary">Attendance Rate</h3>
                    <span class="text-xs text-text-muted">Last 7 days</span>
                </div>
                <div>
                    <div style="height: 180px; position: relative;">
                        <canvas id="chart-attendance"></canvas>
                    </div>
                </div>
            </div>

            {{-- Grade Distribution Bar Chart --}}
            <div class="bg-surface border border-border rounded-2xl p-6 shadow-card">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-semibold text-text-primary">Grade Distribution</h3>
                    <span class="text-xs text-text-muted">All exams</span>
                </div>
                <div>
                    <div style="height: 180px; position: relative;">
                        <canvas id="chart-grade"></canvas>
                    </div>
                </div>
            </div>

        </div>

    {{-- ================================================================
         TEACHER VIEW
         Has attendance.view + exams.view, but NOT settings.manage
    ================================================================ --}}
    @elseif($can['attendance'] && $can['exams'])

        {{-- 3 Stat Cards --}}
        <div class="grid grid-cols-2 xl:grid-cols-3 gap-4">
            <div class="bg-surface border border-border rounded-2xl p-5 shadow-card">
                <div class="flex items-start justify-between gap-2 mb-3">
                    <p class="text-sm font-medium text-text-secondary">Attendance Today</p>
                    <div class="w-8 h-8 rounded-lg bg-info-lightest flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                </div>
                <p class="text-[30px] font-semibold text-text-primary leading-none mb-1">{{ $stats['attendance_today'] }}<span class="text-lg font-medium text-text-muted">%</span></p>
                <div class="mt-2 h-1.5 bg-border-light rounded-full overflow-hidden">
                    <div class="h-full bg-info rounded-full" style="width: {{ $stats['attendance_today'] }}%"></div>
                </div>
            </div>
            <div class="bg-surface border border-border rounded-2xl p-5 shadow-card">
                <div class="flex items-start justify-between gap-2 mb-3">
                    <p class="text-sm font-medium text-text-secondary">My Students</p>
                    <div class="w-8 h-8 rounded-lg bg-accent-muted flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-[30px] font-semibold text-text-primary leading-none mb-1">{{ number_format($stats['total_students']) }}</p>
                <span class="text-xs text-text-muted">total enrolled</span>
            </div>
            @if($can['reports'])
            <a href="{{ request()->getSchemeAndHttpHost() }}/reports?tab=alerts"
               class="bg-surface border border-border rounded-2xl p-5 shadow-card hover:border-error hover:bg-error-light transition-colors block">
                <div class="flex items-start justify-between gap-2 mb-3">
                    <p class="text-sm font-medium text-text-secondary">Chronic Absentees</p>
                    <div class="w-8 h-8 rounded-lg bg-error-light flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-error" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-[30px] font-semibold {{ $stats['chronic_absentees'] > 0 ? 'text-error' : 'text-text-primary' }} leading-none mb-1">{{ $stats['chronic_absentees'] }}</p>
                <span class="text-xs text-text-muted">below 80% this term</span>
            </a>
            @endif
        </div>

        {{-- Assignments badge for teacher --}}
        @if ($ungradedSubmissions > 0)
        <a href="{{ request()->getSchemeAndHttpHost() }}/assignments"
           class="flex items-center gap-3 bg-warning-light border border-warning rounded-2xl px-5 py-3.5 max-w-2xl hover:bg-amber-50 transition-colors">
            <svg class="w-5 h-5 text-warning shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span class="text-sm font-medium text-warning">
                {{ $ungradedSubmissions }} {{ Str::plural('submission', $ungradedSubmissions) }} waiting to be graded
            </span>
            <svg class="w-4 h-4 text-warning ml-auto shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
        @endif

        {{-- Recent Activity --}}
        <div class="bg-surface border border-border rounded-2xl p-6 shadow-card max-w-2xl">
            <h3 class="text-base font-semibold text-text-primary mb-4">Recent Activity</h3>
            <div class="flex flex-col gap-3">
                @foreach(array_slice($activity, 0, 3) as $entry)
                @php
                    $dotColors = [
                        'attendance' => ['outer' => '#DBEAFE', 'inner' => '#2563EB'],
                        'exam'       => ['outer' => '#CFFAFE', 'inner' => '#06B6D4'],
                        'announce'   => ['outer' => '#FFF7ED', 'inner' => '#FF8904'],
                        'student'    => ['outer' => '#DBEAFE', 'inner' => '#2563EB'],
                        'fee'        => ['outer' => '#D0FAE5', 'inner' => '#00BC7D'],
                    ];
                    $colors = $dotColors[$entry['type']] ?? $dotColors['student'];
                @endphp
                <div class="flex items-start gap-3">
                    <div class="w-4 h-4 rounded-full flex items-center justify-center shrink-0 mt-0.5"
                         style="background: {{ $colors['outer'] }}">
                        <div class="w-2 h-2 rounded-full" style="background: {{ $colors['inner'] }}"></div>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-text-primary leading-snug">{{ $entry['text'] }}</p>
                        <p class="text-xs text-text-muted mt-0.5">{{ $entry['time'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Attendance Chart --}}
        <div class="bg-surface border border-border rounded-2xl p-6 shadow-card max-w-2xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold text-text-primary">Attendance Rate</h3>
                <span class="text-xs text-text-muted">Last 7 days</span>
            </div>
            <div>
                <div style="height: 180px; position: relative;">
                    <canvas id="chart-attendance"></canvas>
                </div>
            </div>
        </div>

    {{-- ================================================================
         ACCOUNTANT VIEW
         Has fees.view, typically no students/staff/exams permissions
    ================================================================ --}}
    @elseif($can['fees'])

        {{-- 2 Fee Stat Cards --}}
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-surface border border-border rounded-2xl p-5 shadow-card">
                <div class="flex items-start justify-between gap-2 mb-3">
                    <p class="text-sm font-medium text-text-secondary">Fees Collected</p>
                    <div class="w-8 h-8 rounded-lg bg-success-lightest flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-[30px] font-semibold text-text-primary leading-none mb-1"><span class="text-lg font-medium text-text-muted">GH₵</span> {{ number_format($stats['fees_this_term'], 2) }}</p>
                <span class="text-xs text-text-muted">this term</span>
            </div>
            <div class="bg-surface border border-border rounded-2xl p-5 shadow-card">
                <div class="flex items-start justify-between gap-2 mb-3">
                    <p class="text-sm font-medium text-text-secondary">Outstanding</p>
                    <div class="w-8 h-8 rounded-lg bg-error-light flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-error" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-[30px] font-semibold text-text-primary leading-none mb-1"><span class="text-lg font-medium text-text-muted">GH₵</span> {{ number_format($stats['fees_outstanding'], 2) }}</p>
                <div class="flex items-center gap-2 flex-wrap mt-1">
                    <span class="text-xs text-text-muted">awaiting collection</span>
                    @if(($stats['overdue_count'] ?? 0) > 0)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-error-light text-error">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        {{ $stats['overdue_count'] }} overdue
                    </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            {{-- Recent Activity --}}
            <div class="bg-surface border border-border rounded-2xl p-6 shadow-card">
                <h3 class="text-base font-semibold text-text-primary mb-4">Recent Activity</h3>
                <div class="flex flex-col gap-3">
                    @foreach(array_slice($activity, 0, 4) as $entry)
                    @php
                        $dotColors = [
                            'fee'        => ['outer' => '#D0FAE5', 'inner' => '#00BC7D'],
                            'attendance' => ['outer' => '#DBEAFE', 'inner' => '#2563EB'],
                            'announce'   => ['outer' => '#FFF7ED', 'inner' => '#FF8904'],
                            'student'    => ['outer' => '#DBEAFE', 'inner' => '#2563EB'],
                            'exam'       => ['outer' => '#CFFAFE', 'inner' => '#06B6D4'],
                        ];
                        $colors = $dotColors[$entry['type']] ?? $dotColors['fee'];
                    @endphp
                    <div class="flex items-start gap-3">
                        <div class="w-4 h-4 rounded-full flex items-center justify-center shrink-0 mt-0.5"
                             style="background: {{ $colors['outer'] }}">
                            <div class="w-2 h-2 rounded-full" style="background: {{ $colors['inner'] }}"></div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-text-primary leading-snug">{{ $entry['text'] }}</p>
                            <p class="text-xs text-text-muted mt-0.5">{{ $entry['time'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Fee Collection Chart --}}
            <div class="bg-surface border border-border rounded-2xl p-6 shadow-card">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-semibold text-text-primary">Fee Collection</h3>
                    <span class="text-xs text-text-muted">Last 6 months</span>
                </div>
                <div>
                    <div style="height: 180px; position: relative;">
                        <canvas id="chart-fee"></canvas>
                    </div>
                </div>
            </div>
        </div>

    {{-- ================================================================
         STUDENT / PARENT VIEW
         Only view permissions — no charts, no sensitive data
    ================================================================ --}}
    @else

        {{-- Summary cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-surface border border-border rounded-2xl p-5 shadow-card">
                <div class="w-9 h-9 rounded-lg bg-info-lightest flex items-center justify-center mb-3">
                    <svg class="w-4.5 h-4.5 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-text-secondary mb-1">Attendance Today</p>
                <p class="text-[30px] font-semibold text-text-primary leading-none">{{ $stats['attendance_today'] }}<span class="text-base font-medium text-text-muted">%</span></p>
                <p class="text-xs text-text-muted mt-1">Present today</p>
            </div>
            <div class="bg-surface border border-border rounded-2xl p-5 shadow-card">
                <div class="w-9 h-9 rounded-lg bg-success-lightest flex items-center justify-center mb-3">
                    <svg class="w-4.5 h-4.5 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-text-secondary mb-1">Fees</p>
                <a href="{{ request()->getSchemeAndHttpHost() . '/fees' }}"
                   class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-accent text-white hover:bg-accent-dark transition-colors">
                    View my fees
                </a>
            </div>
            <div class="bg-surface border border-border rounded-2xl p-5 shadow-card">
                <div class="w-9 h-9 rounded-lg bg-accent-muted flex items-center justify-center mb-3">
                    <svg class="w-4.5 h-4.5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-text-secondary mb-1">Announcements</p>
                <p class="text-[30px] font-semibold text-text-primary leading-none">{{ $recentAnnouncements->count() }}</p>
                <p class="text-xs text-text-muted mt-1">recent</p>
            </div>
        </div>

        {{-- Assignments due soon badge for student --}}
        @if ($assignmentsDueSoon > 0)
        <a href="{{ request()->getSchemeAndHttpHost() }}/assignments"
           class="flex items-center gap-3 bg-warning-light border border-warning rounded-2xl px-5 py-3.5 max-w-2xl hover:bg-amber-50 transition-colors">
            <svg class="w-5 h-5 text-warning shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-sm font-medium text-warning">
                {{ $assignmentsDueSoon }} {{ Str::plural('assignment', $assignmentsDueSoon) }} due within 3 days
            </span>
            <svg class="w-4 h-4 text-warning ml-auto shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
        @endif

        {{-- Recent Announcements --}}
        <div class="bg-surface border border-border rounded-2xl p-6 shadow-card max-w-2xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold text-text-primary">Recent Announcements</h3>
                <a href="{{ request()->getSchemeAndHttpHost() . '/announcements' }}" class="text-xs font-medium text-accent hover:text-accent-dark transition-colors">View all</a>
            </div>
            @if($recentAnnouncements->isEmpty())
            <p class="text-sm text-text-muted">No announcements yet.</p>
            @else
            <div class="flex flex-col divide-y divide-border">
                @foreach($recentAnnouncements as $ann)
                <div class="py-3 first:pt-0 last:pb-0">
                    <div class="flex items-start justify-between gap-2">
                        <p class="text-sm font-medium text-text-primary">{{ $ann->title }}</p>
                        <span class="text-xs text-text-muted shrink-0">{{ $ann->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-xs text-text-muted mt-0.5 leading-relaxed">{{ Str::limit($ann->body, 120) }}</p>
                </div>
                @endforeach
            </div>
            @endif
        </div>

    @endif

</div>
@endsection

@push('scripts')
<script>
(function () {
    var feeCanvas = document.getElementById('chart-fee');
    if (feeCanvas) {
        var fctx = feeCanvas.getContext('2d');
        var grad = fctx.createLinearGradient(0, 0, 0, 180);
        grad.addColorStop(0, 'rgba(37,99,235,0.18)');
        grad.addColorStop(1, 'rgba(37,99,235,0)');
        new Chart(fctx, {
            type: 'line',
            data: {
                labels: @json($feeChart['labels'] ?? []),
                datasets: [{
                    data: @json($feeChart['values'] ?? []),
                    borderColor: '#2563EB',
                    borderWidth: 2.5,
                    backgroundColor: grad,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointBackgroundColor: '#2563EB',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 1.5,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { callbacks: { label: function(c) { return 'GH₵ ' + Number(c.raw).toLocaleString('en-GH', {minimumFractionDigits:2,maximumFractionDigits:2}); } } } },
                scales: {
                    x: { grid: { color: '#E7EAF3', borderDash: [4,4] }, ticks: { color: '#9CA3AF', font: { size: 12 } }, border: { display: false } },
                    y: { grid: { color: '#E7EAF3', borderDash: [4,4] }, ticks: { color: '#9CA3AF', font: { size: 12 }, callback: function(v) { return 'GH₵ ' + Number(v).toLocaleString('en-GH', {minimumFractionDigits:0,maximumFractionDigits:0}); } }, border: { display: false } }
                }
            }
        });
    }

    var attCanvas = document.getElementById('chart-attendance');
    if (attCanvas) {
        var actx = attCanvas.getContext('2d');
        new Chart(actx, {
            type: 'bar',
            data: {
                labels: @json($attendanceChart['labels'] ?? []),
                datasets: [{
                    data: @json($attendanceChart['values'] ?? []),
                    backgroundColor: '#06B6D4',
                    borderRadius: 4,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { callbacks: { label: function(c) { return c.raw + '%'; } } } },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#9CA3AF', font: { size: 12 } }, border: { display: false } },
                    y: { min: 0, max: 100, grid: { color: '#E7EAF3', borderDash: [4,4] }, ticks: { color: '#9CA3AF', font: { size: 12 }, callback: function(v) { return v + '%'; } }, border: { display: false } }
                }
            }
        });
    }

    var gradeCanvas = document.getElementById('chart-grade');
    if (gradeCanvas) {
        var gctx = gradeCanvas.getContext('2d');
        new Chart(gctx, {
            type: 'bar',
            data: {
                labels: @json($gradeChart['labels'] ?? []),
                datasets: [{
                    data: @json($gradeChart['values'] ?? []),
                    backgroundColor: ['#10B981','#06B6D4','#FF8904','#EF4444','#EF4444'],
                    borderRadius: 4,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { callbacks: { label: function(c) { return c.raw + '% of students'; } } } },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#9CA3AF', font: { size: 11 } }, border: { display: false } },
                    y: { min: 0, grid: { color: '#E7EAF3', borderDash: [4,4] }, ticks: { color: '#9CA3AF', font: { size: 12 }, callback: function(v) { return v + '%'; } }, border: { display: false } }
                }
            }
        });
    }
})();
</script>
@endpush
