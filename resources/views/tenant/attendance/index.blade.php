@extends('layouts.tenant')

@section('title', 'Attendance')
@section('page-title', 'Attendance')

@section('content')
@php
    $host = request()->getSchemeAndHttpHost();
    $canEdit = auth()->user()->can('attendance.edit');

    $classesJson = $classes->map(fn ($c) => [
        'id'       => $c->id,
        'name'     => $c->name,
        'sections' => $c->sections->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])->values()->toArray(),
    ])->values()->toJson();

    $studentsJson = $students->map(fn ($s) => [
        'id'           => $s->id,
        'full_name'    => $s->full_name,
        'admission_no' => $s->admission_no,
        'initial'      => strtoupper(mb_substr($s->full_name, 0, 1)),
    ])->values()->toJson();

    $existingJson = $existingRecords->map(fn ($r) => $r->status)->toJson();
@endphp

<div x-data="attendancePage({{ $classesJson }}, '{{ $classId }}', '{{ $sectionId }}')" class="flex flex-col gap-6">

    {{-- Flash messages --}}

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-base font-semibold text-text-primary">Daily Attendance</h2>
            <p class="text-xs text-text-muted mt-0.5">Mark student attendance by class and date</p>
        </div>
        <a href="{{ $host }}/attendance/staff"
           class="flex items-center gap-2 px-4 py-2 bg-surface border border-border text-text-primary text-sm font-medium rounded-md hover:bg-surface-secondary transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Staff Attendance
        </a>
    </div>

    {{-- Filter bar --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card p-5">
        <form method="GET" action="{{ $host }}/attendance" class="flex flex-wrap items-end gap-4">

            {{-- Class --}}
            <div class="flex flex-col gap-1.5 min-w-[180px]">
                <label class="text-xs font-medium text-text-dark">Class</label>
                <select name="class_id" x-model="classId" @change="onClassChange()"
                        class="px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                    <option value="">Select Class</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}" @selected(request('class_id') === $class->id)>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Section (shown only when selected class has sections) --}}
            <div class="flex flex-col gap-1.5 min-w-[150px]" x-show="hasSections">
                <label class="text-xs font-medium text-text-dark">Section</label>
                <select name="section_id" x-model="sectionId"
                        class="px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                    <option value="">Select Section</option>
                    <template x-for="section in currentSections" :key="section.id">
                        <option :value="section.id" x-text="section.name"></option>
                    </template>
                </select>
            </div>

            {{-- Date --}}
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-medium text-text-dark">Date</label>
                <input type="date" name="date" value="{{ $date }}" max="{{ now()->toDateString() }}"
                       class="px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
            </div>

            <button type="submit"
                    class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors">
                Load
            </button>
        </form>
    </div>

    {{-- Attendance Sheet --}}
    @if($classId && $date)

        @if($students->isEmpty())
        {{-- Empty state: section required or no students --}}
        <div class="bg-surface border border-border rounded-2xl shadow-card">
            <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
                <div class="w-12 h-12 rounded-xl bg-surface-secondary flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                @if($selectedClass && $selectedClass->sections->isNotEmpty() && !$sectionId)
                <p class="text-sm font-medium text-text-primary mb-1">Select a section to continue</p>
                <p class="text-xs text-text-muted">This class has multiple sections. Choose one from the filter above.</p>
                @else
                <p class="text-sm font-medium text-text-primary mb-1">No active students found</p>
                <p class="text-xs text-text-muted">No active students are enrolled in this class{{ $sectionId ? '/section' : '' }}.</p>
                @endif
            </div>
        </div>

        @else
        {{-- Attendance sheet with Alpine-managed P/A/L state --}}
        <div x-data="attendanceSheet({{ $studentsJson }}, {{ $existingJson }})" class="bg-surface border border-border rounded-2xl shadow-card">

            {{-- Sheet header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-border">
                <div>
                    <h3 class="text-sm font-semibold text-text-primary">
                        {{ $selectedClass?->name }}{{ $selectedSection ? ' — ' . $selectedSection->name : '' }}
                        &middot; {{ \Carbon\Carbon::parse($date)->format('D, d M Y') }}
                    </h3>
                    <p class="text-xs text-text-muted mt-0.5">
                        <span x-text="markedCount"></span>/{{ $students->count() }} students marked
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    @if($canEdit)
                    <button type="button" @click="markAllPresent()"
                            class="px-3 py-1.5 text-xs font-medium bg-success-lightest text-success-foreground border border-success-light rounded-md hover:bg-success-light transition-colors">
                        Mark all present
                    </button>
                    @endif
                    <a href="{{ $host }}/attendance/report?class_id={{ $classId }}{{ $sectionId ? '&section_id='.$sectionId : '' }}&month={{ \Carbon\Carbon::parse($date)->format('Y-m') }}"
                       class="px-3 py-1.5 text-xs font-medium text-text-secondary border border-border rounded-md hover:bg-surface-secondary transition-colors">
                        Monthly Report
                    </a>
                </div>
            </div>

            {{-- Table + form --}}
            <form method="POST" action="{{ $host }}/attendance">
                @csrf
                <input type="hidden" name="class_id" value="{{ $classId }}">
                <input type="hidden" name="section_id" value="{{ $sectionId }}">
                <input type="hidden" name="date" value="{{ $date }}">

                <table class="w-full">
                    <thead>
                        <tr class="border-b border-border">
                            <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary w-10">#</th>
                            <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Student</th>
                            <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary hidden sm:table-cell">Admission No.</th>
                            <th class="text-center px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Attendance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $i => $student)
                        <tr class="border-b border-border last:border-b-0 hover:bg-surface-secondary transition-colors">
                            <td class="px-6 py-3.5 text-sm text-text-muted">{{ $i + 1 }}</td>
                            <td class="px-6 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-accent-muted flex items-center justify-center shrink-0">
                                        <span class="text-xs font-semibold text-accent">{{ strtoupper(mb_substr($student->full_name, 0, 1)) }}</span>
                                    </div>
                                    <span class="text-sm font-medium text-text-primary">{{ $student->full_name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-3.5 text-sm text-text-muted hidden sm:table-cell">{{ $student->admission_no }}</td>
                            <td class="px-6 py-3.5">
                                {{-- Hidden input always tracks Alpine state --}}
                                <input type="hidden" name="statuses[{{ $student->id }}]"
                                       :value="statuses['{{ $student->id }}'] ?? ''">

                                <div class="flex items-center justify-center gap-2">
                                    @if($canEdit)
                                    {{-- Present --}}
                                    <button type="button"
                                            @click="setStatus('{{ $student->id }}', 'present')"
                                            :class="isActive('{{ $student->id }}', 'present')
                                                ? 'bg-success-lightest text-success-foreground border-success-light font-semibold'
                                                : 'bg-surface text-text-secondary border-border hover:bg-success-lightest hover:text-success-foreground hover:border-success-light'"
                                            class="px-3 py-1.5 text-xs border rounded-md transition-colors min-w-[64px]">
                                        Present
                                    </button>
                                    {{-- Absent --}}
                                    <button type="button"
                                            @click="setStatus('{{ $student->id }}', 'absent')"
                                            :class="isActive('{{ $student->id }}', 'absent')
                                                ? 'bg-error-light text-error border-error font-semibold'
                                                : 'bg-surface text-text-secondary border-border hover:bg-error-light hover:text-error hover:border-error'"
                                            class="px-3 py-1.5 text-xs border rounded-md transition-colors min-w-[60px]">
                                        Absent
                                    </button>
                                    {{-- Late --}}
                                    <button type="button"
                                            @click="setStatus('{{ $student->id }}', 'late')"
                                            :class="isActive('{{ $student->id }}', 'late')
                                                ? 'bg-warning-light text-warning border-warning font-semibold'
                                                : 'bg-surface text-text-secondary border-border hover:bg-warning-light hover:text-warning hover:border-warning'"
                                            class="px-3 py-1.5 text-xs border rounded-md transition-colors min-w-[48px]">
                                        Late
                                    </button>
                                    @else
                                    {{-- Read-only badge for non-editors --}}
                                    @php $record = $existingRecords->get($student->id); @endphp
                                    @if($record)
                                        @if($record->status === 'present')
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-success-lightest text-success-foreground">Present</span>
                                        @elseif($record->status === 'absent')
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-error-light text-error">Absent</span>
                                        @else
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-warning-light text-warning">Late</span>
                                        @endif
                                    @else
                                    <span class="text-xs text-text-muted">Not marked</span>
                                    @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                @if($canEdit)
                <div class="flex items-center justify-between px-6 py-4 border-t border-border">
                    <p class="text-xs text-text-muted">
                        <span x-text="markedCount"></span> of {{ $students->count() }} students marked
                    </p>
                    <button type="submit"
                            class="px-5 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors">
                        Save Attendance
                    </button>
                </div>
                @endif
            </form>
        </div>
        @endif

    @else
    {{-- Initial state: prompt to select class and date --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card">
        <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
            <div class="w-12 h-12 rounded-xl bg-accent-muted flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-text-primary mb-1">Select a class and date to begin</p>
            <p class="text-xs text-text-muted">
                @if($classes->isEmpty())
                No classes have been created yet. Add classes in Settings first.
                @else
                Choose a class and date from the filter above to load students.
                @endif
            </p>
        </div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
    function attendancePage(classes, classId, sectionId) {
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

    function attendanceSheet(students, existingRecords) {
        return {
            students,
            statuses: {},

            init() {
                this.students.forEach(s => {
                    this.statuses[s.id] = existingRecords[s.id] || null;
                });
            },

            setStatus(studentId, status) {
                // Toggle off if already selected
                this.statuses[studentId] = this.statuses[studentId] === status ? null : status;
            },

            isActive(studentId, status) {
                return this.statuses[studentId] === status;
            },

            markAllPresent() {
                this.students.forEach(s => {
                    this.statuses[s.id] = 'present';
                });
            },

            get markedCount() {
                return Object.values(this.statuses).filter(v => v !== null).length;
            },
        };
    }
</script>
@endpush
