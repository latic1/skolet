@extends('layouts.tenant')

@section('title', 'Exams')
@section('page-title', 'Exams')

@section('content')
@php
    $host      = request()->getSchemeAndHttpHost();
    $canCreate = auth()->user()->can('exams.create');
    $canEdit   = auth()->user()->can('exams.edit');
    $canDelete = auth()->user()->can('exams.delete');

    $statusLabels = [
        'upcoming'  => ['label' => 'Upcoming',  'bg' => 'bg-accent-muted',       'text' => 'text-accent'],
        'ongoing'   => ['label' => 'Ongoing',   'bg' => 'bg-info-light',         'text' => 'text-info-foreground'],
        'completed' => ['label' => 'Completed', 'bg' => 'bg-surface-secondary',  'text' => 'text-text-secondary'],
        'published' => ['label' => 'Published', 'bg' => 'bg-success-lightest',   'text' => 'text-success-foreground'],
    ];

    $termsJson = $terms->map(fn ($t) => [
        'id'           => $t->id,
        'name'         => $t->name,
        'academic_year' => $t->academicYear ? ['id' => $t->academicYear->id, 'name' => $t->academicYear->name] : null,
    ])->values()->toJson();
@endphp

<div x-data="examsPage({{ $termsJson }})" class="flex flex-col gap-6">

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
    @if($errors->any())
    <div class="flex items-start gap-3 bg-error-light border border-error text-error text-sm px-4 py-3 rounded-xl">
        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Page header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-base font-semibold text-text-primary">Exams</h2>
            <p class="text-xs text-text-muted mt-0.5">{{ $exams->count() }} exam{{ $exams->count() !== 1 ? 's' : '' }}</p>
        </div>
        <div class="flex items-center gap-3">
            @if(auth()->user()->can('exams.view'))
            <a href="{{ $host }}/exams/report-card"
               class="flex items-center gap-2 px-4 py-2 bg-surface border border-border text-text-primary text-sm font-medium rounded-md hover:bg-surface-secondary transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Report Cards
            </a>
            <a href="{{ $host }}/exams/marks"
               class="flex items-center gap-2 px-4 py-2 bg-surface border border-border text-text-primary text-sm font-medium rounded-md hover:bg-surface-secondary transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Enter Marks
            </a>
            @endif
            @if($canCreate)
            <button type="button" @click="openAdd()"
                    class="flex items-center gap-2 px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Exam
            </button>
            @endif
        </div>
    </div>

    {{-- Exams table --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card">

        @if($exams->isEmpty())
        <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
            <div class="w-12 h-12 rounded-xl bg-accent-muted flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-text-primary mb-1">No exams yet</p>
            <p class="text-xs text-text-muted mb-4">Create your first exam to start tracking results.</p>
            @if($canCreate)
            <button type="button" @click="openAdd()"
                    class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors">
                Add Exam
            </button>
            @endif
        </div>
        @else

        {{-- Table header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-border">
            <h3 class="text-sm font-semibold text-text-primary">All Exams</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-border bg-surface-secondary">
                        <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Name</th>
                        <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Term</th>
                        <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary hidden md:table-cell">Academic Year</th>
                        <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary hidden lg:table-cell">Date Range</th>
                        <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Status</th>
                        <th class="text-right px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($exams as $exam)
                    @php
                        $now = now();
                        if ($exam->is_published) {
                            $status = 'published';
                        } elseif ($exam->end_date && $exam->end_date->isPast()) {
                            $status = 'completed';
                        } elseif ($exam->start_date && $exam->start_date->isPast()) {
                            $status = 'ongoing';
                        } else {
                            $status = 'upcoming';
                        }
                        $badge = $statusLabels[$status];
                    @endphp
                    <tr class="border-b border-border last:border-b-0 hover:bg-surface-secondary transition-colors">

                        {{-- Name --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-accent-muted flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-text-primary">{{ $exam->name }}</span>
                                @if($exam->exam_role === 'ca')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-accent-muted text-accent">CA</span>
                                @elseif($exam->exam_role === 'end_of_term')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-success-lightest text-success-foreground">End of Term</span>
                                @endif
                            </div>
                        </td>

                        {{-- Term --}}
                        <td class="px-6 py-4 text-sm text-text-secondary">{{ $exam->term?->name ?? '—' }}</td>

                        {{-- Academic Year --}}
                        <td class="px-6 py-4 text-sm text-text-secondary hidden md:table-cell">
                            {{ $exam->term?->academicYear?->name ?? '—' }}
                        </td>

                        {{-- Date Range --}}
                        <td class="px-6 py-4 text-sm text-text-secondary hidden lg:table-cell">
                            @if($exam->start_date && $exam->end_date)
                                {{ $exam->start_date->format('d M Y') }} &ndash; {{ $exam->end_date->format('d M Y') }}
                            @elseif($exam->start_date)
                                From {{ $exam->start_date->format('d M Y') }}
                            @else
                                &mdash;
                            @endif
                        </td>

                        {{-- Status --}}
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $badge['bg'] }} {{ $badge['text'] }}">
                                {{ $badge['label'] }}
                            </span>
                        </td>

                        {{-- Actions --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2 flex-wrap">
                                @if(auth()->user()->can('exams.view'))
                                <a href="{{ $host }}/exams/marks?exam_id={{ $exam->id }}"
                                   class="text-xs font-medium text-accent hover:text-accent-dark transition-colors px-2 py-1 rounded hover:bg-accent-muted">
                                    Marks
                                </a>
                                @if(in_array($status, ['completed', 'published']))
                                <a href="{{ $host }}/exams/report-card?exam_id={{ $exam->id }}"
                                   class="text-xs font-medium text-success-foreground hover:text-success-dark transition-colors px-2 py-1 rounded hover:bg-success-lightest">
                                    Report Cards
                                </a>
                                @endif
                                @endif
                                @if($canEdit && !$exam->is_published)
                                <form method="POST" action="{{ $host }}/exams/{{ $exam->id }}/publish"
                                      onsubmit="return confirm('Publish \'{{ addslashes($exam->name) }}\'? Students and parents will be able to view their results.')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="text-xs font-medium text-info-foreground hover:text-info-dark transition-colors px-2 py-1 rounded hover:bg-info-light">
                                        Publish
                                    </button>
                                </form>
                                @endif
                                @if($canEdit)
                                <button type="button"
                                        @click="openEdit({
                                            id: '{{ $exam->id }}',
                                            name: {{ json_encode($exam->name) }},
                                            term_id: '{{ $exam->term_id ?? '' }}',
                                            start_date: '{{ $exam->start_date?->format('Y-m-d') ?? '' }}',
                                            end_date: '{{ $exam->end_date?->format('Y-m-d') ?? '' }}',
                                            exam_role: '{{ $exam->exam_role }}'
                                        })"
                                        class="text-xs font-medium text-text-secondary hover:text-text-primary transition-colors px-2 py-1 rounded hover:bg-surface-secondary">
                                    Edit
                                </button>
                                @endif
                                @if($canDelete)
                                <form method="POST" action="{{ $host }}/exams/{{ $exam->id }}"
                                      onsubmit="return confirm('Delete \'{{ addslashes($exam->name) }}\'? All marks entered for this exam will also be deleted.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-xs font-medium text-error hover:text-red-700 transition-colors px-2 py-1 rounded hover:bg-error-light">
                                        Delete
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- Add / Edit Modal --}}
    <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="display:none">
        <div class="absolute inset-0 bg-overlay/40" @click="close()"></div>

        <div class="relative w-full max-w-lg bg-surface rounded-2xl shadow-xl border border-border p-6"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            {{-- Modal header --}}
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-semibold text-text-primary" x-text="mode === 'add' ? 'Add Exam' : 'Edit Exam'"></h3>
                <button type="button" @click="close()"
                        class="p-1.5 rounded-md text-text-muted hover:bg-surface-secondary transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Add form --}}
            <form x-show="mode === 'add'" method="POST" action="{{ $host }}/exams" class="flex flex-col gap-4"
                  @submit="submitting = true">
                @csrf
                @include('tenant.exams._form')
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="close()"
                            class="px-4 py-2 bg-surface border border-border text-text-primary text-sm font-medium rounded-md hover:bg-surface-secondary transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            :disabled="submitting"
                            :class="submitting ? 'opacity-60 cursor-not-allowed' : 'hover:bg-accent-dark'"
                            class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md transition-colors">
                        <span x-show="!submitting">Create Exam</span>
                        <span x-show="submitting">Saving&hellip;</span>
                    </button>
                </div>
            </form>

            {{-- Edit form --}}
            <form x-show="mode === 'edit'" method="POST" :action="`{{ $host }}/exams/${form.id}`" class="flex flex-col gap-4"
                  @submit="submitting = true">
                @csrf
                @method('PUT')
                @include('tenant.exams._form')
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="close()"
                            class="px-4 py-2 bg-surface border border-border text-text-primary text-sm font-medium rounded-md hover:bg-surface-secondary transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            :disabled="submitting"
                            :class="submitting ? 'opacity-60 cursor-not-allowed' : 'hover:bg-accent-dark'"
                            class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md transition-colors">
                        <span x-show="!submitting">Save Changes</span>
                        <span x-show="submitting">Saving&hellip;</span>
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function examsPage(terms) {
        return {
            terms,
            showModal: false,
            submitting: false,
            mode: 'add',
            form: {
                id: '',
                name: '',
                term_id: '',
                start_date: '',
                end_date: '',
                exam_role: 'none',
            },

            openAdd() {
                this.mode = 'add';
                this.form = { id: '', name: '', term_id: '', start_date: '', end_date: '', exam_role: 'none' };
                this.showModal = true;
            },

            openEdit(exam) {
                this.mode = 'edit';
                this.form = { ...exam };
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
