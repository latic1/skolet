@extends('layouts.tenant')

@section('title', 'Settings â€” Academic Calendar')
@section('page-title', 'Academics')

@section('content')
@php
    $host               = request()->getSchemeAndHttpHost();
    $currentPeriodSystem = $schoolProfile?->period_system ?? '3_term';

    $yearsJson = $academicYears->map(fn ($y) => [
        'id'         => $y->id,
        'name'       => $y->name,
        'start_date' => $y->start_date->format('Y-m-d'),
        'end_date'   => $y->end_date->format('Y-m-d'),
        'is_current' => $y->is_current,
        'terms'      => $y->terms->map(fn ($t) => [
            'id'         => $t->id,
            'name'       => $t->name,
            'start_date' => $t->start_date?->format('Y-m-d'),
            'end_date'   => $t->end_date?->format('Y-m-d'),
            'is_current' => $t->is_current,
        ])->values()->toArray(),
    ])->values()->toJson();

    $currentYear = $academicYears->firstWhere('is_current', true);
    $currentTerm = $currentYear ? $currentYear->terms->firstWhere('is_current', true) : null;
@endphp

<div class="flex flex-col gap-6" x-data="academicYearPage({{ $yearsJson }}, {{ json_encode($yearOpen) }})" x-init="init()">

    @include('partials.academics-tabs')

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="flex items-start gap-3 bg-success-lightest border border-success-light text-success-foreground text-sm px-4 py-3 rounded-xl">
        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="flex items-start gap-3 bg-error-light border border-error text-error text-sm px-4 py-3 rounded-xl">
        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- =========================================================
         Section 1: Academic Period System
         ========================================================= --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card p-6">
        <div class="mb-4">
            <h3 class="text-base font-semibold text-text-primary">Academic Period System</h3>
            <p class="text-xs text-text-muted mt-0.5">Determines how terms are structured across the school year. Set once before creating academic years â€” cannot be changed after terms exist.</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <form method="POST" action="{{ $host }}/settings/academic-year/period-system">
                @csrf
                <input type="hidden" name="period_system" value="3_term">
                <button type="submit" class="w-full text-left p-4 rounded-xl border-2 transition-colors
                    {{ $currentPeriodSystem === '3_term'
                        ? 'border-accent bg-accent'
                        : 'border-border bg-surface hover:border-accent hover:bg-accent-muted' }}">
                    <p class="font-semibold text-sm mb-1 {{ $currentPeriodSystem === '3_term' ? 'text-white' : 'text-text-primary' }}">3-Term System</p>
                    <p class="text-xs {{ $currentPeriodSystem === '3_term' ? 'text-white/80' : 'text-text-muted' }}">Term 1 Â· Term 2 Â· Term 3</p>
                    @if($currentPeriodSystem === '3_term')
                    <span class="inline-flex items-center gap-1 mt-2 text-xs text-white/80">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        Selected
                    </span>
                    @endif
                </button>
            </form>
            <form method="POST" action="{{ $host }}/settings/academic-year/period-system">
                @csrf
                <input type="hidden" name="period_system" value="2_semester">
                <button type="submit" class="w-full text-left p-4 rounded-xl border-2 transition-colors
                    {{ $currentPeriodSystem === '2_semester'
                        ? 'border-accent bg-accent'
                        : 'border-border bg-surface hover:border-accent hover:bg-accent-muted' }}">
                    <p class="font-semibold text-sm mb-1 {{ $currentPeriodSystem === '2_semester' ? 'text-white' : 'text-text-primary' }}">2-Semester System</p>
                    <p class="text-xs {{ $currentPeriodSystem === '2_semester' ? 'text-white/80' : 'text-text-muted' }}">Semester 1 Â· Semester 2</p>
                    @if($currentPeriodSystem === '2_semester')
                    <span class="inline-flex items-center gap-1 mt-2 text-xs text-white/80">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        Selected
                    </span>
                    @endif
                </button>
            </form>
        </div>
    </div>

    {{-- =========================================================
         Section 2: Grading Scale
         ========================================================= --}}
@php
    $defaultScale = config('skolet.default_grading_scale', []);
    $savedScale   = $schoolProfile?->grading_scale ?? $defaultScale;
    $scaleJson    = json_encode(array_values($savedScale));
@endphp
<div class="bg-surface border border-border rounded-2xl shadow-card"
     x-data="{
        submitting: false,
        bands: {{ $scaleJson }},
        addBand() {
            this.bands.push({ min: '', max: '', grade: '', remark: '' });
        },
        removeBand(index) {
            this.bands.splice(index, 1);
        }
     }">

    <div class="px-6 py-4 border-b border-border flex items-center justify-between gap-4 flex-wrap">
        <div>
            <h3 class="text-base font-semibold text-text-primary">Grading Scale</h3>
            <p class="text-xs text-text-muted mt-0.5">Define how numeric marks are converted to grade letters and remarks on report cards. Must cover 0â€“100 with no gaps or overlaps.</p>
        </div>
    </div>

    {{-- Grading scale errors --}}
    @if($errors->hasBag('default') && $errors->has('bands'))
    <div class="mx-6 mt-4 bg-error-light border border-error text-error text-sm px-4 py-3 rounded-xl">
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->get('bands') as $msg)
            <li>{{ is_array($msg) ? $msg[0] : $msg }}</li>
            @endforeach
            @foreach($errors->get('bands.*') as $msgs)
                @foreach($msgs as $msg)
                <li>{{ $msg }}</li>
                @endforeach
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ $host }}/settings/grading-scale"
          @submit="submitting = true" class="p-6 space-y-4">
        @csrf

        {{-- Band rows --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border">
                        <th class="pb-2 text-left text-xs font-medium text-text-muted pr-3 w-24">Min Score</th>
                        <th class="pb-2 text-left text-xs font-medium text-text-muted pr-3 w-24">Max Score</th>
                        <th class="pb-2 text-left text-xs font-medium text-text-muted pr-3 w-20">Grade</th>
                        <th class="pb-2 text-left text-xs font-medium text-text-muted pr-3">Remark</th>
                        <th class="pb-2 w-8"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <template x-for="(band, index) in bands" :key="index">
                        <tr>
                            <td class="py-2 pr-3">
                                <input type="number" :name="'bands[' + index + '][min]'"
                                       x-model="band.min"
                                       min="0" max="100" required
                                       class="w-full px-2 py-1.5 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent">
                            </td>
                            <td class="py-2 pr-3">
                                <input type="number" :name="'bands[' + index + '][max]'"
                                       x-model="band.max"
                                       min="0" max="100" required
                                       class="w-full px-2 py-1.5 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent">
                            </td>
                            <td class="py-2 pr-3">
                                <input type="text" :name="'bands[' + index + '][grade]'"
                                       x-model="band.grade"
                                       maxlength="5" required placeholder="A"
                                       class="w-full px-2 py-1.5 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent">
                            </td>
                            <td class="py-2 pr-3">
                                <input type="text" :name="'bands[' + index + '][remark]'"
                                       x-model="band.remark"
                                       maxlength="50" required placeholder="Excellent"
                                       class="w-full px-2 py-1.5 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent">
                            </td>
                            <td class="py-2">
                                <button type="button" @click="removeBand(index)"
                                        x-show="bands.length > 1"
                                        class="p-1 text-text-muted hover:text-error transition-colors rounded">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- Grade band preview --}}
        <div class="flex gap-1 h-5 rounded-md overflow-hidden" x-show="bands.length > 0">
            <template x-for="(band, index) in [...bands].sort((a,b) => b.min - a.min)" :key="index">
                <div class="flex-1 rounded-sm"
                     :style="`background: ${['#10b981','#61a8ff','#ff8904','#ef4444','#a78bfa','#f59e0b'][index % 6]}; opacity: 0.7`"
                     :title="band.grade + ': ' + band.min + 'â€“' + band.max">
                </div>
            </template>
        </div>
        <p class="text-xs text-text-muted -mt-2">Preview â€” grade bands from highest (left) to lowest (right)</p>

        <div class="flex items-center gap-3 pt-2">
            <button type="button" @click="addBand()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-accent border border-accent rounded-md hover:bg-accent-muted transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Band
            </button>
            <button type="submit"
                    :disabled="submitting"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
                <span x-show="!submitting">Save Grading Scale</span>
                <span x-show="submitting">Savingâ€¦</span>
            </button>
        </div>
    </form>
</div>

    {{-- =========================================================
         Section 3: Academic Years
         ========================================================= --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card">

        <div class="flex items-center justify-between px-6 py-4 border-b border-border">
            <div>
                <h3 class="text-base font-semibold text-text-primary">Academic Years</h3>
                <p class="text-xs text-text-muted mt-0.5">Click a year to manage its terms and settings</p>
            </div>
            <button @click="openAdd()"
                    class="flex items-center gap-2 px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Year
            </button>
        </div>

        {{-- Year pill buttons --}}
        <div class="px-6 py-4 {{ $academicYears->isNotEmpty() ? 'border-b border-border' : '' }}">
            @if($academicYears->isEmpty())
            <div class="flex flex-col items-center justify-center py-8 text-center">
                <div class="w-10 h-10 rounded-xl bg-accent-muted flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-text-primary mb-1">No academic years yet</p>
                <p class="text-xs text-text-muted mb-4">Terms are auto-generated when you add a year, based on your period system selection above.</p>
                <button @click="openAdd()"
                        class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors">
                    Add First Academic Year
                </button>
            </div>
            @else
            <div class="flex flex-wrap gap-2">
                @foreach($academicYears as $year)
                <button @click="selectYear('{{ $year->id }}')"
                        :class="selectedYearId === '{{ $year->id }}'
                            ? 'bg-accent text-white border-accent'
                            : 'bg-surface-secondary text-text-primary border-border hover:border-accent hover:text-accent'"
                        class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full border text-sm font-medium transition-colors">
                    {{ $year->name }}
                    @if($year->is_current)
                    <span class="w-2 h-2 rounded-full bg-success shrink-0"></span>
                    @endif
                </button>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Selected year detail panel --}}
        <template x-if="selectedYear">
            <div class="px-6 py-5">

                {{-- Year meta + actions --}}
                <div class="flex items-start justify-between gap-4 mb-5 flex-wrap">
                    <div>
                        <p class="text-sm font-semibold text-text-primary" x-text="selectedYear.name"></p>
                        <p class="text-xs text-text-muted mt-0.5"
                           x-text="selectedYear.start_date + ' â†’ ' + selectedYear.end_date"></p>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <template x-if="selectedYear.is_current">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-lightest text-success-foreground">
                                Current Year
                            </span>
                        </template>
                        <template x-if="!selectedYear.is_current">
                            <form :action="`{{ $host }}/settings/academic-year/${selectedYear.id}/set-current`"
                                  method="POST" style="display:contents">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="text-xs font-medium text-text-secondary hover:text-text-primary transition-colors px-3 py-1.5 rounded-md hover:bg-surface-secondary border border-border">
                                    Set as Current
                                </button>
                            </form>
                        </template>
                        <button @click="openEdit({
                                    id: selectedYear.id,
                                    name: selectedYear.name,
                                    start_date: selectedYear.start_date,
                                    end_date: selectedYear.end_date
                                })"
                                class="text-xs font-medium text-text-secondary hover:text-text-primary transition-colors px-3 py-1.5 rounded-md hover:bg-surface-secondary border border-border">
                            Edit Year
                        </button>
                        <form :action="`{{ $host }}/settings/academic-year/${selectedYear.id}`"
                              method="POST" style="display:contents">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    @click.prevent="if(confirm('Delete ' + selectedYear.name + '? All its terms will also be deleted. This cannot be undone.')) $el.closest('form').submit()"
                                    class="text-xs font-medium text-error hover:text-red-700 transition-colors px-3 py-1.5 rounded-md hover:bg-error-light border border-error/30">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Terms --}}
                <div>
                    <p class="text-xs font-semibold text-text-secondary uppercase tracking-wide mb-3">Terms</p>

                    <template x-if="selectedYear.terms.length === 0">
                        <div class="rounded-xl border border-border px-6 py-8 text-center">
                            <p class="text-sm text-text-muted">No terms for this year.</p>
                            <p class="text-xs text-text-muted mt-1">This may be an older year created before auto-generation was active.</p>
                            <template x-if="years.some(y => y.id !== selectedYear.id && y.terms.length > 0)">
                                <form :action="`{{ $host }}/settings/academic-year/${selectedYear.id}/copy-terms`"
                                      method="POST" class="mt-4 inline-block">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center gap-2 px-4 py-2 bg-surface border border-border text-sm font-medium text-text-secondary rounded-md hover:bg-surface-secondary hover:text-text-primary transition-colors">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                        </svg>
                                        Copy term structure from previous year
                                    </button>
                                </form>
                            </template>
                        </div>
                    </template>

                    <template x-if="selectedYear.terms.length > 0">
                        <div class="rounded-xl border border-border overflow-hidden">
                            <template x-for="term in selectedYear.terms" :key="term.id">
                                <div class="border-b border-border last:border-b-0">

                                    {{-- Term row --}}
                                    <div class="flex items-center gap-3 px-4 py-3 hover:bg-surface-secondary transition-colors">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-text-primary" x-text="term.name"></p>
                                            <p class="text-xs text-text-muted mt-0.5"
                                               x-text="(term.start_date && term.end_date)
                                                    ? term.start_date + ' â†’ ' + term.end_date
                                                    : 'No dates set'">
                                            </p>
                                        </div>
                                        <div class="flex items-center gap-1.5 shrink-0">
                                            <span x-show="term.is_current"
                                                  class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-success-lightest text-success-foreground">
                                                Active
                                            </span>
                                            <template x-if="!term.is_current">
                                                <form :action="`{{ $host }}/settings/terms/${term.id}/set-current`"
                                                      method="POST" style="display:contents">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit"
                                                            class="text-xs font-medium text-text-secondary hover:text-text-primary transition-colors px-2 py-1 rounded hover:bg-surface-secondary whitespace-nowrap">
                                                        Set Active
                                                    </button>
                                                </form>
                                            </template>
                                            <button @click="startEditTerm(term)"
                                                    class="text-xs font-medium text-accent hover:text-accent-dark transition-colors px-2 py-1 rounded hover:bg-accent-muted whitespace-nowrap">
                                                Edit Dates
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Inline date edit form --}}
                                    <div x-show="editingTermId === term.id"
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="opacity-0"
                                         x-transition:enter-end="opacity-100"
                                         class="px-4 py-3 bg-surface-secondary border-t border-border">
                                        <form :action="`{{ $host }}/settings/terms/${termForm.id}`"
                                              method="POST" @submit="termSubmitting = true">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="name" :value="termForm.name">
                                            <div class="flex flex-wrap items-end gap-3">
                                                <div>
                                                    <label class="block text-xs font-medium text-text-secondary mb-1">Start Date</label>
                                                    <input type="date" name="start_date" x-model="termForm.start_date"
                                                           class="px-3 py-1.5 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-text-secondary mb-1">End Date</label>
                                                    <input type="date" name="end_date" x-model="termForm.end_date"
                                                           class="px-3 py-1.5 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                                                </div>
                                                <button type="submit"
                                                        :disabled="termSubmitting"
                                                        :class="termSubmitting ? 'opacity-60 cursor-not-allowed' : 'hover:bg-accent-dark'"
                                                        class="px-3 py-1.5 bg-accent text-accent-foreground text-sm font-medium rounded-md transition-colors">
                                                    <span x-show="!termSubmitting">Save Dates</span>
                                                    <span x-show="termSubmitting">Savingâ€¦</span>
                                                </button>
                                                <button type="button" @click="cancelEditTerm()"
                                                        class="px-3 py-1.5 bg-surface border border-border text-sm font-medium text-text-primary rounded-md hover:bg-surface-secondary transition-colors">
                                                    Cancel
                                                </button>
                                            </div>
                                        </form>
                                    </div>

                                </div>
                            </template>
                        </div>
                    </template>
                </div>

            </div>
        </template>

        <template x-if="!selectedYear && years.length > 0">
            <div class="px-6 py-6 text-center text-sm text-text-muted">
                Select a year above to manage its terms.
            </div>
        </template>

    </div>

    {{-- =========================================================
         Section 3: Active Configuration
         ========================================================= --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card p-6">
        <h3 class="text-base font-semibold text-text-primary mb-4">Active Configuration</h3>
        @if($currentYear)
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-accent-muted flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-text-primary">
                    {{ $currentYear->name }} Â· {{ $currentPeriodSystem === '3_term' ? '3-Term System' : '2-Semester System' }}
                </p>
                <p class="text-xs text-text-muted mt-0.5">
                    @if($currentTerm)
                        Current Term: <span class="font-medium text-text-primary">{{ $currentTerm->name }}</span>
                        @if($currentTerm->start_date && $currentTerm->end_date)
                            Â· {{ $currentTerm->start_date->format('M j') }} â€“ {{ $currentTerm->end_date->format('M j, Y') }}
                        @endif
                    @else
                        No term is currently active â€” click "Set Active" on a term above
                    @endif
                </p>
            </div>
        </div>
        @elseif($academicYears->isEmpty())
        <p class="text-sm text-text-muted">No academic years created yet. Add one above to get started.</p>
        @else
        <p class="text-sm text-text-muted">No academic year is currently active. Use "Set as Current" on a year to activate it.</p>
        @endif
    </div>

    {{-- =========================================================
         Add / Edit Year Modal
         ========================================================= --}}
    <div x-show="showModal"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display: none;">

        <div class="absolute inset-0 bg-overlay/40" @click="close()"></div>

        <div class="relative w-full max-w-md bg-surface rounded-2xl shadow-xl border border-border p-6"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-semibold text-text-primary"
                    x-text="mode === 'add' ? 'Add Academic Year' : 'Edit Academic Year'"></h3>
                <button @click="close()" class="p-1.5 rounded-md text-text-muted hover:bg-surface-secondary transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Add Form --}}
            <form x-show="mode === 'add'" method="POST" action="{{ $host }}/settings/academic-year"
                  class="flex flex-col gap-4" @submit="submitting = true">
                @csrf
                <input type="hidden" name="_modal_mode" value="add">
                <p class="text-xs text-text-muted -mt-2">
                    Terms will be auto-generated:
                    <strong>{{ $currentPeriodSystem === '3_term' ? 'Term 1, Term 2, Term 3' : 'Semester 1, Semester 2' }}</strong>
                </p>
                <div>
                    <label class="block text-sm font-medium text-text-dark mb-1.5">Name <span class="text-error">*</span></label>
                    <input type="text" name="name" x-model="form.name" placeholder="e.g. 2025/2026"
                           class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors"
                           required>
                    @error('name')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-text-dark mb-1.5">Start Date <span class="text-error">*</span></label>
                        <input type="date" name="start_date" x-model="form.start_date"
                               class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors"
                               required>
                        @error('start_date')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-dark mb-1.5">End Date <span class="text-error">*</span></label>
                        <input type="date" name="end_date" x-model="form.end_date"
                               class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors"
                               required>
                        @error('end_date')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-2">
                    <button type="button" @click="close()"
                            class="px-4 py-2 bg-surface border border-border text-sm font-medium text-text-primary rounded-md hover:bg-surface-secondary transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            :disabled="submitting"
                            :class="submitting ? 'opacity-60 cursor-not-allowed' : 'hover:bg-accent-dark'"
                            class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md transition-colors">
                        <span x-show="!submitting">Add Year</span>
                        <span x-show="submitting">Savingâ€¦</span>
                    </button>
                </div>
            </form>

            {{-- Edit Form --}}
            <form x-show="mode === 'edit'" method="POST"
                  :action="`{{ $host }}/settings/academic-year/${form.id}`"
                  class="flex flex-col gap-4" @submit="submitting = true">
                @csrf
                @method('PUT')
                <input type="hidden" name="_modal_mode" value="edit">
                <input type="hidden" name="_modal_id" :value="form.id">
                <div>
                    <label class="block text-sm font-medium text-text-dark mb-1.5">Name <span class="text-error">*</span></label>
                    <input type="text" name="name" x-model="form.name" placeholder="e.g. 2025/2026"
                           class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors"
                           required>
                    @error('name')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-text-dark mb-1.5">Start Date <span class="text-error">*</span></label>
                        <input type="date" name="start_date" x-model="form.start_date"
                               class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors"
                               required>
                        @error('start_date')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-dark mb-1.5">End Date <span class="text-error">*</span></label>
                        <input type="date" name="end_date" x-model="form.end_date"
                               class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors"
                               required>
                        @error('end_date')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-2">
                    <button type="button" @click="close()"
                            class="px-4 py-2 bg-surface border border-border text-sm font-medium text-text-primary rounded-md hover:bg-surface-secondary transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            :disabled="submitting"
                            :class="submitting ? 'opacity-60 cursor-not-allowed' : 'hover:bg-accent-dark'"
                            class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md transition-colors">
                        <span x-show="!submitting">Save Changes</span>
                        <span x-show="submitting">Savingâ€¦</span>
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function academicYearPage(yearsData, yearOpen) {
    return {
        years: yearsData,
        selectedYearId: yearOpen || (yearsData.find(y => y.is_current)?.id) || (yearsData[0]?.id) || null,

        get selectedYear() {
            return this.years.find(y => y.id === this.selectedYearId) || null;
        },

        // Year add/edit modal
        showModal: false,
        mode: 'add',
        form: { id: '', name: '', start_date: '', end_date: '' },
        submitting: false,

        // Inline term date editing
        editingTermId: null,
        termForm: { id: '', name: '', start_date: '', end_date: '' },
        termSubmitting: false,

        openAdd() {
            this.mode = 'add';
            this.form = { id: '', name: '', start_date: '', end_date: '' };
            this.showModal = true;
        },
        openEdit(data) {
            this.mode = 'edit';
            this.form = { ...data };
            this.showModal = true;
        },
        close() { this.showModal = false; this.submitting = false; },

        selectYear(yearId) {
            this.selectedYearId = yearId;
            this.editingTermId  = null;
        },

        startEditTerm(term) {
            this.termForm = {
                id:         term.id,
                name:       term.name,
                start_date: term.start_date || '',
                end_date:   term.end_date   || '',
            };
            this.editingTermId = term.id;
        },
        cancelEditTerm() {
            this.editingTermId = null;
        },

        init() {
            const modalMode = @json(old('_modal_mode'));
            if (modalMode === 'add' || modalMode === 'edit') {
                this.$nextTick(() => {
                    this.mode = modalMode;
                    this.form = {
                        id:         @json(old('_modal_id', '')),
                        name:       @json(old('name', '')),
                        start_date: @json(old('start_date', '')),
                        end_date:   @json(old('end_date', '')),
                    };
                    this.showModal = true;
                });
            }
        },
    };
}
</script>
@endpush
