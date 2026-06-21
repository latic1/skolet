@extends('layouts.central')

@section('title', 'Pricing — SchoolFlow')
@section('meta_description', 'SchoolFlow pricing plans for schools of every size. Start free, upgrade when you\'re ready. Basic, Standard, and Premium plans with transparent pricing.')

@push('og_tags')
<meta property="og:type" content="website">
<meta property="og:title" content="Pricing — SchoolFlow">
<meta property="og:description" content="Transparent pricing for schools of every size. Start free with the Basic plan, upgrade to unlock advanced features.">
<meta property="og:url" content="{{ url('/pricing') }}">
@endpush

@section('content')

{{-- Pricing Header --}}
<section class="bg-background pt-16 pb-12">
    <div class="max-w-360 mx-auto px-6 text-center">
        <h1 class="text-4xl font-bold text-text-primary">Simple, transparent pricing</h1>
        <p class="mt-4 text-lg text-text-secondary max-w-xl mx-auto">Start for free. Upgrade when you're ready. No hidden fees, no per-student charges.</p>
    </div>
</section>

{{-- Plan Cards --}}
<section class="bg-background pb-20">
    <div class="max-w-360 mx-auto px-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl mx-auto">

            {{-- Basic Plan --}}
            <div class="bg-surface border border-border rounded-2xl p-6 shadow-card flex flex-col">
                <div class="mb-6">
                    <p class="text-xs font-semibold uppercase tracking-wider text-text-muted mb-3">Basic</p>
                    <div class="flex items-end gap-1 mb-1">
                        <span class="text-4xl font-bold text-text-primary">Free</span>
                    </div>
                    <p class="text-sm text-text-secondary">For small schools getting started</p>
                </div>

                <a href="{{ route('register-school') }}"
                   class="w-full px-4 py-2.5 border border-border text-text-primary text-sm font-medium rounded-md hover:bg-surface-secondary transition-colors text-center mb-6">
                    Get Started Free
                </a>

                <div class="flex flex-col gap-3 flex-1">
                    <p class="text-xs font-semibold uppercase tracking-wider text-text-muted">Includes</p>
                    @foreach ([
                        'Up to 200 students',
                        'Attendance tracking',
                        'Fee recording (cash)',
                        'Exam & marks entry',
                        'Auto school public page',
                        'Free subdomain',
                        'Email support',
                    ] as $feature)
                    <div class="flex items-center gap-2.5">
                        <div class="w-4 h-4 rounded-full bg-success-lightest flex items-center justify-center shrink-0">
                            <svg class="w-2.5 h-2.5 text-success" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <span class="text-sm text-text-secondary">{{ $feature }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Standard Plan — Most Popular --}}
            <div class="bg-surface border-2 border-accent rounded-2xl p-6 shadow-card flex flex-col relative">
                <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                    <span class="bg-accent text-accent-foreground text-xs font-semibold px-3 py-1 rounded-full">Most Popular</span>
                </div>

                <div class="mb-6">
                    <p class="text-xs font-semibold uppercase tracking-wider text-accent mb-3">Standard</p>
                    <div class="flex items-end gap-1 mb-1">
                        <span class="text-xl font-semibold text-text-secondary">GH₵</span>
                        <span class="text-4xl font-bold text-text-primary">500</span>
                    </div>
                    <p class="text-sm text-text-secondary">per term · billed termly</p>
                </div>

                <a href="{{ route('register-school') }}"
                   class="w-full px-4 py-2.5 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors text-center mb-6">
                    Start Free Trial
                </a>

                <div class="flex flex-col gap-3 flex-1">
                    <p class="text-xs font-semibold uppercase tracking-wider text-text-muted">Everything in Basic, plus</p>
                    @foreach ([
                        'Unlimited students',
                        'Paystack online payments',
                        'PDF report cards',
                        'PDF fee receipts',
                        'Bulk student import (CSV)',
                        'Custom roles & permissions',
                        'Timetable builder',
                        'Priority email support',
                    ] as $feature)
                    <div class="flex items-center gap-2.5">
                        <div class="w-4 h-4 rounded-full bg-accent-light flex items-center justify-center shrink-0">
                            <svg class="w-2.5 h-2.5 text-accent" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <span class="text-sm text-text-secondary">{{ $feature }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Premium Plan --}}
            <div class="bg-surface border border-border rounded-2xl p-6 shadow-card flex flex-col">
                <div class="mb-6">
                    <p class="text-xs font-semibold uppercase tracking-wider text-text-muted mb-3">Premium</p>
                    <div class="flex items-end gap-1 mb-1">
                        <span class="text-xl font-semibold text-text-secondary">GH₵</span>
                        <span class="text-4xl font-bold text-text-primary">1,200</span>
                    </div>
                    <p class="text-sm text-text-secondary">per term · billed termly</p>
                </div>

                <a href="{{ route('register-school') }}"
                   class="w-full px-4 py-2.5 border border-border text-text-primary text-sm font-medium rounded-md hover:bg-surface-secondary transition-colors text-center mb-6">
                    Start Free Trial
                </a>

                <div class="flex flex-col gap-3 flex-1">
                    <p class="text-xs font-semibold uppercase tracking-wider text-text-muted">Everything in Standard, plus</p>
                    @foreach ([
                        'Custom domain support',
                        'School branding (logo, colors)',
                        'Advanced attendance reports',
                        'Exportable fee & exam reports',
                        'Parent & student portal',
                        'Announcement notifications',
                        'Dedicated support',
                    ] as $feature)
                    <div class="flex items-center gap-2.5">
                        <div class="w-4 h-4 rounded-full bg-success-lightest flex items-center justify-center shrink-0">
                            <svg class="w-2.5 h-2.5 text-success" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <span class="text-sm text-text-secondary">{{ $feature }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <p class="text-center text-sm text-text-muted mt-8">All plans include a 30-day free trial. No credit card required to register.</p>
    </div>
</section>

{{-- Feature Comparison Table --}}
<section class="bg-surface border-t border-border py-20">
    <div class="max-w-360 mx-auto px-6">
        <div class="max-w-4xl mx-auto">
            <h2 class="text-2xl font-bold text-text-primary text-center mb-10">Full feature comparison</h2>

            <div class="bg-surface border border-border rounded-2xl overflow-hidden shadow-card">
                {{-- Table header --}}
                <div class="grid grid-cols-4 gap-0 border-b border-border">
                    <div class="px-5 py-4">
                        <span class="text-sm font-semibold text-text-primary">Feature</span>
                    </div>
                    <div class="px-4 py-4 text-center border-l border-border">
                        <span class="text-sm font-semibold text-text-secondary">Basic</span>
                    </div>
                    <div class="px-4 py-4 text-center border-l border-border bg-accent-muted">
                        <span class="text-sm font-semibold text-accent">Standard</span>
                    </div>
                    <div class="px-4 py-4 text-center border-l border-border">
                        <span class="text-sm font-semibold text-text-secondary">Premium</span>
                    </div>
                </div>

                @php
                $rows = [
                    ['Student limit', '200', 'Unlimited', 'Unlimited'],
                    ['Attendance tracking', true, true, true],
                    ['Fee recording (cash)', true, true, true],
                    ['Exam & marks entry', true, true, true],
                    ['Auto public school page', true, true, true],
                    ['Free subdomain', true, true, true],
                    ['Paystack online payments', false, true, true],
                    ['PDF report cards', false, true, true],
                    ['PDF fee receipts', false, true, true],
                    ['Bulk student import', false, true, true],
                    ['Custom roles & permissions', false, true, true],
                    ['Timetable builder', false, true, true],
                    ['Custom domain', false, false, true],
                    ['Exportable reports (PDF/Excel)', false, false, true],
                    ['Parent & student portal', false, false, true],
                    ['Dedicated support', false, false, true],
                ];
                @endphp

                @foreach ($rows as $i => $row)
                <div class="grid grid-cols-4 gap-0 border-b border-border last:border-b-0 {{ $i % 2 === 0 ? '' : 'bg-surface-secondary' }}">
                    <div class="px-5 py-3.5">
                        <span class="text-sm text-text-primary">{{ $row[0] }}</span>
                    </div>
                    @foreach (array_slice($row, 1) as $j => $cell)
                    <div class="px-4 py-3.5 text-center border-l border-border {{ $j === 1 ? 'bg-accent-muted' : '' }}">
                        @if ($cell === true)
                            <svg class="w-4 h-4 text-success mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        @elseif ($cell === false)
                            <span class="text-border-muted">—</span>
                        @else
                            <span class="text-sm font-medium text-text-primary">{{ $cell }}</span>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="bg-background py-16 border-t border-border">
    <div class="max-w-360 mx-auto px-6 text-center">
        <h2 class="text-2xl font-bold text-text-primary">Start your free trial today</h2>
        <p class="mt-3 text-text-secondary">No credit card required. Your school is live in minutes.</p>
        <a href="{{ route('register-school') }}"
           class="inline-block mt-6 px-8 py-3 bg-accent text-accent-foreground font-medium rounded-md hover:bg-accent-dark transition-colors text-sm">
            Register Your School
        </a>
    </div>
</section>

@endsection
