@extends('layouts.central')

@section('title', 'Log in to Your School — Skolet')
@section('description', 'Enter your school\'s subdomain to access your Skolet dashboard.')

@section('content')
<div class="min-h-[calc(100vh-72px)] bg-background flex items-center justify-center px-4 py-16">
    <div class="w-full max-w-md">

        {{-- Card --}}
        <div class="bg-surface border border-border rounded-2xl p-8 shadow-sm"
             x-data="{
                subdomain: '',
                get loginUrl() {
                    const base = window.location.hostname.replace(/^www\./i, '').replace(/:\d+$/, '');
                    const port = window.location.port ? ':' + window.location.port : '';
                    return window.location.protocol + '//' + this.subdomain.trim().toLowerCase() + '.' + base + port + '/login';
                },
                submit() {
                    const s = this.subdomain.trim().toLowerCase();
                    if (!s) return;
                    window.location.href = this.loginUrl;
                }
             }">

            {{-- Logo --}}
            <div class="flex justify-center mb-8">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-[12px] flex items-center justify-center flex-shrink-0"
                         style="background: linear-gradient(45deg, #2563eb 0%, #1e3a8a 100%)">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10 2L3 6V10C3 13.866 6.134 17 10 17C13.866 17 17 13.866 17 10V6L10 2Z" fill="white" fill-opacity="0.9"/>
                            <path d="M7 10L9 12L13 8" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-text-darkest tracking-tight">Skolet</span>
                </a>
            </div>

            <h1 class="text-2xl font-bold text-text-darkest text-center mb-1">Find your school</h1>
            <p class="text-sm text-text-secondary text-center mb-8">
                Enter your school's subdomain to continue to your dashboard.
            </p>

            <form @submit.prevent="submit" novalidate>
                <div class="mb-5">
                    <label for="subdomain" class="block text-sm font-medium text-text-dark mb-1.5">
                        School subdomain
                    </label>
                    <div class="flex rounded-lg border border-border focus-within:border-accent focus-within:ring-1 focus-within:ring-accent overflow-hidden transition-all">
                        <input
                            id="subdomain"
                            type="text"
                            x-model="subdomain"
                            placeholder="yourschool"
                            autocomplete="off"
                            autocapitalize="none"
                            class="flex-1 px-3 py-2.5 text-sm text-text-darkest bg-surface outline-none placeholder:text-text-muted"
                        />
                        <span class="flex items-center px-3 text-sm text-text-secondary bg-surface-secondary border-l border-border select-none whitespace-nowrap">
                            .{{ parse_url(config('app.url'), PHP_URL_HOST) ?? 'skolet.com' }}
                        </span>
                    </div>
                </div>

                <button
                    type="submit"
                    :disabled="!subdomain.trim()"
                    class="w-full py-2.5 px-4 rounded-lg text-sm font-semibold bg-accent text-accent-foreground hover:bg-accent-dark transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    Go to my school
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-text-secondary">
                Don't have a school yet?
                <a href="{{ route('register-school') }}" class="text-accent font-medium hover:underline">Register School</a>
            </p>
        </div>

    </div>
</div>
@endsection
