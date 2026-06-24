@extends('layouts.tenant')

@section('title', $staff->full_name . ' — Staff Profile')
@section('page-title', 'Staff')

@section('content')
@php
    $host       = request()->getSchemeAndHttpHost();
    $systemRole = $staff->user?->getRoleNames()->first();
@endphp
<div class="flex flex-col gap-6 max-w-3xl">


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
                <dd class="text-sm text-text-primary">{{ $staff->phone ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-text-muted uppercase tracking-wide mb-1">Status</dt>
                <dd class="text-sm text-text-primary">{{ ucfirst($staff->status) }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-text-muted uppercase tracking-wide mb-1">Role Title</dt>
                <dd class="text-sm text-text-primary">{{ $staff->role_title ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-text-muted uppercase tracking-wide mb-1">System Role</dt>
                <dd class="text-sm text-text-primary capitalize">{{ $systemRole ? str_replace('_', ' ', $systemRole) : '—' }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-xs font-medium text-text-muted uppercase tracking-wide mb-1">Email Address</dt>
                <dd class="text-sm text-text-primary">
                    @if($staff->user?->email)
                    <a href="mailto:{{ $staff->user->email }}" class="text-accent hover:text-accent-dark transition-colors">
                        {{ $staff->user->email }}
                    </a>
                    @else
                    —
                    @endif
                </dd>
            </div>
        </dl>
    </div>

    {{-- Assigned Classes & Subjects (Phase 3 placeholder) --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-base font-semibold text-text-primary">Assigned Classes &amp; Subjects</h3>
            <span class="text-xs text-text-muted bg-surface-secondary px-2 py-1 rounded-md">Available in Phase 3</span>
        </div>
        <div class="flex items-center justify-center py-8 text-center">
            <div>
                <div class="w-10 h-10 rounded-xl bg-surface-secondary flex items-center justify-center mx-auto mb-3">
                    <svg class="w-5 h-5 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <p class="text-sm text-text-muted">Class and subject assignments will appear here once the timetable is set up.</p>
            </div>
        </div>
    </div>

    {{-- Attendance Summary (Phase 3 placeholder) --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-base font-semibold text-text-primary">Attendance Record</h3>
            <span class="text-xs text-text-muted bg-surface-secondary px-2 py-1 rounded-md">Available in Phase 3</span>
        </div>
        <div class="flex items-center justify-center py-8 text-center">
            <div>
                <div class="w-10 h-10 rounded-xl bg-surface-secondary flex items-center justify-center mx-auto mb-3">
                    <svg class="w-5 h-5 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <p class="text-sm text-text-muted">Staff attendance records will appear here once attendance tracking is set up.</p>
            </div>
        </div>
    </div>

</div>
@endsection
