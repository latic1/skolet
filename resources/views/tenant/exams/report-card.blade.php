@extends('layouts.tenant')

@section('title', 'Report Cards')
@section('page-title', 'Report Cards')

@section('content')
@php
    $host       = request()->getSchemeAndHttpHost();
    $anyClassHasSections = $classes->some(fn ($c) => $c->sections->isNotEmpty());

    // For Alpine: classes with their sections
    $classesJson = $classes->map(fn ($c) => [
        'id'       => $c->id,
        'name'     => $c->name,
        'sections' => $c->sections->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])->values(),
    ])->values()->toJson();

    $studentsJson = $students->map(fn ($s) => ['id' => $s->id, 'name' => $s->full_name])->values()->toJson();

    // Grade badge classes
    $gradeBadge = function (string $grade) {
        return match ($grade) {
            'A'     => 'bg-success-lightest text-success-foreground',
            'B'     => 'bg-info-light text-info-foreground',
            'C'     => 'bg-warning-light text-warning',
            default => 'bg-error-light text-error',
        };
    };
@endphp

<div x-data="reportCardPage({{ $classesJson }}, {{ $studentsJson }}, {{ json_encode($classId) }}, {{ json_encode($sectionId) }}, {{ json_encode($studentId) }})"
     class="flex flex-col gap-6">

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="flex items-start gap-3 bg-success-lightest border border-success-light text-success-foreground text-sm px-4 py-3 rounded-xl">
        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="flex items-start gap-3 bg-error-light border border-error text-error text-sm px-4 py-3 rounded-xl">
        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- Page header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-base font-semibold text-text-primary">Report Cards</h2>
            <p class="text-xs text-text-muted mt-0.5">View and download student report cards by exam</p>
        </div>
        <a href="{{ $host }}/exams"
           class="flex items-center gap-2 px-4 py-2 bg-surface border border-border text-text-primary text-sm font-medium rounded-md hover:bg-surface-secondary transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            All Exams
        </a>
    </div>

    {{-- Filter card --}}
    <form method="GET" action="{{ $host }}/exams/report-card" class="bg-surface border border-border rounded-2xl shadow-card p-5">
        <div class="flex flex-wrap items-end gap-4">

            {{-- Exam --}}
            <div class="flex flex-col gap-1 min-w-[200px]">
                <label class="text-xs font-medium text-text-muted uppercase tracking-wide">Exam</label>
                <select name="exam_id" x-model="examId"
                        class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent">
                    <option value="">Select exam...</option>
                    @foreach($exams as $exam)
                    <option value="{{ $exam->id }}" {{ $examId === $exam->id ? 'selected' : '' }}>
                        {{ $exam->name }}
                        @if($exam->term?->academicYear) ({{ $exam->term->academicYear->name }})@endif
                        @if(!$canManageAll && $exam->is_published) ✓@endif
                    </option>
                    @endforeach
                </select>
            </div>

            @if($canManageAll)
            {{-- Class --}}
            <div class="flex flex-col gap-1 min-w-[160px]">
                <label class="text-xs font-medium text-text-muted uppercase tracking-wide">Class</label>
                <select name="class_id" x-model="classId" @change="onClassChange()"
                        class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent">
                    <option value="">Select class...</option>
                    @foreach($classes as $cls)
                    <option value="{{ $cls->id }}" {{ $classId === $cls->id ? 'selected' : '' }}>{{ $cls->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Section (conditional) --}}
            <div class="flex flex-col gap-1 min-w-[140px]" x-show="hasSections" style="display:none">
                <label class="text-xs font-medium text-text-muted uppercase tracking-wide">Section</label>
                <select name="section_id" x-model="sectionId"
                        class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent">
                    <option value="">All sections</option>
                    <template x-for="sec in currentSections" :key="sec.id">
                        <option :value="sec.id" :selected="sectionId === sec.id" x-text="sec.name"></option>
                    </template>
                </select>
            </div>

            {{-- Student --}}
            <div class="flex flex-col gap-1 min-w-[200px]">
                <label class="text-xs font-medium text-text-muted uppercase tracking-wide">Student</label>
                <select name="student_id" x-model="studentId"
                        class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent">
                    <option value="">
                        {{ $students->isEmpty() && !$classId ? 'Select class first...' : 'Select student...' }}
                    </option>
                    @foreach($students as $s)
                    <option value="{{ $s->id }}" {{ $studentId === $s->id ? 'selected' : '' }}>{{ $s->full_name }}</option>
                    @endforeach
                </select>
            </div>
            @else
            {{-- Student/parent: student name is displayed, id passed as hidden --}}
            @if($linkedStudent)
            <input type="hidden" name="student_id" value="{{ $linkedStudent->id }}">
            <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-text-muted uppercase tracking-wide">Student</label>
                <div class="px-3 py-2 bg-surface-secondary border border-border rounded-md text-sm text-text-primary">
                    {{ $linkedStudent->full_name }}
                </div>
            </div>
            @endif
            @endif

            <button type="submit"
                    class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors shrink-0">
                View Report Card
            </button>
        </div>

        @if(!$canManageAll && $exams->isEmpty())
        <p class="mt-3 text-xs text-text-muted">No published exam results are available yet.</p>
        @endif
    </form>

    {{-- Access error --}}
    @if($accessError)
    <div class="flex items-start gap-3 bg-warning-light border border-warning text-warning text-sm px-4 py-3 rounded-xl">
        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
        {{ $accessError }}
    </div>
    @endif

    {{-- Report Card Preview --}}
    @if($cardData)
    @php
        $student = $cardData['student'];
        $exam    = $cardData['exam'];
        $results = $cardData['results'];
        $average = $cardData['average'];
        $avgGrade  = $cardData['average_grade'];
        $avgRemark = $cardData['average_remark'];
    @endphp

    <div class="bg-surface border border-border rounded-2xl shadow-card overflow-hidden">

        {{-- Card header: student info + download button --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 px-6 py-5 border-b border-border">
            <div class="flex items-center gap-4">
                {{-- Avatar --}}
                <div class="w-14 h-14 rounded-2xl bg-accent-muted flex items-center justify-center shrink-0">
                    <span class="text-xl font-semibold text-accent">{{ strtoupper(substr($student->full_name, 0, 1)) }}</span>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-text-primary">{{ $student->full_name }}</h3>
                    <p class="text-xs text-text-muted mt-0.5">
                        Adm. No: <span class="font-medium text-text-secondary">{{ $student->admission_no }}</span>
                        &nbsp;·&nbsp;
                        {{ $student->schoolClass?->name ?? '—' }}
                        @if($student->section) / {{ $student->section->name }}@endif
                    </p>
                    <div class="flex items-center gap-2 mt-1.5">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-accent-muted text-accent">
                            {{ $exam->name }}
                        </span>
                        @if($exam->term?->academicYear)
                        <span class="text-xs text-text-muted">{{ $exam->term->academicYear->name }}</span>
                        @endif
                        @if($exam->term)
                        <span class="text-xs text-text-muted">· {{ $exam->term->name }}</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Download / Print --}}
            <div class="flex items-center gap-3 shrink-0">
                <a href="{{ $host }}/exams/report-card/download?exam_id={{ $exam->id }}&student_id={{ $student->id }}"
                   class="flex items-center gap-2 px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Download PDF
                </a>
                <button type="button" onclick="window.print()"
                        class="flex items-center gap-2 px-4 py-2 bg-surface border border-border text-text-primary text-sm font-medium rounded-md hover:bg-surface-secondary transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Print
                </button>
            </div>
        </div>

        @if($results->isEmpty())
        <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
            <div class="w-12 h-12 rounded-xl bg-surface-secondary flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-text-primary mb-1">No marks entered</p>
            <p class="text-xs text-text-muted">No marks have been recorded for this student in this exam yet.</p>
        </div>
        @else

        {{-- Results table --}}
        <div class="overflow-x-auto">
            <table class="w-full" style="min-width:520px">
                <thead>
                    <tr class="border-b border-border bg-surface-secondary">
                        <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">#</th>
                        <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Subject</th>
                        <th class="text-center px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Marks (/100)</th>
                        <th class="text-center px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Grade</th>
                        <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary hidden sm:table-cell">Remark</th>
                        <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary hidden md:table-cell">Progress</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($results as $i => $row)
                    <tr class="border-b border-border last:border-b-0 hover:bg-surface-secondary transition-colors">

                        {{-- # --}}
                        <td class="px-6 py-4 text-sm text-text-muted">{{ $i + 1 }}</td>

                        {{-- Subject --}}
                        <td class="px-6 py-4 text-sm font-medium text-text-primary">{{ $row['subject'] }}</td>

                        {{-- Marks --}}
                        <td class="px-6 py-4 text-center">
                            <span class="text-sm font-semibold text-text-primary">
                                {{ number_format($row['marks'], 1) }}
                            </span>
                        </td>

                        {{-- Grade badge --}}
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-sm font-semibold {{ $gradeBadge($row['grade']) }}">
                                {{ $row['grade'] }}
                            </span>
                        </td>

                        {{-- Remark --}}
                        <td class="px-6 py-4 text-sm text-text-secondary hidden sm:table-cell">{{ $row['remark'] }}</td>

                        {{-- Progress bar --}}
                        <td class="px-6 py-4 hidden md:table-cell">
                            <div class="w-full h-1.5 rounded-full bg-border-light" style="min-width:80px">
                                <div class="h-1.5 rounded-full transition-all"
                                     style="width:{{ $row['bar_width'] }}%; background-color:{{ $row['bar_color'] }}">
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>

                {{-- Overall average row --}}
                @if($average !== null)
                <tfoot>
                    <tr class="border-t-2 border-border bg-surface-secondary">
                        <td colspan="2" class="px-6 py-4 text-sm font-semibold text-text-primary">Overall Average</td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-sm font-semibold text-text-primary">{{ number_format($average, 1) }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($avgGrade)
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-sm font-semibold {{ $gradeBadge($avgGrade) }}">
                                {{ $avgGrade }}
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm font-medium text-text-secondary hidden sm:table-cell">
                            {{ $avgRemark ?? '' }}
                        </td>
                        <td class="px-6 py-4 hidden md:table-cell">
                            @if($average !== null)
                            <div class="w-full h-1.5 rounded-full bg-border-light" style="min-width:80px">
                                <div class="h-1.5 rounded-full"
                                     style="width:{{ min(100, (int) round($average)) }}%; background-color:{{ $cardData['results']->first()['bar_color'] ?? '#10b981' }}">
                                </div>
                            </div>
                            @endif
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        {{-- Grading scale key --}}
        <div class="flex flex-wrap items-center gap-3 px-6 py-4 border-t border-border bg-surface-secondary">
            <span class="text-xs text-text-muted font-medium uppercase tracking-wide">Grading Scale:</span>
            @foreach(config('schoolflow.default_grading_scale') as $band)
            <span class="inline-flex items-center gap-1.5 text-xs text-text-secondary">
                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-xs font-semibold {{ $gradeBadge($band['grade']) }}">{{ $band['grade'] }}</span>
                {{ $band['min'] }}–{{ $band['max'] }} ({{ $band['remark'] }})
            </span>
            @endforeach
        </div>

        @endif {{-- results not empty --}}
    </div>
    @endif {{-- cardData --}}

</div>
@endsection

@push('scripts')
<script>
    function reportCardPage(classes, initialStudents, initialClassId, initialSectionId, initialStudentId) {
        return {
            classes,
            examId: '',
            classId: initialClassId ?? '',
            sectionId: initialSectionId ?? '',
            studentId: initialStudentId ?? '',
            currentSections: [],
            hasSections: false,

            init() {
                this.applyClass(this.classId);
            },

            onClassChange() {
                this.sectionId = '';
                this.studentId = '';
                this.applyClass(this.classId);
            },

            applyClass(classId) {
                const cls = this.classes.find(c => c.id === classId);
                this.currentSections = cls?.sections ?? [];
                this.hasSections = this.currentSections.length > 0;
            },
        };
    }
</script>
@endpush
