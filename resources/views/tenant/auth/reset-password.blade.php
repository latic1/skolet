<x-tenant-guest-layout>

    <div class="bg-surface border border-border rounded-2xl p-8 shadow-sm">

        {{-- Logo + heading --}}
        <div class="flex flex-col items-center mb-8">
            @if(isset($schoolProfile) && $schoolProfile?->logo_path)
            <img src="{{ request()->getSchemeAndHttpHost() . '/school-logo' }}"
                 alt="{{ $schoolProfile->school_name }}"
                 class="w-12 h-12 rounded-[10px] object-contain mb-4 shrink-0">
            @else
            <div class="w-12 h-12 rounded-[10px] flex items-center justify-center mb-4 shrink-0"
                 style="background: linear-gradient(45deg, #2563eb 0%, #1e3a8a 100%)">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            @endif
            <h1 class="text-[19px] font-bold text-text-darkest tracking-tight">Set new password</h1>
            <p class="mt-1 text-sm text-text-muted">Must be at least 8 characters.</p>
        </div>

        <form method="POST" action="{{ request()->getSchemeAndHttpHost() . '/reset-password' }}" class="space-y-4">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            <div>
                <label for="email" class="block text-sm font-medium text-text-dark mb-1.5">Email address</label>
                <input id="email"
                       type="email"
                       name="email"
                       value="{{ old('email', $email) }}"
                       required
                       autocomplete="username"
                       class="w-full px-3 py-2 bg-surface border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 transition-colors
                              {{ $errors->has('email') ? 'border-error focus:ring-error focus:border-error' : 'border-border focus:ring-accent focus:border-accent' }}"
                       placeholder="you@school.com">
                @error('email')
                    <p class="mt-1 text-xs text-error">{{ $message }}</p>
                @enderror
            </div>

            <div x-data="{ showPwd: false }">
                <label for="password" class="block text-sm font-medium text-text-dark mb-1.5">New password</label>
                <div class="relative">
                    <input id="password"
                           :type="showPwd ? 'text' : 'password'"
                           name="password"
                           required
                           autocomplete="new-password"
                           class="w-full px-3 py-2 pr-10 bg-surface border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 transition-colors
                                  {{ $errors->has('password') ? 'border-error focus:ring-error focus:border-error' : 'border-border focus:ring-accent focus:border-accent' }}"
                           placeholder="••••••••">
                    <button type="button"
                            @click="showPwd = !showPwd"
                            :aria-label="showPwd ? 'Hide password' : 'Show password'"
                            class="absolute inset-y-0 right-0 flex items-center px-3 text-text-muted hover:text-text-primary transition-colors">
                        <svg x-show="!showPwd" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="showPwd" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
                @error('password')
                    <p class="mt-1 text-xs text-error">{{ $message }}</p>
                @enderror
            </div>

            <div x-data="{ showConfirm: false }">
                <label for="password_confirmation" class="block text-sm font-medium text-text-dark mb-1.5">Confirm new password</label>
                <div class="relative">
                    <input id="password_confirmation"
                           :type="showConfirm ? 'text' : 'password'"
                           name="password_confirmation"
                           required
                           autocomplete="new-password"
                           class="w-full px-3 py-2 pr-10 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors"
                           placeholder="••••••••">
                    <button type="button"
                            @click="showConfirm = !showConfirm"
                            :aria-label="showConfirm ? 'Hide password' : 'Show password'"
                            class="absolute inset-y-0 right-0 flex items-center px-3 text-text-muted hover:text-text-primary transition-colors">
                        <svg x-show="!showConfirm" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="showConfirm" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit"
                    class="w-full px-4 py-2.5 text-sm font-medium bg-accent text-accent-foreground rounded-md hover:bg-accent-dark transition-colors mt-2">
                Reset password
            </button>
        </form>

    </div>

</x-tenant-guest-layout>
