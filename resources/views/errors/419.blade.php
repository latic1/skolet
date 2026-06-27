<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Session Expired — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="font-sans bg-background min-h-screen flex items-center justify-center px-4">

    <div class="flex flex-col items-center text-center max-w-md w-full">

        {{-- Illustration --}}
        <div class="w-24 h-24 rounded-3xl bg-accent-muted flex items-center justify-center mb-8">
            <svg class="w-12 h-12 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>

        {{-- Error code --}}
        <p class="text-8xl font-bold text-accent mb-2 leading-none">419</p>

        {{-- Heading --}}
        <h1 class="text-xl font-semibold text-text-primary mb-3">Session expired</h1>

        {{-- Description --}}
        <p class="text-sm text-text-muted mb-8 leading-relaxed">
            Your session has timed out or the page has been open too long. Refresh to continue where you left off.
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
            <button onclick="window.location.reload()"
               class="w-full sm:w-auto flex items-center justify-center gap-2 px-5 py-2.5 bg-accent text-accent-foreground text-sm font-medium rounded-lg hover:bg-accent-dark transition-colors cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Refresh page
            </button>
        </div>

        {{-- App name --}}
        <p class="mt-12 text-xs text-text-muted">{{ config('app.name') }}</p>

    </div>

</body>
</html>
