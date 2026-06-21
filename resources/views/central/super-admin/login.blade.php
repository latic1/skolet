<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Super Admin — SchoolFlow</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-background text-text-primary min-h-screen flex items-center justify-center">

<div class="w-full max-w-sm px-4">

    {{-- Logo --}}
    <div class="flex flex-col items-center mb-8">
        <div class="w-12 h-12 rounded-[10px] flex items-center justify-center mb-4"
             style="background: linear-gradient(45deg, #2563eb 0%, #1e3a8a 100%)">
            <svg width="24" height="24" viewBox="0 0 20 20" fill="none">
                <path d="M10 2L3 6V10C3 13.866 6.134 17 10 17C13.866 17 17 13.866 17 10V6L10 2Z" fill="white" fill-opacity="0.9"/>
                <path d="M7 10L9 12L13 8" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <h1 class="text-xl font-bold text-text-darkest">SchoolFlow</h1>
        <p class="mt-1 text-sm text-text-muted">Super Admin Portal</p>
    </div>

    {{-- Card --}}
    <div class="bg-surface border border-border rounded-2xl p-8 shadow-sm">

        @if ($errors->any())
            <div class="mb-4 bg-error-light border border-error text-error text-sm px-4 py-3 rounded-xl">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('super-admin.login.post') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-text-dark mb-1.5">Email address</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="w-full px-3 py-2 bg-surface border rounded-md text-sm focus:outline-none focus:ring-1 transition-colors
                              {{ $errors->has('email') ? 'border-error focus:ring-error' : 'border-border focus:ring-accent focus:border-accent' }}"
                       placeholder="admin@schoolflow.com">
            </div>

            <div>
                <label class="block text-sm font-medium text-text-dark mb-1.5">Password</label>
                <input type="password" name="password" required
                       class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors"
                       placeholder="••••••••">
            </div>

            <div class="flex items-center">
                <input id="remember" type="checkbox" name="remember"
                       class="w-4 h-4 rounded border-border text-accent focus:ring-accent">
                <label for="remember" class="ml-2 text-sm text-text-secondary">Remember me</label>
            </div>

            <button type="submit"
                    class="w-full px-4 py-2.5 text-sm font-medium bg-accent text-accent-foreground rounded-md hover:bg-accent-dark transition-colors mt-2">
                Sign in
            </button>
        </form>
    </div>

    <p class="mt-6 text-center text-xs text-text-muted">
        <a href="{{ route('home') }}" class="hover:text-text-secondary transition-colors">← Back to SchoolFlow</a>
    </p>

</div>

</body>
</html>
