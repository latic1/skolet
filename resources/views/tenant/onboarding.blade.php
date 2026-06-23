@extends('layouts.onboarding')

@section('content')
@php $host = request()->getSchemeAndHttpHost(); @endphp

{{-- Progress stepper --}}
<div class="mb-8">
    <div class="flex items-center justify-between mb-3">
        @php
            $stepLabels = ['School Profile', 'Academic Year', 'Classes', 'Subjects', 'Done'];
        @endphp
        @foreach($stepLabels as $i => $label)
        @php $num = $i + 1; @endphp
        <div class="flex flex-col items-center gap-1 flex-1">
            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-semibold
                {{ $step > $num ? 'bg-success text-white' : ($step === $num ? 'bg-accent text-white' : 'bg-surface border-2 border-border text-text-muted') }}">
                @if($step > $num)
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
                @else
                {{ $num }}
                @endif
            </div>
            <span class="text-xs text-center hidden sm:block
                {{ $step === $num ? 'text-accent font-medium' : 'text-text-muted' }}">
                {{ $label }}
            </span>
        </div>
        @if($i < count($stepLabels) - 1)
        <div class="h-px flex-1 mx-1 mb-5 {{ $step > $num ? 'bg-success' : 'bg-border' }}"></div>
        @endif
        @endforeach
    </div>
    <p class="text-xs text-center text-text-muted">Step {{ $step }} of {{ $total }}</p>
</div>

{{-- Error / success flash --}}
@if(session('error'))
<div class="mb-4 bg-error-light border border-error text-error text-sm px-4 py-3 rounded-xl">
    {{ session('error') }}
</div>
@endif

{{-- ── Step 1: School Profile ──────────────────────────────────────── --}}
@if($step === 1)
<div class="bg-surface border border-border rounded-2xl shadow-card" x-data="{ submitting: false, previewUrl: '{{ $profile?->logo_path ? $host . '/school-logo' : '' }}' }">
    <div class="px-6 py-5 border-b border-border">
        <h2 class="text-lg font-semibold text-text-primary">Tell us about your school</h2>
        <p class="text-sm text-text-muted mt-0.5">This appears on your school's public page and report cards.</p>
    </div>
    <form method="POST" action="{{ $host }}/onboarding/1" enctype="multipart/form-data"
          @submit="submitting = true" class="p-6 space-y-5">
        @csrf

        {{-- Logo --}}
        <div>
            <label class="block text-sm font-medium text-text-dark mb-2">School Logo <span class="text-text-muted font-normal">(optional)</span></label>
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-xl border-2 border-border flex items-center justify-center overflow-hidden bg-surface-secondary shrink-0">
                    <template x-if="previewUrl">
                        <img :src="previewUrl" class="w-full h-full object-contain">
                    </template>
                    <template x-if="!previewUrl">
                        <svg class="w-7 h-7 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </template>
                </div>
                <label class="cursor-pointer inline-flex items-center gap-2 px-3 py-2 bg-surface border border-border rounded-md text-sm font-medium text-text-primary hover:bg-surface-secondary transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Upload Logo
                    <input type="file" name="logo" accept="image/*" class="hidden"
                           @change="previewUrl = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : previewUrl">
                </label>
            </div>
        </div>

        {{-- School Name --}}
        <div>
            <label for="school_name" class="block text-sm font-medium text-text-dark mb-1.5">
                School Name <span class="text-error">*</span>
            </label>
            <input id="school_name" type="text" name="school_name"
                   value="{{ old('school_name', $profile?->school_name) }}"
                   required maxlength="150" placeholder="e.g. Accra Academy"
                   class="w-full px-3 py-2 bg-surface border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 transition-colors
                          {{ $errors->has('school_name') ? 'border-error focus:ring-error' : 'border-border focus:ring-accent focus:border-accent' }}">
            @error('school_name')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
        </div>

        {{-- Short Description --}}
        <div>
            <label for="short_description" class="block text-sm font-medium text-text-dark mb-1.5">
                Short Description <span class="text-text-muted font-normal">(optional)</span>
            </label>
            <textarea id="short_description" name="short_description" rows="2" maxlength="500"
                      placeholder="e.g. A leading secondary school in Accra focused on academic excellence."
                      class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors resize-none">{{ old('short_description', $profile?->short_description) }}</textarea>
        </div>

        <div class="flex items-center justify-between pt-2">
            <span class="text-xs text-text-muted">You can update all details later in Settings.</span>
            <button type="submit" :disabled="submitting"
                    class="inline-flex items-center gap-2 px-5 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
                <span x-show="!submitting">Continue</span>
                <span x-show="submitting">Saving…</span>
                <svg x-show="!submitting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>
    </form>
</div>
@endif

{{-- ── Step 2: Academic Calendar ────────────────────────────────────── --}}
@if($step === 2)
<div class="bg-surface border border-border rounded-2xl shadow-card" x-data="{ submitting: false }">
    <div class="px-6 py-5 border-b border-border">
        <h2 class="text-lg font-semibold text-text-primary">Set up your academic calendar</h2>
        <p class="text-sm text-text-muted mt-0.5">Create your first academic year and choose how terms are structured.</p>
    </div>
    <form method="POST" action="{{ $host }}/onboarding/2" @submit="submitting = true" class="p-6 space-y-5">
        @csrf

        {{-- Academic year name --}}
        <div>
            <label for="year_name" class="block text-sm font-medium text-text-dark mb-1.5">
                Academic Year Name <span class="text-error">*</span>
            </label>
            <input id="year_name" type="text" name="year_name"
                   value="{{ old('year_name', date('Y') . '/' . (date('Y') + 1)) }}"
                   required maxlength="100" placeholder="e.g. 2025/2026"
                   class="w-full sm:max-w-xs px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
            @error('year_name')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
        </div>

        {{-- Dates --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="start_date" class="block text-sm font-medium text-text-dark mb-1.5">Start Date <span class="text-error">*</span></label>
                <input id="start_date" type="date" name="start_date" value="{{ old('start_date') }}" required
                       class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                @error('start_date')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium text-text-dark mb-1.5">End Date <span class="text-error">*</span></label>
                <input id="end_date" type="date" name="end_date" value="{{ old('end_date') }}" required
                       class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                @error('end_date')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Period system --}}
        <div>
            <label class="block text-sm font-medium text-text-dark mb-2">Term Structure <span class="text-error">*</span></label>
            <div class="grid grid-cols-2 gap-3">
                <label class="cursor-pointer">
                    <input type="radio" name="period_system" value="3_term" class="sr-only peer"
                           {{ old('period_system', '3_term') === '3_term' ? 'checked' : '' }}>
                    <div class="p-4 rounded-xl border-2 border-border peer-checked:border-accent peer-checked:bg-accent-muted transition-colors">
                        <p class="text-sm font-semibold text-text-primary">3-Term System</p>
                        <p class="text-xs text-text-muted mt-0.5">Term 1 · Term 2 · Term 3</p>
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="period_system" value="2_semester" class="sr-only peer"
                           {{ old('period_system') === '2_semester' ? 'checked' : '' }}>
                    <div class="p-4 rounded-xl border-2 border-border peer-checked:border-accent peer-checked:bg-accent-muted transition-colors">
                        <p class="text-sm font-semibold text-text-primary">2-Semester System</p>
                        <p class="text-xs text-text-muted mt-0.5">Semester 1 · Semester 2</p>
                    </div>
                </label>
            </div>
        </div>

        <div class="flex items-center justify-between pt-2">
            <a href="{{ $host }}/onboarding/1" class="text-sm text-text-muted hover:text-text-primary transition-colors">← Back</a>
            <button type="submit" :disabled="submitting"
                    class="inline-flex items-center gap-2 px-5 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
                <span x-show="!submitting">Continue</span>
                <span x-show="submitting">Saving…</span>
                <svg x-show="!submitting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>
    </form>
</div>
@endif

{{-- ── Step 3: Classes ─────────────────────────────────────────────── --}}
@if($step === 3)
<div class="bg-surface border border-border rounded-2xl shadow-card"
     x-data="{ submitting: false, classes: [{ name: '' }, { name: '' }, { name: '' }], addRow() { this.classes.push({ name: '' }) }, removeRow(i) { if (this.classes.length > 1) this.classes.splice(i, 1) } }">
    <div class="px-6 py-5 border-b border-border">
        <h2 class="text-lg font-semibold text-text-primary">Add your classes</h2>
        <p class="text-sm text-text-muted mt-0.5">Add at least one class (e.g. Grade 1, JSS 2). You can add sections later.</p>
    </div>
    <form method="POST" action="{{ $host }}/onboarding/3" @submit="submitting = true" class="p-6 space-y-4">
        @csrf

        <div class="space-y-2">
            <template x-for="(cls, i) in classes" :key="i">
                <div class="flex items-center gap-2">
                    <input type="text" :name="'classes[' + i + '][name]'"
                           x-model="cls.name"
                           :placeholder="'e.g. ' + ['Grade 1', 'JSS 2', 'SSS 3'][i] || 'Class name'"
                           maxlength="100"
                           class="flex-1 px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                    <button type="button" @click="removeRow(i)" x-show="classes.length > 1"
                            class="p-1.5 text-text-muted hover:text-error transition-colors rounded">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </template>
        </div>

        <button type="button" @click="addRow()"
                class="inline-flex items-center gap-1.5 text-sm text-accent hover:underline">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add another class
        </button>

        @error('classes')<p class="text-xs text-error">{{ $message }}</p>@enderror

        <div class="flex items-center justify-between pt-2">
            <a href="{{ $host }}/onboarding/2" class="text-sm text-text-muted hover:text-text-primary transition-colors">← Back</a>
            <button type="submit" :disabled="submitting"
                    class="inline-flex items-center gap-2 px-5 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
                <span x-show="!submitting">Continue</span>
                <span x-show="submitting">Saving…</span>
                <svg x-show="!submitting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>
    </form>
</div>
@endif

{{-- ── Step 4: Subjects ────────────────────────────────────────────── --}}
@if($step === 4)
<div class="bg-surface border border-border rounded-2xl shadow-card"
     x-data="{ submitting: false, subjects: [{ name: '' }, { name: '' }, { name: '' }, { name: '' }], addRow() { this.subjects.push({ name: '' }) }, removeRow(i) { if (this.subjects.length > 1) this.subjects.splice(i, 1) } }">
    <div class="px-6 py-5 border-b border-border">
        <h2 class="text-lg font-semibold text-text-primary">Add your subjects</h2>
        <p class="text-sm text-text-muted mt-0.5">Add the subjects taught in your school. You can always add more later.</p>
    </div>
    <form method="POST" action="{{ $host }}/onboarding/4" @submit="submitting = true" class="p-6 space-y-4">
        @csrf

        <div class="space-y-2">
            <template x-for="(sub, i) in subjects" :key="i">
                <div class="flex items-center gap-2">
                    <input type="text" :name="'subjects[' + i + '][name]'"
                           x-model="sub.name"
                           :placeholder="['Mathematics', 'English Language', 'Science', 'Social Studies'][i] || 'Subject name'"
                           maxlength="100"
                           class="flex-1 px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                    <button type="button" @click="removeRow(i)" x-show="subjects.length > 1"
                            class="p-1.5 text-text-muted hover:text-error transition-colors rounded">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </template>
        </div>

        <button type="button" @click="addRow()"
                class="inline-flex items-center gap-1.5 text-sm text-accent hover:underline">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add another subject
        </button>

        <div class="flex items-center justify-between pt-2">
            <a href="{{ $host }}/onboarding/3" class="text-sm text-text-muted hover:text-text-primary transition-colors">← Back</a>
            <div class="flex items-center gap-3">
                <a href="{{ $host }}/onboarding/5" class="text-sm text-text-muted hover:text-text-primary transition-colors">Skip for now</a>
                <button type="submit" :disabled="submitting"
                        class="inline-flex items-center gap-2 px-5 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
                    <span x-show="!submitting">Continue</span>
                    <span x-show="submitting">Saving…</span>
                    <svg x-show="!submitting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>
    </form>
</div>
@endif

{{-- ── Step 5: Done ────────────────────────────────────────────────── --}}
@if($step === 5)
<div class="bg-surface border border-border rounded-2xl shadow-card p-8 text-center" x-data="{}">

    {{-- Animated checkmark --}}
    <div class="w-16 h-16 rounded-full bg-success-lightest flex items-center justify-center mx-auto mb-5">
        <svg class="w-8 h-8 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
    </div>

    <h2 class="text-xl font-semibold text-text-primary mb-1">You're all set!</h2>
    <p class="text-sm text-text-muted mb-6 max-w-md mx-auto">
        Your school is ready to use. You can now add students, staff, attendance, exams, and more from the dashboard.
    </p>

    {{-- Summary --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-7 text-left">
        <div class="bg-success-lightest border border-success-light rounded-xl p-3 flex items-center gap-2">
            <svg class="w-4 h-4 text-success shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span class="text-xs font-medium text-success-foreground">School profile</span>
        </div>
        <div class="bg-success-lightest border border-success-light rounded-xl p-3 flex items-center gap-2">
            <svg class="w-4 h-4 text-success shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span class="text-xs font-medium text-success-foreground">Academic year</span>
        </div>
        <div class="bg-success-lightest border border-success-light rounded-xl p-3 flex items-center gap-2">
            <svg class="w-4 h-4 text-success shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span class="text-xs font-medium text-success-foreground">Classes &amp; subjects</span>
        </div>
    </div>

    <form method="POST" action="{{ $host }}/onboarding/5">
        @csrf
        <button type="submit"
                class="inline-flex items-center gap-2 px-6 py-2.5 bg-accent text-accent-foreground font-medium rounded-md hover:bg-accent-dark transition-colors">
            Go to Dashboard
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </button>
    </form>
</div>
@endif

@endsection
