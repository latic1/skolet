@extends('layouts.tenant')

@section('title', 'Add Student')
@section('page-title', 'Students')

@section('content')
@php $host = request()->getSchemeAndHttpHost(); @endphp
<div class="flex flex-col gap-6 max-w-3xl"
     x-data="studentForm({{ json_encode($classesJson) }}, null, null)">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-text-muted">
        <a href="{{ $host }}/students" class="hover:text-text-primary transition-colors">Students</a>
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-text-primary font-medium">Add Student</span>
    </div>

    {{-- Pre-fill notice from admission application --}}
    @if($application && $application->isPending())
    <div class="bg-accent-muted border border-accent rounded-xl px-5 py-4 flex items-start gap-3">
        <svg class="w-4 h-4 text-accent shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
            <p class="text-sm font-medium text-accent">Pre-filled from admission application</p>
            <p class="text-xs text-text-secondary mt-0.5">
                This form has been pre-filled with details from <strong>{{ $application->applicant_name }}</strong>'s application.
                Saving this form will accept the application and create the student record.
            </p>
        </div>
    </div>
    @endif

    @if($errors->any())
    <div class="bg-error-light border border-error text-error text-sm px-4 py-3 rounded-xl flex items-start gap-3">
        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        <div>
            <p class="font-medium mb-1">Please fix the following errors:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <form method="POST" action="{{ $host }}/students" enctype="multipart/form-data" class="flex flex-col gap-6"
          x-data="{ submitting: false, previewUrl: '', onPhotoChange(e) { const file = e.target.files[0]; if (file) { this.previewUrl = URL.createObjectURL(file); } } }"
          @submit="submitting = true">
        @csrf
        @if($application && $application->isPending())
        <input type="hidden" name="from_application_id" value="{{ $application->id }}">
        @endif

        {{-- Personal Information --}}
        <div class="bg-surface border border-border rounded-2xl shadow-card p-6 flex flex-col gap-5">
            <h3 class="text-base font-semibold text-text-primary">Personal Information</h3>

            {{-- Photo Upload --}}
            <div>
                <label class="block text-sm font-medium text-text-dark mb-3">Photo</label>
                <div class="flex items-start gap-5 flex-wrap">
                    <div class="w-20 h-20 rounded-xl border-2 border-border flex items-center justify-center shrink-0 overflow-hidden bg-surface-secondary">
                        <template x-if="previewUrl">
                            <img :src="previewUrl" alt="Student photo" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!previewUrl">
                            <span class="text-2xl font-semibold text-accent">S</span>
                        </template>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="photo"
                               class="inline-flex items-center gap-2 px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                            Upload Photo
                        </label>
                        <input id="photo" type="file" name="photo" accept="image/*" class="hidden"
                               @change="onPhotoChange($event)">
                        <p class="text-xs text-text-muted">JPG, PNG, WebP up to 2 MB.</p>
                    </div>
                </div>
                @error('photo')<p class="mt-2 text-xs text-error">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-text-dark mb-1.5">Full Name <span class="text-error">*</span></label>
                    <input type="text" name="full_name" value="{{ old('full_name', $prefill['full_name'] ?? '') }}" required
                           placeholder="e.g. Jane Doe"
                           class="w-full px-3 py-2 bg-surface border @error('full_name') border-error @else border-border @enderror rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                    @error('full_name')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-dark mb-1.5">Date of Birth</label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $prefill['date_of_birth'] ?? '') }}"
                           class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                    @error('date_of_birth')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-dark mb-1.5">Gender</label>
                    <select name="gender"
                            class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                        <option value="">Not specified</option>
                        <option value="male"   @selected(old('gender', $prefill['gender'] ?? '') === 'male')>Male</option>
                        <option value="female" @selected(old('gender', $prefill['gender'] ?? '') === 'female')>Female</option>
                        <option value="other"  @selected(old('gender', $prefill['gender'] ?? '') === 'other')>Other</option>
                    </select>
                    @error('gender')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Academic Information --}}
        <div class="bg-surface border border-border rounded-2xl shadow-card p-6 flex flex-col gap-5">
            <h3 class="text-base font-semibold text-text-primary">Academic Information</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-text-dark mb-1.5">Class <span class="text-error">*</span></label>
                    <select name="class_id" x-model="selectedClassId" @change="onClassChange()" required
                            class="w-full px-3 py-2 bg-surface border @error('class_id') border-error @else border-border @enderror rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                        <option value="">Select a class</option>
                        @foreach($classes as $class)
                        <option value="{{ $class->id }}" @selected(old('class_id') === $class->id)>{{ $class->name }}</option>
                        @endforeach
                    </select>
                    @error('class_id')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div x-show="currentSections.length > 0" style="display: none;">
                    <label class="block text-sm font-medium text-text-dark mb-1.5">Section</label>
                    <select name="section_id" x-model="selectedSectionId"
                            class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                        <option value="">Select a section</option>
                        <template x-for="section in currentSections" :key="section.id">
                            <option :value="section.id" x-text="section.name"></option>
                        </template>
                    </select>
                    @error('section_id')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-text-dark mb-1.5">Admission Number</label>
                <input type="text" value="Auto-generated on save" disabled
                       class="w-full px-3 py-2 bg-surface-secondary border border-border rounded-md text-sm text-text-muted cursor-not-allowed">
                <p class="mt-1 text-xs text-text-muted">Format: {{ now()->year }}/0001, {{ now()->year }}/0002, &hellip;</p>
            </div>
        </div>

        {{-- Guardian Information --}}
        <div class="bg-surface border border-border rounded-2xl shadow-card p-6 flex flex-col gap-5">
            <h3 class="text-base font-semibold text-text-primary">Guardian Information</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-text-dark mb-1.5">Guardian Name</label>
                    <input type="text" name="guardian_name" value="{{ old('guardian_name', $prefill['guardian_name'] ?? '') }}"
                           placeholder="e.g. John Doe"
                           class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                    @error('guardian_name')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-dark mb-1.5">Guardian Contact <span class="text-error">*</span></label>
                    <input type="text" name="guardian_contact" value="{{ old('guardian_contact', $prefill['guardian_contact'] ?? '') }}" required
                           placeholder="e.g. 0244123456"
                           class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                    @error('guardian_contact')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-dark mb-1.5">Guardian Email</label>
                    <input type="email" name="guardian_email" value="{{ old('guardian_email', $prefill['guardian_email'] ?? '') }}"
                           placeholder="e.g. guardian@example.com"
                           class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                    @error('guardian_email')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-dark mb-1.5">Address</label>
                    <input type="text" name="address" value="{{ old('address') }}"
                           placeholder="e.g. 12 School Lane"
                           class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                    @error('address')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Form Actions --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ $host }}/students"
               class="px-4 py-2 bg-surface border border-border text-sm font-medium text-text-primary rounded-md hover:bg-surface-secondary transition-colors">
                Cancel
            </a>
            <button type="submit"
                    :disabled="submitting"
                    :class="submitting ? 'opacity-60 cursor-not-allowed' : 'hover:bg-accent-dark'"
                    class="px-6 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md transition-colors">
                <span x-show="!submitting">Add Student</span>
                <span x-show="submitting">Saving&hellip;</span>
            </button>
        </div>

    </form>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('studentForm', (classes, initialClassId, initialSectionId) => ({
        classes: classes,
        selectedClassId: initialClassId ?? '{{ old('class_id', '') }}',
        selectedSectionId: initialSectionId ?? '{{ old('section_id', '') }}',
        currentSections: [],

        init() {
            this.onClassChange();
        },

        onClassChange() {
            const cls = this.classes.find(c => c.id === this.selectedClassId);
            this.currentSections = cls ? cls.sections : [];
            if (!cls || cls.sections.length === 0) {
                this.selectedSectionId = '';
            }
        },
    }));
});
</script>
@endpush
@endsection
