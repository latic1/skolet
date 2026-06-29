@extends('layouts.tenant')

@section('title', 'Settings â€” Audit Log')
@section('page-title', 'Settings')

@section('content')
@php $host = request()->getSchemeAndHttpHost(); @endphp
<div class="flex flex-col gap-6">

    @include('partials.settings-tabs')

    {{-- Filter Card --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card p-5">
        <form method="GET" action="{{ $host }}/settings/audit-log" class="flex flex-wrap items-end gap-4">
            {{-- Date From --}}
            <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-text-secondary">From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent">
            </div>

            {{-- Date To --}}
            <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-text-secondary">To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent">
            </div>

            {{-- User --}}
            <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-text-secondary">User</label>
                <select name="causer_id"
                        class="px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent">
                    <option value="">All users</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected(request('causer_id') === $user->id)>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Record Type --}}
            <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-text-secondary">Record Type</label>
                <select name="log_name"
                        class="px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent">
                    <option value="">All types</option>
                    @foreach($logNames as $key => $label)
                        <option value="{{ $key }}" @selected(request('log_name') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit"
                    class="px-4 py-2 text-sm font-medium bg-accent text-accent-foreground rounded-md hover:bg-accent-dark transition-colors">
                Filter
            </button>

            @if(request()->hasAny(['date_from', 'date_to', 'causer_id', 'log_name']))
            <a href="{{ $host }}/settings/audit-log"
               class="px-4 py-2 text-sm font-medium bg-surface border border-border text-text-primary rounded-md hover:bg-surface-secondary transition-colors">
                Clear
            </a>
            @endif
        </form>
    </div>

    {{-- Log Table --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card">
        <div class="flex items-center justify-between px-6 py-4 border-b border-border">
            <div>
                <h2 class="text-base font-semibold text-text-primary">Activity Log</h2>
                <p class="text-xs text-text-muted mt-0.5">{{ $logs->total() }} {{ Str::plural('entry', $logs->total()) }}</p>
            </div>
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span class="text-xs text-text-muted">Logs kept for 90 days</span>
            </div>
        </div>

        @if($logs->isEmpty())
        <div class="flex flex-col items-center justify-center py-16 text-center px-6">
            <div class="w-12 h-12 rounded-xl bg-surface-secondary flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-text-primary mb-1">No activity yet</p>
            <p class="text-xs text-text-muted">Changes to students, staff, fees, and other records will appear here.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full" style="min-width: 700px">
                <thead>
                    <tr class="border-b border-border bg-surface-secondary">
                        <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Date</th>
                        <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">User</th>
                        <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Action</th>
                        <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Record Type</th>
                        <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary hidden md:table-cell">Summary</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                    @php
                        $event     = $log->event ?? $log->description ?? 'updated';
                        $logLabel  = $logNames[$log->log_name] ?? ucfirst(str_replace('_', ' ', $log->log_name ?? ''));
                        $changes   = collect($log->properties['attributes'] ?? [])
                                        ->except(['updated_at', 'created_at'])
                                        ->take(2);
                        $eventClass = match($event) {
                            'created' => 'bg-success-lightest text-success-foreground',
                            'deleted' => 'bg-error-light text-error',
                            default   => 'bg-accent-muted text-accent',
                        };
                    @endphp
                    <tr class="border-b border-border last:border-b-0 hover:bg-surface-secondary transition-colors">
                        {{-- Date --}}
                        <td class="px-6 py-4 text-sm text-text-primary whitespace-nowrap">
                            {{ $log->created_at->format('d M Y') }}
                            <div class="text-xs text-text-muted">{{ $log->created_at->format('H:i') }}</div>
                        </td>

                        {{-- User --}}
                        <td class="px-6 py-4 text-sm">
                            @if($log->causer)
                                <div class="font-medium text-text-primary">{{ $log->causer->name }}</div>
                                <div class="text-xs text-text-muted truncate max-w-[160px]">{{ $log->causer->email }}</div>
                            @else
                                <span class="text-text-muted text-xs">System</span>
                            @endif
                        </td>

                        {{-- Action --}}
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $eventClass }}">
                                {{ ucfirst($event) }}
                            </span>
                        </td>

                        {{-- Record Type --}}
                        <td class="px-6 py-4 text-sm text-text-primary">
                            {{ $logLabel }}
                        </td>

                        {{-- Summary --}}
                        <td class="px-6 py-4 hidden md:table-cell">
                            @if($changes->isNotEmpty())
                                <div class="flex flex-col gap-0.5">
                                    @foreach($changes as $field => $value)
                                        <span class="text-xs text-text-secondary">
                                            <span class="font-medium text-text-primary">{{ str_replace('_', ' ', $field) }}</span>:
                                            {{ is_array($value) ? json_encode($value) : Str::limit((string) $value, 40) }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-xs text-text-muted">â€”</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($logs->hasPages())
        <div class="px-6 py-4 border-t border-border">
            {{ $logs->links() }}
        </div>
        @endif
        @endif
    </div>

</div>
@endsection
