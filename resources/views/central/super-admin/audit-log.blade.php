<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Impersonation Audit Log — Skolet Super Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-background text-text-primary">

{{-- Top bar --}}
<header class="bg-surface border-b border-border sticky top-0 z-50">
    <div class="max-w-[1400px] mx-auto px-6 h-14 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
                 style="background: linear-gradient(45deg, #2563eb 0%, #1e3a8a 100%)">
                <svg width="16" height="16" viewBox="0 0 20 20" fill="none">
                    <path d="M10 2L3 6V10C3 13.866 6.134 17 10 17C13.866 17 17 13.866 17 10V6L10 2Z" fill="white" fill-opacity="0.9"/>
                    <path d="M7 10L9 12L13 8" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <span class="font-bold text-text-darkest">Skolet</span>
            <span class="text-border">|</span>
            <span class="text-sm text-text-muted">Super Admin</span>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-sm text-text-secondary hidden sm:inline">{{ Auth::guard('super_admin')->user()->name }}</span>
            <form method="POST" action="{{ route('super-admin.logout') }}">
                @csrf
                <button type="submit" class="text-sm text-text-muted hover:text-error transition-colors">Sign out</button>
            </form>
        </div>
    </div>
</header>

<main class="max-w-[1400px] mx-auto px-6 py-8">

    {{-- Flash --}}
    @if (session('success'))
        <div class="mb-6 bg-success-lightest border border-success-light text-success-foreground text-sm px-4 py-3 rounded-xl flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-6 bg-error-light border border-error text-error text-sm px-4 py-3 rounded-xl flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Back nav --}}
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('super-admin.dashboard') }}"
           class="text-sm text-text-muted hover:text-accent transition-colors">← Back to Dashboard</a>
    </div>

    {{-- Page header --}}
    <div class="mb-8 flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-darkest">Impersonation Audit Log</h1>
            <p class="text-sm text-text-muted mt-1">Every Super Admin support session — who accessed which school, when, and for how long.</p>
        </div>
        @php
            $exportQuery = http_build_query(array_filter(request()->only(['date_from', 'date_to', 'search'])));
        @endphp
        <a href="{{ route('super-admin.audit-log.export') . ($exportQuery ? '?' . $exportQuery : '') }}"
           class="shrink-0 flex items-center gap-2 px-4 py-2 text-sm font-medium bg-surface border border-border text-text-secondary rounded-lg hover:bg-surface-secondary hover:text-text-primary transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Export CSV
        </a>
    </div>

    {{-- Filter bar --}}
    <form method="GET" action="{{ route('super-admin.audit-log') }}" class="mb-6">
        <div class="bg-surface border border-border rounded-2xl shadow-card p-5">
            <div class="flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-[140px]">
                    <label class="block text-xs font-medium text-text-muted uppercase tracking-wide mb-1.5">From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                           class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                </div>
                <div class="flex-1 min-w-[140px]">
                    <label class="block text-xs font-medium text-text-muted uppercase tracking-wide mb-1.5">To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                           class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-medium text-text-muted uppercase tracking-wide mb-1.5">School Name</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search school…"
                               class="w-full pl-9 pr-3 py-2 bg-surface border border-border rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium bg-accent text-accent-foreground rounded-md hover:bg-accent-dark transition-colors">
                        Filter
                    </button>
                    @if (request()->hasAny(['date_from', 'date_to', 'search']))
                        <a href="{{ route('super-admin.audit-log') }}"
                           class="px-4 py-2 text-sm font-medium bg-surface border border-border text-text-secondary rounded-md hover:bg-surface-secondary transition-colors">
                            Clear
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </form>

    {{-- Table --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card overflow-hidden">
        <div class="px-6 py-4 border-b border-border flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-text-darkest">Sessions</h2>
                <p class="text-xs text-text-muted mt-0.5">{{ $logs->total() }} total · {{ $logs->count() }} on this page</p>
            </div>
            <span class="text-xs text-text-muted">Page {{ $logs->currentPage() }} of {{ $logs->lastPage() }}</span>
        </div>

        @if ($logs->isEmpty())
            <div class="flex flex-col items-center justify-center py-16">
                <div class="w-12 h-12 rounded-xl bg-accent-muted flex items-center justify-center mb-3">
                    <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-text-dark">No sessions found</p>
                <p class="text-xs text-text-muted mt-1">
                    @if (request()->hasAny(['date_from', 'date_to', 'search']))
                        Try adjusting your filters.
                    @else
                        No Super Admin impersonation sessions have been recorded yet.
                    @endif
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm" style="min-width: 700px">
                    <thead>
                        <tr class="border-b border-border bg-surface-secondary">
                            <th class="px-5 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wide w-8"></th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wide">Date</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wide">Super Admin</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wide">School</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wide">Duration</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wide">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach ($logs as $log)
                        @php
                            $hasEnded    = $log->ended_at !== null;
                            $timedOut    = !$hasEnded && $log->started_at->lt(now()->subHour());
                            $isActive    = !$hasEnded && !$timedOut;
                            $durationMin = $hasEnded ? $log->started_at->diffInMinutes($log->ended_at) : null;
                        @endphp
                        <tbody x-data="{ expanded: false }">
                        <tr class="hover:bg-surface-secondary/50 transition-colors cursor-pointer" @click="expanded = !expanded">
                            {{-- Expand chevron --}}
                            <td class="px-5 py-4">
                                <svg class="w-4 h-4 text-text-muted transition-transform" :class="expanded ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </td>

                            {{-- Date --}}
                            <td class="px-5 py-4">
                                <div class="font-medium text-text-darkest">{{ $log->started_at->format('d M Y') }}</div>
                                <div class="text-xs text-text-muted mt-0.5">{{ $log->started_at->format('H:i') }}</div>
                            </td>

                            {{-- Super Admin --}}
                            <td class="px-5 py-4">
                                <div class="font-medium text-text-primary">{{ $log->superAdmin?->name ?? '—' }}</div>
                                <div class="text-xs text-text-muted mt-0.5">{{ $log->superAdmin?->email ?? '' }}</div>
                            </td>

                            {{-- School --}}
                            <td class="px-5 py-4">
                                <div class="font-medium text-text-primary">{{ $log->tenant?->name ?? '—' }}</div>
                            </td>

                            {{-- Duration --}}
                            <td class="px-5 py-4">
                                @if ($hasEnded)
                                    <span class="text-text-primary">
                                        @if ($durationMin < 1)
                                            &lt; 1 min
                                        @else
                                            {{ $durationMin }} min
                                        @endif
                                    </span>
                                @elseif ($timedOut)
                                    <span class="text-text-muted">Timed out</span>
                                @else
                                    <span class="text-accent font-medium">Active</span>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="px-5 py-4">
                                @if ($hasEnded)
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-success-lightest text-success-foreground">Normal exit</span>
                                @elseif ($timedOut)
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-warning-light text-warning">Timed out</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-accent-muted text-accent">Active</span>
                                @endif
                            </td>
                        </tr>

                        {{-- Expanded detail row --}}
                        <tr x-show="expanded" x-cloak class="bg-surface-secondary/40">
                            <td></td>
                            <td colspan="5" class="px-5 py-4">
                                <dl class="grid grid-cols-2 sm:grid-cols-4 gap-x-8 gap-y-3 text-sm">
                                    <div>
                                        <dt class="text-xs font-medium text-text-muted uppercase tracking-wide mb-0.5">Started At</dt>
                                        <dd class="text-text-primary font-mono text-xs">{{ $log->started_at->format('d M Y H:i:s') }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-medium text-text-muted uppercase tracking-wide mb-0.5">Ended At</dt>
                                        <dd class="text-text-primary font-mono text-xs">
                                            @if ($log->ended_at)
                                                {{ $log->ended_at->format('d M Y H:i:s') }}
                                            @elseif ($timedOut)
                                                <span class="text-warning">Session expired (no explicit exit)</span>
                                            @else
                                                <span class="text-accent">Still active</span>
                                            @endif
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-medium text-text-muted uppercase tracking-wide mb-0.5">Duration</dt>
                                        <dd class="text-text-primary">
                                            @if ($hasEnded)
                                                @if ($durationMin < 1)
                                                    Less than 1 minute
                                                @else
                                                    {{ $durationMin }} minute{{ $durationMin !== 1 ? 's' : '' }}
                                                @endif
                                            @elseif ($timedOut)
                                                Over 60 minutes (timed out)
                                            @else
                                                {{ $log->started_at->diffForHumans(null, true) }} (ongoing)
                                            @endif
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-medium text-text-muted uppercase tracking-wide mb-0.5">Session ID</dt>
                                        <dd class="text-text-muted font-mono text-xs truncate" title="{{ $log->id }}">{{ substr($log->id, 0, 8) }}…</dd>
                                    </div>
                                </dl>
                            </td>
                        </tr>
                        </tbody>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($logs->hasPages())
                <div class="px-6 py-4 border-t border-border flex items-center justify-between gap-4">
                    <p class="text-xs text-text-muted">
                        Showing {{ $logs->firstItem() }}–{{ $logs->lastItem() }} of {{ $logs->total() }} sessions
                    </p>
                    <div class="flex items-center gap-1">
                        @if ($logs->onFirstPage())
                            <span class="px-3 py-1.5 text-xs text-text-muted bg-surface border border-border rounded-md cursor-not-allowed">← Prev</span>
                        @else
                            <a href="{{ $logs->previousPageUrl() }}"
                               class="px-3 py-1.5 text-xs text-text-secondary bg-surface border border-border rounded-md hover:bg-surface-secondary transition-colors">← Prev</a>
                        @endif

                        @foreach ($logs->getUrlRange(max(1, $logs->currentPage() - 2), min($logs->lastPage(), $logs->currentPage() + 2)) as $page => $url)
                            @if ($page === $logs->currentPage())
                                <span class="px-3 py-1.5 text-xs font-medium bg-accent text-accent-foreground rounded-md">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="px-3 py-1.5 text-xs text-text-secondary bg-surface border border-border rounded-md hover:bg-surface-secondary transition-colors">{{ $page }}</a>
                            @endif
                        @endforeach

                        @if ($logs->hasMorePages())
                            <a href="{{ $logs->nextPageUrl() }}"
                               class="px-3 py-1.5 text-xs text-text-secondary bg-surface border border-border rounded-md hover:bg-surface-secondary transition-colors">Next →</a>
                        @else
                            <span class="px-3 py-1.5 text-xs text-text-muted bg-surface border border-border rounded-md cursor-not-allowed">Next →</span>
                        @endif
                    </div>
                </div>
            @endif
        @endif
    </div>

    <p class="mt-6 text-xs text-text-muted">
        Sessions are recorded automatically on every impersonation start and stop. A <span class="font-medium text-warning">Timed out</span> status means the Super Admin's browser session expired before they clicked Exit — the school's data was not accessible beyond the 1-hour window.
    </p>

</main>

</body>
</html>
