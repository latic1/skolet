<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $schoolName = $profile?->school_name ?? tenant('name') ?? config('app.name');
        $logoUrl    = $profile?->logo_path ? request()->getSchemeAndHttpHost() . '/school-logo' : null;
    @endphp
    <title>Application Received &mdash; {{ $schoolName }}</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans bg-background min-h-screen">

    {{-- Navbar --}}
    <header class="bg-surface border-b border-border sticky top-0 z-50">
        <div class="max-w-2xl mx-auto flex items-center justify-between h-16 px-4 lg:px-8">
            <a href="{{ request()->getSchemeAndHttpHost() }}/" class="flex items-center gap-2.5">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $schoolName }} logo" class="w-9 h-9 rounded-[10px] object-contain">
                @else
                    <div class="w-9 h-9 rounded-[10px] flex items-center justify-center shrink-0"
                         style="background: linear-gradient(45deg, #2563eb 0%, #1e3a8a 100%)">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                @endif
                <span class="text-[19px] font-bold leading-7 text-text-darkest tracking-tight">{{ $schoolName }}</span>
            </a>
        </div>
    </header>

    <main class="max-w-2xl mx-auto px-4 lg:px-8 py-12 flex flex-col items-center text-center gap-6">

        {{-- Success icon --}}
        <div class="w-16 h-16 rounded-full bg-success-lightest flex items-center justify-center">
            <svg class="w-8 h-8 text-success-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>

        {{-- Confirmation card --}}
        <div class="w-full bg-surface border border-border rounded-2xl shadow-card p-8 flex flex-col gap-5 text-left">
            <div class="text-center">
                <h1 class="text-xl font-bold text-text-primary">Application Received!</h1>
                <p class="text-sm text-text-muted mt-1">
                    Thank you for applying to <strong>{{ $schoolName }}</strong>.
                    Your application has been submitted and is currently under review.
                </p>
            </div>

            <div class="border-t border-border pt-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <p class="text-xs font-medium text-text-secondary uppercase tracking-wide mb-0.5">Applicant</p>
                    <p class="text-sm font-semibold text-text-primary">{{ $application->applicant_name }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-text-secondary uppercase tracking-wide mb-0.5">Class Applied For</p>
                    <p class="text-sm font-semibold text-text-primary">{{ $application->class_applying_for }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-text-secondary uppercase tracking-wide mb-0.5">Guardian</p>
                    <p class="text-sm text-text-primary">{{ $application->guardian_name }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-text-secondary uppercase tracking-wide mb-0.5">Contact</p>
                    <p class="text-sm text-text-primary">{{ $application->guardian_contact }}</p>
                </div>
                @if($application->guardian_email)
                <div class="sm:col-span-2">
                    <p class="text-xs font-medium text-text-secondary uppercase tracking-wide mb-0.5">Email</p>
                    <p class="text-sm text-text-primary">{{ $application->guardian_email }}</p>
                </div>
                @endif
            </div>

            @if($application->guardian_email)
            <div class="bg-accent-muted border border-accent rounded-lg px-4 py-3 flex items-start gap-3">
                <svg class="w-4 h-4 text-accent shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <p class="text-xs text-accent">A confirmation email has been sent to <strong>{{ $application->guardian_email }}</strong>.</p>
            </div>
            @endif

            <div class="border-t border-border pt-5 text-center">
                <p class="text-xs text-text-muted mb-4">We will contact you once a decision has been made. If you have questions, please contact the school office directly.</p>
                <a href="{{ request()->getSchemeAndHttpHost() }}/"
                   class="inline-flex items-center gap-2 px-5 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to School Page
                </a>
            </div>
        </div>

    </main>

</body>
</html>
