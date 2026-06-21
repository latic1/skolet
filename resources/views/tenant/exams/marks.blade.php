@extends('layouts.tenant')

@section('title', 'Marks Entry')
@section('page-title', 'Marks Entry')

@section('content')
@php
    $host     = request()->getSchemeAndHttpHost();
    $canEdit  = auth()->user()->can('exams.edit');

    $classesJson = $classes->map(fn ($c) => [
        'id'       => $c->id,
        'name'     => $c->name,
        'sections' => $c->sections->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])->values()->toArray(),
    ])->values()->toJson();

    $subjectsJson = $subjects->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])->values()->toJson();

    // Teacher assignments: [{class_id, section_id, subject_id}]
    $assignmentsJson = $teacherAssignments->values()->toJson();

    $studentsJson = $students->map(fn ($s) => [
        'id'           => $s->id,
        'full_name'    => $s->full_name,
        'admission_no' => $s->admission_no,
        'initial'      => strtoupper(mb_substr($s->full_name, 0, 1)),
        'marks'        => isset($existingMarks[$s->id]) ? (string) $existingMarks[$s->id]->marks : '',
        'grade'        => $existingMarks[$s->id]?->grade ?? '',
    ])->values()->toJson();
@endphp

<div x-data="marksFilter({{ $classesJson }}, {{ $subjectsJson }}, {{ $assignmentsJson }}, '{{ $canManageAll ? 'admin' : 'teacher' }}', '{{ $examId }}', '{{ $classId }}', '{{ $sectionId }}', '{{ $subjectId }}')"
     class="flex flex-col gap-6">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-text-muted">
        <a href="{{ $host }}/exams" class="hover:text-text-primary transition-colors">Exams</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-text-primary font-medium">Marks Entry</span>
    </div>

    {{-- Flash messages --}}

    {{-- Access denied banner --}}
    @if($accessDenied)
    <div class="bg-error-light border border-error text-error text-sm px-4 py-3 rounded-xl flex items-start gap-3">
        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        You are not assigned to teach this subject for the selected class.
    </div>
    @endif

    {{-- Filter bar --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card p-5">
        <form method="GET" action="{{ $host }}/exams/marks" class="flex flex-col gap-4">

            <div class="flex flex-wrap items-end gap-4">

                {{-- Exam --}}
                <div class="flex flex-col gap-1.5 min-w-[200px] flex-1">
                    <label class="text-xs font-medium text-text-dark">Exam</label>
                    <select name="exam_id" x-model="examId"
                            class="px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                        <option value="">Select Exam</option>
                        @foreach($exams as $exam)
                        <option value="{{ $exam->id }}" @selected(request('exam_id') === $exam->id)>
                            {{ $exam->name }}{{ $exam->term ? ' — ' . $exam->term->name : '' }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Class --}}
                <div class="flex flex-col gap-1.5 min-w-[160px]">
                    <label class="text-xs font-medium text-text-dark">Class</label>
                    <select name="class_id" x-model="classId" @change="onClassChange()"
                            class="px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                        <option value="">Select Class</option>
                        <template x-for="cls in availableClasses" :key="cls.id">
                            <option :value="cls.id" x-text="cls.name"></option>
                        </template>
                    </select>
                </div>

                {{-- Section (shown only when selected class has sections) --}}
                <div class="flex flex-col gap-1.5 min-w-[140px]" x-show="hasSections">
                    <label class="text-xs font-medium text-text-dark">Section</label>
                    <select name="section_id" x-model="sectionId"
                            class="px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                        <option value="">Select Section</option>
                        <template x-for="sec in currentSections" :key="sec.id">
                            <option :value="sec.id" x-text="sec.name"></option>
                        </template>
                    </select>
                </div>

                {{-- Subject --}}
                <div class="flex flex-col gap-1.5 min-w-[160px]">
                    <label class="text-xs font-medium text-text-dark">Subject</label>
                    <select name="subject_id" x-model="subjectId"
                            class="px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                        <option value="">Select Subject</option>
                        <template x-for="sub in availableSubjects" :key="sub.id">
                            <option :value="sub.id" x-text="sub.name"></option>
                        </template>
                    </select>
                </div>

                <button type="submit"
                        class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors">
                    Load
                </button>
            </div>

            @if(!$canManageAll && $teacherAssignments->isEmpty())
            <p class="text-xs text-text-muted">
                No timetable assignments found for your account. Ask your School Admin to assign you to classes via the Timetable.
            </p>
            @endif

        </form>
    </div>

    {{-- Marks sheet --}}
    @if($examId && $classId && $subjectId && !$accessDenied)

        @if($students->isEmpty())
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

        <div x-data="marksSheet({{ $studentsJson }})" class="bg-surface border border-border rounded-2xl shadow-card">

            {{-- Sheet header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-border flex-wrap gap-3">
                <div>
                    <h3 class="text-sm font-semibold text-text-primary">
                        {{ $selectedExam?->name }}
                        &middot; {{ $selectedClass?->name }}{{ $selectedSection ? ' — ' . $selectedSection->name : '' }}
                        &middot; {{ $selectedSubject?->name }}
                    </h3>
                    <p class="text-xs text-text-muted mt-0.5">
                        <span x-text="enteredCount"></span>/{{ $students->count() }} students have marks entered
                    </p>
                </div>
                @if($canEdit)
                <button type="button" @click="clearAll()"
                        class="px-3 py-1.5 text-xs font-medium text-text-secondary border border-border rounded-md hover:bg-surface-secondary transition-colors">
                    Clear All
                </button>
                @endif
            </div>

            {{-- Marks form --}}
            @if($canEdit)
            <form method="POST" action="{{ $host }}/exams/marks">
                @csrf
                <input type="hidden" name="exam_id" value="{{ $examId }}">
                <input type="hidden" name="class_id" value="{{ $classId }}">
                <input type="hidden" name="section_id" value="{{ $sectionId }}">
                <input type="hidden" name="subject_id" value="{{ $subjectId }}">
            @endif

                <div class="overflow-x-auto">
                    <table class="w-full" style="min-width: 600px">
                        <thead>
                            <tr class="border-b border-border bg-surface-secondary">
                                <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary w-10">#</th>
                                <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Student</th>
                                <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary hidden sm:table-cell">Adm. No.</th>
                                <th class="text-center px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary" style="min-width:140px">
                                    Marks <span class="normal-case font-normal">(out of 100)</span>
                                </th>
                                <th class="text-center px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Grade</th>
                                <th class="px-6 py-3" style="min-width:120px"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $i => $student)
                            <tr class="border-b border-border last:border-b-0 hover:bg-surface-secondary transition-colors">
                                <td class="px-6 py-3.5 text-sm text-text-muted">{{ $i + 1 }}</td>

                                {{-- Student --}}
                                <td class="px-6 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-accent-muted flex items-center justify-center shrink-0">
                                            <span class="text-xs font-semibold text-accent">{{ strtoupper(mb_substr($student->full_name, 0, 1)) }}</span>
                                        </div>
                                        <span class="text-sm font-medium text-text-primary">{{ $student->full_name }}</span>
                                    </div>
                                </td>

                                {{-- Admission No --}}
                                <td class="px-6 py-3.5 text-sm text-text-muted hidden sm:table-cell">{{ $student->admission_no }}</td>

                                {{-- Marks input / read-only --}}
                                <td class="px-6 py-3.5 text-center">
                                    @if($canEdit)
                                    <input type="number" name="marks[{{ $student->id }}]"
                                           x-ref="marks_{{ $student->id }}"
                                           @input="updateMarks('{{ $student->id }}', $event.target.value)"
                                           value="{{ $existingMarks[$student->id]?->marks ?? '' }}"
                                           min="0" max="100" step="0.5"
                                           placeholder="—"
                                           class="w-24 px-3 py-1.5 bg-surface border border-border rounded-md text-sm text-text-primary text-center placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                                    @else
                                    <span class="text-sm {{ isset($existingMarks[$student->id]) ? 'text-text-primary font-medium' : 'text-text-muted' }}">
                                        {{ isset($existingMarks[$student->id]) ? number_format($existingMarks[$student->id]->marks, 1) : '—' }}
                                    </span>
                                    @endif
                                </td>

                                {{-- Grade badge --}}
                                <td class="px-6 py-3.5 text-center">
                                    @if($canEdit)
                                    <span x-text="gradeLabel('{{ $student->id }}')"
                                          :class="gradeClass('{{ $student->id }}')"
                                          class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium min-w-[28px] justify-center">
                                    </span>
                                    @else
                                        @if(isset($existingMarks[$student->id]))
                                        @php $grade = $existingMarks[$student->id]->grade; @endphp
                                        @if($grade === 'A')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-success-lightest text-success-foreground">A</span>
                                        @elseif($grade === 'B')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-info-lightest text-info-foreground">B</span>
                                        @elseif($grade === 'C')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-warning-light text-warning" style="background:#FFF7ED">C</span>
                                        @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-error-light text-error">{{ $grade }}</span>
                                        @endif
                                        @else
                                        <span class="text-xs text-text-muted">—</span>
                                        @endif
                                    @endif
                                </td>

                                {{-- Progress bar --}}
                                <td class="px-6 py-3.5">
                                    @if($canEdit)
                                    <div class="w-full h-1 rounded-full bg-border-light overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-200"
                                             :style="`width: ${progressWidth('{{ $student->id }}')}%; background: ${progressColor('{{ $student->id }}')}`">
                                        </div>
                                    </div>
                                    @else
                                        @if(isset($existingMarks[$student->id]))
                                        @php
                                            $pct = min(100, (float) $existingMarks[$student->id]->marks);
                                            $g = $existingMarks[$student->id]->grade;
                                            $pColor = $g === 'A' ? '#10b981' : ($g === 'B' ? '#06b6d4' : ($g === 'C' ? '#ff8904' : '#ef4444'));
                                        @endphp
                                        <div class="w-full h-1 rounded-full bg-border-light overflow-hidden">
                                            <div class="h-full rounded-full" style="width:{{ $pct }}%; background:{{ $pColor }}"></div>
                                        </div>
                                        @endif
                                    @endif
                                </td>

                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            @if($canEdit)
                <div class="flex items-center justify-between px-6 py-4 border-t border-border">
                    <p class="text-xs text-text-muted">
                        <span x-text="enteredCount"></span> of {{ $students->count() }} students have marks entered
                    </p>
                    <button type="submit"
                            class="px-5 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors">
                        Save Marks
                    </button>
                </div>
            </form>
            @endif

        </div>
        @endif

    @elseif(!$examId || !$classId || !$subjectId)
    {{-- Prompt state --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card">
        <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
            <div class="w-12 h-12 rounded-xl bg-accent-muted flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-text-primary mb-1">Select exam, class, and subject to begin</p>
            <p class="text-xs text-text-muted">
                @if($exams->isEmpty())
                No exams have been created yet. <a href="{{ $host }}/exams" class="text-accent hover:underline">Add an exam</a> first.
                @else
                Choose filters above and click Load to view the student marks sheet.
                @endif
            </p>
        </div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
    function marksFilter(classes, subjects, assignments, role, examId, classId, sectionId, subjectId) {
        return {
            classes,
            subjects,
            assignments, // [{class_id, section_id, subject_id}] — empty for admins
            role,        // 'admin' | 'teacher'
            examId:    examId || '',
            classId:   classId || '',
            sectionId: sectionId || '',
            subjectId: subjectId || '',

            get isTeacher() {
                return this.role === 'teacher';
            },

            get currentClass() {
                return this.classes.find(c => c.id === this.classId) || null;
            },

            get currentSections() {
                return this.currentClass ? this.currentClass.sections : [];
            },

            get hasSections() {
                return this.currentSections.length > 0;
            },

            get availableClasses() {
                if (!this.isTeacher || this.assignments.length === 0) return this.classes;
                const assignedClassIds = [...new Set(this.assignments.map(a => a.class_id))];
                return this.classes.filter(c => assignedClassIds.includes(c.id));
            },

            get availableSubjects() {
                if (!this.isTeacher || this.assignments.length === 0) return this.subjects;
                if (!this.classId) return [];
                const assigned = this.assignments.filter(a => {
                    if (a.class_id !== this.classId) return false;
                    if (this.sectionId && a.section_id && a.section_id !== this.sectionId) return false;
                    return true;
                });
                const assignedSubjectIds = [...new Set(assigned.map(a => a.subject_id))];
                return this.subjects.filter(s => assignedSubjectIds.includes(s.id));
            },

            onClassChange() {
                this.sectionId = '';
                this.subjectId = '';
            },
        };
    }

    function marksSheet(students) {
        return {
            students,
            marks: {},

            init() {
                this.students.forEach(s => {
                    this.marks[s.id] = s.marks;
                });
            },

            updateMarks(studentId, value) {
                this.marks[studentId] = value;
            },

            clearAll() {
                this.students.forEach(s => {
                    this.marks[s.id] = '';
                    // Reset the input DOM value
                    const input = document.querySelector(`input[name="marks[${s.id}]"]`);
                    if (input) input.value = '';
                });
            },

            computeGrade(studentId) {
                const val = this.marks[studentId];
                if (val === '' || val === null || val === undefined) return null;
                const m = parseFloat(val);
                if (isNaN(m)) return null;
                if (m >= 70) return 'A';
                if (m >= 60) return 'B';
                if (m >= 50) return 'C';
                if (m >= 40) return 'D';
                return 'F';
            },

            gradeLabel(studentId) {
                return this.computeGrade(studentId) || '—';
            },

            gradeClass(studentId) {
                const g = this.computeGrade(studentId);
                if (!g) return 'text-text-muted';
                const map = {
                    A: 'bg-success-lightest text-success-foreground',
                    B: 'bg-info-lightest text-info-foreground',
                    C: 'text-warning',
                    D: 'bg-error-light text-error',
                    F: 'bg-error-light text-error',
                };
                return map[g] || 'text-text-muted';
            },

            progressWidth(studentId) {
                const val = this.marks[studentId];
                if (val === '' || val === null || val === undefined) return 0;
                return Math.min(100, Math.max(0, parseFloat(val) || 0));
            },

            progressColor(studentId) {
                const g = this.computeGrade(studentId);
                const map = { A: '#10b981', B: '#06b6d4', C: '#ff8904', D: '#ef4444', F: '#ef4444' };
                return map[g] || '#e7eaf3';
            },

            get enteredCount() {
                return Object.values(this.marks).filter(v => v !== '' && v !== null && v !== undefined).length;
            },
        };
    }
</script>
@endpush
