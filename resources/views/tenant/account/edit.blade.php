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


    {{-- API Tokens Section --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card" id="api-tokens"
         x-data="{
             showCreateModal: false,
             showNewToken: {{ session('new_token') ? 'true' : 'false' }},
             newToken: {{ session('new_token') ? json_encode(session('new_token')) : "''" }},
             newTokenName: {{ session('new_token_name') ? json_encode(session('new_token_name')) : "''" }},
             copied: false,
             copyToken() {
                 navigator.clipboard.writeText(this.newToken).then(() => {
                     this.copied = true;
                     setTimeout(() => { this.copied = false; }, 2000);
                 });
             },
             submitting: false,
         }">

        <div class="px-6 py-4 border-b border-border flex items-center justify-between">
            <div>
                <h3 class="text-base font-semibold text-text-primary">API Tokens</h3>
                <p class="text-xs text-text-muted mt-0.5">Tokens authenticate API requests to <code class="bg-surface-secondary px-1 rounded text-xs">{{ request()->getSchemeAndHttpHost() }}/api/v1/</code></p>
            </div>
            <button @click="showCreateModal = true"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Generate Token
            </button>
        </div>

        {{-- Newly created token (one-time display) --}}
        <template x-if="showNewToken">
            <div class="mx-6 mt-4 bg-success-lightest border border-success text-success-foreground text-sm px-4 py-3 rounded-xl">
                <p class="font-semibold mb-2">Token "<span x-text="newTokenName"></span>" created — copy it now, it won't be shown again.</p>
                <div class="flex items-center gap-2 flex-wrap">
                    <code class="flex-1 break-all bg-surface px-3 py-2 rounded-lg text-xs text-text-primary border border-border font-mono" x-text="newToken"></code>
                    <button @click="copyToken()"
                            class="shrink-0 px-3 py-2 bg-success text-white text-xs font-medium rounded-md hover:opacity-90 transition-opacity">
                        <span x-show="!copied">Copy</span>
                        <span x-show="copied">Copied!</span>
                    </button>
                </div>
            </div>
        </template>

        @if(session('success') && !session('new_token'))
        <div class="mx-6 mt-4 bg-success-lightest border border-success text-success-foreground text-sm px-4 py-3 rounded-xl">
            {{ session('success') }}
        </div>
        @endif

        <div class="p-6">
            @if($tokens->isEmpty())
            <div class="text-center py-10 text-text-muted">
                <svg class="w-10 h-10 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
                <p class="text-sm">No API tokens yet. Generate one to start integrating.</p>
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm" style="min-width:520px">
                    <thead>
                        <tr class="text-left text-xs text-text-muted border-b border-border">
                            <th class="pb-2 pr-4 font-medium">Name</th>
                            <th class="pb-2 pr-4 font-medium">Scope</th>
                            <th class="pb-2 pr-4 font-medium">Created</th>
                            <th class="pb-2 font-medium">Last Used</th>
                            <th class="pb-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach($tokens as $token)
                        <tr>
                            <td class="py-3 pr-4 font-medium text-text-primary">{{ $token->name }}</td>
                            <td class="py-3 pr-4">
                                @if(in_array('write', $token->abilities ?? []))
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-accent-muted text-accent">Full Access</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-surface-secondary text-text-muted">Read Only</span>
                                @endif
                            </td>
                            <td class="py-3 pr-4 text-text-muted text-xs">{{ $token->created_at->format('d M Y') }}</td>
                            <td class="py-3 pr-4 text-text-muted text-xs">
                                {{ $token->last_used_at ? $token->last_used_at->diffForHumans() : 'Never' }}
                            </td>
                            <td class="py-3 text-right">
                                <form method="POST" action="{{ request()->getSchemeAndHttpHost() }}/account/tokens/{{ $token->id }}"
                                      onsubmit="return confirm('Revoke this token? Any integrations using it will stop working.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-xs text-error hover:underline">Revoke</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        {{-- Generate Token Modal --}}
        <template x-if="showCreateModal">
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
                 @click.self="showCreateModal = false">
                <div class="bg-surface rounded-2xl shadow-xl w-full max-w-sm" @click.stop>
                    <div class="flex items-center justify-between px-6 py-4 border-b border-border">
                        <h4 class="text-base font-semibold text-text-primary">Generate API Token</h4>
                        <button @click="showCreateModal = false" class="text-text-muted hover:text-text-primary">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <form method="POST" action="{{ request()->getSchemeAndHttpHost() }}/account/tokens"
                          class="p-6 space-y-4" @submit="submitting = true">
                        @csrf
                        <div>
                            <label for="token_name" class="block text-sm font-medium text-text-dark mb-1.5">
                                Token Name <span class="text-error">*</span>
                            </label>
                            <input id="token_name" type="text" name="token_name"
                                   required maxlength="100" placeholder="e.g. Biometric Gate, Mobile App"
                                   class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-dark mb-1.5">Scope</label>
                            <div class="space-y-2">
                                <label class="flex items-start gap-3 cursor-pointer">
                                    <input type="radio" name="token_scope" value="read-only" checked
                                           class="mt-0.5 accent-[var(--color-accent)]">
                                    <div>
                                        <p class="text-sm font-medium text-text-primary">Read Only</p>
                                        <p class="text-xs text-text-muted">GET endpoints only (students, fees, exams, announcements)</p>
                                    </div>
                                </label>
                                <label class="flex items-start gap-3 cursor-pointer">
                                    <input type="radio" name="token_scope" value="full"
                                           class="mt-0.5 accent-[var(--color-accent)]">
                                    <div>
                                        <p class="text-sm font-medium text-text-primary">Full Access</p>
                                        <p class="text-xs text-text-muted">Read + write (includes POST /attendance for biometric gates)</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-3 pt-1">
                            <button type="button" @click="showCreateModal = false"
                                    class="px-4 py-2 text-sm text-text-muted hover:text-text-primary transition-colors">Cancel</button>
                            <button type="submit"
                                    :disabled="submitting"
                                    :class="submitting ? 'opacity-60 cursor-not-allowed' : 'hover:bg-accent-dark'"
                                    class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md transition-colors">
                                <span x-show="!submitting">Generate</span>
                                <span x-show="submitting">Generating…</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </div>

</div>
@endsection
