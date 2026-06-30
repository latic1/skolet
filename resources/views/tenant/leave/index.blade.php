@extends('layouts.tenant')

@section('title', 'Leave Management')

@section('content')
@php
    $base = request()->getSchemeAndHttpHost();
    $leaveTypes = [
        'sick'      => 'Sick Leave',
        'annual'    => 'Annual Leave',
        'maternity' => 'Maternity Leave',
        'paternity' => 'Paternity Leave',
        'personal'  => 'Personal Leave',
        'other'     => 'Other',
    ];
@endphp

<div
    x-data="leavePage('{{ request()->get('tab', $canManage ? 'all' : 'my') }}')"
    class="flex flex-col gap-6"
>
    {{-- Page header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-text-primary">Leave Management</h1>
            <p class="text-sm text-text-muted mt-0.5">Submit and track leave requests</p>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-success-lightest border border-success-light text-success-foreground text-sm font-medium">
        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-error-light border border-error text-error text-sm font-medium">
        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- Tabs --}}
    <div class="border-b border-border">
        <nav class="flex gap-6" aria-label="Leave tabs">
            <button type="button"
                    @click="tab = 'my'"
                    :class="tab === 'my' ? 'border-b-2 border-accent text-accent font-semibold' : 'border-b-2 border-transparent text-text-secondary hover:text-text-primary'"
                    class="pb-3 text-sm transition-colors">
                My Requests
            </button>
            @if($canManage)
            <button type="button"
                    @click="tab = 'all'"
                    :class="tab === 'all' ? 'border-b-2 border-accent text-accent font-semibold' : 'border-b-2 border-transparent text-text-secondary hover:text-text-primary'"
                    class="pb-3 text-sm transition-colors flex items-center gap-2">
                All Requests
                @if($pendingRequests->isNotEmpty())
                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-warning text-white text-[10px] font-bold">{{ $pendingRequests->count() }}</span>
                @endif
            </button>
            @endif
        </nav>
    </div>

    {{-- ── MY REQUESTS TAB ── --}}
    <div x-show="tab === 'my'" x-cloak>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Submit form --}}
            <div class="lg:col-span-1">
                <div class="bg-surface border border-border rounded-2xl shadow-card p-6">
                    <h2 class="text-base font-semibold text-text-primary mb-4">New Leave Request</h2>
                    @if($currentStaff)
                    <form method="POST" action="{{ $base . '/leave' }}" class="flex flex-col gap-4">
                        @csrf
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold text-text-secondary uppercase tracking-wide">Leave Type</label>
                            <select name="leave_type" required
                                    class="w-full px-3 py-2 rounded-lg border border-border bg-surface text-sm text-text-primary focus:outline-none focus:ring-2 focus:ring-accent @error('leave_type') border-error @enderror">
                                <option value="">Select type&hellip;</option>
                                @foreach($leaveTypes as $val => $label)
                                <option value="{{ $val }}" {{ old('leave_type') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('leave_type') <span class="text-xs text-error">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-semibold text-text-secondary uppercase tracking-wide">Start Date</label>
                                <input type="date" name="start_date" value="{{ old('start_date') }}" required
                                       class="w-full px-3 py-2 rounded-lg border border-border bg-surface text-sm text-text-primary focus:outline-none focus:ring-2 focus:ring-accent @error('start_date') border-error @enderror">
                                @error('start_date') <span class="text-xs text-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-semibold text-text-secondary uppercase tracking-wide">End Date</label>
                                <input type="date" name="end_date" value="{{ old('end_date') }}" required
                                       class="w-full px-3 py-2 rounded-lg border border-border bg-surface text-sm text-text-primary focus:outline-none focus:ring-2 focus:ring-accent @error('end_date') border-error @enderror">
                                @error('end_date') <span class="text-xs text-error">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold text-text-secondary uppercase tracking-wide">Reason</label>
                            <textarea name="reason" rows="4" required maxlength="1000"
                                      placeholder="Briefly describe the reason for your leave&hellip;"
                                      class="w-full px-3 py-2 rounded-lg border border-border bg-surface text-sm text-text-primary focus:outline-none focus:ring-2 focus:ring-accent resize-none @error('reason') border-error @enderror">{{ old('reason') }}</textarea>
                            @error('reason') <span class="text-xs text-error">{{ $message }}</span> @enderror
                        </div>

                        <button type="submit"
                                class="w-full py-2 px-4 rounded-xl bg-accent text-white text-sm font-semibold hover:bg-accent/90 transition-colors">
                            Submit Request
                        </button>
                    </form>
                    @else
                    <p class="text-sm text-text-muted">No staff profile is linked to your account. Contact an administrator.</p>
                    @endif
                </div>
            </div>

            {{-- My requests list --}}
            <div class="lg:col-span-2">
                <div class="bg-surface border border-border rounded-2xl shadow-card overflow-hidden">
                    <div class="px-6 py-4 border-b border-border">
                        <h2 class="text-base font-semibold text-text-primary">My Leave History</h2>
                    </div>

                    @if($myRequests->isEmpty())
                    <div class="flex flex-col items-center justify-center py-14 text-center gap-3">
                        <svg class="w-10 h-10 text-text-muted/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p class="text-sm text-text-muted">No leave requests yet.</p>
                    </div>
                    @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-border bg-muted/30">
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-text-muted uppercase tracking-wide">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-text-muted uppercase tracking-wide">Dates</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-text-muted uppercase tracking-wide hidden md:table-cell">Days</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-text-muted uppercase tracking-wide">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-text-muted uppercase tracking-wide hidden lg:table-cell">Reason</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                @foreach($myRequests as $req)
                                <tr class="hover:bg-muted/20 transition-colors">
                                    <td class="px-6 py-3.5 font-medium text-text-primary">{{ $leaveTypes[$req->leave_type] ?? ucfirst($req->leave_type) }}</td>
                                    <td class="px-6 py-3.5 text-text-secondary whitespace-nowrap">
                                        {{ $req->start_date->format('d M Y') }}
                                        @if(!$req->start_date->equalTo($req->end_date))
                                        <span class="text-text-muted mx-1">&ndash;</span>{{ $req->end_date->format('d M Y') }}
                                        @endif
                                    </td>
                                    <td class="px-6 py-3.5 text-text-secondary hidden md:table-cell">{{ $req->leave_days }}d</td>
                                    <td class="px-6 py-3.5">
                                        @if($req->status === 'pending')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-warning-light text-warning">Pending</span>
                                        @elseif($req->status === 'approved')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-lightest text-success-foreground">Approved</span>
                                        @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-error-light text-error">Rejected</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3.5 text-text-secondary max-w-xs hidden lg:table-cell">
                                        <p class="truncate">{{ $req->reason }}</p>
                                        @if($req->status === 'rejected' && $req->rejection_reason)
                                        <p class="text-xs text-error mt-0.5">Rejection reason: {{ $req->rejection_reason }}</p>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($myRequests->hasPages())
                    <div class="px-6 py-4 border-t border-border">
                        {{ $myRequests->links() }}
                    </div>
                    @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ── ALL REQUESTS TAB ── --}}
    @if($canManage)
    <div x-show="tab === 'all'" x-cloak class="flex flex-col gap-6">

        {{-- Pending requests --}}
        <div class="bg-surface border border-border rounded-2xl shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-border flex items-center justify-between">
                <h2 class="text-base font-semibold text-text-primary">Pending Requests</h2>
                @if($pendingRequests->isNotEmpty())
                <span class="text-xs font-medium text-text-muted">{{ $pendingRequests->count() }} awaiting action</span>
                @endif
            </div>

            @if($pendingRequests->isEmpty())
            <div class="flex flex-col items-center justify-center py-10 text-center gap-2">
                <svg class="w-8 h-8 text-success-foreground/50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm text-text-muted">No pending leave requests.</p>
            </div>
            @else
            <div class="overflow-x-auto" x-data="{ rejectId: null, rejectReason: '' }">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/30">
                            <th class="px-6 py-3 text-left text-xs font-semibold text-text-muted uppercase tracking-wide">Staff</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-text-muted uppercase tracking-wide">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-text-muted uppercase tracking-wide">Dates</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-text-muted uppercase tracking-wide hidden md:table-cell">Days</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-text-muted uppercase tracking-wide hidden lg:table-cell">Reason</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-text-muted uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach($pendingRequests as $req)
                        <tr class="hover:bg-muted/20 transition-colors">
                            <td class="px-6 py-3.5">
                                <span class="font-medium text-text-primary">{{ $req->staff->full_name }}</span>
                                <span class="block text-xs text-text-muted">{{ $req->staff->role_title ?? '' }}</span>
                            </td>
                            <td class="px-6 py-3.5 text-text-secondary">{{ $leaveTypes[$req->leave_type] ?? ucfirst($req->leave_type) }}</td>
                            <td class="px-6 py-3.5 text-text-secondary whitespace-nowrap">
                                {{ $req->start_date->format('d M Y') }}
                                @if(!$req->start_date->equalTo($req->end_date))
                                <span class="text-text-muted mx-1">&ndash;</span>{{ $req->end_date->format('d M Y') }}
                                @endif
                            </td>
                            <td class="px-6 py-3.5 text-text-secondary hidden md:table-cell">{{ $req->leave_days }}d</td>
                            <td class="px-6 py-3.5 text-text-secondary max-w-xs hidden lg:table-cell">
                                <p class="truncate">{{ $req->reason }}</p>
                            </td>
                            <td class="px-6 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    {{-- Approve --}}
                                    <form method="POST" action="{{ $base . '/leave/' . $req->id . '/approve' }}"
                                          onsubmit="return confirm('Approve this leave request?')">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                                class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-success-lightest text-success-foreground border border-success-light hover:bg-success-light transition-colors">
                                            Approve
                                        </button>
                                    </form>
                                    {{-- Reject toggle --}}
                                    <button type="button"
                                            @click="rejectId = (rejectId === '{{ $req->id }}' ? null : '{{ $req->id }}'); rejectReason = ''"
                                            :class="rejectId === '{{ $req->id }}' ? 'bg-error-light text-error border-error' : 'bg-surface text-error border-border hover:bg-error-light hover:border-error'"
                                            class="px-3 py-1.5 text-xs font-semibold rounded-lg border transition-colors">
                                        Reject
                                    </button>
                                </div>
                                {{-- Inline reject form --}}
                                <div x-show="rejectId === '{{ $req->id }}'"
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0 -translate-y-1"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     class="mt-2 flex flex-col gap-2 items-end">
                                    <form method="POST" action="{{ $base . '/leave/' . $req->id . '/reject' }}"
                                          class="w-full max-w-xs"
                                          @submit.prevent="if(rejectReason.trim()) $el.submit()">
                                        @csrf @method('PATCH')
                                        <textarea
                                            name="rejection_reason"
                                            x-model="rejectReason"
                                            rows="2"
                                            required
                                            maxlength="500"
                                            placeholder="Reason for rejection&hellip;"
                                            class="w-full px-3 py-2 rounded-lg border border-error bg-surface text-xs text-text-primary focus:outline-none focus:ring-1 focus:ring-error resize-none"
                                        ></textarea>
                                        <div class="flex gap-2 mt-1 justify-end">
                                            <button type="button" @click="rejectId = null; rejectReason = ''"
                                                    class="px-2.5 py-1 text-xs text-text-secondary hover:text-text-primary border border-border rounded-lg transition-colors">
                                                Cancel
                                            </button>
                                            <button type="submit"
                                                    class="px-2.5 py-1 text-xs font-semibold bg-error text-white rounded-lg hover:bg-error/90 transition-colors">
                                                Confirm Reject
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        {{-- History --}}
        <div class="bg-surface border border-border rounded-2xl shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-border">
                <h2 class="text-base font-semibold text-text-primary">Request History</h2>
            </div>

            @if($historyRequests->isEmpty())
            <div class="flex flex-col items-center justify-center py-10 text-center gap-2">
                <p class="text-sm text-text-muted">No approved or rejected requests yet.</p>
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/30">
                            <th class="px-6 py-3 text-left text-xs font-semibold text-text-muted uppercase tracking-wide">Staff</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-text-muted uppercase tracking-wide">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-text-muted uppercase tracking-wide">Dates</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-text-muted uppercase tracking-wide hidden md:table-cell">Days</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-text-muted uppercase tracking-wide">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-text-muted uppercase tracking-wide hidden lg:table-cell">Decided by</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach($historyRequests as $req)
                        <tr class="hover:bg-muted/20 transition-colors">
                            <td class="px-6 py-3.5">
                                <span class="font-medium text-text-primary">{{ $req->staff->full_name }}</span>
                            </td>
                            <td class="px-6 py-3.5 text-text-secondary">{{ $leaveTypes[$req->leave_type] ?? ucfirst($req->leave_type) }}</td>
                            <td class="px-6 py-3.5 text-text-secondary whitespace-nowrap">
                                {{ $req->start_date->format('d M Y') }}
                                @if(!$req->start_date->equalTo($req->end_date))
                                <span class="text-text-muted mx-1">&ndash;</span>{{ $req->end_date->format('d M Y') }}
                                @endif
                            </td>
                            <td class="px-6 py-3.5 text-text-secondary hidden md:table-cell">{{ $req->leave_days }}d</td>
                            <td class="px-6 py-3.5">
                                @if($req->status === 'approved')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-lightest text-success-foreground">Approved</span>
                                @else
                                <div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-error-light text-error">Rejected</span>
                                    @if($req->rejection_reason)
                                    <p class="text-xs text-text-muted mt-0.5 max-w-[180px] truncate" title="{{ $req->rejection_reason }}">{{ $req->rejection_reason }}</p>
                                    @endif
                                </div>
                                @endif
                            </td>
                            <td class="px-6 py-3.5 text-text-secondary hidden lg:table-cell">
                                {{ $req->approvedBy?->name ?? '—' }}
                                @if($req->approved_at)
                                <span class="block text-xs text-text-muted">{{ $req->approved_at->format('d M Y') }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($historyRequests->hasPages())
            <div class="px-6 py-4 border-t border-border">
                {{ $historyRequests->links() }}
            </div>
            @endif
            @endif
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
    function leavePage(initialTab) {
        return {
            tab: initialTab,
        };
    }
</script>
@endpush
@endsection
