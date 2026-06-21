@extends('layouts.tenant')

@section('title', 'My Account')
@section('page-title', 'My Account')

@section('content')
@php $host = request()->getSchemeAndHttpHost(); @endphp

<div class="flex flex-col gap-6 max-w-2xl">

    {{-- Profile Section --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card"
         x-data="{
             previewUrl: {{ json_encode($avatarUrl ?? '') }},
             submitting: false,
             onAvatarChange(e) {
                 const file = e.target.files[0];
                 if (file) { this.previewUrl = URL.createObjectURL(file); }
             }
         }">

        <div class="px-6 py-4 border-b border-border">
            <h3 class="text-base font-semibold text-text-primary">Profile</h3>
            <p class="text-xs text-text-muted mt-0.5">Update your name, email, phone number, and profile picture</p>
        </div>

        @if($errors->hasAny(['name','email','phone','avatar']))
        <div class="mx-6 mt-4 bg-error-light border border-error text-error text-sm px-4 py-3 rounded-xl">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->only(['name','email','phone','avatar']) as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ $host }}/account" enctype="multipart/form-data"
              class="p-6 space-y-5" @submit="submitting = true">
            @csrf
            @method('PATCH')

            {{-- Avatar Upload --}}
            <div>
                <label class="block text-sm font-medium text-text-dark mb-3">Profile Picture</label>
                <div class="flex items-start gap-5 flex-wrap">
                    <div class="w-20 h-20 rounded-xl border-2 border-border flex items-center justify-center shrink-0 overflow-hidden bg-surface-secondary">
                        <template x-if="previewUrl">
                            <img :src="previewUrl" alt="Avatar" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!previewUrl">
                            <span class="text-2xl font-semibold text-accent">
                                {{ mb_strtoupper(mb_substr($user->name ?? 'U', 0, 1)) }}
                            </span>
                        </template>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="avatar"
                               class="inline-flex items-center gap-2 px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                            Upload Photo
                        </label>
                        <input id="avatar" type="file" name="avatar" accept="image/*" class="hidden"
                               @change="onAvatarChange($event)">
                        <p class="text-xs text-text-muted">JPG, PNG, WebP up to 2 MB.</p>
                    </div>
                </div>
                @error('avatar')
                <p class="mt-2 text-xs text-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Name --}}
            <div>
                <label for="name" class="block text-sm font-medium text-text-dark mb-1.5">
                    Full Name <span class="text-error">*</span>
                </label>
                <input id="name" type="text" name="name"
                       value="{{ old('name', $user->name) }}"
                       required maxlength="150"
                       class="w-full px-3 py-2 bg-surface border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 transition-colors
                              {{ $errors->has('name') ? 'border-error focus:ring-error focus:border-error' : 'border-border focus:ring-accent focus:border-accent' }}">
                @error('name')
                <p class="mt-1 text-xs text-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email --}}
            <div>
                <label for="email" class="block text-sm font-medium text-text-dark mb-1.5">
                    Email Address <span class="text-error">*</span>
                </label>
                <input id="email" type="email" name="email"
                       value="{{ old('email', $user->email) }}"
                       required maxlength="150"
                       class="w-full px-3 py-2 bg-surface border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 transition-colors
                              {{ $errors->has('email') ? 'border-error focus:ring-error focus:border-error' : 'border-border focus:ring-accent focus:border-accent' }}">
                @error('email')
                <p class="mt-1 text-xs text-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Phone --}}
            <div>
                <label for="phone" class="block text-sm font-medium text-text-dark mb-1.5">Phone Number</label>
                <input id="phone" type="text" name="phone"
                       value="{{ old('phone', $user->phone) }}"
                       maxlength="30"
                       placeholder="+233 20 000 0000"
                       class="w-full px-3 py-2 bg-surface border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 transition-colors
                              {{ $errors->has('phone') ? 'border-error focus:ring-error focus:border-error' : 'border-border focus:ring-accent focus:border-accent' }}">
                @error('phone')
                <p class="mt-1 text-xs text-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-1">
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

    {{-- Password Section --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card"
         x-data="{ submitting: false }">

        <div class="px-6 py-4 border-b border-border">
            <h3 class="text-base font-semibold text-text-primary">Change Password</h3>
            <p class="text-xs text-text-muted mt-0.5">Enter your current password to set a new one</p>
        </div>

        @if($errors->hasAny(['current_password','new_password']))
        <div class="mx-6 mt-4 bg-error-light border border-error text-error text-sm px-4 py-3 rounded-xl">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->only(['current_password','new_password']) as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ $host }}/account/password"
              class="p-6 space-y-5" @submit="submitting = true">
            @csrf
            @method('PUT')

            {{-- Current Password --}}
            <div>
                <label for="current_password" class="block text-sm font-medium text-text-dark mb-1.5">
                    Current Password <span class="text-error">*</span>
                </label>
                <input id="current_password" type="password" name="current_password"
                       required autocomplete="current-password"
                       class="w-full px-3 py-2 bg-surface border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 transition-colors
                              {{ $errors->has('current_password') ? 'border-error focus:ring-error focus:border-error' : 'border-border focus:ring-accent focus:border-accent' }}">
                @error('current_password')
                <p class="mt-1 text-xs text-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- New Password + Confirm --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="new_password" class="block text-sm font-medium text-text-dark mb-1.5">
                        New Password <span class="text-error">*</span>
                    </label>
                    <input id="new_password" type="password" name="new_password"
                           required autocomplete="new-password" minlength="8"
                           class="w-full px-3 py-2 bg-surface border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 transition-colors
                                  {{ $errors->has('new_password') ? 'border-error focus:ring-error focus:border-error' : 'border-border focus:ring-accent focus:border-accent' }}">
                    @error('new_password')
                    <p class="mt-1 text-xs text-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="new_password_confirmation" class="block text-sm font-medium text-text-dark mb-1.5">
                        Confirm New Password <span class="text-error">*</span>
                    </label>
                    <input id="new_password_confirmation" type="password" name="new_password_confirmation"
                           required autocomplete="new-password"
                           class="w-full px-3 py-2 bg-surface border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 transition-colors border-border focus:ring-accent focus:border-accent">
                </div>
            </div>

            <div class="pt-1">
                <button type="submit"
                        :disabled="submitting"
                        :class="submitting ? 'opacity-60 cursor-not-allowed' : 'hover:bg-accent-dark'"
                        class="px-5 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md transition-colors">
                    <span x-show="!submitting">Change Password</span>
                    <span x-show="submitting">Saving…</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
