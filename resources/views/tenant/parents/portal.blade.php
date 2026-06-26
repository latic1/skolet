@extends('layouts.tenant')

@section('title', 'My Children')
@section('page-title', 'My Children')

@section('content')
@php $host = request()->getSchemeAndHttpHost(); @endphp
<div class="flex flex-col gap-6 max-w-4xl">

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="bg-success-lightest border border-success-light text-success-foreground text-sm px-4 py-3 rounded-xl flex items-start gap-3">
        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="bg-error-light border border-error/20 text-error text-sm px-4 py-3 rounded-xl">
        {{ session('error') }}
    </div>
    @endif

    {{-- No children linked --}}
    @if($children->isEmpty())
    <div class="bg-surface border border-border rounded-2xl shadow-card p-12 text-center">
        <div class="w-14 h-14 rounded-2xl bg-surface-secondary flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>
        <h3 class="text-base font-semibold text-text-primary mb-2">No children linked</h3>
        <p class="text-sm text-text-muted max-w-sm mx-auto">Your account has not been linked to any students yet. Please contact the school administration.</p>
    </div>
    @else

    {{-- Child selector tabs (multiple children) --}}
    @if($children->count() > 1)
    <div class="flex flex-wrap gap-2">
        @foreach($children as $child)
        <a href="{{ $host }}/my-children?child_id={{ $child->id }}"
           class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium transition-colors
                  {{ $childId === $child->id ? 'bg-accent text-accent-foreground shadow-sm' : 'bg-surface border border-border text-text-primary hover:bg-surface-secondary' }}">
            <span class="w-6 h-6 rounded-lg bg-white/20 flex items-center justify-center text-xs font-semibold shrink-0
                         {{ $childId === $child->id ? 'text-accent-foreground' : 'bg-accent-muted text-accent' }}">
                {{ mb_strtoupper(mb_substr($child->full_name, 0, 1)) }}
            </span>
            {{ $child->full_name }}
        </a>
        @endforeach
    </div>
    @endif

    @if($selectedChild)

    {{-- Child profile header --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card p-6">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-accent-muted flex items-center justify-center shrink-0">
                <span class="text-xl font-semibold text-accent">{{ mb_strtoupper(mb_substr($selectedChild->full_name, 0, 1)) }}</span>
            </div>
            <div>
                <h2 class="text-base font-semibold text-text-primary">{{ $selectedChild->full_name }}</h2>
                <p class="text-sm text-text-muted mt-0.5">{{ $selectedChild->admission_no }}</p>
                <div class="flex items-center gap-2 mt-2">
                    @php
                        $statusClass = match($selectedChild->status) {
                            'active'    => 'bg-success-lightest text-success-foreground',
                            'inactive'  => 'bg-surface-secondary text-text-secondary',
                            'graduated' => 'bg-accent-muted text-accent',
                            default     => 'bg-surface-secondary text-text-secondary',
                        };
                    @endphp
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                        {{ ucfirst($selectedChild->status) }}
                    </span>
                    @if($selectedChild->schoolClass)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-accent-muted text-accent">
                        {{ $selectedChild->schoolClass->name }}{{ $selectedChild->section ? ' — ' . $selectedChild->section->name : '' }}
                    </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Attendance this month --}}
        <div class="bg-surface border border-border rounded-2xl shadow-card p-6">
            <h3 class="text-base font-semibold text-text-primary mb-4">Attendance — {{ now()->format('F Y') }}</h3>

            @if($attendanceSummary->isEmpty())
            <p class="text-sm text-text-muted">No attendance records for this month yet.</p>
            @else
            <div class="grid grid-cols-3 gap-3">
                @php
                    $present = $attendanceSummary->get('present', 0);
                    $absent  = $attendanceSummary->get('absent', 0);
                    $late    = $attendanceSummary->get('late', 0);
                @endphp
                <div class="text-center p-3 bg-success-lightest rounded-xl">
                    <div class="text-2xl font-bold text-success-foreground">{{ $present }}</div>
                    <div class="text-xs text-success-foreground mt-1">Present</div>
                </div>
                <div class="text-center p-3 bg-error-light rounded-xl">
                    <div class="text-2xl font-bold text-error">{{ $absent }}</div>
                    <div class="text-xs text-error mt-1">Absent</div>
                </div>
                <div class="text-center p-3 bg-surface-secondary rounded-xl">
                    <div class="text-2xl font-bold text-text-primary">{{ $late }}</div>
                    <div class="text-xs text-text-muted mt-1">Late</div>
                </div>
            </div>
            @if(($present + $absent + $late) > 0)
            @php $total = $present + $absent + $late; @endphp
            <div class="mt-3">
                <div class="flex justify-between text-xs text-text-muted mb-1">
                    <span>Attendance rate</span>
                    <span>{{ $total > 0 ? round(($present / $total) * 100) : 0 }}%</span>
                </div>
                <div class="w-full bg-surface-secondary rounded-full h-2">
                    <div class="bg-success-foreground h-2 rounded-full transition-all"
                         style="width: {{ $total > 0 ? round(($present / $total) * 100) : 0 }}%"></div>
                </div>
            </div>
            @endif
            @endif
        </div>

        {{-- Fee Status --}}
        <div class="bg-surface border border-border rounded-2xl shadow-card p-6">
            <h3 class="text-base font-semibold text-text-primary mb-4">
                Fee Status
                @if($currentTerm)
                <span class="text-xs font-normal text-text-muted">— {{ $currentTerm->name }}</span>
                @endif
            </h3>

            @if(empty($feeItems))
            <p class="text-sm text-text-muted">No fee records for the current term.</p>
            @else
            <div class="flex flex-col gap-2">
                @php
                    $totalDue  = collect($feeItems)->sum('amount');
                    $totalPaid = collect($feeItems)->sum('paid');
                    $balance   = $totalDue - $totalPaid;
                @endphp
                @foreach($feeItems as $item)
                @php
                    $isPaid = $item['paid'] >= $item['amount'];
                @endphp
                <div class="flex items-center justify-between py-2 border-b border-border last:border-0">
                    <span class="text-sm text-text-primary">{{ $item['label'] }}</span>
                    <span class="flex items-center gap-2">
                        <span class="text-sm font-medium text-text-primary">{{ format_money($item['amount'], $currencySymbol) }}</span>
                        @if($isPaid)
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-success-lightest text-success-foreground">Paid</span>
                        @else
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-error-light text-error">Owing</span>
                        @endif
                    </span>
                </div>
                @endforeach

                <div class="mt-2 pt-2 border-t border-border flex justify-between">
                    <span class="text-sm font-semibold text-text-primary">Balance</span>
                    <span class="text-sm font-bold {{ $balance > 0 ? 'text-error' : 'text-success-foreground' }}">
                        {{ format_money(abs($balance), $currencySymbol) }}
                        {{ $balance > 0 ? 'owing' : ($balance < 0 ? 'overpaid' : 'settled') }}
                    </span>
                </div>
            </div>
            @endif
        </div>

    </div>

    {{-- Exam Results --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card p-6">
        <h3 class="text-base font-semibold text-text-primary mb-5">Exam Results</h3>

        @if($publishedExams->isEmpty())
        <div class="flex items-center justify-center py-8 text-center">
            <div>
                <div class="w-10 h-10 rounded-xl bg-surface-secondary flex items-center justify-center mx-auto mb-3">
                    <svg class="w-5 h-5 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <p class="text-sm text-text-muted">No published exam results yet.</p>
            </div>
        </div>
        @else

        <div class="flex flex-col gap-5">
            @foreach($publishedExams as $exam)
            <div>
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h4 class="text-sm font-semibold text-text-primary">{{ $exam->name }}</h4>
                        <p class="text-xs text-text-muted mt-0.5">{{ $exam->term?->name ?? '' }}{{ $exam->term?->academicYear ? ' · ' . $exam->term->academicYear->name : '' }}</p>
                    </div>
                    @php
                        $avg = $exam->studentResults->avg('marks');
                    @endphp
                    <div class="text-right">
                        <span class="text-sm font-bold text-text-primary">{{ number_format($avg, 1) }}%</span>
                        <p class="text-xs text-text-muted">Average</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left text-xs font-medium text-text-muted uppercase tracking-wide pb-2 pr-4">Subject</th>
                                <th class="text-right text-xs font-medium text-text-muted uppercase tracking-wide pb-2 pr-4">Marks</th>
                                <th class="text-right text-xs font-medium text-text-muted uppercase tracking-wide pb-2">Grade</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @foreach($exam->studentResults as $result)
                            <tr>
                                <td class="py-2 pr-4 text-text-primary">{{ $result->subject?->name ?? '—' }}</td>
                                <td class="py-2 pr-4 text-right font-medium text-text-primary">{{ number_format($result->marks, 1) }}</td>
                                <td class="py-2 text-right">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                                                 {{ in_array($result->grade, ['A', 'A+', 'A-']) ? 'bg-success-lightest text-success-foreground' :
                                                    (in_array($result->grade, ['B', 'B+', 'B-']) ? 'bg-accent-muted text-accent' :
                                                    (in_array($result->grade, ['F', 'F-']) ? 'bg-error-light text-error' : 'bg-surface-secondary text-text-secondary')) }}">
                                        {{ $result->grade ?? '—' }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @if(!$loop->last)
            <div class="border-t border-border"></div>
            @endif
            @endforeach
        </div>

        @endif
    </div>

    @endif {{-- end selectedChild --}}
    @endif {{-- end children not empty --}}

</div>
@endsection
