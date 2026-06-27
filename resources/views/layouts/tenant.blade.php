<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') — {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="font-sans bg-background min-h-screen" x-data="{ sidebarOpen: false }">

    <div class="flex h-screen overflow-hidden">

        {{-- Sidebar --}}
        <aside class="w-65 shrink-0 bg-surface border-r border-border flex flex-col sticky top-0 h-screen">

            {{-- Logo --}}
            <div class="flex items-center gap-2.5 px-4 h-16 border-b border-border shrink-0">
                @if(isset($schoolProfile) && $schoolProfile?->logo_path)
                <img src="{{ request()->getSchemeAndHttpHost() . '/school-logo' }}"
                     alt="{{ $schoolProfile->school_name }}"
                     class="w-9 h-9 rounded-[10px] object-contain shrink-0">
                @else
                <div class="w-9 h-9 rounded-[10px] flex items-center justify-center shrink-0"
                     style="background: linear-gradient(45deg, #2563eb 0%, #1e3a8a 100%)">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                @endif
                <span class="text-[19px] font-bold leading-7 text-text-darkest tracking-tight">
                    {{ $schoolProfile?->school_name ?? config('app.name') }}
                </span>
            </div>

            {{-- Nav --}}
            <div class="flex-1 overflow-y-auto p-3">
                <x-sidebar-nav />
            </div>

            {{-- User footer --}}
            <div class="border-t border-border p-3 shrink-0">
                <div class="flex items-center justify-between gap-2 px-2 py-1.5">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-text-primary truncate">{{ auth()->user()?->name }}</p>
                        <p class="text-xs text-text-muted capitalize">{{ auth()->user()?->role }}</p>
                    </div>
                    <form method="POST" action="{{ request()->getSchemeAndHttpHost() . '/logout' }}">
                        @csrf
                        <button type="submit"
                                class="p-1.5 rounded-md text-text-muted hover:text-text-primary hover:bg-surface-secondary transition-colors"
                                title="Sign out">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Main content --}}
        <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">

            {{-- Super Admin impersonation banner — non-dismissible, always visible while active --}}
            @if(session('impersonating'))
            <div class="shrink-0 bg-warning-light border-b-2 border-warning/50 px-6 py-3 flex items-center justify-between gap-4">
                <div class="flex items-center gap-2.5 text-sm font-medium text-warning min-w-0">
                    <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <span class="truncate">
                        You are viewing as <strong>{{ tenant('name') }}</strong> (Super Admin support session) — any changes are real and attributed to the school admin account.
                    </span>
                </div>
                <form method="POST" action="{{ request()->getSchemeAndHttpHost() }}/impersonate/exit">
                    @csrf
                    <button type="submit"
                            class="shrink-0 text-xs font-semibold text-warning border border-warning bg-white/60 rounded-md px-3 py-1.5 hover:bg-white/90 transition-colors whitespace-nowrap">
                        Exit Impersonation
                    </button>
                </form>
            </div>
            @endif

            {{-- Critical platform broadcast banner — non-dismissible, shown on every page --}}
            @if(!empty($criticalBroadcast))
            <div class="shrink-0 bg-error-light border-b-2 border-error/40 px-6 py-3 flex items-center justify-between gap-4">
                <div class="flex items-center gap-2.5 text-sm font-medium text-error min-w-0">
                    <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <div class="min-w-0">
                        <span class="font-semibold">{{ $criticalBroadcast['subject'] }}:</span>
                        <span class="ml-1 truncate">{{ Str::limit($criticalBroadcast['message'], 120) }}</span>
                    </div>
                </div>
                <span class="shrink-0 text-xs font-semibold text-error border border-error/30 bg-white/60 rounded-md px-2 py-1 whitespace-nowrap">Platform Alert</span>
            </div>
            @endif

            {{-- Info/Warning platform broadcast — dismissible, shown on dashboard only --}}
            @if(!empty($activeBroadcast) && request()->routeIs('tenant.dashboard'))
            <div class="shrink-0 {{ $activeBroadcast['severity'] === 'warning' ? 'bg-warning-light border-b-2 border-warning/40' : 'bg-accent-muted border-b border-accent/20' }} px-6 py-3 flex items-center justify-between gap-4">
                <div class="flex items-center gap-2.5 text-sm font-medium {{ $activeBroadcast['severity'] === 'warning' ? 'text-warning' : 'text-accent' }} min-w-0">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                    </svg>
                    <div class="min-w-0">
                        <span class="font-semibold">{{ $activeBroadcast['subject'] }}:</span>
                        <span class="ml-1">{{ Str::limit($activeBroadcast['message'], 120) }}</span>
                    </div>
                </div>
                <form method="POST" action="{{ request()->getSchemeAndHttpHost() }}/platform-notice/{{ $activeBroadcast['notification_id'] }}/dismiss">
                    @csrf
                    @method('PATCH')
                    <button type="submit"
                            class="shrink-0 text-xs font-semibold {{ $activeBroadcast['severity'] === 'warning' ? 'text-warning border-warning/40' : 'text-accent border-accent/40' }} border bg-white/60 rounded-md px-3 py-1.5 hover:bg-white/90 transition-colors whitespace-nowrap">
                        Dismiss
                    </button>
                </form>
            </div>
            @endif

            {{-- Top bar --}}
            <header class="h-16 shrink-0 bg-surface border-b border-border flex items-center px-6 gap-4">
                <h2 class="text-base font-semibold text-text-primary flex-1">
                    @yield('page-title', 'Dashboard')
                </h2>

                @php
                    $notifUnreadCount = 0;
                    $notifRecent = collect();
                    try {
                        if (auth()->check() && tenancy()->initialized) {
                            $notifUnreadCount = \App\Models\Tenant\TenantNotification::where('user_id', auth()->id())->whereNull('read_at')->count();
                            $notifRecent      = \App\Models\Tenant\TenantNotification::where('user_id', auth()->id())->latest()->limit(5)->get();
                        }
                    } catch (\Throwable) {}
                @endphp
                <div class="flex items-center gap-2">
                    {{-- Notification bell --}}
                    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                        <button @click="open = !open"
                                class="relative p-2 rounded-md text-text-muted hover:text-text-primary hover:bg-surface-secondary transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            @if($notifUnreadCount > 0)
                            <span class="absolute top-1 right-1 min-w-[16px] h-4 bg-error text-white text-[10px] font-bold rounded-full flex items-center justify-center px-0.5 leading-none">
                                {{ $notifUnreadCount > 9 ? '9+' : $notifUnreadCount }}
                            </span>
                            @endif
                        </button>

                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 top-full mt-2 w-80 bg-surface border border-border rounded-xl shadow-xl z-50 overflow-hidden"
                             style="display:none">

                            {{-- Header --}}
                            <div class="flex items-center justify-between px-4 py-3 border-b border-border">
                                <h3 class="text-sm font-semibold text-text-primary">Notifications</h3>
                                @if($notifUnreadCount > 0)
                                <form method="POST" action="{{ request()->getSchemeAndHttpHost() }}/notifications/read-all">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-xs font-medium text-accent hover:text-accent-dark transition-colors">
                                        Mark all as read
                                    </button>
                                </form>
                                @endif
                            </div>

                            {{-- Notification items --}}
                            <div class="max-h-72 overflow-y-auto divide-y divide-border">
                                @forelse($notifRecent as $notif)
                                <div class="flex items-start gap-3 px-4 py-3 {{ is_null($notif->read_at) ? 'bg-accent-muted/20' : '' }} hover:bg-surface-secondary transition-colors">
                                    <div class="shrink-0 mt-1.5">
                                        @if(is_null($notif->read_at))
                                        <div class="w-2 h-2 rounded-full bg-accent"></div>
                                        @else
                                        <div class="w-2 h-2 rounded-full bg-transparent"></div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm {{ is_null($notif->read_at) ? 'font-semibold text-text-primary' : 'font-medium text-text-dark' }} leading-snug truncate">
                                            {{ $notif->message }}
                                        </p>
                                        <p class="text-xs text-text-muted mt-0.5">{{ $notif->created_at->diffForHumans() }}</p>
                                    </div>
                                    @if(is_null($notif->read_at))
                                    <form method="POST" action="{{ request()->getSchemeAndHttpHost() }}/notifications/{{ $notif->id }}/read" class="shrink-0">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-xs text-text-muted hover:text-text-primary transition-colors mt-0.5">✓</button>
                                    </form>
                                    @endif
                                </div>
                                @empty
                                <div class="flex flex-col items-center justify-center py-8 px-4 text-center">
                                    <p class="text-sm text-text-muted">No notifications yet</p>
                                </div>
                                @endforelse
                            </div>

                            {{-- Footer --}}
                            <div class="border-t border-border px-4 py-3">
                                <a href="{{ request()->getSchemeAndHttpHost() }}/notifications"
                                   class="text-xs font-medium text-accent hover:text-accent-dark transition-colors">
                                    View all notifications →
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Account dropdown --}}
                    @php
                        $topbarUser      = auth()->user();
                        $topbarAvatarUrl = $topbarUser?->avatar_path
                            ? request()->getSchemeAndHttpHost() . '/account/avatar'
                            : null;
                        $topbarInitial   = mb_strtoupper(mb_substr($topbarUser?->name ?? 'U', 0, 1));
                        $topbarHost      = request()->getSchemeAndHttpHost();
                    @endphp
                    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                        <button @click="open = !open"
                                class="flex items-center gap-2 p-1 rounded-lg hover:bg-surface-secondary transition-colors">
                            @if($topbarAvatarUrl)
                            <img src="{{ $topbarAvatarUrl }}"
                                 alt="{{ $topbarUser?->name }}"
                                 class="w-8 h-8 rounded-full object-cover border border-border">
                            @else
                            <div class="w-8 h-8 rounded-full bg-accent-muted flex items-center justify-center shrink-0">
                                <span class="text-xs font-semibold text-accent">{{ $topbarInitial }}</span>
                            </div>
                            @endif
                            <svg class="w-3.5 h-3.5 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 top-full mt-2 w-56 bg-surface border border-border rounded-xl shadow-xl z-50 overflow-hidden"
                             style="display:none">

                            {{-- User info --}}
                            <div class="px-4 py-3 border-b border-border">
                                <p class="text-sm font-medium text-text-primary truncate">{{ $topbarUser?->name }}</p>
                                <p class="text-xs text-text-muted truncate">{{ $topbarUser?->email }}</p>
                            </div>

                            {{-- Menu items --}}
                            <div class="py-1">
                                <a href="{{ $topbarHost }}/account"
                                   class="flex items-center gap-2.5 px-4 py-2 text-sm text-text-dark hover:bg-surface-secondary hover:text-text-primary transition-colors">
                                    <svg class="w-4 h-4 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    My Account
                                </a>
                            </div>

                            <div class="border-t border-border py-1">
                                <form method="POST" action="{{ $topbarHost }}/logout">
                                    @csrf
                                    <button type="submit"
                                            class="w-full flex items-center gap-2.5 px-4 py-2 text-sm text-text-dark hover:bg-surface-secondary hover:text-text-primary transition-colors text-left">
                                        <svg class="w-4 h-4 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        Sign out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Page content --}}
            <main class="flex-1 p-8">
                @yield('content')
            </main>
        </div>
    </div>

    {{-- Global toast — driven by session flash (success / error) --}}
    @if(session('success') || session('error'))
    <div x-data="{
            show: true,
            type: '{{ session('success') ? 'success' : 'error' }}',
            message: {{ json_encode(session('success') ?? session('error')) }},
            init() { setTimeout(() => { this.show = false }, 4000); },
            dismiss() { this.show = false; }
         }"
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-3"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-3"
         class="fixed bottom-6 right-6 z-50 max-w-sm w-full pointer-events-none">
        <div class="pointer-events-auto flex items-start gap-3 px-4 py-3.5 rounded-xl shadow-lg border"
             :class="type === 'success'
                 ? 'bg-success-lightest border-success-light text-success-foreground'
                 : 'bg-error-light border-error text-error'">

            {{-- Icon --}}
            <svg x-show="type === 'success'" class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <svg x-show="type === 'error'" class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>

            <p class="text-sm font-medium flex-1 leading-snug" x-text="message"></p>

            {{-- Dismiss button --}}
            <button @click="dismiss()"
                    class="shrink-0 -mt-0.5 -mr-0.5 p-1 rounded-md opacity-60 hover:opacity-100 transition-opacity">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>
    @endif

    @stack('scripts')
</body>
</html>
