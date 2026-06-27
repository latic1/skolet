@extends('layouts.tenant')

@section('title', 'Webhook Deliveries')
@section('page-title', 'Settings')

@section('content')
@php $host = request()->getSchemeAndHttpHost(); @endphp

<div class="flex flex-col gap-6">

    {{-- Back / breadcrumb --}}
    <div class="flex items-center gap-3">
        <a href="{{ $host }}/settings/webhooks"
           class="flex items-center gap-1.5 text-sm text-text-muted hover:text-text-primary transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Webhooks
        </a>
        <svg class="w-3 h-3 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-sm text-text-primary font-medium truncate max-w-xs">{{ $webhook->url }}</span>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="flex items-center gap-3 bg-success-lightest border border-success-light text-success-foreground text-sm px-4 py-3 rounded-xl">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Webhook info --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card p-5">
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div>
                <p class="text-xs text-text-muted uppercase tracking-wide font-medium mb-1">Endpoint</p>
                <p class="font-mono text-sm text-text-primary break-all">{{ $webhook->url }}</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <div class="flex flex-wrap gap-1">
                    @foreach ($webhook->events as $ev)
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs bg-surface-secondary text-text-secondary border border-border">
                            {{ str_replace('_', ' ', $ev) }}
                        </span>
                    @endforeach
                </div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium
                             {{ $webhook->active ? 'bg-success-lightest text-success-foreground' : 'bg-surface-secondary text-text-muted border border-border' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $webhook->active ? 'bg-success-foreground' : 'bg-text-muted' }}"></span>
                    {{ $webhook->active ? 'Active' : 'Inactive' }}
                </span>
            </div>
        </div>
    </div>

    {{-- Deliveries table --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card overflow-hidden">
        <div class="px-5 py-4 border-b border-border flex items-center justify-between">
            <h2 class="text-sm font-semibold text-text-primary">Delivery Log</h2>
            <span class="text-xs text-text-muted">{{ $deliveries->total() }} total</span>
        </div>

        @if ($deliveries->isEmpty())
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <p class="text-sm text-text-muted">No deliveries recorded yet.</p>
                <p class="text-xs text-text-muted mt-1">Deliveries appear here once events are triggered.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm" style="min-width: 680px">
                    <thead>
                        <tr class="border-b border-border bg-surface-secondary">
                            <th class="px-5 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wide">Event</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wide">Status</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wide">Attempts</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wide">Timestamp</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach ($deliveries as $delivery)
                        <tr x-data="{ expanded: false }" class="hover:bg-surface-secondary/50 transition-colors">
                            {{-- Event --}}
                            <td class="px-5 py-3.5">
                                <span class="font-mono text-xs text-text-primary">{{ $delivery->event }}</span>
                            </td>

                            {{-- Status --}}
                            <td class="px-5 py-3.5">
                                @if ($delivery->isSuccess())
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-success-lightest text-success-foreground">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        {{ $delivery->response_status }}
                                    </span>
                                @elseif ($delivery->response_status)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-error-light text-error">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        {{ $delivery->response_status }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-warning-light text-warning">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Network error
                                    </span>
                                @endif
                            </td>

                            {{-- Attempts --}}
                            <td class="px-5 py-3.5">
                                <span class="text-xs text-text-muted">{{ $delivery->attempt_count }} / 3</span>
                                @if ($delivery->next_retry_at)
                                    <p class="text-xs text-text-muted mt-0.5">Retry {{ $delivery->next_retry_at->diffForHumans() }}</p>
                                @endif
                            </td>

                            {{-- Timestamp --}}
                            <td class="px-5 py-3.5">
                                <span class="text-xs text-text-primary">{{ $delivery->attempted_at->format('d M Y, H:i') }}</span>
                                <p class="text-xs text-text-muted">{{ $delivery->attempted_at->diffForHumans() }}</p>
                            </td>

                            {{-- Actions --}}
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="expanded = !expanded"
                                            class="px-2.5 py-1 text-xs font-medium rounded-md border border-border text-text-secondary bg-surface hover:bg-surface-secondary transition-colors">
                                        <span x-text="expanded ? 'Hide' : 'Details'">Details</span>
                                    </button>
                                    @if ($delivery->canRetry())
                                        <form method="POST" action="{{ $host }}/settings/webhooks/{{ $webhook->id }}/deliveries/{{ $delivery->id }}/retry">
                                            @csrf
                                            <button type="submit"
                                                    class="px-2.5 py-1 text-xs font-medium rounded-md border border-accent/30 text-accent bg-accent/5 hover:bg-accent/10 transition-colors">
                                                Retry
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        {{-- Expanded row: payload + response --}}
                        <tr x-show="expanded" x-cloak class="bg-surface-secondary">
                            <td colspan="5" class="px-5 pb-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                                    <div>
                                        <p class="text-xs font-medium text-text-muted mb-1 uppercase tracking-wide">Payload sent</p>
                                        <pre class="text-xs bg-surface border border-border rounded-lg p-3 overflow-x-auto text-text-primary max-h-40">{{ json_encode($delivery->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-text-muted mb-1 uppercase tracking-wide">Response body</p>
                                        <pre class="text-xs bg-surface border border-border rounded-lg p-3 overflow-x-auto text-text-primary max-h-40">{{ $delivery->response_body ?: '(empty)' }}</pre>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($deliveries->hasPages())
                <div class="px-5 py-4 border-t border-border">
                    {{ $deliveries->links() }}
                </div>
            @endif
        @endif
    </div>

</div>
@endsection
