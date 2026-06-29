@extends('layouts.tenant')

@section('title', 'Students')
@section('page-title', 'Students')

@section('content')
@php $host = request()->getSchemeAndHttpHost(); @endphp
<div class="flex flex-col gap-6"
     x-data="studentsIndex()">

    {{-- Import row-level errors --}}
    @if(session('student_import_errors'))
    <div class="bg-error-light border border-error rounded-2xl p-5">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-error shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-error mb-1">
                    Import failed &mdash; {{ count(session('student_import_errors')) }} {{ count(session('student_import_errors')) !== 1 ? 'errors' : 'error' }} found. No records were imported.
                </p>
                <p class="text-xs text-text-secondary mb-3">Fix these errors in your file and try again.</p>
                <ul class="space-y-1 max-h-48 overflow-y-auto">
                    @foreach(session('student_import_errors') as $err)
                    <li class="text-xs text-error">{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    {{-- Promotion result banner --}}
    @if(session('promotion_result'))
    @php $pr = session('promotion_result'); @endphp
    <div class="bg-success-lightest border border-success-light text-success-foreground text-sm px-4 py-3 rounded-xl flex items-start gap-3">
        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <div>
            <span class="font-medium">Promotion complete.</span>
            {{ $pr['promoted'] }} promoted &middot; {{ $pr['retained'] }} retained &middot; {{ $pr['graduated'] }} graduated
            @if(! empty($pr['errors']))
            <ul class="mt-1 list-disc list-inside text-xs text-warning space-y-0.5">
                @foreach($pr['errors'] as $err)<li>{{ $err }}</li>@endforeach
            </ul>
            @endif
        </div>
    </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-base font-semibold text-text-primary">All Students</h2>
            <p class="text-xs text-text-muted mt-0.5">{{ $students->total() }} student{{ $students->total() !== 1 ? 's' : '' }} total</p>
        </div>
        <div class="flex items-center gap-2">
            @can('students.edit')
            <a href="{{ $host }}/students/promote"
               class="flex items-center gap-2 px-4 py-2 bg-surface border border-border text-sm font-medium text-text-primary rounded-md hover:bg-surface-secondary transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
                End of Year Promotion
            </a>
            @endcan
            @can('students.create')
            <button @click="showImport = true"
                    class="flex items-center gap-2 px-4 py-2 bg-surface border border-border text-sm font-medium text-text-primary rounded-md hover:bg-surface-secondary transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Import CSV
            </button>
            <a href="{{ $host }}/students/create"
               class="flex items-center gap-2 px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Student
            </a>
            @endcan
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ $host }}/students" class="flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[200px] max-w-xs">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
            </svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or admission no."
                   class="w-full pl-9 pr-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
        </div>

        <select name="class_id" onchange="this.form.submit()"
                class="px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
            <option value="">All Classes</option>
            @foreach($classes as $class)
            <option value="{{ $class->id }}" @selected(request('class_id') === $class->id)>{{ $class->name }}</option>
            @endforeach
        </select>

        @if($anyClassHasSections)
        @php
            $selectedClass = $classes->firstWhere('id', request('class_id'));
        @endphp
        @if($selectedClass && $selectedClass->sections->isNotEmpty())
        <select name="section_id" onchange="this.form.submit()"
                class="px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
            <option value="">All Sections</option>
            @foreach($selectedClass->sections as $section)
            <option value="{{ $section->id }}" @selected(request('section_id') === $section->id)>{{ $section->name }}</option>
            @endforeach
        </select>
        @endif
        @endif

        @if(request()->hasAny(['search', 'class_id', 'section_id']))
        <a href="{{ $host }}/students"
           class="px-3 py-2 text-sm text-text-secondary hover:text-text-primary transition-colors">
            Clear
        </a>
        @endif

        <button type="submit"
                class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors">
            Search
        </button>
    </form>

    {{-- Table Card --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card">
        @if($students->isEmpty())
        <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
            <div class="w-12 h-12 rounded-xl bg-accent-muted flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            @if(request()->hasAny(['search', 'class_id', 'section_id']))
            <p class="text-sm font-medium text-text-primary mb-1">No students found</p>
            <p class="text-xs text-text-muted mb-4">Try adjusting your filters or search term</p>
            <a href="{{ $host }}/students" class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors">
                Clear Filters
            </a>
            @else
            <p class="text-sm font-medium text-text-primary mb-1">No students yet</p>
            <p class="text-xs text-text-muted mb-4">Add your first student or import from CSV</p>
            @can('students.create')
            <a href="{{ $host }}/students/create"
               class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors">
                Add Student
            </a>
            @endcan
            @endif
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-border">
                        <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary whitespace-nowrap">Admission No.</th>
                        <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Full Name</th>
                        <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Class</th>
                        @if($anyClassHasSections)
                        <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Section</th>
                        @endif
                        <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Guardian Contact</th>
                        <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Status</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $student)
                    <tr class="border-b border-border last:border-b-0 hover:bg-surface-secondary transition-colors">
                        <td class="px-6 py-4 text-sm font-medium text-text-muted whitespace-nowrap">{{ $student->admission_no }}</td>
                        <td class="px-6 py-4">
                            <a href="{{ $host }}/students/{{ $student->id }}"
                               class="text-sm font-medium text-text-primary hover:text-accent transition-colors">
                                {{ $student->full_name }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-sm text-text-primary">{{ $student->schoolClass?->name ?? '&mdash;' }}</td>
                        @if($anyClassHasSections)
                        <td class="px-6 py-4 text-sm text-text-primary">{{ $student->section?->name ?? '&mdash;' }}</td>
                        @endif
                        <td class="px-6 py-4 text-sm text-text-secondary">{{ $student->guardian_contact ?? '&mdash;' }}</td>
                        <td class="px-6 py-4">
                            @php
                                $statusClass = match($student->status) {
                                    'active'    => 'bg-success-lightest text-success-foreground',
                                    'inactive'  => 'bg-surface-secondary text-text-secondary',
                                    'graduated' => 'bg-accent-muted text-accent',
                                    default     => 'bg-surface-secondary text-text-secondary',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                                {{ ucfirst($student->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ $host }}/students/{{ $student->id }}"
                                   class="text-xs font-medium text-text-secondary hover:text-text-primary transition-colors px-2 py-1 rounded hover:bg-surface-secondary">
                                    View
                                </a>
                                @can('students.edit')
                                <a href="{{ $host }}/students/{{ $student->id }}/edit"
                                   class="text-xs font-medium text-text-secondary hover:text-text-primary transition-colors px-2 py-1 rounded hover:bg-surface-secondary">
                                    Edit
                                </a>
                                @endcan
                                @can('students.delete')
                                <form method="POST" action="{{ $host }}/students/{{ $student->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            onclick="return confirm('Remove {{ addslashes($student->full_name) }}? This cannot be undone.')"
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

        @if($students->hasPages())
        <div class="px-6 py-4 border-t border-border">
            {{ $students->links() }}
        </div>
        @endif
        @endif
    </div>

    {{-- Import Modal --}}
    @can('students.create')
    <div x-show="showImport"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display: none;">

        <div class="absolute inset-0 bg-overlay/40" @click="showImport = false"></div>

        <div class="relative w-full max-w-md bg-surface rounded-2xl shadow-xl border border-border p-6"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-semibold text-text-primary">Import Students</h3>
                <button @click="showImport = false" class="p-1.5 rounded-md text-text-muted hover:bg-surface-secondary transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Step 1: Download template --}}
            <div class="mb-4 p-4 rounded-xl bg-accent-muted border border-accent-light flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-accent flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-text-primary mb-1">Step 1 &mdash; Download the template</p>
                    <p class="text-xs text-text-secondary mb-2">Fill in your student data. Do not change the column headers.</p>
                    <a href="{{ $host }}/students/import/template"
                       class="inline-flex items-center gap-1.5 text-xs font-semibold text-accent hover:text-accent-dark transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Download skolet-students-import-template.xlsx
                    </a>
                </div>
            </div>

            {{-- Step 2: Upload --}}
            <form method="POST" action="{{ $host }}/students/import" enctype="multipart/form-data"
                  class="flex flex-col gap-4" x-data="{ submitting: false }" @submit="submitting = true">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-text-dark mb-1.5">
                        Step 2 &mdash; Upload completed file <span class="text-error">*</span>
                    </label>
                    <input type="file" name="import_file" accept=".xlsx,.csv" required
                           class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-accent-muted file:text-accent hover:file:bg-accent-light">
                    <p class="mt-1 text-xs text-text-muted">Accepts .xlsx or .csv &mdash; max 5 MB</p>
                    @error('import_file')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>
                <div class="flex items-center justify-end gap-2 pt-1">
                    <button type="button" @click="showImport = false"
                            class="px-4 py-2 bg-surface border border-border text-sm font-medium text-text-primary rounded-md hover:bg-surface-secondary transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            :disabled="submitting"
                            :class="submitting ? 'opacity-60 cursor-not-allowed' : 'hover:bg-accent-dark'"
                            class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md transition-colors">
                        <span x-show="!submitting">Import Students</span>
                        <span x-show="submitting">Importing&hellip;</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endcan

</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('studentsIndex', () => ({
        showImport: {{ ($errors->has('import_file') || session('show_import')) ? 'true' : 'false' }},
    }));
});
</script>
@endpush
@endsection
