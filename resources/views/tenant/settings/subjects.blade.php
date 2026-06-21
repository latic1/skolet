@extends('layouts.tenant')

@section('title', 'Subjects')
@section('page-title', 'Subjects')

@section('content')
@php
    $host = request()->getSchemeAndHttpHost();
    $subjectFormError = ($errors->has('name') || $errors->has('code')) && old('_subject_mode');
@endphp
<div class="flex flex-col gap-6" x-data="{
    showModal: {{ $subjectFormError ? 'true' : 'false' }},
    submitting: false,
    mode: '{{ old('_subject_mode', 'add') }}',
    form: {
        id:   {{ json_encode(old('_subject_id', '')) }},
        name: {{ json_encode(old('name', '')) }},
        code: {{ json_encode(old('code', '')) }},
    },
    openAdd() {
        this.mode = 'add';
        this.form = { id: '', name: '', code: '' };
        this.showModal = true;
    },
    openEdit(data) {
        this.mode = 'edit';
        this.form = data;
        this.showModal = true;
    },
    close() {
        this.showModal = false;
        this.submitting = false;
    }
}">

    {{-- Main Card --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card">
        <div class="flex items-center justify-between px-6 py-4 border-b border-border">
            <div>
                <h3 class="text-base font-semibold text-text-primary">Subjects</h3>
                <p class="text-xs text-text-muted mt-0.5">Subjects taught across your school's classes</p>
            </div>
            <button @click="openAdd()"
                    class="flex items-center gap-2 px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Subject
            </button>
        </div>

        @if($subjects->isEmpty())
        <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
            <div class="w-12 h-12 rounded-xl bg-accent-muted flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-text-primary mb-1">No subjects yet</p>
            <p class="text-xs text-text-muted mb-4">Add subjects like Mathematics, English, Science</p>
            <button @click="openAdd()"
                    class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors">
                Add Subject
            </button>
        </div>
        @else
        <table class="w-full">
            <thead>
                <tr class="border-b border-border">
                    <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Subject Name</th>
                    <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Code</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($subjects as $subject)
                <tr class="border-b border-border last:border-b-0 hover:bg-surface-secondary transition-colors">
                    <td class="px-6 py-4 text-sm font-medium text-text-primary">{{ $subject->name }}</td>
                    <td class="px-6 py-4">
                        @if($subject->code)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-surface-secondary text-text-secondary border border-border">
                                {{ $subject->code }}
                            </span>
                        @else
                            <span class="text-xs text-text-muted">—</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <button @click="openEdit({
                                        id: '{{ $subject->id }}',
                                        name: '{{ addslashes($subject->name) }}',
                                        code: '{{ addslashes($subject->code ?? '') }}'
                                    })"
                                    class="text-xs font-medium text-text-secondary hover:text-text-primary transition-colors px-2 py-1 rounded hover:bg-surface-secondary">
                                Edit
                            </button>
                            <form method="POST" action="{{ $host }}/settings/subjects/{{ $subject->id }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        onclick="return confirm('Delete {{ addslashes($subject->name) }}? This cannot be undone.')"
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

    {{-- Add / Edit Modal --}}
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

        <div class="relative w-full max-w-sm bg-surface rounded-2xl shadow-xl border border-border p-6"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-semibold text-text-primary"
                    x-text="mode === 'add' ? 'Add Subject' : 'Edit Subject'"></h3>
                <button @click="close()" class="p-1.5 rounded-md text-text-muted hover:bg-surface-secondary transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Add Form --}}
            <form x-show="mode === 'add'" method="POST" action="{{ $host }}/settings/subjects"
                  class="flex flex-col gap-4" @submit="submitting = true">
                @csrf
                <input type="hidden" name="_subject_mode" value="add">
                <div>
                    <label class="block text-sm font-medium text-text-dark mb-1.5">Subject Name <span class="text-error">*</span></label>
                    <input type="text" name="name" x-model="form.name" placeholder="e.g. Mathematics, English Language"
                           class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors"
                           required>
                    @error('name')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-text-dark mb-1.5">Code <span class="text-text-muted font-normal">(optional)</span></label>
                    <input type="text" name="code" x-model="form.code" placeholder="e.g. MATH, ENG"
                           class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                    @error('code')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
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
                        <span x-show="!submitting">Add Subject</span>
                        <span x-show="submitting">Saving…</span>
                    </button>
                </div>
            </form>

            {{-- Edit Form --}}
            <form x-show="mode === 'edit'" method="POST"
                  :action="`{{ $host }}/settings/subjects/${form.id}`"
                  class="flex flex-col gap-4" @submit="submitting = true">
                @csrf
                @method('PUT')
                <input type="hidden" name="_subject_mode" value="edit">
                <input type="hidden" name="_subject_id" :value="form.id">
                <div>
                    <label class="block text-sm font-medium text-text-dark mb-1.5">Subject Name <span class="text-error">*</span></label>
                    <input type="text" name="name" x-model="form.name"
                           class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors"
                           required>
                    @error('name')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-text-dark mb-1.5">Code <span class="text-text-muted font-normal">(optional)</span></label>
                    <input type="text" name="code" x-model="form.code" placeholder="e.g. MATH, ENG"
                           class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                    @error('code')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
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
                        <span x-show="submitting">Saving…</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
