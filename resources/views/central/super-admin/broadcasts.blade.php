<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Platform Broadcasts — Skolet Super Admin</title>
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

    {{-- Nav --}}
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('super-admin.dashboard') }}"
           class="text-sm text-text-muted hover:text-accent transition-colors">← Back to Dashboard</a>
    </div>

    {{-- Page header --}}
    <div class="mb-8 flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-darkest">Platform Broadcasts</h1>
            <p class="text-sm text-text-muted mt-1">Send messages to all school admins — maintenance notices, feature announcements, or urgent alerts.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="broadcastsPage()">

        {{-- Compose form --}}
        <div class="lg:col-span-1">
            <div class="bg-surface border border-border rounded-2xl shadow-card overflow-hidden">
                <div class="px-6 py-4 border-b border-border">
                    <h2 class="font-semibold text-text-darkest">Compose Broadcast</h2>
                    <p class="text-xs text-text-muted mt-0.5">Delivered to all active school admins</p>
                </div>
                <form method="POST" action="{{ route('super-admin.broadcasts.store') }}" @submit="submitting = true" class="p-6 flex flex-col gap-4">
                    @csrf

                    {{-- Subject --}}
                    <div>
                        <label class="block text-sm font-medium text-text-dark mb-1.5">Subject</label>
                        <input type="text" name="subject" value="{{ old('subject') }}" required maxlength="255"
                               placeholder="e.g. Scheduled maintenance this weekend"
                               class="w-full px-3 py-2 bg-surface border {{ $errors->has('subject') ? 'border-error' : 'border-border' }} rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                        @error('subject')
                            <p class="mt-1 text-xs text-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Message --}}
                    <div>
                        <label class="block text-sm font-medium text-text-dark mb-1.5">Message</label>
                        <textarea name="message" rows="5" required maxlength="5000"
                                  placeholder="Describe the notice, update, or alert…"
                                  class="w-full px-3 py-2 bg-surface border {{ $errors->has('message') ? 'border-error' : 'border-border' }} rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors resize-y">{{ old('message') }}</textarea>
                        @error('message')
                            <p class="mt-1 text-xs text-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Severity --}}
                    <div>
                        <label class="block text-sm font-medium text-text-dark mb-1.5">Severity</label>
                        <select name="severity" x-model="severity"
                                class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                            <option value="info" {{ old('severity', 'info') === 'info' ? 'selected' : '' }}>ℹ Info — dismissible dashboard card</option>
                            <option value="warning" {{ old('severity') === 'warning' ? 'selected' : '' }}>⚠ Warning — dismissible dashboard card</option>
                            <option value="critical" {{ old('severity') === 'critical' ? 'selected' : '' }}>🚨 Critical — non-dismissible top banner</option>
                        </select>
                        <div class="mt-2 text-xs rounded-lg px-3 py-2 leading-relaxed"
                             :class="severity === 'critical' ? 'bg-error-light text-error' : (severity === 'warning' ? 'bg-warning-light text-warning' : 'bg-accent-muted text-accent')">
                            <template x-if="severity === 'info'">
                                <span>School admins see a dismissible info card on their dashboard.</span>
                            </template>
                            <template x-if="severity === 'warning'">
                                <span>School admins see a dismissible warning card on their dashboard.</span>
                            </template>
                            <template x-if="severity === 'critical'">
                                <span>School admins see a <strong>non-dismissible banner</strong> at the top of every page until you delete the broadcast.</span>
                            </template>
                        </div>
                    </div>

                    {{-- Schedule --}}
                    <div>
                        <label class="block text-sm font-medium text-text-dark mb-1.5">Send</label>
                        <select x-model="scheduleMode"
                                class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors mb-2">
                            <option value="now">Send Now</option>
                            <option value="schedule">Schedule for later</option>
                        </select>
                        <div x-show="scheduleMode === 'schedule'" style="display:none">
                            <input type="datetime-local" name="send_at"
                                   value="{{ old('send_at') }}"
                                   :required="scheduleMode === 'schedule'"
                                   class="w-full px-3 py-2 bg-surface border {{ $errors->has('send_at') ? 'border-error' : 'border-border' }} rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                            @error('send_at')
                                <p class="mt-1 text-xs text-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <button type="submit" :disabled="submitting"
                            class="w-full px-4 py-2.5 text-sm font-medium bg-accent text-accent-foreground rounded-md hover:bg-accent-dark transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
                        <span x-show="!submitting">Send Broadcast</span>
                        <span x-show="submitting" style="display:none">Sending…</span>
                    </button>
                </form>
            </div>
        </div>

        {{-- Sent broadcasts table --}}
        <div class="lg:col-span-2">
            <div class="bg-surface border border-border rounded-2xl shadow-card overflow-hidden">
                <div class="px-6 py-4 border-b border-border flex items-center justify-between">
                    <div>
                        <h2 class="font-semibold text-text-darkest">Sent Broadcasts</h2>
                        <p class="text-xs text-text-muted mt-0.5">{{ $broadcasts->count() }} total</p>
                    </div>
                </div>

                @if($broadcasts->isEmpty())
                    <div class="flex flex-col items-center justify-center py-16">
                        <div class="w-12 h-12 rounded-xl bg-accent-muted flex items-center justify-center mb-3">
                            <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-text-dark">No broadcasts sent yet</p>
                        <p class="text-xs text-text-muted mt-1">Use the compose form to send your first broadcast.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm" style="min-width: 500px">
                            <thead>
                                <tr class="border-b border-border bg-surface-secondary">
                                    <th class="px-5 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wide">Subject</th>
                                    <th class="px-5 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wide">Severity</th>
                                    <th class="px-5 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wide">Status</th>
                                    <th class="px-5 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wide">Sent / Scheduled</th>
                                    <th class="px-5 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wide">Recipients</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                @foreach($broadcasts as $broadcast)
                                <tr class="hover:bg-surface-secondary/50 transition-colors">
                                    <td class="px-5 py-4">
                                        <div class="font-medium text-text-darkest">{{ $broadcast->subject }}</div>
                                        <div class="text-xs text-text-muted mt-0.5 line-clamp-2">{{ Str::limit($broadcast->message, 100) }}</div>
                                    </td>
                                    <td class="px-5 py-4">
                                        @if($broadcast->severity === 'critical')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-error-light text-error border border-error/20">🚨 Critical</span>
                                        @elseif($broadcast->severity === 'warning')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-warning-light text-warning border border-warning/20">⚠ Warning</span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-accent-muted text-accent border border-accent/20">ℹ Info</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        @if($broadcast->sent_at)
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-success-lightest text-success-foreground">Sent</span>
                                        @elseif($broadcast->send_at && $broadcast->send_at->isFuture())
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-accent-muted text-accent">Scheduled</span>
                                        @else
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-surface-secondary text-text-muted">Pending</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-text-secondary">
                                        @if($broadcast->sent_at)
                                            {{ $broadcast->sent_at->format('d M Y H:i') }}
                                        @elseif($broadcast->send_at)
                                            {{ $broadcast->send_at->format('d M Y H:i') }}
                                        @else
                                            <span class="text-text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        @if($broadcast->sent_at)
                                            <span class="text-text-primary font-medium">{{ $broadcast->notifications()->count() }}</span>
                                            <span class="text-xs text-text-muted"> schools</span>
                                        @else
                                            <span class="text-text-muted text-xs">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

    </div>

</main>

<script>
function broadcastsPage() {
    return {
        severity: '{{ old('severity', 'info') }}',
        scheduleMode: '{{ old('send_at') ? 'schedule' : 'now' }}',
        submitting: false,
    };
}
</script>

</body>
</html>
