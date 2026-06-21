@extends('layouts.tenant')

@section('title', 'My Timetable')
@section('page-title', 'My Timetable')

@section('content')
@php
    $entriesJson = json_encode($entriesForView, JSON_HEX_TAG);
@endphp

<div class="flex flex-col gap-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-base font-semibold text-text-primary">
                @if($isAdmin && $selectedTeacher)
                    {{ $selectedTeacher->full_name }}'s Timetable
                @elseif($selectedTeacher)
                    My Timetable
                @else
                    Teacher Timetable
                @endif
            </h2>
            <p class="text-xs text-text-muted mt-0.5">Weekly schedule across all assigned classes</p>
        </div>
        <a href="{{ $host }}/timetable"
           class="flex items-center gap-2 px-4 py-2 bg-surface border border-border text-text-primary text-sm font-medium rounded-md hover:bg-surface-secondary transition-colors">
            <svg class="w-4 h-4 text-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
            </svg>
            Class Timetable
        </a>
    </div>

    {{-- Teacher selector (admin only) --}}
    @if($isAdmin)
    <div class="bg-surface border border-border rounded-2xl shadow-card p-5">
        <form method="GET" action="{{ $host }}/timetable/my" class="flex flex-wrap items-end gap-4">
            <div class="flex flex-col gap-1.5 min-w-[220px]">
                <label class="text-xs font-medium text-text-dark">View timetable for</label>
                <select name="teacher_id"
                        class="px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                    <option value="">Select teacher…</option>
                    @foreach($allStaff as $member)
                    <option value="{{ $member->id }}" @selected($teacherId === $member->id)>{{ $member->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                    class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors">
                Load
            </button>
        </form>
    </div>
    @endif

    {{-- Timetable grid --}}
    @if($teacherId && $selectedTeacher)

        @php
            $filledCount = count($entriesForView);
        @endphp

        <div class="bg-surface border border-border rounded-2xl shadow-card">

            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-border">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-accent-muted flex items-center justify-center shrink-0">
                        <span class="text-sm font-semibold text-accent">
                            {{ strtoupper(mb_substr($selectedTeacher->full_name, 0, 1)) }}
                        </span>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-text-primary">{{ $selectedTeacher->full_name }}</p>
                        <p class="text-xs text-text-muted">{{ $selectedTeacher->role_title }} &middot; {{ $filledCount }} {{ Str::plural('period', $filledCount) }} assigned</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-surface-secondary text-text-secondary text-xs font-medium">
                    Read-only
                </span>
            </div>

            {{-- Scrollable grid --}}
            <div class="overflow-x-auto">
                <table class="min-w-full" style="min-width: 900px;">
                    <thead>
                        <tr class="border-b border-border bg-surface-secondary">
                            <th class="text-left px-4 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary w-28 shrink-0">Day</th>
                            @foreach($periods as $period)
                            <th class="text-center px-3 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary min-w-[130px]">
                                P{{ $period }}
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($days as $day)
                        <tr class="border-b border-border last:border-b-0 hover:bg-surface-secondary/50 transition-colors">
                            {{-- Day label --}}
                            <td class="px-4 py-3 w-28 shrink-0">
                                <span class="text-sm font-semibold text-text-primary">{{ substr($day, 0, 3) }}</span>
                                <span class="block text-xs text-text-muted">{{ substr($day, 3) }}</span>
                            </td>

                            {{-- Period cells --}}
                            @foreach($periods as $period)
                            @php
                                $key   = $day . '-' . $period;
                                $entry = $entriesForView[$key] ?? null;
                            @endphp
                            <td class="px-2 py-2 min-w-[130px]">
                                @if($entry)
                                <div class="rounded-xl border border-success-light bg-success-lightest p-2.5 min-h-[68px] flex flex-col justify-between">
                                    <div>
                                        <p class="text-xs font-semibold text-success-foreground leading-snug">{{ $entry['subject_name'] }}</p>
                                        <p class="text-xs text-text-secondary mt-0.5 leading-snug">
                                            {{ $entry['class_name'] }}{{ $entry['section_name'] ? ' — ' . $entry['section_name'] : '' }}
                                        </p>
                                    </div>
                                </div>
                                @else
                                <div class="rounded-xl border border-dashed border-border min-h-[68px] flex items-center justify-center">
                                    <span class="text-xs text-text-muted">—</span>
                                </div>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Summary footer --}}
            @if($filledCount > 0)
            <div class="px-6 py-3 border-t border-border bg-surface-secondary rounded-b-2xl">
                <p class="text-xs text-text-muted">
                    {{ $filledCount }} {{ Str::plural('period', $filledCount) }} assigned across
                    {{ collect($entriesForView)->pluck('class_name')->unique()->count() }}
                    {{ Str::plural('class', collect($entriesForView)->pluck('class_name')->unique()->count()) }}
                    this week.
                </p>
            </div>
            @endif
        </div>

    @elseif($teacherId && !$selectedTeacher)
    {{-- Teacher not found --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card">
        <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
            <p class="text-sm font-medium text-text-primary mb-1">Teacher not found</p>
            <p class="text-xs text-text-muted">The selected staff member could not be found.</p>
        </div>
    </div>

    @elseif(!$isAdmin && !$teacherId)
    {{-- Teacher has no staff record linked --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card">
        <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
            <div class="w-12 h-12 rounded-xl bg-accent-muted flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-text-primary mb-1">No timetable found</p>
            <p class="text-xs text-text-muted">Your account is not linked to a staff record, or no periods have been assigned yet.</p>
        </div>
    </div>

    @else
    {{-- Admin with no teacher selected --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card">
        <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
            <div class="w-12 h-12 rounded-xl bg-accent-muted flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-text-primary mb-1">Select a teacher to view their schedule</p>
            <p class="text-xs text-text-muted">Choose a staff member from the dropdown above.</p>
        </div>
    </div>
    @endif

</div>
@endsection
