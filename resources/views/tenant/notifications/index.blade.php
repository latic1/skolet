@extends('layouts.tenant')

@section('title', 'Notifications')
@section('page-title', 'Notifications')

@section('content')
@php $host = request()->getSchemeAndHttpHost(); @endphp
<div class="flex flex-col gap-6">

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="bg-success-lightest border border-success-light text-success-foreground text-sm px-4 py-3 rounded-xl flex items-start gap-3">
        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    {{-- Page header --}}
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-base font-semibold text-text-primary">Notifications</h1>
            <p class="text-xs text-text-muted mt-0.5">{{ $notifications->total() }} {{ Str::plural('notification', $notifications->total()) }}</p>
        </div>
        @if($notifications->total() > 0)
        <form method="POST" action="{{ $host }}/notifications/read-all">
            @csrf
            @method('PATCH')
            <button type="submit"
                    class="px-4 py-2 bg-surface border border-border text-sm font-medium text-text-primary rounded-md hover:bg-surface-secondary transition-colors">
                Mark all as read
            </button>
        </form>
        @endif
    </div>

    {{-- Notifications list --}}
    @if($notifications->isEmpty())
    <div class="bg-surface border border-border rounded-2xl shadow-card">
        <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
            <div class="w-12 h-12 rounded-xl bg-accent-muted flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-text-primary mb-1">No notifications yet</p>
            <p class="text-xs text-text-muted">You'll see notifications here when announcements are posted.</p>
        </div>
    </div>
    @else
    <div class="bg-surface border border-border rounded-2xl shadow-card divide-y divide-border overflow-hidden">
        @foreach($notifications as $notification)
        <div class="flex items-start gap-4 px-5 py-4 {{ is_null($notification->read_at) ? 'bg-accent-muted/20' : '' }} hover:bg-surface-secondary transition-colors">

            {{-- Unread dot --}}
            <div class="shrink-0 mt-1.5">
                @if(is_null($notification->read_at))
                <div class="w-2 h-2 rounded-full bg-accent"></div>
                @else
                <div class="w-2 h-2 rounded-full bg-transparent"></div>
                @endif
            </div>

            {{-- Content --}}
            <div class="flex-1 min-w-0">
                <p class="text-sm {{ is_null($notification->read_at) ? 'font-semibold text-text-primary' : 'font-medium text-text-dark' }} leading-snug">
                    {{ $notification->message }}
                </p>
                @if($notification->announcement)
                <p class="text-xs text-text-muted mt-0.5 line-clamp-2">
                    {{ Str::limit($notification->announcement->body, 120) }}
                </p>
                @endif
                <p class="text-xs text-text-muted mt-1">{{ $notification->created_at->diffForHumans() }}</p>
            </div>

            {{-- Actions --}}
            <div class="shrink-0 flex items-center gap-2">
                @if($notification->announcement)
                <a href="{{ $host }}/announcements"
                   class="text-xs font-medium text-accent hover:text-accent-dark transition-colors whitespace-nowrap">
                    View →
                </a>
                @endif
                @if(is_null($notification->read_at))
                <form method="POST" action="{{ $host }}/notifications/{{ $notification->id }}/read">
                    @csrf
                    @method('PATCH')
                    <button type="submit"
                            class="text-xs text-text-muted hover:text-text-primary transition-colors whitespace-nowrap">
                        Mark read
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    @if($notifications->hasPages())
    <div>
        {{ $notifications->links() }}
    </div>
    @endif
    @endif

</div>
@endsection
