@extends('layouts.tenant')

@section('title', 'Settings — Notifications')
@section('page-title', 'Settings')

@section('content')
@php $host = request()->getSchemeAndHttpHost(); @endphp

<div class="flex flex-col gap-6">

    {{-- Settings Sub-Nav --}}
    <div class="flex items-center gap-1 border-b border-border pb-0 overflow-x-auto">
        <a href="{{ $host }}/settings/academic-year"
           class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors whitespace-nowrap border-transparent text-text-secondary hover:text-text-primary">
            Academic Calendar
        </a>
        <a href="{{ $host }}/settings/roles"
           class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors whitespace-nowrap border-transparent text-text-secondary hover:text-text-primary">
            Roles &amp; Permissions
        </a>
        <a href="{{ $host }}/settings/profile"
           class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors whitespace-nowrap border-transparent text-text-secondary hover:text-text-primary">
            School Profile
        </a>
        <a href="{{ $host }}/settings/domain"
           class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors whitespace-nowrap border-transparent text-text-secondary hover:text-text-primary">
            Custom Domain
        </a>
        <a href="{{ $host }}/settings/notifications"
           class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors whitespace-nowrap border-accent text-accent">
            Notifications
        </a>
        <a href="{{ $host }}/settings/audit-log"
           class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors whitespace-nowrap border-transparent text-text-secondary hover:text-text-primary">
            Audit Log
        </a>
    </div>

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
    <div class="bg-error-light border border-error text-error text-sm px-4 py-3 rounded-xl">{{ session('error') }}</div>
    @endif

    {{-- Page header --}}
    <div>
        <h2 class="text-base font-semibold text-text-primary">Email Notifications</h2>
        <p class="text-sm text-text-muted mt-0.5">Control which email notifications are sent automatically to guardians and students.</p>
    </div>

    {{-- Toggle list --}}
    @php
        $eventMeta = [
            'absent_alert'           => ['icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z', 'desc' => 'Sent to guardian when a student is marked absent.', 'to' => 'Guardian email'],
            'fee_overdue_reminder'   => ['icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'desc' => 'Sent weekly to guardians with outstanding overdue fees.', 'to' => 'Guardian email'],
            'exam_results_published' => ['icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', 'desc' => 'Sent to students when an exam is published.', 'to' => 'Student login email'],
            'payment_confirmation'   => ['icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'desc' => 'Sent to guardian after a successful fee payment.', 'to' => 'Guardian email'],
            'welcome_email'          => ['icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'desc' => 'Sent to new staff members with their login credentials.', 'to' => 'Staff email'],
        ];
    @endphp

    <form method="POST" action="{{ $host }}/settings/notifications" x-data="{ submitting: false }" @submit="submitting = true">
        @csrf

        <div class="bg-surface border border-border rounded-2xl shadow-card divide-y divide-border">
            @foreach($events as $key => $label)
            @php $meta = $eventMeta[$key]; $enabled = $settings[$key]['email'] ?? true; @endphp
            <div class="flex items-center justify-between gap-4 px-6 py-4">
                <div class="flex items-start gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-lg bg-surface-secondary flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-4 h-4 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $meta['icon'] }}"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-text-primary">{{ $label }}</p>
                        <p class="text-xs text-text-muted mt-0.5">{{ $meta['desc'] }}</p>
                        <p class="text-xs text-text-muted mt-0.5">
                            <span class="inline-flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                                Recipient: {{ $meta['to'] }}
                            </span>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3 shrink-0">
                    {{-- Test email button --}}
                    <form method="POST" action="{{ $host }}/settings/notifications/test/{{ $key }}" class="inline">
                        @csrf
                        <button type="submit"
                                class="text-xs text-text-muted hover:text-accent transition-colors px-2 py-1 border border-border rounded-md hover:border-accent">
                            Send test
                        </button>
                    </form>

                    {{-- Toggle switch --}}
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="email_{{ $key }}" value="1" class="sr-only peer"
                               {{ $enabled ? 'checked' : '' }}>
                        <div class="w-10 h-6 rounded-full border-2 border-border bg-surface peer-checked:bg-accent peer-checked:border-accent transition-colors
                                    after:content-[''] after:absolute after:top-[3px] after:left-[3px] after:bg-white after:border after:border-border after:rounded-full after:h-4 after:w-4 after:transition-all
                                    peer-checked:after:translate-x-4 peer-checked:after:border-white">
                        </div>
                    </label>
                </div>
            </div>
            @endforeach
        </div>

        <div class="flex justify-end mt-4">
            <button type="submit" :disabled="submitting"
                    class="inline-flex items-center gap-2 px-5 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
                <span x-show="!submitting">Save Preferences</span>
                <span x-show="submitting">Saving…</span>
            </button>
        </div>
    </form>

    {{-- Info card --}}
    <div class="bg-surface-secondary border border-border rounded-xl p-4 flex items-start gap-3">
        <svg class="w-4 h-4 text-text-muted shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
            <p class="text-xs font-medium text-text-primary mb-0.5">Mail configuration</p>
            <p class="text-xs text-text-muted leading-relaxed">
                Emails are sent via your server's <code class="bg-surface border border-border px-1 rounded text-xs">MAIL_*</code> configuration in <code class="bg-surface border border-border px-1 rounded text-xs">.env</code>.
                Use <strong>SMTP</strong> (e.g. Mailgun, Brevo, SendGrid) in production.
                SMS notifications are not yet enabled — contact support for availability.
            </p>
        </div>
    </div>

</div>
@endsection
