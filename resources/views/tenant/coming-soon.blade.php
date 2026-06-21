@extends('layouts.tenant')

@section('title', $section ?? 'Coming Soon')
@section('page-title', $section ?? 'Coming Soon')

@section('content')
<div class="max-w-lg">
    <div class="bg-surface border border-border rounded-2xl p-8 shadow-card text-center">
        <div class="w-12 h-12 bg-accent-muted rounded-xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h3 class="text-base font-semibold text-text-primary mb-1">{{ $section ?? 'This section' }} is coming soon</h3>
        <p class="text-sm text-text-muted">
            This feature is planned for Phase {{ $phase ?? 'upcoming' }} of the build plan.
        </p>
        <a href="{{ request()->getSchemeAndHttpHost() . '/dashboard' }}"
           class="mt-5 inline-flex items-center gap-2 px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Dashboard
        </a>
    </div>
</div>
@endsection
