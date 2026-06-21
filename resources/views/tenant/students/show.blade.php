@extends('layouts.tenant')

@section('title', $student->full_name . ' — Student Profile')
@section('page-title', 'Students')

@section('content')
@php $host = request()->getSchemeAndHttpHost(); @endphp
<div class="flex flex-col gap-6 max-w-3xl">

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="bg-success-lightest border border-success-light text-success-foreground text-sm px-4 py-3 rounded-xl flex items-start gap-3">
        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-text-muted">
        <a href="{{ $host }}/students" class="hover:text-text-primary transition-colors">Students</a>
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-text-primary font-medium">{{ $student->full_name }}</span>
    </div>

    {{-- Profile Header Card --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card p-6">
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-accent-muted flex items-center justify-center shrink-0">
                    <span class="text-xl font-semibold text-accent">{{ mb_strtoupper(mb_substr($student->full_name, 0, 1)) }}</span>
                </div>
                <div>
                    <h2 class="text-base font-semibold text-text-primary">{{ $student->full_name }}</h2>
                    <p class="text-sm text-text-muted mt-0.5">{{ $student->admission_no }}</p>
                    <div class="flex items-center gap-2 mt-2">
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
                        @if($student->schoolClass)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-accent-muted text-accent">
                            {{ $student->schoolClass->name }}{{ $student->section ? ' — ' . $student->section->name : '' }}
                        </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                @can('students.edit')
                <a href="{{ $host }}/students/{{ $student->id }}/edit"
                   class="flex items-center gap-2 px-4 py-2 bg-surface border border-border text-sm font-medium text-text-primary rounded-md hover:bg-surface-secondary transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit
                </a>
                @endcan
                @can('students.delete')
                <form method="POST" action="{{ $host }}/students/{{ $student->id }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            onclick="return confirm('Remove {{ addslashes($student->full_name) }}? This cannot be undone.')"
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

    {{-- Personal Details --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card p-6">
        <h3 class="text-base font-semibold text-text-primary mb-5">Personal Details</h3>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
            <div>
                <dt class="text-xs font-medium text-text-muted uppercase tracking-wide mb-1">Date of Birth</dt>
                <dd class="text-sm text-text-primary">{{ $student->date_of_birth?->format('d M Y') ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-text-muted uppercase tracking-wide mb-1">Gender</dt>
                <dd class="text-sm text-text-primary">{{ $student->gender ? ucfirst($student->gender) : '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-text-muted uppercase tracking-wide mb-1">Address</dt>
                <dd class="text-sm text-text-primary">{{ $student->address ?? '—' }}</dd>
            </div>
        </dl>
    </div>

    {{-- Guardian Details --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card p-6">
        <h3 class="text-base font-semibold text-text-primary mb-5">Guardian Details</h3>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
            <div>
                <dt class="text-xs font-medium text-text-muted uppercase tracking-wide mb-1">Guardian Name</dt>
                <dd class="text-sm text-text-primary">{{ $student->guardian_name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-text-muted uppercase tracking-wide mb-1">Contact</dt>
                <dd class="text-sm text-text-primary">{{ $student->guardian_contact ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-text-muted uppercase tracking-wide mb-1">Email</dt>
                <dd class="text-sm text-text-primary">
                    @if($student->guardian_email)
                    <a href="mailto:{{ $student->guardian_email }}" class="text-accent hover:text-accent-dark transition-colors">{{ $student->guardian_email }}</a>
                    @else
                    —
                    @endif
                </dd>
            </div>
        </dl>
    </div>

    {{-- Academic --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card p-6">
        <h3 class="text-base font-semibold text-text-primary mb-5">Academic Details</h3>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
            <div>
                <dt class="text-xs font-medium text-text-muted uppercase tracking-wide mb-1">Class</dt>
                <dd class="text-sm text-text-primary">{{ $student->schoolClass?->name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-text-muted uppercase tracking-wide mb-1">Section</dt>
                <dd class="text-sm text-text-primary">{{ $student->section?->name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-text-muted uppercase tracking-wide mb-1">Admission Number</dt>
                <dd class="text-sm font-medium text-text-primary">{{ $student->admission_no }}</dd>
            </div>
        </dl>
    </div>

    {{-- Login Account --}}
    @can('students.edit')
    <div class="bg-surface border border-border rounded-2xl shadow-card p-6"
         x-data="{ submitting: false }">
        <h3 class="text-base font-semibold text-text-primary mb-5">Login Account</h3>

        @if($student->user)
        {{-- Account exists --}}
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-success-lightest flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-success-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-text-primary">{{ $student->user->email }}</p>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-accent-muted text-accent capitalize mt-1">
                        {{ $student->user->getRoleNames()->first() ?? 'No role' }}
                    </span>
                </div>
            </div>
            <form method="POST" action="{{ $host }}/students/{{ $student->id }}/login"
                  @submit="submitting = true"
                  onsubmit="return confirm('Remove login access for {{ addslashes($student->full_name) }}? They will no longer be able to log in.')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        :disabled="submitting"
                        :class="submitting ? 'opacity-60 cursor-not-allowed' : 'hover:bg-red-100'"
                        class="px-3 py-1.5 bg-error-light text-error text-xs font-medium rounded-md transition-colors">
                    <span x-show="!submitting">Revoke Access</span>
                    <span x-show="submitting">Revoking…</span>
                </button>
            </form>
        </div>
        @else
        {{-- No account --}}
        <div class="mb-5 flex items-start gap-3 p-4 bg-surface-secondary rounded-xl">
            <svg class="w-4 h-4 text-text-muted mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-xs text-text-secondary">No login account yet. Create one so this student or their parent can log in to view fees, report cards, and announcements.</p>
        </div>

        @if($errors->hasAny(['email', 'password', 'password_confirmation', 'role']))
        <div class="mb-4 p-3 bg-error-light border border-error rounded-xl text-xs text-error">
            <ul class="space-y-0.5">
                @foreach($errors->only(['email', 'password', 'password_confirmation', 'role']) as $msgs)
                    @foreach($msgs as $msg)
                    <li>{{ $msg }}</li>
                    @endforeach
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ $host }}/students/{{ $student->id }}/login"
              class="flex flex-col gap-4"
              @submit="submitting = true">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-text-dark mb-1.5">Email Address <span class="text-error">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           placeholder="{{ $student->guardian_email ?? 'student@example.com' }}"
                           class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors @error('email') border-error @enderror">
                </div>
                <div>
                    <label class="block text-sm font-medium text-text-dark mb-1.5">Password <span class="text-error">*</span></label>
                    <input type="password" name="password" required minlength="8"
                           class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors @error('password') border-error @enderror">
                </div>
                <div>
                    <label class="block text-sm font-medium text-text-dark mb-1.5">Confirm Password <span class="text-error">*</span></label>
                    <input type="password" name="password_confirmation" required
                           class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                </div>
                <div>
                    <label class="block text-sm font-medium text-text-dark mb-1.5">Account Role <span class="text-error">*</span></label>
                    <select name="role" required
                            class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors @error('role') border-error @enderror">
                        <option value="student" @selected(old('role', 'student') === 'student')>Student (logs in as themselves)</option>
                        <option value="parent"  @selected(old('role') === 'parent')>Parent (logs in on behalf of student)</option>
                    </select>
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit"
                        :disabled="submitting"
                        :class="submitting ? 'opacity-60 cursor-not-allowed' : 'hover:bg-accent-dark'"
                        class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md transition-colors">
                    <span x-show="!submitting">Create Login Account</span>
                    <span x-show="submitting">Creating…</span>
                </button>
            </div>
        </form>
        @endif
    </div>
    @endcan

    {{-- Attendance Summary (Phase 3 placeholder) --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-base font-semibold text-text-primary">Attendance History</h3>
            <span class="text-xs text-text-muted bg-surface-secondary px-2 py-1 rounded-md">Available in Phase 3</span>
        </div>
        <div class="flex items-center justify-center py-8 text-center">
            <div>
                <div class="w-10 h-10 rounded-xl bg-surface-secondary flex items-center justify-center mx-auto mb-3">
                    <svg class="w-5 h-5 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <p class="text-sm text-text-muted">Attendance records will appear here once daily attendance is set up.</p>
            </div>
        </div>
    </div>

    {{-- Exam Results (Phase 4 placeholder) --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-base font-semibold text-text-primary">Exam Results</h3>
            <span class="text-xs text-text-muted bg-surface-secondary px-2 py-1 rounded-md">Available in Phase 4</span>
        </div>
        <div class="flex items-center justify-center py-8 text-center">
            <div>
                <div class="w-10 h-10 rounded-xl bg-surface-secondary flex items-center justify-center mx-auto mb-3">
                    <svg class="w-5 h-5 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <p class="text-sm text-text-muted">Exam results will appear here once exams are set up.</p>
            </div>
        </div>
    </div>

    {{-- Fee Status (Phase 5 placeholder) --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-base font-semibold text-text-primary">Fee Status</h3>
            <span class="text-xs text-text-muted bg-surface-secondary px-2 py-1 rounded-md">Available in Phase 5</span>
        </div>
        <div class="flex items-center justify-center py-8 text-center">
            <div>
                <div class="w-10 h-10 rounded-xl bg-surface-secondary flex items-center justify-center mx-auto mb-3">
                    <svg class="w-5 h-5 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <p class="text-sm text-text-muted">Fee payments will appear here once fee structures are set up.</p>
            </div>
        </div>
    </div>

</div>
@endsection
