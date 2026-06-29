@extends('layouts.central')

@section('title', 'Register Your School &mdash; Skolet')
@section('meta_description', 'Register your school on Skolet in minutes. Choose a subdomain, create your admin account, and your school management platform is ready.')

@section('content')
@php $errors = $errors ?? new \Illuminate\Support\ViewErrorBag; @endphp
<section class="bg-background min-h-screen py-16">
    <div class="max-w-360 mx-auto px-6">
        <div class="max-w-lg mx-auto">

            {{-- Header --}}
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-text-primary">Register your school</h1>
                <p class="mt-2 text-text-secondary text-sm">Your school will be live at <span class="font-medium text-text-primary">yourschool.skolet.com</span> in minutes.</p>
            </div>

            {{-- Flash success --}}
            @if (session('status'))
            <div class="mb-6 flex items-start gap-3 bg-success-lightest border border-success-light text-success-foreground text-sm px-4 py-3 rounded-xl">
                <svg class="w-4 h-4 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ session('status') }}
            </div>
            @endif

            {{-- Validation errors --}}
            @if ($errors->any())
            <div class="mb-6 flex items-start gap-3 bg-error-light border border-error text-error text-sm px-4 py-3 rounded-xl">
                <svg class="w-4 h-4 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p class="font-medium mb-1">Please fix the following errors:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif

            {{-- Form Card --}}
            <div class="bg-surface border border-border rounded-2xl p-8 shadow-card">
                <form action="{{ route('register-school.store') }}" method="POST" novalidate
                      x-data="{
                          subdomain: '{{ old('subdomain', '') }}',
                          get preview() {
                              return this.subdomain.trim().toLowerCase().replace(/[^a-z0-9-]/g, '') || 'yourschool';
                          }
                      }">
                    @csrf

                    {{-- Section: School Details --}}
                    <div class="mb-6">
                        <p class="text-xs font-semibold uppercase tracking-wider text-text-muted mb-4">School Details</p>

                        {{-- School Name --}}
                        <div class="mb-4">
                            <label for="school_name" class="block text-sm font-medium text-text-dark mb-1.5">
                                School name <span class="text-error">*</span>
                            </label>
                            <input
                                type="text"
                                id="school_name"
                                name="school_name"
                                value="{{ old('school_name') }}"
                                placeholder="e.g. Greenfield Academy"
                                autocomplete="organization"
                                class="w-full px-3 py-2 bg-surface border rounded-md text-sm text-text-primary placeholder:text-text-muted focus:outline-none focus:ring-1 transition-colors
                                       {{ $errors->has('school_name') ? 'border-error focus:ring-error focus:border-error' : 'border-border focus:ring-accent focus:border-accent' }}"
                            >
                            @error('school_name')
                            <p class="mt-1 text-xs text-error">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Subdomain --}}
                        <div>
                            <label for="subdomain" class="block text-sm font-medium text-text-dark mb-1.5">
                                Subdomain <span class="text-error">*</span>
                            </label>
                            <div class="flex items-stretch rounded-md overflow-hidden border transition-colors
                                        {{ $errors->has('subdomain') ? 'border-error' : 'border-border focus-within:ring-1 focus-within:ring-accent focus-within:border-accent' }}">
                                <input
                                    type="text"
                                    id="subdomain"
                                    name="subdomain"
                                    x-model="subdomain"
                                    value="{{ old('subdomain') }}"
                                    placeholder="yourschool"
                                    autocomplete="off"
                                    spellcheck="false"
                                    class="flex-1 px-3 py-2 bg-surface text-sm text-text-primary placeholder:text-text-muted focus:outline-none"
                                >
                                <div class="flex items-center bg-surface-secondary border-l border-border px-3 text-xs text-text-muted whitespace-nowrap shrink-0">
                                    .skolet.com
                                </div>
                            </div>
                            @error('subdomain')
                            <p class="mt-1 text-xs text-error">{{ $message }}</p>
                            @else
                            <p class="mt-1.5 text-xs text-text-muted">
                                Your school will be at:
                                <span class="font-medium text-accent" x-text="preview + '.skolet.com'">yourschool.skolet.com</span>
                            </p>
                            @enderror
                        </div>
                    </div>

                    {{-- Divider --}}
                    <div class="border-t border-border mb-6"></div>

                    {{-- Section: Admin Account --}}
                    <div class="mb-6">
                        <p class="text-xs font-semibold uppercase tracking-wider text-text-muted mb-4">Admin Account</p>

                        {{-- Admin Name --}}
                        <div class="mb-4">
                            <label for="admin_name" class="block text-sm font-medium text-text-dark mb-1.5">
                                Your full name <span class="text-error">*</span>
                            </label>
                            <input
                                type="text"
                                id="admin_name"
                                name="admin_name"
                                value="{{ old('admin_name') }}"
                                placeholder="e.g. Kwame Asante"
                                autocomplete="name"
                                class="w-full px-3 py-2 bg-surface border rounded-md text-sm text-text-primary placeholder:text-text-muted focus:outline-none focus:ring-1 transition-colors
                                       {{ $errors->has('admin_name') ? 'border-error focus:ring-error focus:border-error' : 'border-border focus:ring-accent focus:border-accent' }}"
                            >
                            @error('admin_name')
                            <p class="mt-1 text-xs text-error">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Admin Email --}}
                        <div class="mb-4">
                            <label for="admin_email" class="block text-sm font-medium text-text-dark mb-1.5">
                                Email address <span class="text-error">*</span>
                            </label>
                            <input
                                type="email"
                                id="admin_email"
                                name="admin_email"
                                value="{{ old('admin_email') }}"
                                placeholder="you@yourschool.com"
                                autocomplete="email"
                                class="w-full px-3 py-2 bg-surface border rounded-md text-sm text-text-primary placeholder:text-text-muted focus:outline-none focus:ring-1 transition-colors
                                       {{ $errors->has('admin_email') ? 'border-error focus:ring-error focus:border-error' : 'border-border focus:ring-accent focus:border-accent' }}"
                            >
                            @error('admin_email')
                            <p class="mt-1 text-xs text-error">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Admin Phone --}}
                        <div class="mb-4">
                            <label for="admin_phone" class="block text-sm font-medium text-text-dark mb-1.5">
                                Phone number <span class="text-text-muted text-xs font-normal">(optional &mdash; for SMS backup)</span>
                            </label>
                            <div class="flex items-stretch rounded-md overflow-hidden border transition-colors
                                        {{ $errors->has('admin_phone') ? 'border-error' : 'border-border focus-within:ring-1 focus-within:ring-accent focus-within:border-accent' }}">
                                <div class="flex items-center bg-surface-secondary border-r border-border px-3 text-xs text-text-muted whitespace-nowrap shrink-0">
                                    🇬🇭 +233
                                </div>
                                <input
                                    type="tel"
                                    id="admin_phone"
                                    name="admin_phone"
                                    value="{{ old('admin_phone') }}"
                                    placeholder="0244123456"
                                    autocomplete="tel"
                                    class="flex-1 px-3 py-2 bg-surface text-sm text-text-primary placeholder:text-text-muted focus:outline-none"
                                >
                            </div>
                            @error('admin_phone')
                            <p class="mt-1 text-xs text-error">{{ $message }}</p>
                            @else
                            <p class="mt-1 text-xs text-text-muted">Used to deliver login credentials by SMS if email fails.</p>
                            @enderror
                        </div>

                        {{-- Password --}}
                        <div class="mb-4">
                            <label for="admin_password" class="block text-sm font-medium text-text-dark mb-1.5">
                                Password <span class="text-error">*</span>
                            </label>
                            <input
                                type="password"
                                id="admin_password"
                                name="admin_password"
                                placeholder="Min. 8 characters"
                                autocomplete="new-password"
                                class="w-full px-3 py-2 bg-surface border rounded-md text-sm text-text-primary placeholder:text-text-muted focus:outline-none focus:ring-1 transition-colors
                                       {{ $errors->has('admin_password') ? 'border-error focus:ring-error focus:border-error' : 'border-border focus:ring-accent focus:border-accent' }}"
                            >
                            @error('admin_password')
                            <p class="mt-1 text-xs text-error">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Confirm Password --}}
                        <div class="mb-4">
                            <label for="admin_password_confirmation" class="block text-sm font-medium text-text-dark mb-1.5">
                                Confirm password <span class="text-error">*</span>
                            </label>
                            <input
                                type="password"
                                id="admin_password_confirmation"
                                name="admin_password_confirmation"
                                placeholder="Repeat your password"
                                autocomplete="new-password"
                                class="w-full px-3 py-2 bg-surface border rounded-md text-sm text-text-primary placeholder:text-text-muted focus:outline-none focus:ring-1 transition-colors border-border focus:ring-accent focus:border-accent"
                            >
                        </div>
                    </div>

                    {{-- Submit --}}
                    <button type="submit"
                            class="w-full px-4 py-3 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors">
                        Register School
                    </button>

                    <p class="mt-4 text-xs text-center text-text-muted">
                        By registering, you agree to Skolet's Terms of Service.
                        Already registered?
                        <a href="{{ route('login') }}" class="text-accent hover:underline">Log in</a>
                    </p>
                </form>
            </div>

            {{-- Trust signals --}}
            <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-6 text-xs text-text-muted">
                <div class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-success" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                    </svg>
                    Isolated database per school
                </div>
                <div class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-success" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    No credit card needed
                </div>
                <div class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-success" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                    </svg>
                    Live in minutes
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
