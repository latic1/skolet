@extends('layouts.tenant')

@section('title', 'Class Register & Lesson Plans')

@section('content')
@php
    $classesJson  = $classes->map(fn($c) => [
        'id'       => $c->id,
        'name'     => $c->name,
        'sections' => $c->sections->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->values()->toArray(),
    ])->values()->toJson();

    $subjectsJson = $subjects->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->values()->toJson();

    $weekDates = collect(range(0, 4))->map(fn($i) => $weekStart->copy()->addDays($i));
@endphp

<div
    x-data="registerPage(
        '{{ $activeTab }}',
        {{ $classesJson }},
        {{ $subjectsJson }},
        '{{ $selectedClassId }}',
        '{{ $selectedSectionId }}',
        '{{ $weekStart->toDateString() }}'
    )"
    class="flex flex-col gap-6"
>
    {{-- Page header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-text-primary">Class Register & Lesson Plans</h1>
            <p class="text-sm text-text-muted mt-0.5">Track lessons taught and weekly teaching plans</p>
        </div>
        @if($canManage && $currentStaff)
        <a href="{{ route('tenant.register.pdf', [$currentStaff, now()->format('Y-m')]) }}"
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-xl bg-surface border border-border text-text-secondary hover:bg-muted/30 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Export Monthly PDF
        </a>
        @endif
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-success-lightest border border-success-light text-success-foreground text-sm font-medium">
        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-error-light border border-error text-error text-sm font-medium">
        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- Tabs --}}
    <div class="border-b border-border">
        <nav class="flex gap-6" aria-label="Register tabs">
            <button type="button"
                    @click="tab = 'register'"
                    :class="tab === 'register' ? 'border-b-2 border-accent text-accent font-semibold' : 'border-b-2 border-transparent text-text-secondary hover:text-text-primary'"
                    class="pb-3 text-sm transition-colors">
                Class Register
            </button>
            <button type="button"
                    @click="tab = 'plans'"
                    :class="tab === 'plans' ? 'border-b-2 border-accent text-accent font-semibold' : 'border-b-2 border-transparent text-text-secondary hover:text-text-primary'"
                    class="pb-3 text-sm transition-colors">
                Lesson Plans
            </button>
        </nav>
    </div>

    {{-- ── CLASS REGISTER TAB ── --}}
    <div x-show="tab === 'register'" x-cloak class="flex flex-col gap-6">

        {{-- Filter bar --}}
        <div class="bg-surface border border-border rounded-2xl shadow-card p-5">
            <form method="GET" action="{{ route('tenant.register.index') }}" class="flex flex-wrap items-end gap-3">
                <input type="hidden" name="tab" value="register">

                @if($canManage)
                <div class="flex flex-col gap-1.5 min-w-[160px]">
                    <label class="text-xs font-semibold text-text-secondary uppercase tracking-wide">Teacher</label>
                    <select name="reg_teacher_id" class="px-3 py-2 rounded-lg border border-border bg-surface text-sm text-text-primary focus:outline-none focus:ring-2 focus:ring-accent">
                        @foreach($allStaff as $s)
                        <option value="{{ $s->id }}" {{ $selectedTeacherId === $s->id ? 'selected' : '' }}>{{ $s->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="flex flex-col gap-1.5 min-w-[140px]">
                    <label class="text-xs font-semibold text-text-secondary uppercase tracking-wide">Class</label>
                    <select name="reg_class_id" x-model="regClassId" @change="regSectionId = ''"
                            class="px-3 py-2 rounded-lg border border-border bg-surface text-sm text-text-primary focus:outline-none focus:ring-2 focus:ring-accent">
                        <option value="">Select class…</option>
                        @foreach($classes as $cls)
                        <option value="{{ $cls->id }}" {{ $selectedClassId === $cls->id ? 'selected' : '' }}>{{ $cls->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col gap-1.5 min-w-[130px]">
                    <label class="text-xs font-semibold text-text-secondary uppercase tracking-wide">Section</label>
                    <select name="reg_section_id" x-model="regSectionId"
                            class="px-3 py-2 rounded-lg border border-border bg-surface text-sm text-text-primary focus:outline-none focus:ring-2 focus:ring-accent">
                        <option value="">All sections</option>
                        <template x-for="s in sectionsForClass" :key="s.id">
                            <option :value="s.id" :selected="s.id === '{{ $selectedSectionId }}'" x-text="s.name"></option>
                        </template>
                    </select>
                </div>

                <div class="flex flex-col gap-1.5 min-w-[140px]">
                    <label class="text-xs font-semibold text-text-secondary uppercase tracking-wide">Subject</label>
                    <select name="reg_subject_id"
                            class="px-3 py-2 rounded-lg border border-border bg-surface text-sm text-text-primary focus:outline-none focus:ring-2 focus:ring-accent">
                        <option value="">Select subject…</option>
                        @foreach($subjects as $sub)
                        <option value="{{ $sub->id }}" {{ $selectedSubjectId === $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-text-secondary uppercase tracking-wide">Date</label>
                    <input type="date" name="reg_date" value="{{ $selectedDate }}" max="{{ now()->toDateString() }}"
                           class="px-3 py-2 rounded-lg border border-border bg-surface text-sm text-text-primary focus:outline-none focus:ring-2 focus:ring-accent">
                </div>

                <button type="submit"
                        class="px-4 py-2 rounded-xl bg-accent text-white text-sm font-semibold hover:bg-accent/90 transition-colors self-end">
                    Load
                </button>
            </form>
        </div>

        @if($selectedClassId && $selectedSubjectId)
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

            {{-- Entry form --}}
            <div class="lg:col-span-2">
                <div class="bg-surface border border-border rounded-2xl shadow-card p-6">
                    <h2 class="text-base font-semibold text-text-primary mb-1">
                        Register Entry
                    </h2>
                    <p class="text-xs text-text-muted mb-4">
                        {{ \Carbon\Carbon::parse($selectedDate)->format('l, d M Y') }}
                    </p>
                    <form method="POST" action="{{ route('tenant.register.store') }}" class="flex flex-col gap-4">
                        @csrf
                        <input type="hidden" name="class_id" value="{{ $selectedClassId }}">
                        <input type="hidden" name="section_id" value="{{ $selectedSectionId ?: '' }}">
                        <input type="hidden" name="subject_id" value="{{ $selectedSubjectId }}">
                        <input type="hidden" name="date" value="{{ $selectedDate }}">

                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold text-text-secondary uppercase tracking-wide">Topic Covered <span class="text-error">*</span></label>
                            <input type="text" name="topic_covered"
                                   value="{{ old('topic_covered', $existingEntry?->topic_covered) }}"
                                   required maxlength="255"
                                   placeholder="e.g. Introduction to Algebra"
                                   class="px-3 py-2 rounded-lg border border-border bg-surface text-sm text-text-primary focus:outline-none focus:ring-2 focus:ring-accent @error('topic_covered') border-error @enderror">
                            @error('topic_covered') <span class="text-xs text-error">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold text-text-secondary uppercase tracking-wide">Notes</label>
                            <textarea name="notes" rows="5" maxlength="2000"
                                      placeholder="Class notes, homework assigned, observations…"
                                      class="px-3 py-2 rounded-lg border border-border bg-surface text-sm text-text-primary focus:outline-none focus:ring-2 focus:ring-accent resize-none @error('notes') border-error @enderror">{{ old('notes', $existingEntry?->notes) }}</textarea>
                            @error('notes') <span class="text-xs text-error">{{ $message }}</span> @enderror
                        </div>

                        <button type="submit"
                                class="w-full py-2 px-4 rounded-xl bg-accent text-white text-sm font-semibold hover:bg-accent/90 transition-colors">
                            {{ $existingEntry ? 'Update Entry' : 'Save Entry' }}
                        </button>
                    </form>
                </div>
            </div>

            {{-- Register history --}}
            <div class="lg:col-span-3">
                <div class="bg-surface border border-border rounded-2xl shadow-card overflow-hidden">
                    <div class="px-6 py-4 border-b border-border">
                        <h2 class="text-base font-semibold text-text-primary">Previous Entries</h2>
                    </div>

                    @if($registerHistory->isEmpty())
                    <div class="flex flex-col items-center justify-center py-12 gap-3 text-center">
                        <svg class="w-9 h-9 text-text-muted/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <p class="text-sm text-text-muted">No previous entries for this class/subject.</p>
                    </div>
                    @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-border bg-muted/30">
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-text-muted uppercase tracking-wide">Date</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-text-muted uppercase tracking-wide">Topic Covered</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-text-muted uppercase tracking-wide hidden lg:table-cell">Notes</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                @foreach($registerHistory as $entry)
                                <tr class="hover:bg-muted/20 transition-colors">
                                    <td class="px-5 py-3 whitespace-nowrap text-text-secondary">{{ $entry->date->format('d M Y') }}</td>
                                    <td class="px-5 py-3 font-medium text-text-primary">{{ $entry->topic_covered }}</td>
                                    <td class="px-5 py-3 text-text-secondary max-w-xs hidden lg:table-cell">
                                        <p class="truncate">{{ $entry->notes ?? '—' }}</p>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @else
        <div class="flex flex-col items-center justify-center py-16 gap-4 text-center bg-surface border border-border rounded-2xl shadow-card">
            <svg class="w-10 h-10 text-text-muted/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            <div>
                <p class="text-sm font-medium text-text-primary">Select a class and subject to load the register</p>
                <p class="text-xs text-text-muted mt-1">Choose from the filter above, then click Load</p>
            </div>
        </div>
        @endif
    </div>

    {{-- ── LESSON PLANS TAB ── --}}
    <div x-show="tab === 'plans'" x-cloak class="flex flex-col gap-6">

        {{-- Week navigation + filter --}}
        <div class="bg-surface border border-border rounded-2xl shadow-card p-5">
            <div class="flex flex-wrap items-center justify-between gap-4">
                {{-- Week range display + prev/next --}}
                <div class="flex items-center gap-3">
                    <a href="{{ route('tenant.register.index', ['tab' => 'plans', 'week_start' => $weekStart->copy()->subWeek()->toDateString(), 'plan_teacher_id' => $planTeacherId]) }}"
                       class="p-2 rounded-lg border border-border text-text-secondary hover:bg-muted/30 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                    <div class="text-center">
                        <p class="text-sm font-semibold text-text-primary">
                            {{ $weekStart->format('d M') }} – {{ $weekStart->copy()->endOfWeek(Carbon::FRIDAY)->format('d M Y') }}
                        </p>
                        <div class="flex gap-2 mt-1 justify-center">
                            @foreach($weekDates as $day)
                            <span class="text-[10px] font-medium px-1.5 py-0.5 rounded bg-muted/40 text-text-muted">
                                {{ $day->format('D') }}
                            </span>
                            @endforeach
                        </div>
                    </div>
                    <a href="{{ route('tenant.register.index', ['tab' => 'plans', 'week_start' => $weekStart->copy()->addWeek()->toDateString(), 'plan_teacher_id' => $planTeacherId]) }}"
                       class="p-2 rounded-lg border border-border text-text-secondary hover:bg-muted/30 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('tenant.register.index', ['tab' => 'plans', 'week_start' => now()->startOfWeek(Carbon::MONDAY)->toDateString(), 'plan_teacher_id' => $planTeacherId]) }}"
                       class="px-3 py-1 text-xs font-medium rounded-lg border border-border text-text-secondary hover:bg-muted/30 transition-colors">
                        This week
                    </a>
                </div>

                <div class="flex items-center gap-3">
                    {{-- Admin teacher filter --}}
                    @if($canManage)
                    <form method="GET" action="{{ route('tenant.register.index') }}" class="flex items-end gap-2">
                        <input type="hidden" name="tab" value="plans">
                        <input type="hidden" name="week_start" value="{{ $weekStart->toDateString() }}">
                        <select name="plan_teacher_id"
                                class="px-3 py-2 rounded-lg border border-border bg-surface text-sm text-text-primary focus:outline-none focus:ring-2 focus:ring-accent">
                            <option value="">All teachers</option>
                            @foreach($allStaff as $s)
                            <option value="{{ $s->id }}" {{ $planTeacherId === $s->id ? 'selected' : '' }}>{{ $s->full_name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="px-3 py-2 rounded-lg bg-accent text-white text-sm font-semibold hover:bg-accent/90 transition-colors">Filter</button>
                    </form>
                    @endif

                    {{-- Create Plan button --}}
                    @can('register.create')
                    <button type="button" @click="createModal = true"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-accent text-white text-sm font-semibold hover:bg-accent/90 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        New Plan
                    </button>
                    @endcan
                </div>
            </div>
        </div>

        {{-- Lesson plans for the week --}}
        @if($lessonPlans->isEmpty())
        <div class="flex flex-col items-center justify-center py-16 gap-4 text-center bg-surface border border-border rounded-2xl shadow-card">
            <svg class="w-10 h-10 text-text-muted/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <div>
                <p class="text-sm font-medium text-text-primary">No lesson plans for this week</p>
                @can('register.create')
                <p class="text-xs text-text-muted mt-1">Click "New Plan" to create one</p>
                @endcan
            </div>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($lessonPlans as $plan)
            <div class="bg-surface border border-border rounded-2xl shadow-card p-5 flex flex-col gap-3"
                 x-data="{ editing: false }">
                {{-- Plan header --}}
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="text-sm font-semibold text-text-primary">
                            {{ $plan->subject->name }}
                        </p>
                        <p class="text-xs text-text-muted mt-0.5">
                            {{ $plan->schoolClass->name }}@if($plan->section) — {{ $plan->section->name }}@endif
                        </p>
                        @if($canManage)
                        <p class="text-xs text-accent mt-0.5">{{ $plan->teacher->full_name }}</p>
                        @endif
                    </div>
                    @can('register.create')
                    <div class="flex items-center gap-1.5 shrink-0">
                        <button type="button" @click="editing = !editing"
                                class="p-1.5 rounded-lg text-text-muted hover:text-text-primary hover:bg-muted/30 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <form method="POST" action="{{ route('tenant.lesson-plan.destroy', $plan) }}"
                              onsubmit="return confirm('Delete this lesson plan?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="p-1.5 rounded-lg text-text-muted hover:text-error hover:bg-error-light transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                    @endcan
                </div>

                {{-- Read view --}}
                <div x-show="!editing" class="flex flex-col gap-2">
                    @if($plan->objectives)
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-text-muted mb-1">Objectives</p>
                        <p class="text-xs text-text-secondary leading-relaxed">{{ $plan->objectives }}</p>
                    </div>
                    @endif
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-text-muted mb-1">Content</p>
                        <p class="text-xs text-text-secondary leading-relaxed">{{ $plan->content }}</p>
                    </div>
                </div>

                {{-- Edit view --}}
                @can('register.create')
                <form x-show="editing" method="POST" action="{{ route('tenant.lesson-plan.update', $plan) }}"
                      class="flex flex-col gap-3" x-cloak>
                    @csrf @method('PATCH')
                    <div class="flex flex-col gap-1">
                        <label class="text-[10px] font-semibold uppercase tracking-wide text-text-muted">Objectives</label>
                        <textarea name="objectives" rows="2" maxlength="2000"
                                  class="px-2 py-1.5 rounded-lg border border-border bg-surface text-xs text-text-primary focus:outline-none focus:ring-1 focus:ring-accent resize-none">{{ $plan->objectives }}</textarea>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-[10px] font-semibold uppercase tracking-wide text-text-muted">Content <span class="text-error">*</span></label>
                        <textarea name="content" rows="4" maxlength="5000" required
                                  class="px-2 py-1.5 rounded-lg border border-border bg-surface text-xs text-text-primary focus:outline-none focus:ring-1 focus:ring-accent resize-none">{{ $plan->content }}</textarea>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-accent text-white text-xs font-semibold hover:bg-accent/90 transition-colors">Save</button>
                        <button type="button" @click="editing = false" class="px-3 py-1.5 rounded-lg border border-border text-xs text-text-secondary hover:bg-muted/30 transition-colors">Cancel</button>
                    </div>
                </form>
                @endcan
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- ── CREATE LESSON PLAN MODAL ── --}}
    @can('register.create')
    <div x-show="createModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         @keydown.escape.window="createModal = false">
        <div class="absolute inset-0 bg-black/50" @click="createModal = false"></div>
        <div class="relative bg-surface rounded-2xl shadow-xl border border-border w-full max-w-lg flex flex-col gap-0 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-border">
                <h2 class="text-base font-semibold text-text-primary">Create Lesson Plan</h2>
                <button type="button" @click="createModal = false"
                        class="p-1.5 rounded-lg text-text-muted hover:bg-muted/30 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('tenant.lesson-plan.store') }}"
                  class="flex flex-col gap-4 p-6 overflow-y-auto max-h-[80vh]">
                @csrf

                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-text-secondary uppercase tracking-wide">Week Starting <span class="text-error">*</span></label>
                        <input type="date" name="week_start" x-model="planWeekStart" required
                               class="px-3 py-2 rounded-lg border border-border bg-surface text-sm text-text-primary focus:outline-none focus:ring-2 focus:ring-accent">
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-text-secondary uppercase tracking-wide">Subject <span class="text-error">*</span></label>
                        <select name="subject_id" required
                                class="px-3 py-2 rounded-lg border border-border bg-surface text-sm text-text-primary focus:outline-none focus:ring-2 focus:ring-accent">
                            <option value="">Select…</option>
                            @foreach($subjects as $sub)
                            <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-text-secondary uppercase tracking-wide">Class <span class="text-error">*</span></label>
                        <select name="class_id" x-model="planClassId" required
                                @change="planSectionId = ''"
                                class="px-3 py-2 rounded-lg border border-border bg-surface text-sm text-text-primary focus:outline-none focus:ring-2 focus:ring-accent">
                            <option value="">Select…</option>
                            @foreach($classes as $cls)
                            <option value="{{ $cls->id }}">{{ $cls->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-text-secondary uppercase tracking-wide">Section</label>
                        <select name="section_id" x-model="planSectionId"
                                class="px-3 py-2 rounded-lg border border-border bg-surface text-sm text-text-primary focus:outline-none focus:ring-2 focus:ring-accent">
                            <option value="">None</option>
                            <template x-for="s in planSectionsForClass" :key="s.id">
                                <option :value="s.id" x-text="s.name"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-text-secondary uppercase tracking-wide">Objectives</label>
                    <textarea name="objectives" rows="3" maxlength="2000"
                              placeholder="What students should know or be able to do by end of week…"
                              class="px-3 py-2 rounded-lg border border-border bg-surface text-sm text-text-primary focus:outline-none focus:ring-2 focus:ring-accent resize-none"></textarea>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-text-secondary uppercase tracking-wide">Content / Teaching Plan <span class="text-error">*</span></label>
                    <textarea name="content" rows="4" maxlength="5000" required
                              placeholder="Topics, activities, resources, assessments planned for the week…"
                              class="px-3 py-2 rounded-lg border border-border bg-surface text-sm text-text-primary focus:outline-none focus:ring-2 focus:ring-accent resize-none"></textarea>
                </div>

                <div class="flex gap-3 pt-1">
                    <button type="submit"
                            class="flex-1 py-2 rounded-xl bg-accent text-white text-sm font-semibold hover:bg-accent/90 transition-colors">
                        Save Lesson Plan
                    </button>
                    <button type="button" @click="createModal = false"
                            class="px-4 py-2 rounded-xl border border-border text-sm text-text-secondary hover:bg-muted/30 transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endcan
</div>

@push('scripts')
<script>
    function registerPage(initialTab, classesData, subjectsData, selectedClassId, selectedSectionId, currentWeekStart) {
        return {
            tab: initialTab,

            // Register tab — cascading class → section
            regClassId:    selectedClassId,
            regSectionId:  selectedSectionId,

            get sectionsForClass() {
                const cls = classesData.find(c => c.id === this.regClassId);
                return cls ? cls.sections : [];
            },

            // Lesson Plans tab — modal state
            createModal:   false,
            planWeekStart: currentWeekStart,
            planClassId:   '',
            planSectionId: '',

            get planSectionsForClass() {
                const cls = classesData.find(c => c.id === this.planClassId);
                return cls ? cls.sections : [];
            },
        };
    }
</script>
@endpush
@endsection
