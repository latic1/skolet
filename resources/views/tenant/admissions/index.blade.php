@extends('layouts.tenant')

@section('title', 'Admissions')
@section('page-title', 'Admissions')

@section('content')
@php $host = request()->getSchemeAndHttpHost(); @endphp
<div class="flex flex-col gap-6">

    {{-- Page header --}}
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-text-primary">Admissions</h1>
            <p class="text-xs text-text-muted mt-0.5">Review and manage online admission applications</p>
        </div>
        @can('settings.manage')
        <a href="{{ $host }}/settings/profile"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-surface border border-border text-text-secondary rounded-md hover:bg-surface-secondary transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Admissions Settings
        </a>
        @endcan
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="bg-success-lightest border border-success-light text-success-foreground text-sm px-4 py-3 rounded-xl flex items-start gap-3">
        <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="bg-error-light border border-error text-error text-sm px-4 py-3 rounded-xl flex items-start gap-3">
        <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- Filters --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card p-5">
        <form method="GET" action="{{ $host }}/admissions" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-40">
                <label class="block text-xs font-medium text-text-secondary mb-1.5 uppercase tracking-wide">Status</label>
                <select name="status"
                        class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent">
                    <option value="">All statuses</option>
                    <option value="pending"  @selected(request('status') === 'pending')>Pending</option>
                    <option value="accepted" @selected(request('status') === 'accepted')>Accepted</option>
                    <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                </select>
            </div>
            <div class="flex-1 min-w-40">
                <label class="block text-xs font-medium text-text-secondary mb-1.5 uppercase tracking-wide">Class</label>
                <select name="class"
                        class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent">
                    <option value="">All classes</option>
                    @foreach($classes as $cls)
                    <option value="{{ $cls }}" @selected(request('class') === $cls)>{{ $cls }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                    class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors shrink-0">
                Filter
            </button>
            @if(request()->filled('status') || request()->filled('class'))
            <a href="{{ $host }}/admissions"
               class="px-4 py-2 bg-surface border border-border text-sm font-medium text-text-secondary rounded-md hover:bg-surface-secondary transition-colors shrink-0">
                Clear
            </a>
            @endif
        </form>
    </div>

    {{-- Applications table --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card overflow-hidden"
         x-data="{ reviewId: null, reviewName: '', rejectId: null, rejectName: '' }">

        @if($applications->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="w-full" style="min-width:680px">
                <thead>
                    <tr class="border-b border-border bg-surface-secondary">
                        <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Applicant</th>
                        <th class="text-left px-4 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Class</th>
                        <th class="text-left px-4 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary hidden md:table-cell">Guardian</th>
                        <th class="text-center px-4 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Date</th>
                        <th class="text-center px-4 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Status</th>
                        <th class="text-right px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($applications as $app)
                    @php
                        $statusColor = match($app->status) {
                            'accepted' => 'bg-success-lightest text-success-foreground',
                            'rejected' => 'bg-error-light text-error',
                            default    => 'bg-warning-light text-warning',
                        };
                    @endphp
                    <tr class="border-b border-border last:border-b-0 hover:bg-surface-secondary transition-colors">
                        <td class="px-6 py-4">
                            <p class="text-sm font-medium text-text-primary">{{ $app->applicant_name }}</p>
                            @if($app->date_of_birth)
                            <p class="text-xs text-text-muted">DOB: {{ $app->date_of_birth->format('d M Y') }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-sm text-text-secondary">{{ $app->class_applying_for }}</td>
                        <td class="px-4 py-4 hidden md:table-cell">
                            <p class="text-sm text-text-primary">{{ $app->guardian_name }}</p>
                            <p class="text-xs text-text-muted">{{ $app->guardian_contact }}</p>
                        </td>
                        <td class="px-4 py-4 text-xs text-text-muted text-center whitespace-nowrap">
                            {{ $app->created_at->format('d M Y') }}
                        </td>
                        <td class="px-4 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                {{ ucfirst($app->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if($app->isPending())
                            @can('admissions.manage')
                            <div class="flex items-center justify-end gap-2">
                                <button type="button"
                                        @click="reviewId = '{{ $app->id }}'; reviewName = '{{ addslashes($app->applicant_name) }}'"
                                        class="text-xs font-medium px-3 py-1.5 bg-surface border border-border text-text-secondary rounded-md hover:bg-surface-secondary transition-colors">
                                    Review
                                </button>
                            </div>
                            @endcan
                            @else
                            <span class="text-xs text-text-muted">
                                {{ $app->reviewed_at?->format('d M Y') ?? '—' }}
                            </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($applications->hasPages())
        <div class="px-6 py-4 border-t border-border">
            {{ $applications->links() }}
        </div>
        @endif

        @else
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <div class="w-12 h-12 rounded-xl bg-surface-secondary flex items-center justify-center mb-3">
                <svg class="w-6 h-6 text-text-muted" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-text-primary mb-1">No applications found</p>
            <p class="text-xs text-text-muted">
                @if(request()->filled('status') || request()->filled('class'))
                    Try adjusting your filters.
                @else
                    Applications submitted via the public form will appear here.
                @endif
            </p>
        </div>
        @endif

        {{-- Review slide-over --}}
        <div x-show="reviewId !== null" x-cloak
             class="fixed inset-0 z-50 flex"
             @keydown.escape.window="reviewId = null; rejectId = null">

            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-black/40 transition-opacity"
                 @click="reviewId = null; rejectId = null"></div>

            {{-- Panel --}}
            <div class="relative ml-auto w-full max-w-md bg-surface shadow-xl flex flex-col h-full overflow-y-auto"
                 x-show="reviewId !== null"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full">

                @foreach($applications as $app)
                @if($app->isPending())
                <div x-show="reviewId === '{{ $app->id }}'" class="flex flex-col h-full">

                    {{-- Header --}}
                    <div class="flex items-start justify-between gap-4 px-6 py-5 border-b border-border">
                        <div>
                            <h3 class="text-base font-semibold text-text-primary">Review Application</h3>
                            <p class="text-xs text-text-muted mt-0.5">{{ $app->applicant_name }}</p>
                        </div>
                        <button @click="reviewId = null" class="text-text-muted hover:text-text-primary transition-colors mt-0.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Details --}}
                    <div class="flex-1 px-6 py-5 flex flex-col gap-5 overflow-y-auto">

                        {{-- Student details --}}
                        <div>
                            <h4 class="text-xs font-semibold text-text-secondary uppercase tracking-wide mb-3">Student</h4>
                            <dl class="grid grid-cols-2 gap-x-4 gap-y-3">
                                <div>
                                    <dt class="text-xs text-text-muted">Full Name</dt>
                                    <dd class="text-sm font-medium text-text-primary mt-0.5">{{ $app->applicant_name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-text-muted">Class Applying For</dt>
                                    <dd class="text-sm font-medium text-text-primary mt-0.5">{{ $app->class_applying_for }}</dd>
                                </div>
                                @if($app->date_of_birth)
                                <div>
                                    <dt class="text-xs text-text-muted">Date of Birth</dt>
                                    <dd class="text-sm text-text-primary mt-0.5">{{ $app->date_of_birth->format('d M Y') }}</dd>
                                </div>
                                @endif
                                @if($app->gender)
                                <div>
                                    <dt class="text-xs text-text-muted">Gender</dt>
                                    <dd class="text-sm text-text-primary mt-0.5 capitalize">{{ $app->gender }}</dd>
                                </div>
                                @endif
                                @if($app->previous_school)
                                <div class="col-span-2">
                                    <dt class="text-xs text-text-muted">Previous School</dt>
                                    <dd class="text-sm text-text-primary mt-0.5">{{ $app->previous_school }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>

                        {{-- Guardian details --}}
                        <div class="border-t border-border pt-5">
                            <h4 class="text-xs font-semibold text-text-secondary uppercase tracking-wide mb-3">Guardian</h4>
                            <dl class="grid grid-cols-2 gap-x-4 gap-y-3">
                                <div>
                                    <dt class="text-xs text-text-muted">Name</dt>
                                    <dd class="text-sm font-medium text-text-primary mt-0.5">{{ $app->guardian_name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-text-muted">Contact</dt>
                                    <dd class="text-sm text-text-primary mt-0.5">{{ $app->guardian_contact }}</dd>
                                </div>
                                @if($app->guardian_email)
                                <div class="col-span-2">
                                    <dt class="text-xs text-text-muted">Email</dt>
                                    <dd class="text-sm text-text-primary mt-0.5">{{ $app->guardian_email }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>

                        <div class="border-t border-border pt-5">
                            <p class="text-xs text-text-muted">Submitted {{ $app->created_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>

                    {{-- Actions --}}
                    @can('admissions.manage')
                    <div class="border-t border-border px-6 py-5 flex flex-col gap-3">
                        {{-- Accept --}}
                        <form method="POST" action="{{ $host }}/admissions/{{ $app->id }}/accept">
                            @csrf
                            <button type="submit"
                                    class="w-full px-4 py-2.5 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors">
                                Accept — Create Student Record
                            </button>
                        </form>

                        {{-- Reject toggle --}}
                        <div x-data="{ showReject: false }">
                            <button type="button"
                                    @click="showReject = !showReject"
                                    class="w-full px-4 py-2.5 bg-surface border border-error text-error text-sm font-medium rounded-md hover:bg-error-light transition-colors">
                                Reject Application
                            </button>

                            <div x-show="showReject" x-cloak class="mt-3">
                                <form method="POST" action="{{ $host }}/admissions/{{ $app->id }}/reject"
                                      class="flex flex-col gap-3">
                                    @csrf
                                    <div>
                                        <label class="block text-xs font-medium text-text-secondary mb-1.5">Rejection Reason <span class="text-error">*</span></label>
                                        <textarea name="rejection_reason" required rows="3"
                                                  placeholder="Explain why this application is being rejected…"
                                                  class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-error focus:border-error resize-none transition-colors"></textarea>
                                        @error('rejection_reason')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                                    </div>
                                    <button type="submit"
                                            class="w-full px-4 py-2.5 bg-error text-white text-sm font-medium rounded-md hover:opacity-90 transition-opacity">
                                        Confirm Rejection
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endcan

                </div>
                @endif
                @endforeach

            </div>
        </div>

    </div>

</div>
@endsection
