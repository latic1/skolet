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
</div>
@endsection
