@extends('layouts.tenant')

@section('title', 'Settings â€” Data & Privacy')
@section('page-title', 'Settings')

@section('content')
@php $host = request()->getSchemeAndHttpHost(); @endphp
<div class="flex flex-col gap-6" x-data="{ submitting: false }">

    @include('partials.settings-tabs')

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="bg-success-lightest border border-success-light text-success-foreground text-sm px-4 py-3 rounded-xl flex items-start gap-3">
        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="bg-error-light border border-error/20 text-error text-sm px-4 py-3 rounded-xl">{{ session('error') }}</div>
    @endif

    {{-- Deleted Records --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card p-6">
        <h3 class="text-base font-semibold text-text-primary mb-1">Deleted Records</h3>
        <p class="text-sm text-text-muted mb-5">Soft-deleted students and staff are kept for 90 days before being permanently purged. You can restore or permanently delete them from the trash views.</p>

        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ $host }}/students/trash"
               class="flex items-center gap-2 px-4 py-2.5 bg-surface border border-border text-sm font-medium text-text-primary rounded-xl hover:bg-surface-secondary transition-colors">
                <svg class="w-4 h-4 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Student Trash
            </a>
            <a href="{{ $host }}/staff/trash"
               class="flex items-center gap-2 px-4 py-2.5 bg-surface border border-border text-sm font-medium text-text-primary rounded-xl hover:bg-surface-secondary transition-colors">
                <svg class="w-4 h-4 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Staff Trash
            </a>
        </div>
    </div>

    {{-- Data Retention Policy --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card p-6">
        <h3 class="text-base font-semibold text-text-primary mb-1">Data Retention Policy</h3>
        <p class="text-sm text-text-muted mb-4">How long we keep your school's data:</p>
        <div class="flex flex-col gap-3">
            <div class="flex items-start gap-3 p-3 bg-surface-secondary rounded-xl">
                <div class="w-2 h-2 rounded-full bg-accent mt-1.5 shrink-0"></div>
                <div>
                    <p class="text-sm font-medium text-text-primary">Active Records</p>
                    <p class="text-xs text-text-muted mt-0.5">Students, staff, exams, fees, and attendance records are retained indefinitely while your account is active.</p>
                </div>
            </div>
            <div class="flex items-start gap-3 p-3 bg-surface-secondary rounded-xl">
                <div class="w-2 h-2 rounded-full bg-warning mt-1.5 shrink-0"></div>
                <div>
                    <p class="text-sm font-medium text-text-primary">Soft-Deleted Records</p>
                    <p class="text-xs text-text-muted mt-0.5">When you delete a student or staff member, they are soft-deleted and visible in the Trash for <strong>90 days</strong>. After 90 days, they are permanently purged.</p>
                </div>
            </div>
            <div class="flex items-start gap-3 p-3 bg-surface-secondary rounded-xl">
                <div class="w-2 h-2 rounded-full bg-error mt-1.5 shrink-0"></div>
                <div>
                    <p class="text-sm font-medium text-text-primary">Account Termination</p>
                    <p class="text-xs text-text-muted mt-0.5">If your school's subscription expires and is not renewed within 90 days, all tenant data may be permanently deleted.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Export All School Data --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card p-6">
        <h3 class="text-base font-semibold text-text-primary mb-1">Export All School Data</h3>
        <p class="text-sm text-text-muted mb-5">
            Download a full export of your school's data as a ZIP of CSV files. The export includes students, staff, attendance, exam results, fees, and all other records.
            A download link will be emailed to you â€” exports are ready within a few minutes.
        </p>
        <form method="POST" action="{{ $host }}/settings/privacy/export"
              @submit="submitting = true">
            @csrf
            <button type="submit"
                    :disabled="submitting"
                    class="flex items-center gap-2 px-4 py-2.5 bg-accent text-accent-foreground text-sm font-semibold rounded-xl hover:opacity-90 transition-opacity disabled:opacity-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span x-text="submitting ? 'Requestingâ€¦' : 'Request Full Export'"></span>
            </button>
        </form>
    </div>

</div>
@endsection
