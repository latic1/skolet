@extends('layouts.tenant')

@section('title', 'Settings — School Profile')
@section('page-title', 'Settings')

@section('content')
@php $host = request()->getSchemeAndHttpHost(); @endphp
<div class="flex flex-col gap-6"
     x-data="{ previewUrl: {{ json_encode($logoUrl ?? '') }}, submitting: false }">

    {{-- Settings Sub-Nav --}}
    <div class="flex items-center gap-1 border-b border-border pb-0 overflow-x-auto">
        <a href="{{ $host }}/settings/academic-year"
           class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors whitespace-nowrap border-transparent text-text-secondary hover:text-text-primary">
            Academic Calendar
        </a>
        <a href="{{ $host }}/settings/roles"
           class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors whitespace-nowrap border-transparent text-text-secondary hover:text-text-primary">
            Roles &amp; Permissions
        </a>
        <a href="{{ $host }}/settings/profile"
           class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors whitespace-nowrap border-accent text-accent">
            School Profile
        </a>
        <a href="{{ $host }}/settings/domain"
           class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors whitespace-nowrap border-transparent text-text-secondary hover:text-text-primary">
            Custom Domain
        </a>
        <a href="{{ $host }}/settings/notifications"
           class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors whitespace-nowrap border-transparent text-text-secondary hover:text-text-primary">
            Notifications
        </a>
        <a href="{{ $host }}/settings/audit-log"
           class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors whitespace-nowrap border-transparent text-text-secondary hover:text-text-primary">
            Audit Log
        </a>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="bg-success-lightest border border-success-light text-success-foreground text-sm px-4 py-3 rounded-xl flex items-start gap-3">
        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif
    @if($errors->any())
    <div class="bg-error-light border border-error text-error text-sm px-4 py-3 rounded-xl">
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Profile Card --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card">
        <div class="px-6 py-4 border-b border-border">
            <h3 class="text-base font-semibold text-text-primary">School Profile</h3>
            <p class="text-xs text-text-muted mt-0.5">Customize your school's branding and contact information</p>
        </div>

        <form method="POST" action="{{ $host }}/settings/profile" enctype="multipart/form-data"
              class="p-6 space-y-6" @submit="submitting = true">
            @csrf

            {{-- Logo Upload --}}
            <div>
                <label class="block text-sm font-medium text-text-dark mb-3">School Logo</label>
                <div class="flex items-start gap-5 flex-wrap">
                    <div class="w-20 h-20 rounded-xl border-2 border-border flex items-center justify-center shrink-0 overflow-hidden bg-surface-secondary">
                        <template x-if="previewUrl">
                            <img :src="previewUrl" alt="Logo preview" class="w-full h-full object-contain">
                        </template>
                        <template x-if="!previewUrl">
                            <div class="w-10 h-10 rounded-[8px] flex items-center justify-center"
                                 style="background: linear-gradient(45deg, #2563eb 0%, #1e3a8a 100%)">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            </div>
                        </template>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="logo"
                               class="inline-flex items-center gap-2 px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                            Upload Logo
                        </label>
                        <input id="logo" type="file" name="logo" accept="image/*" class="hidden"
                               @change="previewUrl = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : previewUrl">
                        <p class="text-xs text-text-muted">PNG, JPG, SVG up to 2 MB. Square, min 128 × 128 px recommended.</p>
                    </div>
                </div>
                @error('logo')
                <p class="mt-2 text-xs text-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- School Name --}}
            <div>
                <label for="school_name" class="block text-sm font-medium text-text-dark mb-1.5">
                    School Name <span class="text-error">*</span>
                </label>
                <input id="school_name"
                       type="text"
                       name="school_name"
                       value="{{ old('school_name', $profile?->school_name) }}"
                       required
                       maxlength="150"
                       placeholder="e.g. Accra Academy"
                       class="w-full sm:max-w-md px-3 py-2 bg-surface border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 transition-colors
                              {{ $errors->has('school_name') ? 'border-error focus:ring-error focus:border-error' : 'border-border focus:ring-accent focus:border-accent' }}">
                @error('school_name')
                <p class="mt-1 text-xs text-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Short Description --}}
            <div>
                <label for="short_description" class="block text-sm font-medium text-text-dark mb-1.5">Short Description</label>
                <textarea id="short_description"
                          name="short_description"
                          rows="3"
                          maxlength="500"
                          placeholder="A brief description of your school"
                          class="w-full sm:max-w-lg px-3 py-2 bg-surface border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 transition-colors resize-none
                                 {{ $errors->has('short_description') ? 'border-error focus:ring-error focus:border-error' : 'border-border focus:ring-accent focus:border-accent' }}">{{ old('short_description', $profile?->short_description) }}</textarea>
                @error('short_description')
                <p class="mt-1 text-xs text-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Contact fields --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 sm:max-w-lg">
                <div class="sm:col-span-2">
                    <label for="address" class="block text-sm font-medium text-text-dark mb-1.5">Address</label>
                    <input id="address"
                           type="text"
                           name="address"
                           value="{{ old('address', $profile?->address) }}"
                           maxlength="255"
                           placeholder="School physical address"
                           class="w-full px-3 py-2 bg-surface border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 transition-colors
                                  {{ $errors->has('address') ? 'border-error focus:ring-error focus:border-error' : 'border-border focus:ring-accent focus:border-accent' }}">
                    @error('address')
                    <p class="mt-1 text-xs text-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-text-dark mb-1.5">Phone</label>
                    <input id="phone"
                           type="text"
                           name="phone"
                           value="{{ old('phone', $profile?->phone) }}"
                           maxlength="30"
                           placeholder="+233 20 000 0000"
                           class="w-full px-3 py-2 bg-surface border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 transition-colors
                                  {{ $errors->has('phone') ? 'border-error focus:ring-error focus:border-error' : 'border-border focus:ring-accent focus:border-accent' }}">
                    @error('phone')
                    <p class="mt-1 text-xs text-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-text-dark mb-1.5">Email</label>
                    <input id="email"
                           type="email"
                           name="email"
                           value="{{ old('email', $profile?->email) }}"
                           maxlength="150"
                           placeholder="info@school.edu.gh"
                           class="w-full px-3 py-2 bg-surface border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 transition-colors
                                  {{ $errors->has('email') ? 'border-error focus:ring-error focus:border-error' : 'border-border focus:ring-accent focus:border-accent' }}">
                    @error('email')
                    <p class="mt-1 text-xs text-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="website" class="block text-sm font-medium text-text-dark mb-1.5">Website</label>
                    <input id="website"
                           type="url"
                           name="website"
                           value="{{ old('website', $profile?->website) }}"
                           maxlength="255"
                           placeholder="https://school.edu.gh"
                           class="w-full px-3 py-2 bg-surface border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 transition-colors
                                  {{ $errors->has('website') ? 'border-error focus:ring-error focus:border-error' : 'border-border focus:ring-accent focus:border-accent' }}">
                    @error('website')
                    <p class="mt-1 text-xs text-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Admission Number Pattern --}}
            <div class="border-t border-border pt-6" x-data="admissionPattern(
                '{{ old('admission_pattern', $profile?->admission_pattern ?? '{YEAR}/{SEQ:4}') }}',
                {{ ($profile?->admission_counter ?? 0) + 1 }}
            )">
                <div class="mb-4">
                    <h4 class="text-sm font-semibold text-text-primary">Student Index Number Pattern</h4>
                    <p class="text-xs text-text-muted mt-0.5">Define how admission/index numbers are auto-generated when adding students.</p>
                </div>

                <div class="flex flex-col gap-3 sm:max-w-lg">
                    {{-- Pattern input --}}
                    <div>
                        <label class="block text-xs font-medium text-text-secondary uppercase tracking-wide mb-1.5">Pattern</label>
                        <input type="text" name="admission_pattern" x-model="pattern"
                               @input="updatePreview()"
                               maxlength="100"
                               placeholder="{YEAR}/{SEQ:4}"
                               class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary font-mono placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                        @error('admission_pattern')
                        <p class="mt-1 text-xs text-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Token buttons --}}
                    <div class="flex flex-wrap gap-1.5">
                        <span class="text-xs text-text-muted self-center">Insert:</span>
                        <template x-for="token in tokens" :key="token">
                            <button type="button" @click="insertToken(token)"
                                    class="px-2.5 py-1 text-xs font-mono font-medium bg-surface-secondary border border-border rounded-md text-text-dark hover:bg-accent-muted hover:border-accent hover:text-accent transition-colors"
                                    x-text="token"></button>
                        </template>
                    </div>

                    {{-- Live preview --}}
                    <div class="flex items-center gap-3 px-4 py-3 bg-surface-secondary rounded-lg border border-border">
                        <span class="text-xs text-text-muted shrink-0">Next number:</span>
                        <span class="text-sm font-semibold font-mono text-accent" x-text="preview"></span>
                    </div>

                    {{-- Token reference --}}
                    <div class="text-xs text-text-muted space-y-1">
                        <p><span class="font-mono text-text-dark">{YEAR}</span> — 4-digit year (e.g. {{ now()->year }})</p>
                        <p><span class="font-mono text-text-dark">{YY}</span> — 2-digit year (e.g. {{ now()->format('y') }})</p>
                        <p><span class="font-mono text-text-dark">{SEQ:4}</span> — sequence padded to N digits (e.g. 0001, 0042)</p>
                    </div>
                </div>
            </div>

            <div class="pt-2">
                <button type="submit"
                        :disabled="submitting"
                        :class="submitting ? 'opacity-60 cursor-not-allowed' : 'hover:bg-accent-dark'"
                        class="px-5 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md transition-colors">
                    <span x-show="!submitting">Save Profile</span>
                    <span x-show="submitting">Saving…</span>
                </button>
            </div>
        </form>
    </div>

    {{-- Reset Sequence Card --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card">
        <div class="px-6 py-4 border-b border-border">
            <h3 class="text-base font-semibold text-text-primary">Index Number Sequence</h3>
            <p class="text-xs text-text-muted mt-0.5">
                Current counter: <span class="font-semibold text-text-primary">{{ $profile?->admission_counter ?? 0 }}</span>
                — the next student will get sequence <span class="font-semibold text-text-primary">{{ ($profile?->admission_counter ?? 0) + 1 }}</span>.
            </p>
        </div>
        <div class="px-6 py-4">
            <p class="text-sm text-text-secondary mb-4">
                Resetting the counter back to zero means the next student will start from sequence 1.
                Only do this if you are sure there are no existing students, or if you have changed the pattern to avoid duplicate numbers.
            </p>
            <form method="POST" action="{{ $host }}/settings/profile/reset-counter"
                  onsubmit="return confirm('Reset the sequence counter to zero? This could cause duplicate index numbers if students already exist.')">
                @csrf
                <button type="submit"
                        class="px-4 py-2 bg-surface border border-error text-error text-sm font-medium rounded-md hover:bg-error-light transition-colors">
                    Reset Sequence to Zero
                </button>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function admissionPattern(initialPattern, nextCounter) {
    return {
        pattern: initialPattern,
        preview: '',
        nextCounter,
        tokens: ['{YEAR}', '{YY}', '{SEQ:4}', '{SEQ:3}', '{SEQ:5}'],

        init() {
            this.updatePreview();
        },

        updatePreview() {
            const now = new Date();
            const year = now.getFullYear().toString();
            const yy   = year.slice(-2);
            let p = this.pattern || '{YEAR}/{SEQ:4}';
            p = p.replaceAll('{YEAR}', year);
            p = p.replaceAll('{YY}', yy);
            p = p.replace(/\{SEQ(?::(\d+))?\}/g, (_, n) => {
                const pad = n ? parseInt(n) : 4;
                return this.nextCounter.toString().padStart(pad, '0');
            });
            this.preview = p;
        },

        insertToken(token) {
            this.pattern = (this.pattern || '') + token;
            this.updatePreview();
        },
    };
}
</script>
@endpush
