@extends('layouts.tenant')

@section('title', 'Classes & Sections')
@section('page-title', 'Academics')

@section('content')
@php
    $host = request()->getSchemeAndHttpHost();

    // Detect which modal should re-open after a validation failure.
    // We use hidden sentinels (_class_mode / _section_class_id) so we can
    // distinguish a class-form error from a section-form error even though
    // both validate a field named 'name'.
    $classFormError   = ($errors->has('name') || $errors->has('order')) && old('_class_mode');
    $sectionFormError = $errors->has('name') && old('_section_class_id') && !old('_class_mode');

    // When re-opening after success (class_open) or section error, show sections modal.
    $classOpen = $sectionFormError ? old('_section_class_id') : request('class_open');
@endphp
<div class="flex flex-col gap-6"
     x-data="classesPage({{ json_encode($classesJson) }}, {{ json_encode($classOpen) }}, {{ json_encode((bool) $classFormError) }}, {{ json_encode(['mode' => old('_class_mode', 'add'), 'id' => old('_class_id', ''), 'name' => old('name', ''), 'order' => old('order', '')]) }})">

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

    {{-- Main Card --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card">
        <div class="flex items-center justify-between px-6 py-4 border-b border-border">
            <div>
                <h3 class="text-base font-semibold text-text-primary">Classes &amp; Sections</h3>
                <p class="text-xs text-text-muted mt-0.5">Classes without sections are treated as a single group</p>
            </div>
            <button @click="openAddClass()"
                    class="flex items-center gap-2 px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Class
            </button>
        </div>

        @if($classes->isEmpty())
        <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
            <div class="w-12 h-12 rounded-xl bg-accent-muted flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-text-primary mb-1">No classes yet</p>
            <p class="text-xs text-text-muted mb-4">Add your first class to get started</p>
            <button @click="openAddClass()"
                    class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors">
                Add Class
            </button>
        </div>
        @else
        <table class="w-full">
            <thead>
                <tr class="border-b border-border">
                    <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Class Name</th>
                    <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Order</th>
                    <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Sections</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($classes as $class)
                <tr class="border-b border-border last:border-b-0 hover:bg-surface-secondary transition-colors">
                    <td class="px-6 py-4 text-sm font-medium text-text-primary">{{ $class->name }}</td>
                    <td class="px-6 py-4 text-sm text-text-muted">{{ $class->order }}</td>
                    <td class="px-6 py-4">
                        @if($class->sections->isEmpty())
                            <span class="text-xs text-text-muted">No sections</span>
                        @else
                            <div class="flex flex-wrap gap-1">
                                @foreach($class->sections as $section)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-accent-muted text-accent">
                                    {{ $section->name }}
                                </span>
                                @endforeach
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <button @click="openSections(classes.find(c => c.id === '{{ $class->id }}'))"
                                    class="text-xs font-medium text-text-secondary hover:text-text-primary transition-colors px-2 py-1 rounded hover:bg-surface-secondary">
                                Manage Sections
                            </button>
                            <button @click="openEditClass({
                                        id: '{{ $class->id }}',
                                        name: '{{ addslashes($class->name) }}',
                                        order: '{{ $class->order }}'
                                    })"
                                    class="text-xs font-medium text-text-secondary hover:text-text-primary transition-colors px-2 py-1 rounded hover:bg-surface-secondary">
                                Edit
                            </button>
                            <form method="POST" action="{{ $host }}/settings/classes/{{ $class->id }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        onclick="return confirm('Delete {{ addslashes($class->name) }} and all its sections? This cannot be undone.')"
                                        class="text-xs font-medium text-error hover:text-red-700 transition-colors px-2 py-1 rounded hover:bg-error-light">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    {{-- Add / Edit Class Modal --}}
    <div x-show="showClassModal"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display: none;">

        <div class="absolute inset-0 bg-overlay/40" @click="closeClassModal()"></div>

        <div class="relative w-full max-w-sm bg-surface rounded-2xl shadow-xl border border-border p-6"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-semibold text-text-primary"
                    x-text="classMode === 'add' ? 'Add Class' : 'Edit Class'"></h3>
                <button @click="closeClassModal()" class="p-1.5 rounded-md text-text-muted hover:bg-surface-secondary transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Add Form --}}
            <form x-show="classMode === 'add'" method="POST" action="{{ $host }}/settings/classes"
                  class="flex flex-col gap-4" @submit="submitting = true">
                @csrf
                <input type="hidden" name="_class_mode" value="add">
                <div>
                    <label class="block text-sm font-medium text-text-dark mb-1.5">Class Name <span class="text-error">*</span></label>
                    <input type="text" name="name" x-model="classForm.name" placeholder="e.g. Grade 1, JSS 2"
                           class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors"
                           required>
                    @error('name')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-text-dark mb-1.5">Sort Order</label>
                    <input type="number" name="order" x-model="classForm.order" placeholder="0" min="0"
                           class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                    @error('order')
                        <p class="mt-1 text-xs text-error">{{ $message }}</p>
                    @else
                        <p class="mt-1 text-xs text-text-muted">Leave blank to append at end</p>
                    @enderror
                </div>
                <div class="flex justify-end gap-3 mt-2">
                    <button type="button" @click="closeClassModal()"
                            class="px-4 py-2 bg-surface border border-border text-sm font-medium text-text-primary rounded-md hover:bg-surface-secondary transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            :disabled="submitting"
                            :class="submitting ? 'opacity-60 cursor-not-allowed' : 'hover:bg-accent-dark'"
                            class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md transition-colors">
                        <span x-show="!submitting">Add Class</span>
                        <span x-show="submitting">Saving&hellip;</span>
                    </button>
                </div>
            </form>

            {{-- Edit Form --}}
            <form x-show="classMode === 'edit'" method="POST"
                  :action="`{{ $host }}/settings/classes/${classForm.id}`"
                  class="flex flex-col gap-4" @submit="submitting = true">
                @csrf
                @method('PUT')
                <input type="hidden" name="_class_mode" value="edit">
                <input type="hidden" name="_class_id" :value="classForm.id">
                <div>
                    <label class="block text-sm font-medium text-text-dark mb-1.5">Class Name <span class="text-error">*</span></label>
                    <input type="text" name="name" x-model="classForm.name"
                           class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors"
                           required>
                    @error('name')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-text-dark mb-1.5">Sort Order</label>
                    <input type="number" name="order" x-model="classForm.order" min="0"
                           class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                    @error('order')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>
                <div class="flex justify-end gap-3 mt-2">
                    <button type="button" @click="closeClassModal()"
                            class="px-4 py-2 bg-surface border border-border text-sm font-medium text-text-primary rounded-md hover:bg-surface-secondary transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            :disabled="submitting"
                            :class="submitting ? 'opacity-60 cursor-not-allowed' : 'hover:bg-accent-dark'"
                            class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md transition-colors">
                        <span x-show="!submitting">Save Changes</span>
                        <span x-show="submitting">Saving&hellip;</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Manage Sections Modal --}}
    <div x-show="showSectionsModal"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display: none;">

        <div class="absolute inset-0 bg-overlay/40" @click="closeSectionsModal()"></div>

        <div class="relative w-full max-w-sm bg-surface rounded-2xl shadow-xl border border-border p-6"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            <div class="flex items-center justify-between mb-1">
                <h3 class="text-base font-semibold text-text-primary">
                    Sections &mdash; <span x-text="currentClass?.name"></span>
                </h3>
                <button @click="closeSectionsModal()" class="p-1.5 rounded-md text-text-muted hover:bg-surface-secondary transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <p class="text-xs text-text-muted mb-5">Classes without sections are treated as a single implicit group</p>

            {{-- Existing Sections --}}
            <div class="flex flex-col gap-2 mb-5 min-h-[40px]">
                <template x-if="currentClass?.sections?.length === 0">
                    <p class="text-xs text-text-muted text-center py-2">No sections added yet</p>
                </template>
                <template x-for="section in (currentClass?.sections || [])" :key="section.id">
                    <div class="flex items-center justify-between px-3 py-2 rounded-lg border border-border bg-surface-secondary">
                        <span class="text-sm font-medium text-text-primary" x-text="section.name"></span>
                        <form method="POST" :action="`{{ $host }}/settings/sections/${section.id}`"
                              @submit.prevent="if(confirm('Delete section ' + section.name + '?')) $el.submit()">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="p-1 rounded text-text-muted hover:text-error hover:bg-error-light transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </template>
            </div>

            {{-- Add Section Form --}}
            <form method="POST" :action="`{{ $host }}/settings/classes/${currentClass?.id}/sections`"
                  class="flex flex-col gap-2 border-t border-border pt-4" @submit="submitting = true">
                @csrf
                <input type="hidden" name="_section_class_id" :value="currentClass?.id">
                @if($sectionFormError)
                <p class="text-xs text-error">{{ $errors->first('name') }}</p>
                @endif
                <div class="flex gap-2">
                <input type="text" name="name" placeholder="Section name (e.g. A, B, Gold)"
                       class="flex-1 px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors"
                       required>
                <button type="submit"
                        :disabled="submitting"
                        :class="submitting ? 'opacity-60 cursor-not-allowed' : 'hover:bg-accent-dark'"
                        class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md transition-colors whitespace-nowrap">
                    <span x-show="!submitting">Add Section</span>
                    <span x-show="submitting">Saving&hellip;</span>
                </button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('classesPage', (classes, openClassId, hasClassError, oldClassData) => ({
        classes: classes,
        submitting: false,
        showClassModal: hasClassError,
        classMode: oldClassData.mode,
        classForm: {
            id:    oldClassData.id,
            name:  oldClassData.name,
            order: oldClassData.order,
        },
        showSectionsModal: !!openClassId,
        currentClass: openClassId ? (classes.find(c => c.id === openClassId) ?? null) : null,

        openAddClass() {
            this.classMode = 'add';
            this.classForm = { id: '', name: '', order: '' };
            this.showClassModal = true;
        },
        openEditClass(data) {
            this.classMode = 'edit';
            this.classForm = data;
            this.showClassModal = true;
        },
        openSections(cls) {
            this.currentClass = cls;
            this.showSectionsModal = true;
        },
        closeClassModal() {
            this.showClassModal = false;
            this.submitting = false;
        },
        closeSectionsModal() {
            this.showSectionsModal = false;
            this.submitting = false;
        },
    }));
});
</script>
@endpush
@endsection
