<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @php
        $schoolName    = $profile?->school_name ?? tenant('name') ?? config('app.name');
        $description   = $profile?->short_description;
        $logoPath      = $profile?->logo_path;
        $logoUrl       = $logoPath ? request()->getSchemeAndHttpHost() . '/school-logo' : null;
        $address       = $profile?->address;
        $metaTitle     = $schoolName;
        $metaDesc      = $description
            ?? ($address
                ? $schoolName . ' — located at ' . $address . '. Find our latest news, announcements, and contact information.'
                : 'Welcome to ' . $schoolName . '. Find our latest announcements and get in touch with us.');
    @endphp

    <title>{{ $metaTitle }}</title>
    <meta name="description" content="{{ Str::limit($metaDesc, 160) }}">

    {{-- OpenGraph --}}
    <meta property="og:type"        content="website">
    <meta property="og:url"         content="{{ url('/') }}">
    <meta property="og:title"       content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ Str::limit($metaDesc, 200) }}">
    @if($logoUrl)
        <meta property="og:image" content="{{ $logoUrl }}">
    @endif

    {{-- Twitter Card --}}
    <meta name="twitter:card"        content="{{ $logoUrl ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title"       content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ Str::limit($metaDesc, 200) }}">
    @if($logoUrl)
        <meta name="twitter:image" content="{{ $logoUrl }}">
    @endif

    {{-- Indexable: no noindex tag; robots.txt allows / --}}
    <meta name="robots" content="index, follow">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans bg-background min-h-screen">

    {{-- ── Top Navbar ──────────────────────────────────────────────────── --}}
    <header class="bg-surface border-b border-border sticky top-0 z-50">
        <div class="max-w-4xl mx-auto flex items-center justify-between h-16 px-4 lg:px-8">

            <div class="flex items-center gap-2.5">
                @if($logoPath)
                    <img src="{{ request()->getSchemeAndHttpHost() . '/school-logo' }}"
                         alt="{{ $schoolName }} logo"
                         class="w-9 h-9 rounded-[10px] object-contain">
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
            </div>

            <div class="flex items-center gap-2">
                @if($profile?->admissions_open)
                <a href="{{ request()->getSchemeAndHttpHost() }}/apply"
                   class="px-4 py-2 text-sm font-medium bg-accent-muted text-accent border border-accent rounded-md hover:bg-accent hover:text-white transition-colors">
                    Apply Now
                </a>
                @endif
                <a href="{{ request()->getSchemeAndHttpHost() . '/login' }}"
                   class="px-4 py-2 text-sm font-medium bg-accent text-accent-foreground rounded-md hover:bg-accent-dark transition-colors">
                    Login
                </a>
            </div>
        </div>
    </header>

    {{-- ── Main Content ─────────────────────────────────────────────────── --}}
    <main class="max-w-4xl mx-auto px-4 lg:px-8 py-10 lg:py-12 flex flex-col gap-8">

        {{-- Hero ──────────────────────────────────────────────────────────── --}}
        <section class="bg-surface border border-border rounded-2xl shadow-card p-8 flex flex-col items-center gap-5 text-center">

            {{-- Logo --}}
            @if($logoPath)
                <img src="{{ request()->getSchemeAndHttpHost() . '/school-logo' }}"
                     alt="{{ $schoolName }} logo"
                     class="w-24 h-24 rounded-2xl object-contain border border-border shadow-sm">
            @else
                <div class="w-20 h-20 rounded-2xl flex items-center justify-center shrink-0"
                     style="background: linear-gradient(45deg, #2563eb 0%, #1e3a8a 100%)">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
            @endif

            {{-- Name + description --}}
            <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-text-primary tracking-tight">{{ $schoolName }}</h1>
                @if($description)
                    <p class="mt-2 text-sm lg:text-base text-text-secondary leading-relaxed max-w-xl mx-auto">{{ $description }}</p>
                @endif
            </div>

            {{-- Contact strip in hero --}}
            @if($profile && ($address || $profile->phone || $profile->email))
                <div class="flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-sm text-text-muted">
                    @if($address)
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            {{ $address }}
                        </span>
                    @endif
                    @if($profile->phone)
                        <a href="tel:{{ $profile->phone }}" class="flex items-center gap-1.5 hover:text-accent transition-colors">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            {{ $profile->phone }}
                        </a>
                    @endif
                    @if($profile->email)
                        <a href="mailto:{{ $profile->email }}" class="flex items-center gap-1.5 hover:text-accent transition-colors">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            {{ $profile->email }}
                        </a>
                    @endif
                </div>
            @endif
        </section>

        {{-- Recent Announcements ────────────────────────────────────────── --}}
        <section>
            <h2 class="text-base font-semibold text-text-primary mb-4">Recent Announcements</h2>

            @if($announcements->isNotEmpty())
                <div class="flex flex-col gap-4">
                    @foreach($announcements as $announcement)
                        <article class="bg-surface border border-border rounded-2xl shadow-card overflow-hidden">
                            <div class="flex items-start gap-4 px-6 py-4 border-b border-border">
                                <div class="w-8 h-8 rounded-lg bg-warning-light flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-4 h-4 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm font-semibold text-text-primary">{{ $announcement->title }}</h3>
                                    <p class="text-xs text-text-muted mt-0.5">{{ $announcement->created_at->format('d M Y') }}</p>
                                </div>
                            </div>
                            <div class="px-6 py-4">
                                <p class="text-sm text-text-primary leading-relaxed whitespace-pre-line">{{ Str::limit($announcement->body, 300) }}</p>
                                @if(strlen($announcement->body) > 300)
                                    <p class="mt-2 text-xs text-text-muted italic">… Login to the school portal to read the full announcement.</p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="bg-surface border border-border rounded-2xl shadow-card flex flex-col items-center justify-center py-12 px-6 text-center">
                    <div class="w-12 h-12 rounded-xl bg-warning-light flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                        </svg>
                    </div>
                    <p class="text-sm text-text-muted">No public announcements yet.</p>
                </div>
            @endif
        </section>

        {{-- Contact Info ─────────────────────────────────────────────────── --}}
        @if($profile && ($address || $profile->phone || $profile->email || $profile->website))
            <section class="bg-surface border border-border rounded-2xl shadow-card overflow-hidden">
                <div class="px-6 py-4 border-b border-border">
                    <h2 class="text-base font-semibold text-text-primary">Contact Us</h2>
                </div>
                <dl class="px-6 py-5 flex flex-col gap-5">
                    @if($address)
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-accent-muted flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-text-muted uppercase tracking-wide mb-0.5">Address</dt>
                                <dd class="text-sm text-text-primary">{{ $address }}</dd>
                            </div>
                        </div>
                    @endif
                    @if($profile->phone)
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-accent-muted flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-text-muted uppercase tracking-wide mb-0.5">Phone</dt>
                                <dd class="text-sm text-text-primary">
                                    <a href="tel:{{ $profile->phone }}" class="hover:text-accent transition-colors">{{ $profile->phone }}</a>
                                </dd>
                            </div>
                        </div>
                    @endif
                    @if($profile->email)
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-accent-muted flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-text-muted uppercase tracking-wide mb-0.5">Email</dt>
                                <dd class="text-sm text-text-primary">
                                    <a href="mailto:{{ $profile->email }}" class="text-accent hover:text-accent-dark transition-colors">{{ $profile->email }}</a>
                                </dd>
                            </div>
                        </div>
                    @endif
                    @if($profile->website)
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-accent-muted flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                </svg>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-text-muted uppercase tracking-wide mb-0.5">Website</dt>
                                <dd class="text-sm text-text-primary">
                                    <a href="{{ $profile->website }}" target="_blank" rel="noopener noreferrer"
                                       class="text-accent hover:text-accent-dark transition-colors">{{ $profile->website }}</a>
                                </dd>
                            </div>
                        </div>
                    @endif
                </dl>
            </section>
        @endif

    </main>

    {{-- ── Footer ───────────────────────────────────────────────────────── --}}
    <footer class="border-t border-border mt-4 py-8">
        <div class="max-w-4xl mx-auto px-4 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-2">
            <p class="text-xs text-text-muted">© {{ date('Y') }} {{ $schoolName }}. All rights reserved.</p>
            <p class="text-xs text-text-muted">
                Powered by <span class="font-medium text-text-secondary">Skolet</span>
            </p>
        </div>
    </footer>

</body>
</html>
