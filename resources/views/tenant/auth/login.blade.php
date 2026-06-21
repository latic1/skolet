<x-tenant-guest-layout>

    {{-- Card --}}
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
            <h1 class="text-[19px] font-bold text-text-darkest tracking-tight">{{ $schoolProfile?->school_name ?? config('app.name') }}</h1>
            <p class="mt-1 text-sm text-text-muted">Sign in to your school account</p>
        </div>

        {{-- Session status --}}
        @if (session('status'))
            <div class="mb-4 bg-success-lightest border border-success-light text-success-foreground text-sm px-4 py-3 rounded-xl">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ request()->getSchemeAndHttpHost() . '/login' }}" class="space-y-4">
            @csrf

            {{-- Email --}}
            <div>
                <label for="email" class="block text-sm font-medium text-text-dark mb-1.5">Email address</label>
                <input id="email"
                       type="email"
                       name="email"
                       value="{{ old('email') }}"
                       required
                       autofocus
                       autocomplete="username"
                       class="w-full px-3 py-2 bg-surface border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 transition-colors
                              {{ $errors->has('email') ? 'border-error focus:ring-error focus:border-error' : 'border-border focus:ring-accent focus:border-accent' }}"
                       placeholder="you@school.com">
                @error('email')
                    <p class="mt-1 text-xs text-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password --}}
            <div>
                <label for="password" class="block text-sm font-medium text-text-dark mb-1.5">Password</label>
                <input id="password"
                       type="password"
                       name="password"
                       required
                       autocomplete="current-password"
                       class="w-full px-3 py-2 bg-surface border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 transition-colors
                              {{ $errors->has('password') ? 'border-error focus:ring-error focus:border-error' : 'border-border focus:ring-accent focus:border-accent' }}"
                       placeholder="••••••••">
                @error('password')
                    <p class="mt-1 text-xs text-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Remember me --}}
            <div class="flex items-center">
                <input id="remember_me" type="checkbox" name="remember"
                       class="w-4 h-4 rounded border-border text-accent focus:ring-accent">
                <label for="remember_me" class="ml-2 text-sm text-text-secondary">Remember me</label>
            </div>

            {{-- Submit --}}
            <button type="submit"
                    class="w-full px-4 py-2.5 text-sm font-medium bg-accent text-accent-foreground rounded-md hover:bg-accent-dark transition-colors mt-2">
                Sign in
            </button>
        </form>
    </div>

</x-tenant-guest-layout>
