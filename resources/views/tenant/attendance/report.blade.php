@extends('layouts.tenant')

@section('title', 'Attendance Report')
@section('page-title', 'Attendance')

@section('content')
@php
    $host = request()->getSchemeAndHttpHost();

    $classesJson = $classes->map(fn ($c) => [
        'id'       => $c->id,
        'name'     => $c->name,
        'sections' => $c->sections->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])->values()->toArray(),
    ])->values()->toJson();
@endphp

<div class="flex flex-col gap-6">

    {{-- Breadcrumb + header --}}
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-sm text-text-muted mb-1">
                <a href="{{ $host }}/attendance" class="hover:text-text-primary transition-colors">Attendance</a>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-text-primary font-medium">Monthly Report</span>
            </div>
            <h2 class="text-base font-semibold text-text-primary">Student Attendance Report</h2>
        </div>
    </div>

    {{-- Filter card --}}
    <div x-data="reportFilter({{ $classesJson }}, '{{ $classId }}', '{{ $sectionId }}')"
         class="bg-surface border border-border rounded-2xl shadow-card p-5">
        <form method="GET" action="{{ $host }}/attendance/report" class="flex flex-wrap items-end gap-4">

            {{-- Class --}}
            <div class="flex flex-col gap-1.5 min-w-[160px]">
                <label class="text-xs font-medium text-text-dark">Class</label>
                <select name="class_id" x-model="classId" @change="onClassChange()"
                        class="px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                    <option value="">Select Class</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}" @selected($classId === $class->id)>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Section (conditional) --}}
            <div class="flex flex-col gap-1.5 min-w-[140px]" x-show="hasSections">
                <label class="text-xs font-medium text-text-dark">Section</label>
                <select name="section_id" x-model="sectionId"
                        class="px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                    <option value="">All Sections</option>
                    <template x-for="section in currentSections" :key="section.id">
                        <option :value="section.id" x-text="section.name"></option>
                    </template>
                </select>
            </div>

            {{-- Student --}}
            <div class="flex flex-col gap-1.5 min-w-[200px]">
                <label class="text-xs font-medium text-text-dark">Student</label>
                <select name="student_id"
                        class="px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                    <option value="">Select Student</option>
                    @foreach($studentsForDropdown as $s)
                    <option value="{{ $s->id }}" @selected($studentId === $s->id)>{{ $s->full_name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Month --}}
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-medium text-text-dark">Month</label>
                <input type="month" name="month" value="{{ $month }}" max="{{ now()->format('Y-m') }}"
                       class="px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
            </div>

            <button type="submit"
                    class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors">
                View Report
            </button>

            @if($classId)
            <a href="{{ $host }}/attendance/report"
               class="px-3 py-2 text-sm text-text-secondary hover:text-text-primary transition-colors">
                Clear
            </a>
            @endif
        </form>
    </div>

    {{-- Report content --}}
    @if($selectedStudent && !empty($daysInMonth))

    {{-- Student info + summary row --}}
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        {{-- Student card --}}
        <div class="sm:col-span-1 bg-surface border border-border rounded-2xl shadow-card p-5 flex flex-col gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-accent-muted flex items-center justify-center shrink-0">
                    <span class="text-sm font-semibold text-accent">{{ strtoupper(mb_substr($selectedStudent->full_name, 0, 1)) }}</span>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-text-primary truncate">{{ $selectedStudent->full_name }}</p>
                    <p class="text-xs text-text-muted">{{ $selectedStudent->admission_no }}</p>
                </div>
            </div>
            <dl class="flex flex-col gap-2">
                <div>
                    <dt class="text-xs text-text-muted">Class</dt>
                    <dd class="text-sm text-text-primary">{{ $selectedStudent->schoolClass?->name ?? '—' }}</dd>
                </div>
                @if($selectedStudent->section)
                <div>
                    <dt class="text-xs text-text-muted">Section</dt>
                    <dd class="text-sm text-text-primary">{{ $selectedStudent->section->name }}</dd>
                </div>
                @endif
            </dl>
        </div>

        {{-- Summary stat cards --}}
        <div class="sm:col-span-3 grid grid-cols-2 sm:grid-cols-4 gap-4">
            @php
                $totalDays = count($daysInMonth);
                $pct = $totalDays > 0 ? round(($summary['present'] / $totalDays) * 100) : 0;
            @endphp
            <div class="bg-surface border border-border rounded-2xl shadow-card p-4 flex flex-col gap-1">
                <p class="text-xs text-text-muted">Present</p>
                <p class="text-2xl font-semibold text-success-foreground">{{ $summary['present'] }}</p>
                <p class="text-xs text-text-muted">{{ $pct }}% attendance</p>
            </div>
            <div class="bg-surface border border-border rounded-2xl shadow-card p-4 flex flex-col gap-1">
                <p class="text-xs text-text-muted">Absent</p>
                <p class="text-2xl font-semibold text-error">{{ $summary['absent'] }}</p>
                <p class="text-xs text-text-muted">days absent</p>
            </div>
            <div class="bg-surface border border-border rounded-2xl shadow-card p-4 flex flex-col gap-1">
                <p class="text-xs text-text-muted">Late</p>
                <p class="text-2xl font-semibold text-warning">{{ $summary['late'] }}</p>
                <p class="text-xs text-text-muted">days late</p>
            </div>
            <div class="bg-surface border border-border rounded-2xl shadow-card p-4 flex flex-col gap-1">
                <p class="text-xs text-text-muted">Not Marked</p>
                <p class="text-2xl font-semibold text-text-muted">{{ $summary['unmarked'] }}</p>
                <p class="text-xs text-text-muted">days</p>
            </div>
        </div>
    </div>

    {{-- Monthly attendance table --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card">
        {{-- Table header with prev/next month navigation --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-border">
            <h3 class="text-sm font-semibold text-text-primary">
                {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}
            </h3>
            <div class="flex items-center gap-2">
                <a href="{{ $host }}/attendance/report?{{ http_build_query(array_merge(request()->except('month'), ['month' => $prevMonth])) }}"
                   class="p-1.5 rounded-md text-text-muted hover:text-text-primary hover:bg-surface-secondary transition-colors" title="Previous month">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                @if($month < now()->format('Y-m'))
                <a href="{{ $host }}/attendance/report?{{ http_build_query(array_merge(request()->except('month'), ['month' => $nextMonth])) }}"
                   class="p-1.5 rounded-md text-text-muted hover:text-text-primary hover:bg-surface-secondary transition-colors" title="Next month">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                @endif
            </div>
        </div>

        <table class="w-full">
            <thead>
                <tr class="border-b border-border">
                    <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Date</th>
                    <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Day</th>
                    <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($daysInMonth as $day)
                <tr class="border-b border-border last:border-b-0 hover:bg-surface-secondary transition-colors">
                    <td class="px-6 py-3 text-sm text-text-primary">{{ $day['label'] }}</td>
                    <td class="px-6 py-3 text-sm text-text-muted">{{ $day['day'] }}</td>
                    <td class="px-6 py-3">
                        @if($day['status'] === 'present')
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-success-lightest text-success-foreground">Present</span>
                        @elseif($day['status'] === 'absent')
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-error-light text-error">Absent</span>
                        @elseif($day['status'] === 'late')
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-warning-light text-warning">Late</span>
                        @else
                        <span class="text-xs text-text-muted">Not marked</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @elseif($selectedStudent && empty($daysInMonth))

    {{-- Student selected but no data for this month --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card">
        <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
            <div class="w-12 h-12 rounded-xl bg-surface-secondary flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-text-primary mb-1">No attendance data</p>
            <p class="text-xs text-text-muted">No attendance has been recorded for {{ $selectedStudent->full_name }} in {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}.</p>
        </div>
    </div>

    @elseif(!$selectedStudent && $classId)

    {{-- Class selected but no student chosen --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card">
        <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
            <div class="w-12 h-12 rounded-xl bg-accent-muted flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-text-primary mb-1">Select a student</p>
            <p class="text-xs text-text-muted">Choose a student from the dropdown above to view their monthly attendance report.</p>
        </div>
    </div>

    @else

    {{-- No class selected yet --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card">
        <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
            <div class="w-12 h-12 rounded-xl bg-accent-muted flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-text-primary mb-1">Select class and student to view report</p>
            <p class="text-xs text-text-muted">Choose a class, then a student and month from the filter above.</p>
        </div>
    </div>

    @endif

</div>
@endsection

@push('scripts')
<script>
    function reportFilter(classes, classId, sectionId) {
        return {
            classes,
            classId: classId || '',
            sectionId: sectionId || '',

            get currentClass() {
                return this.classes.find(c => c.id === this.classId) || null;
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
        };
    }
</script>
@endpush
