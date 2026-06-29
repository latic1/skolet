@extends('layouts.tenant')

@section('title', 'Timetable')
@section('page-title', 'Timetable')

@section('content')
@php
    $canEdit = auth()->user()->can('timetable.edit');

    $classesJson = $classes->map(fn ($c) => [
        'id'       => $c->id,
        'name'     => $c->name,
        'sections' => $c->sections->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])->values()->toArray(),
    ])->values()->toJson();

    $subjectsJson = $subjects->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])->values()->toJson();

    $staffJson = $staff->map(fn ($s) => ['id' => $s->id, 'full_name' => $s->full_name])->values()->toJson();

    $entriesJson = json_encode($entriesForView, JSON_HEX_TAG);
@endphp

<div x-data="timetableFilter({{ $classesJson }}, '{{ $classId }}', '{{ $sectionId }}')" class="flex flex-col gap-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-base font-semibold text-text-primary">Timetable</h2>
            <p class="text-xs text-text-muted mt-0.5">Manage weekly class schedules and teacher assignments</p>
        </div>
        <a href="{{ $host }}/timetable/my"
           class="flex items-center gap-2 px-4 py-2 bg-surface border border-border text-text-primary text-sm font-medium rounded-md hover:bg-surface-secondary transition-colors">
            <svg class="w-4 h-4 text-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            My Timetable
        </a>
    </div>

    {{-- Filter bar --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card p-5">
        <form method="GET" action="{{ $host }}/timetable" class="flex flex-wrap items-end gap-4">

            {{-- Class --}}
            <div class="flex flex-col gap-1.5 min-w-45">
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
            <div class="flex flex-col gap-1.5 min-w-37.5" x-show="hasSections" x-cloak>
                <label class="text-xs font-medium text-text-dark">Section</label>
                <select name="section_id" x-model="sectionId"
                        class="px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                    <option value="">All Sections</option>
                    <template x-for="section in currentSections" :key="section.id">
                        <option :value="section.id" x-text="section.name"></option>
                    </template>
                </select>
            </div>

            <button type="submit"
                    class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors">
                Load
            </button>

            @if($classId)
            <a href="{{ $host }}/timetable"
               class="px-4 py-2 bg-surface border border-border text-text-secondary text-sm font-medium rounded-md hover:bg-surface-secondary transition-colors">
                Clear
            </a>
            @endif
        </form>
    </div>

    {{-- Timetable Grid --}}
    @if($classId)

        @php
            $needsSection = $selectedClass && $selectedClass->sections->isNotEmpty();
            $missingSection = $needsSection && ! $sectionId;
        @endphp

        @if($missingSection)
        {{-- Prompt to select a section --}}
        <div class="bg-surface border border-border rounded-2xl shadow-card">
            <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
                <div class="w-12 h-12 rounded-xl bg-accent-muted flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-text-primary mb-1">Select a section to view the timetable</p>
                <p class="text-xs text-text-muted">{{ $selectedClass->name }} has multiple sections. Choose one from the filter above.</p>
            </div>
        </div>

        @else
        {{-- Grid --}}
        <div
            x-data="timetableGrid({{ $entriesJson }}, {{ $subjectsJson }}, {{ $staffJson }}, '{{ $classId }}', '{{ $sectionId }}')"
            class="bg-surface border border-border rounded-2xl shadow-card"
        >
            {{-- Grid header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-border">
                <div>
                    <h3 class="text-sm font-semibold text-text-primary">
                        {{ $selectedClass?->name }}{{ $selectedSection ? ' &mdash; ' . $selectedSection->name : '' }}
                    </h3>
                    <p class="text-xs text-text-muted mt-0.5">
                        @if($canEdit) Click any cell to assign a subject and teacher. @else Read-only view. @endif
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-accent-muted text-accent text-xs font-medium">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Mon &ndash; Fri &middot; 8 Periods
                    </span>
                </div>
            </div>

            {{-- Scrollable grid --}}
            <div class="overflow-x-auto">
                <table class="min-w-full" style="min-width: 900px;">
                    <thead>
                        <tr class="border-b border-border bg-surface-secondary">
                            <th class="text-left px-4 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary w-28 shrink-0">Day</th>
                            @foreach($periods as $period)
                            <th class="text-center px-3 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary min-w-30">
                                P{{ $period }}
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($days as $day)
                        <tr class="border-b border-border last:border-b-0 hover:bg-surface-secondary/50 transition-colors">
                            {{-- Day label --}}
                            <td class="px-4 py-3 w-28 shrink-0">
                                <span class="text-sm font-semibold text-text-primary">{{ substr($day, 0, 3) }}</span>
                                <span class="block text-xs text-text-muted">{{ substr($day, 3) }}</span>
                            </td>

                            {{-- Period cells --}}
                            @foreach($periods as $period)
                            @php $key = $day . '-' . $period; @endphp
                            <td class="px-2 py-2 min-w-30">
                                {{-- Filled cell --}}
                                <template x-if="getEntry('{{ $day }}', {{ $period }})">
                                    <div class="relative group rounded-xl border border-accent-light bg-accent-muted p-2.5 min-h-17 flex flex-col justify-between
                                                {{ $canEdit ? 'cursor-pointer hover:border-accent' : '' }} transition-colors"
                                         @if($canEdit) @click="openModal('{{ $day }}', {{ $period }})" @endif>
                                        <div>
                                            <p class="text-xs font-semibold text-accent leading-snug"
                                               x-text="getEntry('{{ $day }}', {{ $period }}).subject_name"></p>
                                            <p class="text-xs text-text-secondary mt-0.5 leading-snug"
                                               x-text="getEntry('{{ $day }}', {{ $period }}).teacher_name"></p>
                                        </div>
                                        @if($canEdit)
                                        <button type="button"
                                                @click.stop="clearCell('{{ $day }}', {{ $period }})"
                                                class="absolute top-1.5 right-1.5 w-5 h-5 rounded flex items-center justify-center text-text-muted hover:text-error hover:bg-error-light opacity-0 group-hover:opacity-100 transition-all">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                        @endif
                                    </div>
                                </template>

                                {{-- Empty cell --}}
                                <template x-if="!getEntry('{{ $day }}', {{ $period }})">
                                    <div class="rounded-xl border border-dashed border-border min-h-17 flex items-center justify-center transition-colors
                                                {{ $canEdit ? 'cursor-pointer hover:border-accent hover:bg-accent-muted' : '' }}"
                                         @if($canEdit) @click="openModal('{{ $day }}', {{ $period }})" @endif>
                                        @if($canEdit)
                                        <svg class="w-4 h-4 text-text-muted group-hover:text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        @else
                                        <span class="text-xs text-text-muted">&mdash;</span>
                                        @endif
                                    </div>
                                </template>
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Conflict / saved banner (dismissible) --}}
            <div x-show="banner.message" x-cloak
                 :class="banner.type === 'conflict' ? 'bg-warning-light border-warning text-warning' : 'bg-success-lightest border-success-light text-success-foreground'"
                 class="flex items-start gap-3 px-6 py-3 border-t text-sm">
                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          x-bind:d="banner.type === 'conflict'
                              ? 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'
                              : 'M5 13l4 4L19 7'"/>
                </svg>
                <span x-text="banner.message" class="flex-1"></span>
                <button @click="banner.message = ''" class="text-current opacity-60 hover:opacity-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

        </div>

        @endif

    @else
    {{-- Initial empty state --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card">
        <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
            <div class="w-12 h-12 rounded-xl bg-accent-muted flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-text-primary mb-1">Select a class to view its timetable</p>
            <p class="text-xs text-text-muted">
                @if($classes->isEmpty())
                No classes have been set up yet.
                <a href="{{ $host }}/settings/classes" class="text-accent hover:underline">Add classes in Settings.</a>
                @else
                Choose a class from the filter above to load or build its weekly schedule.
                @endif
            </p>
        </div>
    </div>
    @endif

    {{-- Edit Cell Modal --}}
    @if($canEdit && $classId)
    <div x-data x-show="$store.timetableModal.show" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4">
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-overlay/40" @click="$store.timetableModal.close()"></div>

        {{-- Panel --}}
        <div class="relative w-full max-w-md bg-surface rounded-2xl shadow-xl border border-border p-6"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             @click.stop>

            {{-- Header --}}
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="text-base font-semibold text-text-primary"
                        x-text="$store.timetableModal.title"></h3>
                    <p class="text-xs text-text-muted mt-0.5">
                        {{ $selectedClass?->name }}{{ $selectedSection ? ' &mdash; ' . $selectedSection->name : '' }}
                    </p>
                </div>
                <button @click="$store.timetableModal.close()"
                        class="p-1.5 rounded-md text-text-muted hover:bg-surface-secondary transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Conflict warning --}}
            <div x-show="$store.timetableModal.conflict" x-cloak
                 class="mb-4 flex items-start gap-2.5 px-3.5 py-3 rounded-xl bg-warning-light border border-warning text-warning text-xs">
                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span x-text="$store.timetableModal.conflict"></span>
            </div>

            {{-- Error --}}
            <div x-show="$store.timetableModal.error" x-cloak
                 class="mb-4 flex items-start gap-2.5 px-3.5 py-3 rounded-xl bg-error-light border border-error text-error text-xs">
                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <span x-text="$store.timetableModal.error"></span>
            </div>

            {{-- Subject --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-text-dark mb-1.5">Subject</label>
                <select x-model="$store.timetableModal.form.subject_id"
                        class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                    <option value="">Select subject&hellip;</option>
                    @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Teacher --}}
            <div class="mb-6">
                <label class="block text-sm font-medium text-text-dark mb-1.5">Teacher</label>
                <select x-model="$store.timetableModal.form.teacher_id"
                        class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                    <option value="">Select teacher&hellip;</option>
                    @foreach($staff as $member)
                    <option value="{{ $member->id }}">{{ $member->full_name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Footer actions --}}
            <div class="flex items-center justify-between">
                <button type="button"
                        @click="$store.timetableModal.close()"
                        class="px-4 py-2 bg-surface border border-border text-text-primary text-sm font-medium rounded-md hover:bg-surface-secondary transition-colors">
                    Cancel
                </button>
                <button type="button"
                        @click="$store.timetableModal.save()"
                        :disabled="$store.timetableModal.saving"
                        class="px-5 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
                    <span x-show="!$store.timetableModal.saving">Save</span>
                    <span x-show="$store.timetableModal.saving" x-cloak>Saving&hellip;</span>
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
    const _timetableBaseUrl = '{{ $host }}';
    const _timetableClassId = '{{ $classId }}';
    const _timetableSectionId = '{{ $sectionId }}';

    // Alpine store &mdash; shared between grid and modal
    document.addEventListener('alpine:init', () => {
        Alpine.store('timetableModal', {
            show: false,
            title: '',
            day: '',
            period: 0,
            form: { subject_id: '', teacher_id: '' },
            saving: false,
            conflict: '',
            error: '',
            _gridRef: null,   // reference to the grid Alpine component

            open(day, period, entry, gridRef) {
                this.day       = day;
                this.period    = period;
                this.title     = `${day} &middot; Period ${period}`;
                this.conflict  = '';
                this.error     = '';
                this.form.subject_id = entry ? entry.subject_id : '';
                this.form.teacher_id = entry ? entry.teacher_id : '';
                this._gridRef  = gridRef;
                this.show      = true;
            },

            close() {
                this.show     = false;
                this.conflict = '';
                this.error    = '';
            },

            async save() {
                if (!this.form.subject_id || !this.form.teacher_id) {
                    this.error = 'Please select both a subject and a teacher.';
                    return;
                }

                this.saving   = true;
                this.conflict = '';
                this.error    = '';

                try {
                    const res = await fetch(_timetableBaseUrl + '/timetable', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            class_id:   _timetableClassId,
                            section_id: _timetableSectionId || null,
                            subject_id: this.form.subject_id,
                            teacher_id: this.form.teacher_id,
                            day:        this.day,
                            period:     this.period,
                        }),
                    });

                    const data = await res.json();

                    if (data.success) {
                        // Push updated entry back into the grid
                        if (this._gridRef) {
                            this._gridRef.updateEntry(this.day, this.period, data.entry);
                        }

                        if (data.conflict) {
                            // Save succeeded but teacher has a conflict &mdash; stay open and warn
                            this.conflict = data.conflict;
                            if (this._gridRef) {
                                this._gridRef.setBanner('conflict', data.conflict);
                            }
                            this.show = false;
                        } else {
                            this.close();
                            if (this._gridRef) {
                                this._gridRef.setBanner('success', 'Timetable entry saved.');
                            }
                        }
                    } else {
                        this.error = data.error || data.message || 'Failed to save. Please try again.';
                    }
                } catch (e) {
                    this.error = 'Network error. Please try again.';
                } finally {
                    this.saving = false;
                }
            },
        });
    });

    function timetableFilter(classes, classId, sectionId) {
        return {
            classes,
            classId:  classId  || '',
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

    function timetableGrid(entriesData, subjects, staff, classId, sectionId) {
        return {
            entries: Object.fromEntries(
                Object.entries(entriesData).map(([k, v]) => [k, v])
            ),
            subjects,
            staff,
            banner: { type: '', message: '' },
            _bannerTimer: null,

            getEntry(day, period) {
                return this.entries[`${day}-${period}`] || null;
            },

            updateEntry(day, period, entry) {
                this.entries[`${day}-${period}`] = { ...entry };
            },

            setBanner(type, message) {
                clearTimeout(this._bannerTimer);
                this.banner = { type, message };
                this._bannerTimer = setTimeout(() => { this.banner = { type: '', message: '' }; }, 5000);
            },

            openModal(day, period) {
                const entry = this.getEntry(day, period);
                Alpine.store('timetableModal').open(day, period, entry, this);
            },

            async clearCell(day, period) {
                const entry = this.getEntry(day, period);
                if (!entry || !entry.id) return;

                try {
                    await fetch(_timetableBaseUrl + `/timetable/${entry.id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                    });

                    delete this.entries[`${day}-${period}`];
                    this.setBanner('success', 'Entry cleared.');
                } catch (e) {
                    this.setBanner('conflict', 'Failed to clear entry. Please try again.');
                }
            },
        };
    }
</script>
@endpush
