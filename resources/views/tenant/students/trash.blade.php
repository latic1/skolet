@extends('layouts.tenant')

@section('title', 'Deleted Students')
@section('page-title', 'Students')

@section('content')
@php $host = request()->getSchemeAndHttpHost(); @endphp
<div class="flex flex-col gap-6 max-w-4xl">

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="bg-success-lightest border border-success-light text-success-foreground text-sm px-4 py-3 rounded-xl flex items-start gap-3">
        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="bg-error-light border border-error/20 text-error text-sm px-4 py-3 rounded-xl">{{ session('error') }}</div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-base font-semibold text-text-primary">Deleted Students</h2>
            <p class="text-sm text-text-muted mt-0.5">Soft-deleted records — permanently purged after 90 days</p>
        </div>
        <a href="{{ $host }}/students"
           class="flex items-center gap-2 px-4 py-2 bg-surface border border-border text-sm font-medium text-text-primary rounded-xl hover:bg-surface-secondary transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Students
        </a>
    </div>

    @if($students->isEmpty())
    <div class="bg-surface border border-border rounded-2xl shadow-card p-12 text-center">
        <div class="w-14 h-14 rounded-2xl bg-surface-secondary flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
        </div>
        <h3 class="text-base font-semibold text-text-primary mb-2">Trash is empty</h3>
        <p class="text-sm text-text-muted">No students have been deleted.</p>
    </div>
    @else
    <div class="bg-surface border border-border rounded-2xl shadow-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border bg-surface-secondary">
                        <th class="text-left text-xs font-medium text-text-muted uppercase tracking-wide px-4 py-3">Student</th>
                        <th class="text-left text-xs font-medium text-text-muted uppercase tracking-wide px-4 py-3 hidden md:table-cell">Admission No</th>
                        <th class="text-left text-xs font-medium text-text-muted uppercase tracking-wide px-4 py-3 hidden sm:table-cell">Class</th>
                        <th class="text-left text-xs font-medium text-text-muted uppercase tracking-wide px-4 py-3">Deleted</th>
                        <th class="text-right text-xs font-medium text-text-muted uppercase tracking-wide px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($students as $student)
                    <tr class="hover:bg-surface-secondary transition-colors">
                        <td class="px-4 py-3 font-medium text-text-primary">{{ $student->full_name }}</td>
                        <td class="px-4 py-3 text-text-muted hidden md:table-cell">{{ $student->admission_no }}</td>
                        <td class="px-4 py-3 text-text-muted hidden sm:table-cell">{{ $student->schoolClass?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-text-muted text-xs">{{ $student->deleted_at?->diffForHumans() }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <form method="POST" action="{{ $host }}/students/{{ $student->id }}/restore">
                                    @csrf
                                    <button type="submit"
                                            class="px-3 py-1.5 text-xs font-medium bg-accent-muted text-accent rounded-lg hover:bg-accent/20 transition-colors">
                                        Restore
                                    </button>
                                </form>
                                <form method="POST" action="{{ $host }}/students/{{ $student->id }}/force-delete"
                                      onsubmit="return confirm('Permanently delete {{ addslashes($student->full_name) }}? This cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="px-3 py-1.5 text-xs font-medium bg-error-light text-error rounded-lg hover:bg-red-100 transition-colors">
                                        Delete Forever
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection
