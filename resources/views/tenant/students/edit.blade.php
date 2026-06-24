@extends('layouts.tenant')

@section('title', 'Edit ' . $student->full_name)
@section('page-title', 'Students')

@section('content')
@php $host = request()->getSchemeAndHttpHost(); @endphp
<div class="flex flex-col gap-6 max-w-3xl"
     x-data="studentForm({{ json_encode($classesJson) }}, '{{ $student->class_id }}', '{{ $student->section_id }}')">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-text-muted">
        <a href="{{ $host }}/students" class="hover:text-text-primary transition-colors">Students</a>
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ $host }}/students/{{ $student->id }}" class="hover:text-text-primary transition-colors">{{ $student->full_name }}</a>
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-text-primary font-medium">Edit</span>
    </div>

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

    <form method="POST" action="{{ $host }}/students/{{ $student->id }}" class="flex flex-col gap-6"
          x-data="{ submitting: false }" @submit="submitting = true">
        @csrf
        @method('PUT')

        {{-- Personal Information --}}
        <div class="bg-surface border border-border rounded-2xl shadow-card p-6 flex flex-col gap-5">
            <h3 class="text-base font-semibold text-text-primary">Personal Information</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-text-dark mb-1.5">Full Name <span class="text-error">*</span></label>
                    <input type="text" name="full_name" value="{{ old('full_name', $student->full_name) }}" required
                           class="w-full px-3 py-2 bg-surface border @error('full_name') border-error @else border-border @enderror rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                    @error('full_name')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-dark mb-1.5">Date of Birth</label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $student->date_of_birth?->format('Y-m-d')) }}"
                           class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                    @error('date_of_birth')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-dark mb-1.5">Gender</label>
                    <select name="gender"
                            class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                        <option value="">Not specified</option>
                        <option value="male"   @selected(old('gender', $student->gender) === 'male')>Male</option>
                        <option value="female" @selected(old('gender', $student->gender) === 'female')>Female</option>
                        <option value="other"  @selected(old('gender', $student->gender) === 'other')>Other</option>
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
                        <option value="{{ $class->id }}" @selected(old('class_id', $student->class_id) === $class->id)>{{ $class->name }}</option>
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
                            <option :value="section.id"
                                    :selected="section.id === selectedSectionId"
                                    x-text="section.name"></option>
                        </template>
                    </select>
                    @error('section_id')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-text-dark mb-1.5">Admission Number</label>
                    <input type="text" value="{{ $student->admission_no }}" disabled
                           class="w-full px-3 py-2 bg-surface-secondary border border-border rounded-md text-sm text-text-muted cursor-not-allowed">
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-dark mb-1.5">Status <span class="text-error">*</span></label>
                    <select name="status" required
                            class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                        <option value="active"    @selected(old('status', $student->status) === 'active')>Active</option>
                        <option value="inactive"  @selected(old('status', $student->status) === 'inactive')>Inactive</option>
                        <option value="graduated" @selected(old('status', $student->status) === 'graduated')>Graduated</option>
                    </select>
                    @error('status')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Guardian Information --}}
        <div class="bg-surface border border-border rounded-2xl shadow-card p-6 flex flex-col gap-5">
            <h3 class="text-base font-semibold text-text-primary">Guardian Information</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-text-dark mb-1.5">Guardian Name</label>
                    <input type="text" name="guardian_name" value="{{ old('guardian_name', $student->guardian_name) }}"
                           class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                    @error('guardian_name')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-dark mb-1.5">Guardian Contact <span class="text-error">*</span></label>
                    <input type="text" name="guardian_contact" value="{{ old('guardian_contact', $student->guardian_contact) }}" required
                           placeholder="e.g. 0244123456"
                           class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                    @error('guardian_contact')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-dark mb-1.5">Guardian Email</label>
                    <input type="email" name="guardian_email" value="{{ old('guardian_email', $student->guardian_email) }}"
                           class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                    @error('guardian_email')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-dark mb-1.5">Address</label>
                    <input type="text" name="address" value="{{ old('address', $student->address) }}"
                           class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                    @error('address')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Form Actions --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ $host }}/students/{{ $student->id }}"
               class="px-4 py-2 bg-surface border border-border text-sm font-medium text-text-primary rounded-md hover:bg-surface-secondary transition-colors">
                Cancel
            </a>
            <button type="submit"
                    :disabled="submitting"
                    :class="submitting ? 'opacity-60 cursor-not-allowed' : 'hover:bg-accent-dark'"
                    class="px-6 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md transition-colors">
                <span x-show="!submitting">Save Changes</span>
                <span x-show="submitting">Saving…</span>
            </button>
        </div>

    </form>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('studentForm', (classes, initialClassId, initialSectionId) => ({
        classes: classes,
        selectedClassId: initialClassId ?? '',
        selectedSectionId: initialSectionId ?? '',
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
