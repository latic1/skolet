@extends('layouts.tenant')

@section('title', 'Behavior & Discipline')
@section('page-title', 'Behavior & Discipline')

@section('content')
@php $host = request()->getSchemeAndHttpHost(); @endphp
<div class="flex flex-col gap-6" x-data="behaviorPage()">

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="bg-success-lightest border border-success-light text-success-foreground text-sm px-4 py-3 rounded-xl flex items-start gap-3">
        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="bg-error-light border border-error text-error text-sm px-4 py-3 rounded-xl flex items-start gap-3">
        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- Page header --}}
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-base font-semibold text-text-primary">Behavior & Discipline</h1>
            <p class="text-xs text-text-muted mt-0.5">{{ $records->total() }} {{ Str::plural('record', $records->total()) }}</p>
        </div>
        @can('behavior.create')
        <button @click="openModal()"
                class="flex items-center gap-2 px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span class="hidden sm:inline">Log Incident</span>
            <span class="sm:hidden">Log</span>
        </button>
        @endcan
    </div>

    {{-- Filter bar --}}
    <form method="GET" action="{{ $host }}/behavior"
          class="bg-surface border border-border rounded-2xl shadow-card p-5">
        <div class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[140px]">
                <label class="block text-xs font-medium text-text-muted mb-1.5 uppercase tracking-wide">Class</label>
                <select name="class_id"
                        class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                    <option value="">All Classes</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}" @selected(request('class_id') === $class->id)>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[140px]">
                <label class="block text-xs font-medium text-text-muted mb-1.5 uppercase tracking-wide">Incident Type</label>
                <select name="incident_type"
                        class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                    <option value="">All Types</option>
                    @foreach(['warning','detention','suspension','expulsion','commendation'] as $type)
                    <option value="{{ $type }}" @selected(request('incident_type') === $type)>{{ ucfirst($type) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[120px]">
                <label class="block text-xs font-medium text-text-muted mb-1.5 uppercase tracking-wide">Date From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
            </div>
            <div class="flex-1 min-w-[120px]">
                <label class="block text-xs font-medium text-text-muted mb-1.5 uppercase tracking-wide">Date To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
            </div>
            <div class="flex items-center gap-2">
                <button type="submit"
                        class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors">
                    Filter
                </button>
                @if(request()->hasAny(['class_id','incident_type','date_from','date_to']))
                <a href="{{ $host }}/behavior"
                   class="px-4 py-2 bg-surface border border-border text-sm font-medium text-text-primary rounded-md hover:bg-surface-secondary transition-colors">
                    Clear
                </a>
                @endif
            </div>
        </div>
    </form>

    {{-- Records table --}}
    @if($records->isEmpty())
    <div class="bg-surface border border-border rounded-2xl shadow-card">
        <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
            <div class="w-12 h-12 rounded-xl bg-warning-light flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-text-primary mb-1">No records found</p>
            <p class="text-xs text-text-muted mb-4">
                @if(request()->hasAny(['class_id','incident_type','date_from','date_to']))
                    No records match the current filters.
                @else
                    Discipline records will appear here once logged.
                @endif
            </p>
            @can('behavior.create')
            <button @click="openModal()"
                    class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors">
                Log First Incident
            </button>
            @endcan
        </div>
    </div>
    @else
    <div class="bg-surface border border-border rounded-2xl shadow-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full" style="min-width: 750px">
                <thead>
                    <tr class="border-b border-border bg-surface-secondary">
                        <th class="text-left px-5 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Student</th>
                        <th class="text-left px-5 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Type</th>
                        <th class="text-left px-5 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Date</th>
                        <th class="text-left px-5 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary hidden md:table-cell">Description</th>
                        <th class="text-left px-5 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary hidden lg:table-cell">Reported By</th>
                        <th class="text-left px-5 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Parent</th>
                        <th class="text-right px-5 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($records as $record)
                    @php
                        $typeBadge = match($record->incident_type) {
                            'warning'      => 'bg-warning-light text-warning',
                            'detention'    => 'bg-error-light text-error',
                            'suspension'   => 'bg-error-light text-error',
                            'expulsion'    => 'bg-error text-white',
                            'commendation' => 'bg-success-lightest text-success-foreground',
                            default        => 'bg-surface-secondary text-text-secondary',
                        };
                    @endphp
                    <tr class="hover:bg-surface-secondary transition-colors">
                        <td class="px-5 py-3.5">
                            <a href="{{ $host }}/students/{{ $record->student_id }}"
                               class="text-sm font-medium text-text-primary hover:text-accent transition-colors">
                                {{ $record->student->full_name }}
                            </a>
                            <p class="text-xs text-text-muted mt-0.5">{{ $record->student->schoolClass?->name ?? '&mdash;' }}</p>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $typeBadge }}">
                                {{ ucfirst($record->incident_type) }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-sm text-text-primary whitespace-nowrap">
                            {{ $record->date->format('d M Y') }}
                        </td>
                        <td class="px-5 py-3.5 hidden md:table-cell">
                            <p class="text-sm text-text-primary line-clamp-2 max-w-xs">{{ $record->description }}</p>
                        </td>
                        <td class="px-5 py-3.5 hidden lg:table-cell">
                            <p class="text-sm text-text-primary">{{ $record->reportedBy?->name ?? '&mdash;' }}</p>
                        </td>
                        <td class="px-5 py-3.5">
                            @if($record->parent_notified)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-success-lightest text-success-foreground">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Notified
                            </span>
                            @else
                            <span class="text-xs text-text-muted">&mdash;</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            @can('behavior.delete')
                            <form method="POST" action="{{ $host }}/behavior/{{ $record->id }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        onclick="return confirm('Delete this record? This cannot be undone.')"
                                        class="p-1.5 rounded-md text-text-muted hover:text-error hover:bg-error-light transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if($records->hasPages())
    <div>{{ $records->links() }}</div>
    @endif
    @endif

    {{-- Log Incident Modal --}}
    <div x-show="showModal"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-start justify-center p-4 pt-10 sm:items-center sm:pt-4"
         style="display: none;">

        <div class="absolute inset-0 bg-overlay/40" @click="close()"></div>

        <div class="relative w-full max-w-lg bg-surface rounded-2xl shadow-xl border border-border max-h-[90vh] flex flex-col"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            <div class="flex items-center justify-between px-6 py-4 border-b border-border shrink-0">
                <h3 class="text-base font-semibold text-text-primary">Log Incident</h3>
                <button @click="close()" class="p-1.5 rounded-md text-text-muted hover:bg-surface-secondary transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="overflow-y-auto flex-1">
                <form method="POST" action="{{ $host }}/behavior"
                      class="flex flex-col gap-4 px-6 py-5"
                      @submit="submitting = true">
                    @csrf

                    {{-- Student --}}
                    <div>
                        <label class="block text-sm font-medium text-text-dark mb-1.5">
                            Student <span class="text-error">*</span>
                        </label>
                        <select name="student_id" x-model="form.student_id" required
                                class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                            <option value="">Select student&hellip;</option>
                            @foreach($students as $student)
                            <option value="{{ $student->id }}">{{ $student->full_name }} ({{ $student->admission_no }})</option>
                            @endforeach
                        </select>
                        @error('student_id')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>

                    {{-- Type + Date --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-text-dark mb-1.5">
                                Type <span class="text-error">*</span>
                            </label>
                            <select name="incident_type" x-model="form.incident_type" required
                                    class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                                <option value="">Select&hellip;</option>
                                @foreach(['warning','detention','suspension','expulsion','commendation'] as $type)
                                <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                                @endforeach
                            </select>
                            @error('incident_type')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-dark mb-1.5">
                                Date <span class="text-error">*</span>
                            </label>
                            <input type="date" name="date" x-model="form.date" required
                                   max="{{ date('Y-m-d') }}"
                                   class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                            @error('date')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="block text-sm font-medium text-text-dark mb-1.5">
                            Description <span class="text-error">*</span>
                        </label>
                        <textarea name="description" x-model="form.description" rows="3"
                                  placeholder="What happened?"
                                  maxlength="2000" required
                                  class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors resize-y"></textarea>
                        @error('description')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>

                    {{-- Action Taken --}}
                    <div>
                        <label class="block text-sm font-medium text-text-dark mb-1.5">Action Taken</label>
                        <textarea name="action_taken" x-model="form.action_taken" rows="2"
                                  placeholder="Steps taken to address the incident (optional)"
                                  maxlength="1000"
                                  class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors resize-y"></textarea>
                    </div>

                    {{-- Notify Parent --}}
                    <label class="flex items-center gap-3 cursor-pointer select-none">
                        <input type="hidden" name="parent_notified" value="0">
                        <input type="checkbox" name="parent_notified" value="1"
                               x-model="form.parent_notified"
                               class="w-4 h-4 rounded border-border text-accent focus:ring-accent focus:ring-1">
                        <span class="text-sm text-text-primary">Notify parent by email</span>
                    </label>

                    <div class="flex justify-end gap-3 pt-1">
                        <button type="button" @click="close()"
                                class="px-4 py-2 bg-surface border border-border text-sm font-medium text-text-primary rounded-md hover:bg-surface-secondary transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                :disabled="submitting"
                                :class="submitting ? 'opacity-60 cursor-not-allowed' : 'hover:bg-accent-dark'"
                                class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md transition-colors">
                            <span x-show="!submitting">Log Incident</span>
                            <span x-show="submitting">Saving&hellip;</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function behaviorPage() {
    return {
        showModal: false,
        submitting: false,
        form: {
            student_id: '', incident_type: '', description: '',
            action_taken: '', date: '{{ date('Y-m-d') }}', parent_notified: false,
        },

        init() {
            @if($errors->any())
            this.$nextTick(() => {
                this.form = {
                    student_id:     @json(old('student_id', '')),
                    incident_type:  @json(old('incident_type', '')),
                    description:    @json(old('description', '')),
                    action_taken:   @json(old('action_taken', '')),
                    date:           @json(old('date', date('Y-m-d'))),
                    parent_notified: @json((bool) old('parent_notified', false)),
                };
                this.showModal = true;
            });
            @endif
        },

        openModal(prefilledStudentId = '') {
            this.form = {
                student_id: prefilledStudentId, incident_type: '', description: '',
                action_taken: '', date: '{{ date('Y-m-d') }}', parent_notified: false,
            };
            this.showModal = true;
        },

        close() {
            this.showModal = false;
            this.submitting = false;
        },
    };
}
</script>
@endpush
