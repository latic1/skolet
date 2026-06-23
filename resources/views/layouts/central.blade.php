<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Skolet — School Management Platform')</title>
    <meta name="description" content="@yield('meta_description', 'Skolet helps schools manage attendance, exams, fees, and communication in one place. Every school gets its own secure database.')">

    @stack('og_tags')

    <!-- Inter font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head')
</head>
<body class="font-sans antialiased bg-background text-text-primary">

    {{-- Navbar --}}
    <header class="bg-surface border-b border-border sticky top-0 z-50"
            x-data="{ mobileOpen: false }">
        <div class="max-w-360 mx-auto px-6 h-16 flex items-center justify-between">

            {{-- Logo --}}
            <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-[10px] flex items-center justify-center shrink-0"
                     style="background: linear-gradient(45deg, #2563eb 0%, #1e3a8a 100%)">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 2L3 6V10C3 13.866 6.134 17 10 17C13.866 17 17 13.866 17 10V6L10 2Z" fill="white" fill-opacity="0.9"/>
                        <path d="M7 10L9 12L13 8" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <span class="text-[19px] font-bold leading-7 text-text-darkest tracking-tight">Skolet</span>

            </a>

            {{-- Desktop Nav --}}
            <nav class="hidden md:flex items-center gap-1">
                <a href="{{ route('pricing') }}"
                   class="px-4 py-2 text-sm font-medium text-text-dark hover:text-text-primary hover:bg-surface-secondary rounded-md transition-colors">
                    Pricing
                </a>
                <a href="{{ route('login') }}"
                   class="px-4 py-2 text-sm font-medium text-text-dark hover:text-text-primary hover:bg-surface-secondary rounded-md transition-colors">
                    Login
                </a>
                <a href="{{ route('register-school') }}"
                   class="ml-2 px-4 py-2 text-sm font-medium bg-accent text-accent-foreground rounded-md hover:bg-accent-dark transition-colors">
                    Register School
                </a>
            </nav>

            {{-- Mobile menu button --}}
            <button @click="mobileOpen = !mobileOpen"
                    class="md:hidden p-2 rounded-md text-text-secondary hover:bg-surface-secondary transition-colors">
                <svg x-show="!mobileOpen" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg x-show="mobileOpen" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display:none">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Mobile Menu --}}
        <div x-show="mobileOpen"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-1"
             class="md:hidden bg-surface border-t border-border px-6 py-4 flex flex-col gap-1"
             style="display: none">
            <a href="{{ route('pricing') }}"
               class="px-4 py-2.5 text-sm font-medium text-text-dark hover:text-text-primary hover:bg-surface-secondary rounded-md">
                Pricing
            </a>
            <a href="{{ route('login') }}"
               class="px-4 py-2.5 text-sm font-medium text-text-dark hover:text-text-primary hover:bg-surface-secondary rounded-md">
                Login
            </a>
            <a href="{{ route('register-school') }}"
               class="mt-2 px-4 py-2.5 text-sm font-medium text-center bg-accent text-accent-foreground rounded-md hover:bg-accent-dark">
                Register School
            </a>
        </div>
    </header>

    {{-- Page Content --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-surface border-t border-border">
        <div class="max-w-[1440px] mx-auto px-6 py-12">
            <div class="flex flex-col md:flex-row justify-between gap-8">

                {{-- Brand --}}
                <div class="flex flex-col gap-4 max-w-xs">
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-[10px] flex items-center justify-center flex-shrink-0"
                             style="background: linear-gradient(45deg, #2563eb 0%, #1e3a8a 100%)">
                            <svg width="16" height="16" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M10 2L3 6V10C3 13.866 6.134 17 10 17C13.866 17 17 13.866 17 10V6L10 2Z" fill="white" fill-opacity="0.9"/>
                                <path d="M7 10L9 12L13 8" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <span class="text-base font-bold text-text-darkest">Skolet</span>
                    </a>
                    <p class="text-sm text-text-secondary leading-relaxed">
                        The complete school management platform. Attendance, exams, fees, and more — in one place.
                    </p>
                </div>

                {{-- Links --}}
                <div class="flex flex-col sm:flex-row gap-8">
                    <div class="flex flex-col gap-3">
                        <p class="text-xs font-semibold uppercase tracking-wider text-text-muted">Product</p>
                        <a href="{{ route('pricing') }}" class="text-sm text-text-secondary hover:text-text-primary transition-colors">Pricing</a>
                        <a href="{{ route('register-school') }}" class="text-sm text-text-secondary hover:text-text-primary transition-colors">Register School</a>
                        <a href="{{ route('login') }}" class="text-sm text-text-secondary hover:text-text-primary transition-colors">Login</a>
                    </div>
                    <div class="flex flex-col gap-3">
                        <p class="text-xs font-semibold uppercase tracking-wider text-text-muted">Features</p>
                        <span class="text-sm text-text-secondary">Attendance Tracking</span>
                        <span class="text-sm text-text-secondary">Fee Management</span>
                        <span class="text-sm text-text-secondary">Exam & Grading</span>
                        <span class="text-sm text-text-secondary">School Public Page</span>
                    </div>
                </div>
            </div>

            <div class="mt-10 pt-6 border-t border-border flex flex-col sm:flex-row justify-between items-center gap-4">
                <p class="text-sm text-text-muted">© {{ date('Y') }} Skolet. All rights reserved.</p>
                <p class="text-sm text-text-muted">Built for modern schools.</p>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
