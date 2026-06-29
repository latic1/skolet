@extends('layouts.tenant')

@section('title', $staff->full_name . ' &mdash; Staff Profile')
@section('page-title', 'Staff')

@section('content')
@php
    $host       = request()->getSchemeAndHttpHost();
    $systemRole = $staff->user?->getRoleNames()->first();

    // Build sections map keyed by class_id for Alpine
    $sectionsMap = $classes->mapWithKeys(fn ($c) => [
        $c->id => $c->sections->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])->values(),
    ]);
@endphp

{{-- Flash messages --}}
@if(session('success'))
<div class="mb-4 px-4 py-3 bg-success-lightest border border-success-foreground/20 text-success-foreground text-sm rounded-xl">
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-4 px-4 py-3 bg-error-light border border-error/20 text-error text-sm rounded-xl">
    {{ session('error') }}
</div>
@endif

<div class="flex flex-col gap-6 max-w-3xl"
     x-data="{
        sections: {{ $sectionsMap->toJson() }},
        selectedClass: '',
        get currentSections() {
            return this.selectedClass && this.sections[this.selectedClass] ? this.sections[this.selectedClass] : [];
        }
     }">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-text-muted">
        <a href="{{ $host }}/staff" class="hover:text-text-primary transition-colors">Staff</a>
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-text-primary font-medium">{{ $staff->full_name }}</span>
    </div>

    {{-- Profile Header Card --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card p-6">
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-accent-muted flex items-center justify-center shrink-0">
                    <span class="text-xl font-semibold text-accent">{{ mb_strtoupper(mb_substr($staff->full_name, 0, 1)) }}</span>
                </div>
                <div>
                    <h2 class="text-base font-semibold text-text-primary">{{ $staff->full_name }}</h2>
                    <p class="text-sm text-text-muted mt-0.5">{{ $staff->role_title ?? 'Staff Member' }}</p>
                    <div class="flex items-center gap-2 mt-2">
                        @php
                            $statusClass = $staff->status === 'active'
                                ? 'bg-success-lightest text-success-foreground'
                                : 'bg-surface-secondary text-text-secondary';
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                            {{ ucfirst($staff->status) }}
                        </span>
                        @if($systemRole)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-accent-muted text-accent capitalize">
                            {{ str_replace('_', ' ', $systemRole) }}
                        </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                @can('staff.edit')
                @if($staff->phone)
                <form method="POST" action="{{ $host }}/staff/{{ $staff->id }}/resend-credentials">
                    @csrf
                    <button type="submit"
                            onclick="return confirm('This will reset {{ addslashes($staff->full_name) }}\'s password and send new credentials via SMS to {{ $staff->phone }}. Continue?')"
                            class="flex items-center gap-2 px-4 py-2 bg-surface border border-border text-sm font-medium text-text-primary rounded-md hover:bg-surface-secondary transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        Resend via SMS
                    </button>
                </form>
                @endif
                <a href="{{ $host }}/staff/{{ $staff->id }}/edit"
                   class="flex items-center gap-2 px-4 py-2 bg-surface border border-border text-sm font-medium text-text-primary rounded-md hover:bg-surface-secondary transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit
                </a>
                @endcan
                @can('staff.delete')
                <form method="POST" action="{{ $host }}/staff/{{ $staff->id }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            onclick="return confirm('Remove {{ addslashes($staff->full_name) }}? Their login account will also be deleted. This cannot be undone.')"
                            class="flex items-center gap-2 px-4 py-2 bg-error-light text-error text-sm font-medium rounded-md hover:bg-red-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Delete
                    </button>
                </form>
                @endcan
            </div>
        </div>
    </div>

    {{-- Staff Details --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card p-6">
        <h3 class="text-base font-semibold text-text-primary mb-5">Staff Details</h3>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
            <div>
                <dt class="text-xs font-medium text-text-muted uppercase tracking-wide mb-1">Phone Number</dt>
                <dd class="text-sm text-text-primary">{{ $staff->phone ?? '&mdash;' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-text-muted uppercase tracking-wide mb-1">Status</dt>
                <dd class="text-sm text-text-primary">{{ ucfirst($staff->status) }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-text-muted uppercase tracking-wide mb-1">Role Title</dt>
                <dd class="text-sm text-text-primary">{{ $staff->role_title ?? '&mdash;' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-text-muted uppercase tracking-wide mb-1">System Role</dt>
                <dd class="text-sm text-text-primary capitalize">{{ $systemRole ? str_replace('_', ' ', $systemRole) : '&mdash;' }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-xs font-medium text-text-muted uppercase tracking-wide mb-1">Email Address</dt>
                <dd class="text-sm text-text-primary">
                    @if($staff->user?->email)
                    <a href="mailto:{{ $staff->user->email }}" class="text-accent hover:text-accent-dark transition-colors">
                        {{ $staff->user->email }}
                    </a>
                    @else
                    &mdash;
                    @endif
                </dd>
            </div>
        </dl>
    </div>

    {{-- Assigned Classes & Subjects --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card p-6">
        <h3 class="text-base font-semibold text-text-primary mb-5">Assigned Classes &amp; Subjects</h3>

        {{-- Current assignments --}}
        @if($staff->assignments->isEmpty())
        <div class="flex items-center justify-center py-8 text-center">
            <div>
                <div class="w-10 h-10 rounded-xl bg-surface-secondary flex items-center justify-center mx-auto mb-3">
                    <svg class="w-5 h-5 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <p class="text-sm text-text-muted">No subject assignments yet.</p>
            </div>
        </div>
        @else
        <div class="overflow-x-auto mb-5">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border">
                        <th class="text-left text-xs font-medium text-text-muted uppercase tracking-wide pb-2 pr-4">Subject</th>
                        <th class="text-left text-xs font-medium text-text-muted uppercase tracking-wide pb-2 pr-4">Class</th>
                        <th class="text-left text-xs font-medium text-text-muted uppercase tracking-wide pb-2 pr-4">Section</th>
                        @can('staff.edit')
                        <th class="pb-2"></th>
                        @endcan
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($staff->assignments->sortBy([['schoolClass.order', 'asc'], ['subject.name', 'asc']]) as $assignment)
                    <tr>
                        <td class="py-2.5 pr-4 font-medium text-text-primary">{{ $assignment->subject?->name ?? '&mdash;' }}</td>
                        <td class="py-2.5 pr-4 text-text-secondary">{{ $assignment->schoolClass?->name ?? '&mdash;' }}</td>
                        <td class="py-2.5 pr-4 text-text-secondary">{{ $assignment->section?->name ?? '&mdash;' }}</td>
                        @can('staff.edit')
                        <td class="py-2.5 text-right">
                            <form method="POST" action="{{ $host }}/staff/assignments/{{ $assignment->id }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        onclick="return confirm('Remove this assignment?')"
                                        class="text-xs text-error hover:text-red-700 transition-colors">
                                    Remove
                                </button>
                            </form>
                        </td>
                        @endcan
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Add assignment form (admins only) --}}
        @can('staff.edit')
        <div class="border-t border-border pt-5">
            <p class="text-xs font-medium text-text-muted uppercase tracking-wide mb-3">Add Assignment</p>
            <form method="POST" action="{{ $host }}/staff/{{ $staff->id }}/assignments"
                  class="flex flex-col sm:flex-row gap-3 items-start sm:items-end">
                @csrf

                {{-- Subject --}}
                <div class="flex-1 min-w-0">
                    <label class="block text-xs text-text-muted mb-1">Subject <span class="text-error">*</span></label>
                    <select name="subject_id" required
                            class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                        <option value="">Select subject</option>
                        @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                        @endforeach
                    </select>
                    @error('subject_id')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                {{-- Class --}}
                <div class="flex-1 min-w-0">
                    <label class="block text-xs text-text-muted mb-1">Class <span class="text-error">*</span></label>
                    <select name="class_id" required x-model="selectedClass"
                            class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                        <option value="">Select class</option>
                        @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                    @error('class_id')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                {{-- Section (conditional) --}}
                <div class="flex-1 min-w-0" x-show="currentSections.length > 0" x-cloak>
                    <label class="block text-xs text-text-muted mb-1">Section</label>
                    <select name="section_id"
                            class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                        <option value="">All sections</option>
                        <template x-for="sec in currentSections" :key="sec.id">
                            <option :value="sec.id" x-text="sec.name"></option>
                        </template>
                    </select>
                </div>

                <button type="submit"
                        class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors shrink-0">
                    Add
                </button>
            </form>
        </div>
        @endcan
    </div>

</div>
@endsection
