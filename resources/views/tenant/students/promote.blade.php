@extends('layouts.tenant')

@section('title', 'End of Year Promotion')
@section('page-title', 'Students')

@section('content')
@php $host = request()->getSchemeAndHttpHost(); @endphp
<div class="flex flex-col gap-6" x-data="{ submitting: false }">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-text-muted">
        <a href="{{ $host }}/students" class="hover:text-text-primary transition-colors">Students</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-text-primary font-medium">End of Year Promotion</span>
    </div>

    {{-- Error banner --}}
    @if(session('error'))
    <div class="bg-error-light border border-error text-error text-sm px-4 py-3 rounded-xl flex items-start gap-3">
        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-9a1 1 0 012 0v4a1 1 0 01-2 0V9zm1-5a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"/>
        </svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif
    @if(session('promotion_errors'))
    <div class="bg-error-light border border-error text-error text-sm px-4 py-3 rounded-xl">
        <p class="font-medium mb-1">The following errors occurred:</p>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach(session('promotion_errors') as $err)
            <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Page header --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card px-6 py-5">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-warning-light flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </div>
            <div>
                <h2 class="text-base font-semibold text-text-primary">End of Year Promotion</h2>
                <p class="text-sm text-text-muted mt-0.5">
                    Move students to the next class, retain them, or mark them as graduated for
                    <span class="font-medium text-text-primary">{{ $currentYear->name }}</span>.
                </p>
            </div>
        </div>
    </div>

    {{-- ── Step 1: Class selection ──────────────────────────────── --}}
    @if(! $selectedClass)
    <div class="bg-surface border border-border rounded-2xl shadow-card">
        <div class="px-6 py-4 border-b border-border">
            <h3 class="text-base font-semibold text-text-primary">Step 1 — Select Class</h3>
            <p class="text-xs text-text-muted mt-0.5">Choose the class you want to run promotion for. All active students in that class will be listed.</p>
        </div>
        <form method="GET" action="{{ $host }}/students/promote" class="p-6 space-y-4"
              x-data="{
                  classId: '',
                  classes: {{ $classes->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'sections' => $c->sections->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->values()])->values()->toJson() }},
                  get sections() { return this.classes.find(c => c.id === this.classId)?.sections ?? []; }
              }">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-lg">
                <div>
                    <label class="block text-sm font-medium text-text-dark mb-1.5">Class <span class="text-error">*</span></label>
                    <select name="class_id" required x-model="classId"
                            class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                        <option value="">Select a class</option>
                        @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div x-show="sections.length > 0">
                    <label class="block text-sm font-medium text-text-dark mb-1.5">Section <span class="text-text-muted">(optional)</span></label>
                    <select name="section_id"
                            class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                        <option value="">All sections</option>
                        <template x-for="section in sections" :key="section.id">
                            <option :value="section.id" x-text="section.name"></option>
                        </template>
                    </select>
                </div>
            </div>
            <button type="submit" :disabled="!classId"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                Load Students
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </form>
    </div>

    {{-- ── Step 2: Assign outcomes ──────────────────────────────── --}}
    @else
    <div class="bg-surface border border-border rounded-2xl shadow-card"
         x-data="{
             submitting: false,
             defaultOutcome: '{{ $isTopClass ? 'graduated' : 'promoted' }}',
             setAll(outcome) {
                 document.querySelectorAll('[name^=\"outcomes[\"]').forEach(el => el.value = outcome);
             }
         }">
        <div class="px-6 py-4 border-b border-border flex items-center justify-between gap-4 flex-wrap">
            <div>
                <h3 class="text-base font-semibold text-text-primary">
                    Step 2 — Assign Outcomes
                    <span class="text-text-muted font-normal">·</span>
                    <span class="text-text-muted font-normal text-sm">{{ $selectedClass->name }}{{ $selectedSection ? ' / ' . $selectedSection->name : '' }}</span>
                </h3>
                <p class="text-xs text-text-muted mt-0.5">
                    {{ $students->count() }} active student{{ $students->count() !== 1 ? 's' : '' }}.
                    @if($nextClass)
                    Promoting moves students to <span class="font-medium text-text-primary">{{ $nextClass->name }}</span>.
                    @else
                    This is the highest class — students can be Retained or Graduated.
                    @endif
                </p>
            </div>
            <a href="{{ $host }}/students/promote" class="text-sm text-text-muted hover:text-text-primary transition-colors">
                ← Change class
            </a>
        </div>

        @if($students->isEmpty())
        <div class="px-6 py-10 text-center">
            <p class="text-sm text-text-muted">No active students found in this class{{ $selectedSection ? ' / section' : '' }}.</p>
            <a href="{{ $host }}/students/promote" class="mt-3 inline-flex text-sm text-accent hover:underline">← Select a different class</a>
        </div>
        @else

        {{-- Bulk actions --}}
        <div class="px-6 py-3 border-b border-border bg-surface-secondary flex items-center gap-3 flex-wrap">
            <span class="text-xs font-medium text-text-muted">Set all to:</span>
            @if(! $isTopClass)
            <button type="button" @click="setAll('promoted')"
                    class="px-3 py-1 text-xs font-medium text-accent border border-accent rounded-md hover:bg-accent-muted transition-colors">
                Promote All
            </button>
            @endif
            <button type="button" @click="setAll('retained')"
                    class="px-3 py-1 text-xs font-medium text-text-secondary border border-border rounded-md hover:bg-surface transition-colors">
                Retain All
            </button>
            <button type="button" @click="setAll('graduated')"
                    class="px-3 py-1 text-xs font-medium text-text-secondary border border-border rounded-md hover:bg-surface transition-colors">
                Graduate All
            </button>
        </div>

        <form method="POST" action="{{ $host }}/students/promote" @submit="submitting = true">
            @csrf
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-surface-secondary">
                            <th class="px-6 py-3 text-left text-xs font-medium text-text-muted">Student</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-text-muted">Admission No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-text-muted hidden sm:table-cell">Section</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-text-muted w-44">Outcome</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach($students as $student)
                        <tr class="hover:bg-surface-secondary/50 transition-colors">
                            <td class="px-6 py-3">
                                <span class="font-medium text-text-primary">{{ $student->full_name }}</span>
                            </td>
                            <td class="px-6 py-3 text-text-muted">{{ $student->admission_no }}</td>
                            <td class="px-6 py-3 text-text-muted hidden sm:table-cell">{{ $student->section?->name ?? '—' }}</td>
                            <td class="px-6 py-3">
                                <select name="outcomes[{{ $student->id }}]" required
                                        class="w-full px-2 py-1.5 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                                    @if(! $isTopClass)
                                    <option value="promoted" selected>Promote → {{ $nextClass->name }}</option>
                                    @endif
                                    <option value="retained" @if($isTopClass) selected @endif>Retain (same class)</option>
                                    <option value="graduated">Graduate</option>
                                </select>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-border flex items-center justify-between gap-4 flex-wrap bg-surface-secondary rounded-b-2xl">
                <p class="text-xs text-text-muted">
                    This action will update each student's class for the next academic year. It cannot be undone automatically.
                </p>
                <div class="flex items-center gap-3">
                    <a href="{{ $host }}/students/promote" class="px-4 py-2 text-sm font-medium text-text-secondary hover:text-text-primary transition-colors">Cancel</a>
                    <button type="submit" :disabled="submitting"
                            class="inline-flex items-center gap-2 px-5 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
                        <span x-show="!submitting">Execute Promotion</span>
                        <span x-show="submitting">Processing…</span>
                    </button>
                </div>
            </div>
        </form>
        @endif
    </div>
    @endif

</div>
@endsection
