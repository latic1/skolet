<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Too Many Requests — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="font-sans bg-background min-h-screen flex items-center justify-center px-4">

    <div class="flex flex-col items-center text-center max-w-md w-full">

        {{-- Illustration --}}
        <div class="w-24 h-24 rounded-3xl bg-warning-lightest flex items-center justify-center mb-8">
            <svg class="w-12 h-12 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
        </div>

        {{-- Error code --}}
        <p class="text-8xl font-bold text-warning mb-2 leading-none">429</p>

        {{-- Heading --}}
        <h1 class="text-xl font-semibold text-text-primary mb-3">Slow down</h1>

        {{-- Description --}}
        <p class="text-sm text-text-muted mb-8 leading-relaxed">
            You've made too many requests in a short time. Please wait a moment before trying again.
            @if(!empty($exception) && method_exists($exception, 'getHeaders') && isset($exception->getHeaders()['Retry-After']))
                <span class="block mt-2 font-medium text-text-secondary">
                    Try again in {{ $exception->getHeaders()['Retry-After'] }} seconds.
                </span>
            @endif
        </p>

        {{-- Actions --}}
        <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : '/' }}"
               class="w-full sm:w-auto flex items-center justify-center gap-2 px-5 py-2.5 bg-surface border border-border text-sm font-medium text-text-primary rounded-lg hover:bg-surface-secondary transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Go back
            </a>
            <a href="{{ url('/dashboard') }}"
               class="w-full sm:w-auto flex items-center justify-center gap-2 px-5 py-2.5 bg-accent text-accent-foreground text-sm font-medium rounded-lg hover:bg-accent-dark transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Go to Dashboard
            </a>
        </div>

        {{-- App name --}}
        <p class="mt-12 text-xs text-text-muted">{{ config('app.name') }}</p>

    </div>

</body>
</html>
