@extends('layouts.tenant')

@section('title', 'Staff Attendance')
@section('page-title', 'Attendance')

@section('content')
@php
    $host    = request()->getSchemeAndHttpHost();
    $canEdit = auth()->user()->can('attendance.edit');

    $staffJson = $staffList->map(fn ($s) => [
        'id'         => $s->id,
        'full_name'  => $s->full_name,
        'role_title' => $s->role_title ?? '',
        'initial'    => strtoupper(mb_substr($s->full_name, 0, 1)),
    ])->values()->toJson();

    $existingJson = $existingRecords->map(fn ($r) => $r->status)->toJson();
@endphp

<div x-data="staffAttendanceSheet({{ $staffJson }}, {{ $existingJson }})" class="flex flex-col gap-6">

    {{-- Flash messages --}}

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-sm text-text-muted mb-1">
                <a href="{{ $host }}/attendance" class="hover:text-text-primary transition-colors">Attendance</a>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-text-primary font-medium">Staff Attendance</span>
            </div>
            <h2 class="text-base font-semibold text-text-primary">Staff Daily Attendance</h2>
            <p class="text-xs text-text-muted mt-0.5">Mark attendance for all active staff members</p>
        </div>
    </div>

    {{-- Date selector --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card p-5">
        <form method="GET" action="{{ $host }}/attendance/staff" class="flex items-end gap-4">
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-medium text-text-dark">Date</label>
                <input type="date" name="date" value="{{ $date }}" max="{{ now()->toDateString() }}"
                       class="px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
            </div>
            <button type="submit"
                    class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors">
                Load
            </button>
        </form>
    </div>

    {{-- Staff attendance sheet --}}
    @if($staffList->isEmpty())
    <div class="bg-surface border border-border rounded-2xl shadow-card">
        <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
            <div class="w-12 h-12 rounded-xl bg-surface-secondary flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-text-primary mb-1">No active staff found</p>
            <p class="text-xs text-text-muted">Add staff members to mark their attendance.</p>
        </div>
    </div>
    @else
    <div class="bg-surface border border-border rounded-2xl shadow-card">

        {{-- Sheet header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-border">
            <div>
                <h3 class="text-sm font-semibold text-text-primary">
                    {{ \Carbon\Carbon::parse($date)->format('D, d M Y') }}
                </h3>
                <p class="text-xs text-text-muted mt-0.5">
                    <span x-text="markedCount"></span>/{{ $staffList->count() }} staff marked
                </p>
            </div>
            @if($canEdit)
            <button type="button" @click="markAllPresent()"
                    class="px-3 py-1.5 text-xs font-medium bg-success-lightest text-success-foreground border border-success-light rounded-md hover:bg-success-light transition-colors">
                Mark all present
            </button>
            @endif
        </div>

        {{-- Table + form --}}
        <form method="POST" action="{{ $host }}/attendance/staff">
            @csrf
            <input type="hidden" name="date" value="{{ $date }}">

            <table class="w-full">
                <thead>
                    <tr class="border-b border-border">
                        <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary w-10">#</th>
                        <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Staff Member</th>
                        <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary hidden sm:table-cell">Role</th>
                        <th class="text-center px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Attendance</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($staffList as $i => $staffMember)
                    <tr class="border-b border-border last:border-b-0 hover:bg-surface-secondary transition-colors">
                        <td class="px-6 py-3.5 text-sm text-text-muted">{{ $i + 1 }}</td>
                        <td class="px-6 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-accent-muted flex items-center justify-center shrink-0">
                                    <span class="text-xs font-semibold text-accent">{{ strtoupper(mb_substr($staffMember->full_name, 0, 1)) }}</span>
                                </div>
                                <span class="text-sm font-medium text-text-primary">{{ $staffMember->full_name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-3.5 text-sm text-text-muted hidden sm:table-cell">{{ $staffMember->role_title ?? '—' }}</td>
                        <td class="px-6 py-3.5">
                            {{-- Hidden input tracks Alpine status --}}
                            <input type="hidden" name="statuses[{{ $staffMember->id }}]"
                                   :value="statuses['{{ $staffMember->id }}'] ?? ''">

                            <div class="flex items-center justify-center gap-2">
                                @if($canEdit)
                                {{-- Present --}}
                                <button type="button"
                                        @click="setStatus('{{ $staffMember->id }}', 'present')"
                                        :class="isActive('{{ $staffMember->id }}', 'present')
                                            ? 'bg-success-lightest text-success-foreground border-success-light font-semibold'
                                            : 'bg-surface text-text-secondary border-border hover:bg-success-lightest hover:text-success-foreground hover:border-success-light'"
                                        class="px-3 py-1.5 text-xs border rounded-md transition-colors min-w-[64px]">
                                    Present
                                </button>
                                {{-- Absent --}}
                                <button type="button"
                                        @click="setStatus('{{ $staffMember->id }}', 'absent')"
                                        :class="isActive('{{ $staffMember->id }}', 'absent')
                                            ? 'bg-error-light text-error border-error font-semibold'
                                            : 'bg-surface text-text-secondary border-border hover:bg-error-light hover:text-error hover:border-error'"
                                        class="px-3 py-1.5 text-xs border rounded-md transition-colors min-w-[60px]">
                                    Absent
                                </button>
                                {{-- Late --}}
                                <button type="button"
                                        @click="setStatus('{{ $staffMember->id }}', 'late')"
                                        :class="isActive('{{ $staffMember->id }}', 'late')
                                            ? 'bg-warning-light text-warning border-warning font-semibold'
                                            : 'bg-surface text-text-secondary border-border hover:bg-warning-light hover:text-warning hover:border-warning'"
                                        class="px-3 py-1.5 text-xs border rounded-md transition-colors min-w-[48px]">
                                    Late
                                </button>
                                {{-- On Leave --}}
                                <button type="button"
                                        @click="setStatus('{{ $staffMember->id }}', 'on_leave')"
                                        :class="isActive('{{ $staffMember->id }}', 'on_leave')
                                            ? 'bg-accent/10 text-accent border-accent font-semibold'
                                            : 'bg-surface text-text-secondary border-border hover:bg-accent/10 hover:text-accent hover:border-accent'"
                                        class="px-3 py-1.5 text-xs border rounded-md transition-colors min-w-[72px]">
                                    On Leave
                                </button>
                                @else
                                {{-- Read-only badge --}}
                                @php $record = $existingRecords->get($staffMember->id); @endphp
                                @if($record)
                                    @if($record->status === 'present')
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-success-lightest text-success-foreground">Present</span>
                                    @elseif($record->status === 'absent')
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-error-light text-error">Absent</span>
                                    @elseif($record->status === 'on_leave')
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-accent/10 text-accent">On Leave</span>
                                    @else
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-warning-light text-warning">Late</span>
                                    @endif
                                @else
                                <span class="text-xs text-text-muted">Not marked</span>
                                @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @if($canEdit)
            <div class="flex items-center justify-between px-6 py-4 border-t border-border">
                <p class="text-xs text-text-muted">
                    <span x-text="markedCount"></span> of {{ $staffList->count() }} staff marked
                </p>
                <button type="submit"
                        class="px-5 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors">
                    Save Staff Attendance
                </button>
            </div>
            @endif
        </form>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
    function staffAttendanceSheet(staff, existingRecords) {
        return {
            staff,
            statuses: {},

            init() {
                this.staff.forEach(s => {
                    this.statuses[s.id] = existingRecords[s.id] || null;
                });
            },

            setStatus(staffId, status) {
                this.statuses[staffId] = this.statuses[staffId] === status ? null : status;
            },

            isActive(staffId, status) {
                return this.statuses[staffId] === status;
            },

            markAllPresent() {
                this.staff.forEach(s => {
                    this.statuses[s.id] = 'present';
                });
            },

            get markedCount() {
                return Object.values(this.statuses).filter(v => v !== null).length;
            },
        };
    }
</script>
@endpush
