@extends('layouts.tenant')

@section('title', 'Announcements')
@section('page-title', 'Announcements')

@section('content')
@php $host = request()->getSchemeAndHttpHost(); @endphp

@php
    $annFormError = ($errors->has('title') || $errors->has('body')) && old('_ann_mode');
@endphp
<div class="flex flex-col gap-6"
     x-data="announcementsPage({{ Js::from($announcements->map(fn($a) => [
         'id'        => $a->id,
         'title'     => $a->title,
         'body'      => $a->body,
         'is_public' => $a->is_public,
     ])) }})">

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="bg-success-lightest border border-success-light text-success-foreground text-sm px-4 py-3 rounded-xl flex items-start gap-3">
        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="bg-error-light border border-error text-error text-sm px-4 py-3 rounded-xl flex items-start gap-3">
        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- Page header --}}
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-base font-semibold text-text-primary">Announcements</h1>
            <p class="text-xs text-text-muted mt-0.5">
                {{ $announcements->count() }} {{ Str::plural('announcement', $announcements->count()) }}
            </p>
        </div>
        @can('announcements.create')
        <button @click="openAdd()"
                class="flex items-center gap-2 px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span class="hidden sm:inline">Add Announcement</span>
            <span class="sm:hidden">Add</span>
        </button>
        @endcan
    </div>

    {{-- Announcements list --}}
    @if($announcements->isEmpty())
    <div class="bg-surface border border-border rounded-2xl shadow-card">
        <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
            <div class="w-12 h-12 rounded-xl bg-warning-light flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-text-primary mb-1">No announcements yet</p>
            <p class="text-xs text-text-muted mb-4">Post an announcement to notify all staff and students.</p>
            @can('announcements.create')
            <button @click="openAdd()"
                    class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors">
                Post First Announcement
            </button>
            @endcan
        </div>
    </div>
    @else
    <div class="flex flex-col gap-4">
        @foreach($announcements as $announcement)
        <div class="bg-surface border border-border rounded-2xl shadow-card overflow-hidden">

            {{-- Card header --}}
            <div class="flex items-start justify-between gap-4 px-6 py-4 border-b border-border">
                <div class="flex items-start gap-3 min-w-0">
                    {{-- Icon --}}
                    <div class="w-8 h-8 rounded-lg bg-warning-light flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-4 h-4 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-semibold text-text-primary leading-snug">{{ $announcement->title }}</h3>
                        <div class="flex flex-wrap items-center gap-2 mt-1">
                            <span class="text-xs text-text-muted">
                                {{ $announcement->created_at->format('M j, Y') }}
                                · Posted by {{ $announcement->postedBy?->name ?? 'Unknown' }}
                            </span>
                            @if($announcement->is_public)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-success-lightest text-success-foreground">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Public
                            </span>
                            @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-surface-secondary text-text-secondary">
                                Staff Only
                            </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-1 shrink-0">
                    @can('announcements.edit')
                    <button @click="openEdit({{ Js::from(['id' => $announcement->id, 'title' => $announcement->title, 'body' => $announcement->body, 'is_public' => $announcement->is_public]) }})"
                            class="p-1.5 rounded-md text-text-muted hover:text-text-primary hover:bg-surface-secondary transition-colors"
                            title="Edit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </button>
                    @endcan
                    @can('announcements.delete')
                    <form method="POST" action="{{ $host }}/announcements/{{ $announcement->id }}" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                onclick="return confirm('Delete this announcement? This cannot be undone.')"
                                class="p-1.5 rounded-md text-text-muted hover:text-error hover:bg-error-light transition-colors"
                                title="Delete">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>
                    @endcan
                </div>
            </div>

            {{-- Card body --}}
            <div class="px-6 py-5" x-data="{ expanded: false }">
                @php $bodyPreview = Str::limit($announcement->body, 240); @endphp
                @if(strlen($announcement->body) > 240)
                <div class="text-sm text-text-primary leading-relaxed whitespace-pre-line"
                     x-show="!expanded">{{ $bodyPreview }}</div>
                <div class="text-sm text-text-primary leading-relaxed whitespace-pre-line"
                     x-show="expanded" x-cloak>{{ $announcement->body }}</div>
                <button @click="expanded = !expanded"
                        class="mt-2 text-xs font-medium text-accent hover:text-accent-dark transition-colors"
                        x-text="expanded ? 'Show less' : 'Read more'"></button>
                @else
                <p class="text-sm text-text-primary leading-relaxed whitespace-pre-line">{{ $announcement->body }}</p>
                @endif
            </div>

        </div>
        @endforeach
    </div>
    @endif

    {{-- ── Add / Edit Modal ── --}}
    <div x-show="showModal"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-start justify-center p-4 pt-12 sm:items-center sm:pt-4"
         style="display: none;">

        <div class="absolute inset-0 bg-overlay/40" @click="close()"></div>

        <div class="relative w-full max-w-lg bg-surface rounded-2xl shadow-xl border border-border"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            {{-- Modal header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-border">
                <h3 class="text-base font-semibold text-text-primary"
                    x-text="mode === 'add' ? 'Post Announcement' : 'Edit Announcement'"></h3>
                <button @click="close()"
                        class="p-1.5 rounded-md text-text-muted hover:bg-surface-secondary transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Add form --}}
            <form x-show="mode === 'add'" method="POST" action="{{ $host }}/announcements"
                  class="flex flex-col gap-4 px-6 py-5" @submit="submitting = true">
                @csrf
                <input type="hidden" name="_ann_mode" value="add">

                <div>
                    <label class="block text-sm font-medium text-text-dark mb-1.5">
                        Title <span class="text-error">*</span>
                    </label>
                    <input type="text" name="title" x-model="form.title"
                           placeholder="e.g. Term 2 Timetable Released"
                           maxlength="150"
                           class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors"
                           required>
                    @error('title')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-dark mb-1.5">
                        Body <span class="text-error">*</span>
                    </label>
                    <textarea name="body" x-model="form.body" rows="6"
                              placeholder="Write the full announcement text here…"
                              maxlength="5000"
                              class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors resize-y"
                              required></textarea>
                    @error('body')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <label class="flex items-center gap-3 cursor-pointer select-none">
                    <input type="hidden" name="is_public" value="0">
                    <input type="checkbox" name="is_public" value="1"
                           x-model="form.is_public"
                           class="w-4 h-4 rounded border-border text-accent focus:ring-accent focus:ring-1">
                    <span class="text-sm text-text-primary">Show on public school page</span>
                </label>

                <div class="flex justify-end gap-3 pt-1">
                    <button type="button" @click="close()"
                            class="px-4 py-2 bg-surface border border-border text-sm font-medium text-text-primary rounded-md hover:bg-surface-secondary transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            :disabled="submitting"
                            :class="submitting ? 'opacity-60 cursor-not-allowed' : 'hover:bg-accent-dark'"
                            class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md transition-colors">
                        <span x-show="!submitting">Post Announcement</span>
                        <span x-show="submitting">Saving…</span>
                    </button>
                </div>
            </form>

            {{-- Edit form --}}
            <form x-show="mode === 'edit'" method="POST"
                  :action="`{{ $host }}/announcements/${form.id}`"
                  class="flex flex-col gap-4 px-6 py-5" @submit="submitting = true">
                @csrf
                <input type="hidden" name="_method" value="PUT">
                <input type="hidden" name="_ann_mode" value="edit">
                <input type="hidden" name="_ann_id" :value="form.id">

                <div>
                    <label class="block text-sm font-medium text-text-dark mb-1.5">
                        Title <span class="text-error">*</span>
                    </label>
                    <input type="text" name="title" x-model="form.title"
                           maxlength="150"
                           class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors"
                           required>
                    @error('title')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-dark mb-1.5">
                        Body <span class="text-error">*</span>
                    </label>
                    <textarea name="body" x-model="form.body" rows="6"
                              maxlength="5000"
                              class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors resize-y"
                              required></textarea>
                    @error('body')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <label class="flex items-center gap-3 cursor-pointer select-none">
                    <input type="hidden" name="is_public" value="0">
                    <input type="checkbox" name="is_public" value="1"
                           x-model="form.is_public"
                           class="w-4 h-4 rounded border-border text-accent focus:ring-accent focus:ring-1">
                    <span class="text-sm text-text-primary">Show on public school page</span>
                </label>

                <div class="flex justify-end gap-3 pt-1">
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

@push('scripts')
<script>
function announcementsPage(announcements) {
    return {
        showModal: false,
        submitting: false,
        mode: 'add',
        announcements,
        form: { id: '', title: '', body: '', is_public: false },

        init() {
            const annMode = @json(old('_ann_mode'));
            if (annMode === 'add' || annMode === 'edit') {
                this.$nextTick(() => {
                    this.mode = annMode;
                    this.form = {
                        id:        @json(old('_ann_id', '')),
                        title:     @json(old('title', '')),
                        body:      @json(old('body', '')),
                        is_public: @json((bool) old('is_public', false)),
                    };
                    this.showModal = true;
                });
            }
        },

        openAdd() {
            this.mode = 'add';
            this.form = { id: '', title: '', body: '', is_public: false };
            this.showModal = true;
        },

        openEdit(data) {
            this.mode = 'edit';
            this.form = { ...data };
            this.showModal = true;
        },

        close() {
            this.showModal = false;
            this.submitting = false;
        },
    };
}
</script>
@endpush
