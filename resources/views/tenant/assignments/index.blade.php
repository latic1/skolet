@extends('layouts.tenant')

@section('title', 'Assignments')
@section('page-title', 'Assignments')

@section('content')
@php
    $user         = auth()->user();
    $canCreate    = $user->can('assignments.create');
    $canManageAll = $user->can('settings.manage');
    $isStudent    = $user->can('assignments.submit') && ! $canCreate;
    $isParent     = isset($isParent) && $isParent;
@endphp

{{-- Flash messages --}}
@if (session('success'))
    <div class="mb-5 bg-success-lightest border border-success-light text-success-foreground text-sm px-4 py-3 rounded-xl flex items-start gap-3">
        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('success') }}
    </div>
@endif
@if (session('error'))
    <div class="mb-5 bg-error-light border border-error text-error text-sm px-4 py-3 rounded-xl flex items-start gap-3">
        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        {{ session('error') }}
    </div>
@endif

{{-- ============================= STUDENT VIEW ============================= --}}
@if ($isStudent)
    @php
        $now  = now();
        $tabs = ['pending', 'submitted', 'overdue'];
    @endphp
    <div x-data="{ activeTab: 'pending' }">
        {{-- Tab bar --}}
        <div class="flex items-center gap-1 border-b border-border pb-0 mb-6 overflow-x-auto whitespace-nowrap">
            @foreach (['pending' => 'Pending', 'submitted' => 'Submitted', 'overdue' => 'Overdue'] as $key => $label)
                <button @click="activeTab = '{{ $key }}'"
                        :class="activeTab === '{{ $key }}'
                            ? 'border-accent text-accent'
                            : 'border-transparent text-text-secondary hover:text-text-primary'"
                        class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors">
                    {{ $label }}
                    @php
                        $count = match($key) {
                            'pending'   => $pendingAssignments->count(),
                            'submitted' => $submittedAssignments->count(),
                            'overdue'   => $overdueAssignments->count(),
                        };
                    @endphp
                    @if ($count > 0)
                        <span class="ml-1.5 text-xs px-1.5 py-0.5 rounded-full
                            {{ $key === 'overdue' ? 'bg-error-light text-error' : 'bg-accent-muted text-accent' }}">
                            {{ $count }}
                        </span>
                    @endif
                </button>
            @endforeach
        </div>

        {{-- Pending tab --}}
        <div x-show="activeTab === 'pending'">
            @if ($pendingAssignments->isEmpty())
                <div class="bg-surface border border-border rounded-2xl shadow-card p-12 flex flex-col items-center gap-3 text-center">
                    <div class="w-12 h-12 rounded-xl bg-accent-muted flex items-center justify-center">
                        <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-text-primary">No pending assignments</p>
                    <p class="text-xs text-text-muted">All caught up!</p>
                </div>
            @else
                <div class="grid grid-cols-1 gap-4">
                    @foreach ($pendingAssignments as $assignment)
                        @php $daysLeft = now()->diffInDays($assignment->due_date, false); @endphp
                        <div class="bg-surface border border-border rounded-2xl shadow-card overflow-hidden"
                             x-data="{ showSubmit: false, submitting: false }">
                            <div class="flex items-start justify-between gap-4 px-6 py-4 border-b border-border">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-accent-muted flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-semibold text-text-primary">{{ $assignment->title }}</h3>
                                        <p class="text-xs text-text-muted mt-0.5">
                                            {{ $assignment->subject->name }}
                                            @if ($assignment->teacher)
                                                · {{ $assignment->teacher->full_name }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    @if ($daysLeft <= 3 && $daysLeft >= 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-warning-light text-warning">
                                            Due in {{ $daysLeft === 0 ? 'today' : $daysLeft . 'd' }}
                                        </span>
                                    @else
                                        <span class="text-xs text-text-muted">Due {{ $assignment->due_date->format('d M Y') }}</span>
                                    @endif
                                    <button @click="showSubmit = !showSubmit"
                                            class="px-3 py-1.5 text-xs font-medium bg-accent text-accent-foreground rounded-md hover:bg-accent-dark transition-colors">
                                        Submit
                                    </button>
                                </div>
                            </div>
                            <div class="px-6 py-4">
                                <p class="text-sm text-text-secondary leading-relaxed">{{ $assignment->description }}</p>
                                @if ($assignment->total_marks)
                                    <p class="text-xs text-text-muted mt-2">Total marks: {{ $assignment->total_marks }}</p>
                                @endif
                            </div>
                            {{-- Submit form (toggled) --}}
                            <div x-show="showSubmit" x-cloak class="border-t border-border px-6 py-4 bg-surface-secondary">
                                <form method="POST"
                                      action="{{ request()->getSchemeAndHttpHost() }}/assignments/{{ $assignment->id }}/submit"
                                      enctype="multipart/form-data"
                                      @submit="submitting = true">
                                    @csrf
                                    <div class="flex flex-col gap-3">
                                        <div>
                                            <label class="block text-xs font-medium text-text-dark mb-1">Your Answer</label>
                                            <textarea name="submission_text" rows="4"
                                                      class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent resize-y"
                                                      placeholder="Type your answer here..."></textarea>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-text-dark mb-1">Or attach a file (optional)</label>
                                            <input type="file" name="file"
                                                   accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png,.zip"
                                                   class="w-full text-sm text-text-secondary file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-accent-muted file:text-accent cursor-pointer">
                                            <p class="mt-1 text-xs text-text-muted">PDF, Word, image, or ZIP · max 10 MB</p>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button type="submit"
                                                    :disabled="submitting"
                                                    class="px-4 py-2 text-sm font-medium bg-accent text-accent-foreground rounded-md hover:bg-accent-dark transition-colors disabled:opacity-60">
                                                <span x-show="!submitting">Submit Assignment</span>
                                                <span x-show="submitting">Submitting…</span>
                                            </button>
                                            <button type="button" @click="showSubmit = false"
                                                    class="px-4 py-2 text-sm font-medium bg-surface border border-border text-text-primary rounded-md hover:bg-surface-secondary transition-colors">
                                                Cancel
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Submitted tab --}}
        <div x-show="activeTab === 'submitted'" x-cloak>
            @if ($submittedAssignments->isEmpty())
                <div class="bg-surface border border-border rounded-2xl shadow-card p-12 flex flex-col items-center gap-3 text-center">
                    <div class="w-12 h-12 rounded-xl bg-surface-secondary flex items-center justify-center">
                        <svg class="w-6 h-6 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-text-primary">No submitted assignments yet</p>
                </div>
            @else
                <div class="grid grid-cols-1 gap-4">
                    @foreach ($submittedAssignments as $assignment)
                        @php $submission = $assignment->submissions->first(); @endphp
                        <div class="bg-surface border border-border rounded-2xl shadow-card overflow-hidden">
                            <div class="flex items-start justify-between gap-4 px-6 py-4 border-b border-border">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-success-lightest flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-4 h-4 text-success-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-semibold text-text-primary">{{ $assignment->title }}</h3>
                                        <p class="text-xs text-text-muted mt-0.5">
                                            {{ $assignment->subject->name }} · Submitted {{ $submission->submitted_at->format('d M Y') }}
                                        </p>
                                    </div>
                                </div>
                                @if ($submission->marks_awarded !== null)
                                    <span class="shrink-0 inline-flex items-center px-2.5 py-1 rounded-full text-sm font-semibold bg-accent-muted text-accent">
                                        {{ $submission->marks_awarded }}{{ $assignment->total_marks ? '/' . $assignment->total_marks : '' }}
                                    </span>
                                @else
                                    <span class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-surface-secondary text-text-secondary">
                                        Awaiting grade
                                    </span>
                                @endif
                            </div>
                            @if ($submission->feedback)
                                <div class="px-6 py-4">
                                    <p class="text-xs font-medium text-text-muted uppercase tracking-wide mb-1">Teacher Feedback</p>
                                    <p class="text-sm text-text-secondary leading-relaxed">{{ $submission->feedback }}</p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Overdue tab --}}
        <div x-show="activeTab === 'overdue'" x-cloak>
            @if ($overdueAssignments->isEmpty())
                <div class="bg-surface border border-border rounded-2xl shadow-card p-12 flex flex-col items-center gap-3 text-center">
                    <div class="w-12 h-12 rounded-xl bg-success-lightest flex items-center justify-center">
                        <svg class="w-6 h-6 text-success-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-text-primary">No overdue assignments</p>
                    <p class="text-xs text-text-muted">Great job staying on top of your work!</p>
                </div>
            @else
                <div class="grid grid-cols-1 gap-4">
                    @foreach ($overdueAssignments as $assignment)
                        <div class="bg-surface border border-error rounded-2xl shadow-card overflow-hidden">
                            <div class="flex items-start justify-between gap-4 px-6 py-4 border-b border-border">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-error-light flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-4 h-4 text-error" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-semibold text-text-primary">{{ $assignment->title }}</h3>
                                        <p class="text-xs text-text-muted mt-0.5">{{ $assignment->subject->name }}</p>
                                    </div>
                                </div>
                                <span class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-error-light text-error">
                                    Overdue · {{ $assignment->due_date->format('d M Y') }}
                                </span>
                            </div>
                            <div class="px-6 py-4">
                                <p class="text-sm text-text-secondary leading-relaxed">{{ $assignment->description }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

{{-- ============================= TEACHER / ADMIN VIEW ============================= --}}
@elseif (! $isParent)
    <div x-data="assignmentsPage(
        {{ Js::from($classes->values()) }},
        {{ Js::from($subjects->values()) }},
        {{ Js::from($sections->values()) }},
        {{ Js::from($staff->values()) }},
        {{ Js::from($canManageAll) }}
    )">

        {{-- Page header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <p class="text-xs text-text-muted">
                    {{ $assignments->count() }} {{ Str::plural('assignment', $assignments->count()) }}
                </p>
            </div>
            @can('assignments.create')
                <button @click="openAdd()"
                        class="px-4 py-2 text-sm font-medium bg-accent text-accent-foreground rounded-md hover:bg-accent-dark transition-colors">
                    + Create Assignment
                </button>
            @endcan
        </div>

        {{-- Admin class/teacher filter bar --}}
        @if ($canManageAll)
            <form method="GET" action="{{ request()->getSchemeAndHttpHost() }}/assignments"
                  class="bg-surface border border-border rounded-2xl shadow-card p-5 mb-6">
                <div class="flex flex-wrap items-end gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-text-dark">Class</label>
                        <select name="class_id" class="px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent">
                            <option value="">All Classes</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}" {{ $filterClassId === $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-text-dark">Teacher</label>
                        <select name="teacher_id" class="px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent">
                            <option value="">All Teachers</option>
                            @foreach ($staff as $member)
                                <option value="{{ $member->id }}" {{ $filterTeacherId === $member->id ? 'selected' : '' }}>
                                    {{ $member->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium bg-accent text-accent-foreground rounded-md hover:bg-accent-dark transition-colors">
                        Filter
                    </button>
                    @if ($filterClassId || $filterTeacherId)
                        <a href="{{ request()->getSchemeAndHttpHost() }}/assignments"
                           class="text-sm text-text-secondary hover:text-text-primary transition-colors">Clear</a>
                    @endif
                </div>
            </form>
        @endif

        {{-- Assignments table --}}
        @if ($assignments->isEmpty())
            <div class="bg-surface border border-border rounded-2xl shadow-card p-12 flex flex-col items-center gap-3 text-center">
                <div class="w-12 h-12 rounded-xl bg-accent-muted flex items-center justify-center">
                    <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-text-primary">No assignments yet</p>
                @can('assignments.create')
                    <p class="text-xs text-text-muted">Create the first assignment for your class.</p>
                    <button @click="openAdd()"
                            class="mt-2 px-4 py-2 text-sm font-medium bg-accent text-accent-foreground rounded-md hover:bg-accent-dark transition-colors">
                        Create Assignment
                    </button>
                @endcan
            </div>
        @else
            <div class="bg-surface border border-border rounded-2xl shadow-card">
                <div class="flex items-center justify-between px-6 py-4 border-b border-border">
                    <h2 class="text-sm font-semibold text-text-primary">
                        All Assignments
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full" style="min-width: 700px">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Title</th>
                                <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Subject</th>
                                <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary hidden md:table-cell">Class</th>
                                <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Due Date</th>
                                <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Submissions</th>
                                <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary hidden lg:table-cell">Marks</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($assignments as $assignment)
                                @php
                                    $isPast    = $assignment->due_date->isPast();
                                    $isToday   = $assignment->due_date->isToday();
                                    $graded    = $assignment->submissions->filter(fn($s) => $s->marks_awarded !== null)->count();
                                    $ungraded  = $assignment->submissions_count - $graded;
                                @endphp
                                <tr class="border-b border-border last:border-b-0 hover:bg-surface-secondary transition-colors">
                                    <td class="px-6 py-4 text-sm">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-accent-muted flex items-center justify-center shrink-0">
                                                <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="font-medium text-text-primary">{{ $assignment->title }}</p>
                                                @if ($canManageAll && $assignment->teacher)
                                                    <p class="text-xs text-text-muted">{{ $assignment->teacher->full_name }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-text-secondary">
                                        {{ $assignment->subject->name }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-text-secondary hidden md:table-cell">
                                        {{ $assignment->schoolClass->name }}
                                        @if ($assignment->section)
                                            · {{ $assignment->section->name }}
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="{{ $isPast && ! $isToday ? 'text-error' : 'text-text-primary' }}">
                                            {{ $assignment->due_date->format('d M Y') }}
                                        </span>
                                        @if ($isToday)
                                            <span class="ml-1 text-xs text-warning font-medium">Today</span>
                                        @elseif ($isPast)
                                            <span class="ml-1 text-xs text-error font-medium">Past</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                            {{ $ungraded > 0 ? 'bg-warning-light text-warning' : 'bg-success-lightest text-success-foreground' }}">
                                            {{ $assignment->submissions_count }} submitted
                                            @if ($ungraded > 0)
                                                · {{ $ungraded }} ungraded
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-text-secondary hidden lg:table-cell">
                                        {{ $assignment->total_marks ? number_format($assignment->total_marks, 0) : '—' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-end gap-2">
                                            @can('assignments.edit')
                                                <button @click="openEdit({{ Js::from([
                                                    'id'          => $assignment->id,
                                                    'title'       => $assignment->title,
                                                    'description' => $assignment->description,
                                                    'subject_id'  => $assignment->subject_id,
                                                    'class_id'    => $assignment->class_id,
                                                    'section_id'  => $assignment->section_id,
                                                    'due_date'    => $assignment->due_date->format('Y-m-d\TH:i'),
                                                    'total_marks' => $assignment->total_marks,
                                                ]) }})"
                                                        class="text-xs font-medium text-text-secondary hover:text-text-primary transition-colors px-2 py-1 rounded hover:bg-surface-secondary">
                                                    Edit
                                                </button>
                                            @endcan
                                            @if ($assignment->submissions_count > 0)
                                                <button @click="openSubmissions({{ Js::from([
                                                    'id'    => $assignment->id,
                                                    'title' => $assignment->title,
                                                    'total_marks' => $assignment->total_marks,
                                                    'submissions' => $assignment->submissions->map(fn($s) => [
                                                        'id'            => $s->id,
                                                        'student_name'  => $s->student->full_name ?? 'Unknown',
                                                        'submitted_at'  => $s->submitted_at->format('d M Y H:i'),
                                                        'submission_text' => $s->submission_text,
                                                        'marks_awarded' => $s->marks_awarded,
                                                        'feedback'      => $s->feedback,
                                                    ])->values(),
                                                ]) }})"
                                                        class="text-xs font-medium text-accent hover:text-accent-dark transition-colors px-2 py-1 rounded hover:bg-accent-muted">
                                                    View Submissions
                                                </button>
                                            @endif
                                            @can('assignments.delete')
                                                <form method="POST"
                                                      action="{{ request()->getSchemeAndHttpHost() }}/assignments/{{ $assignment->id }}"
                                                      onsubmit="return confirm('Delete this assignment? All submissions will also be deleted.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
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
            </div>
        @endif

        {{-- ===== Create / Edit Modal ===== --}}
        <div x-show="showModal" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-overlay/40" @click="close()"></div>
            <div class="relative w-full max-w-lg bg-surface rounded-2xl shadow-xl border border-border p-6 overflow-y-auto"
                 style="max-height: 90vh"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-base font-semibold text-text-primary" x-text="mode === 'add' ? 'Create Assignment' : 'Edit Assignment'"></h2>
                    <button @click="close()" class="p-1.5 rounded-md text-text-muted hover:bg-surface-secondary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Add form --}}
                <form x-show="mode === 'add'" method="POST"
                      action="{{ request()->getSchemeAndHttpHost() }}/assignments"
                      @submit="submitting = true">
                    @csrf
                    @include('tenant.assignments._form')
                    <div class="flex justify-end gap-2 mt-6">
                        <button type="button" @click="close()"
                                class="px-4 py-2 text-sm font-medium bg-surface border border-border text-text-primary rounded-md hover:bg-surface-secondary transition-colors">
                            Cancel
                        </button>
                        <button type="submit" :disabled="submitting"
                                class="px-4 py-2 text-sm font-medium bg-accent text-accent-foreground rounded-md hover:bg-accent-dark transition-colors disabled:opacity-60">
                            <span x-show="!submitting">Create Assignment</span>
                            <span x-show="submitting">Saving…</span>
                        </button>
                    </div>
                </form>

                {{-- Edit form --}}
                <form x-show="mode === 'edit'" method="POST"
                      :action="`{{ request()->getSchemeAndHttpHost() }}/assignments/${form.id}`"
                      @submit="submitting = true">
                    @csrf
                    <input type="hidden" name="_method" value="PUT">
                    @include('tenant.assignments._form')
                    <div class="flex justify-end gap-2 mt-6">
                        <button type="button" @click="close()"
                                class="px-4 py-2 text-sm font-medium bg-surface border border-border text-text-primary rounded-md hover:bg-surface-secondary transition-colors">
                            Cancel
                        </button>
                        <button type="submit" :disabled="submitting"
                                class="px-4 py-2 text-sm font-medium bg-accent text-accent-foreground rounded-md hover:bg-accent-dark transition-colors disabled:opacity-60">
                            <span x-show="!submitting">Save Changes</span>
                            <span x-show="submitting">Saving…</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ===== Submissions Modal ===== --}}
        <div x-show="showSubmissions" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-overlay/40" @click="showSubmissions = false"></div>
            <div class="relative w-full max-w-2xl bg-surface rounded-2xl shadow-xl border border-border overflow-hidden"
                 style="max-height: 90vh"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">
                <div class="flex items-center justify-between px-6 py-4 border-b border-border">
                    <h2 class="text-base font-semibold text-text-primary"
                        x-text="`Submissions: ${currentAssignment?.title ?? ''}`"></h2>
                    <button @click="showSubmissions = false" class="p-1.5 rounded-md text-text-muted hover:bg-surface-secondary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="overflow-y-auto" style="max-height: calc(90vh - 64px)">
                    <template x-for="submission in currentAssignment?.submissions ?? []" :key="submission.id">
                        <div class="border-b border-border last:border-b-0 px-6 py-4">
                            <div class="flex items-start justify-between gap-4 mb-3">
                                <div>
                                    <p class="text-sm font-medium text-text-primary" x-text="submission.student_name"></p>
                                    <p class="text-xs text-text-muted mt-0.5" x-text="`Submitted ${submission.submitted_at}`"></p>
                                </div>
                                <span x-show="submission.marks_awarded !== null"
                                      class="shrink-0 inline-flex items-center px-2.5 py-1 rounded-full text-sm font-semibold bg-accent-muted text-accent"
                                      x-text="`${submission.marks_awarded}${currentAssignment?.total_marks ? '/' + currentAssignment.total_marks : ''}`"></span>
                                <span x-show="submission.marks_awarded === null"
                                      class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-surface-secondary text-text-secondary">
                                    Not graded
                                </span>
                            </div>
                            <template x-if="submission.submission_text">
                                <div class="bg-surface-secondary rounded-xl px-4 py-3 mb-3">
                                    <p class="text-xs font-medium text-text-muted mb-1">Answer</p>
                                    <p class="text-sm text-text-secondary whitespace-pre-line" x-text="submission.submission_text"></p>
                                </div>
                            </template>
                            @can('assignments.edit')
                                <form :action="`{{ request()->getSchemeAndHttpHost() }}/submissions/${submission.id}/grade`"
                                      method="POST" x-data="{ submitting: false }" @submit="submitting = true"
                                      class="flex items-end gap-2 flex-wrap">
                                    @csrf
                                    @method('PATCH')
                                    <div class="flex flex-col gap-1">
                                        <label class="text-xs font-medium text-text-dark">Marks</label>
                                        <input type="number" name="marks_awarded" step="0.5" min="0"
                                               :max="currentAssignment?.total_marks ?? undefined"
                                               :value="submission.marks_awarded"
                                               class="w-24 px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent">
                                    </div>
                                    <div class="flex flex-col gap-1 flex-1 min-w-[140px]">
                                        <label class="text-xs font-medium text-text-dark">Feedback</label>
                                        <input type="text" name="feedback" :value="submission.feedback"
                                               placeholder="Optional feedback…"
                                               class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent">
                                    </div>
                                    <button type="submit" :disabled="submitting"
                                            class="px-3 py-2 text-xs font-medium bg-accent text-accent-foreground rounded-md hover:bg-accent-dark transition-colors disabled:opacity-60">
                                        <span x-show="!submitting">Save Grade</span>
                                        <span x-show="submitting">Saving…</span>
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </template>
                    <template x-if="!currentAssignment?.submissions?.length">
                        <div class="px-6 py-12 text-center text-sm text-text-muted">No submissions yet.</div>
                    </template>
                </div>
            </div>
        </div>
    </div>

{{-- ============================= PARENT VIEW ============================= --}}
@else
    <div class="bg-surface border border-border rounded-2xl shadow-card p-12 flex flex-col items-center gap-3 text-center">
        <div class="w-12 h-12 rounded-xl bg-accent-muted flex items-center justify-center">
            <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <p class="text-sm font-medium text-text-primary">Assignments are visible to your child</p>
        <p class="text-xs text-text-muted">Your child can view and submit assignments from their account.</p>
    </div>
@endif

@push('scripts')
<script>
function assignmentsPage(classes, subjects, sections, staff, canManageAll) {
    return {
        showModal:        false,
        showSubmissions:  false,
        mode:             'add',
        submitting:       false,
        currentAssignment: null,
        form: {
            id:          '',
            title:       '',
            description: '',
            subject_id:  '',
            class_id:    '',
            section_id:  '',
            due_date:    '',
            total_marks: '',
        },
        classes,
        subjects,
        sections,
        staff,
        canManageAll,

        get currentSections() {
            return this.sections.filter(s => s.class_id === this.form.class_id);
        },
        get hasSections() {
            return this.currentSections.length > 0;
        },

        openAdd() {
            this.mode      = 'add';
            this.form      = { id: '', title: '', description: '', subject_id: '',
                               class_id: '', section_id: '', due_date: '', total_marks: '' };
            this.showModal = true;
            this.submitting = false;
        },
        openEdit(data) {
            this.mode      = 'edit';
            this.form      = { ...data };
            this.showModal = true;
            this.submitting = false;
        },
        openSubmissions(assignment) {
            this.currentAssignment = assignment;
            this.showSubmissions   = true;
        },
        close() {
            this.showModal  = false;
            this.submitting = false;
        },
    };
}
</script>
@endpush
@endsection
