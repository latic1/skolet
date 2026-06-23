@extends('layouts.central')

@section('title', 'Skolet — The Complete School Management Platform')
@section('meta_description', 'Skolet helps schools manage attendance, exams, fee collection, and communication — all in one place. Every school gets its own isolated database and free subdomain.')

@push('og_tags')
<meta property="og:type" content="website">
<meta property="og:title" content="Skolet — The Complete School Management Platform">
<meta property="og:description" content="Streamline attendance, exams, fees, and communications. Every school gets its own secure database with a free subdomain.">
<meta property="og:url" content="{{ url('/') }}">
@endpush

@push('head')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "SoftwareApplication",
    "name": "Skolet",
    "applicationCategory": "EducationalApplication",
    "operatingSystem": "Web",
    "description": "Multi-tenant school management platform for attendance, exams, fee collection, and communication.",
    "offers": {
        "@type": "AggregateOffer",
        "priceCurrency": "GHS",
        "lowPrice": "0",
        "offerCount": "3"
    },
    "url": "{{ url('/') }}"
}
</script>
@endpush

@section('content')

{{-- Hero --}}
<section class="bg-background pt-20 pb-12">
    <div class="max-w-360 mx-auto px-6 text-center">

        {{-- Eyebrow badge --}}
        <div class="inline-flex items-center gap-2 bg-accent-light text-accent px-3 py-1 rounded-full text-xs font-semibold mb-6">
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            Multi-tenant · Per-school isolated database
        </div>

        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-text-primary leading-tight tracking-tight max-w-4xl mx-auto">
            The Complete
            <span style="background: linear-gradient(90deg, #2563eb 0%, #1e3a8a 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                School Management
            </span>
            Platform
        </h1>

        <p class="mt-6 text-lg text-text-secondary max-w-2xl mx-auto leading-relaxed">
            Streamline attendance, exams, fee collection, and school communications — all in one place.
            Every school gets its own secure database and a free subdomain.
        </p>

        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="{{ route('register-school') }}"
               class="w-full sm:w-auto px-6 py-3 bg-accent text-accent-foreground font-medium rounded-md hover:bg-accent-dark transition-colors text-sm">
                Register Your School Free
            </a>
            <a href="{{ route('pricing') }}"
               class="w-full sm:w-auto px-6 py-3 bg-surface border border-border text-text-primary font-medium rounded-md hover:bg-surface-secondary transition-colors text-sm">
                See Pricing
            </a>
        </div>

        <p class="mt-4 text-xs text-text-muted">No credit card required · Your school live in minutes</p>
    </div>
</section>

{{-- Dashboard Preview --}}
<section class="bg-background pb-20">
    <div class="max-w-360 mx-auto px-6">
        <div class="bg-surface rounded-2xl border border-border shadow-card overflow-hidden">

            {{-- Mock browser chrome --}}
            <div class="bg-surface-secondary border-b border-border px-4 py-3 flex items-center gap-2">
                <div class="flex gap-1.5">
                    <div class="w-3 h-3 rounded-full bg-error opacity-60"></div>
                    <div class="w-3 h-3 rounded-full bg-warning opacity-60"></div>
                    <div class="w-3 h-3 rounded-full bg-success opacity-60"></div>
                </div>
                <div class="bg-surface border border-border rounded-md px-3 py-1 text-xs text-text-muted mx-auto max-w-xs w-full text-center">
                    greenfield.skolet.com/dashboard
                </div>
            </div>

            {{-- Mock Dashboard Content --}}
            <div class="flex" style="min-height: 400px">

                {{-- Sidebar --}}
                <div class="hidden sm:flex flex-col bg-surface border-r border-border w-52 shrink-0 p-4 gap-1">
                    <div class="flex items-center gap-2 px-3 py-2 mb-3">
                        <div class="w-7 h-7 rounded-lg shrink-0" style="background: linear-gradient(45deg, #2563eb 0%, #1e3a8a 100%)"></div>
                        <span class="text-sm font-semibold text-text-primary">Greenfield Sch.</span>
                    </div>
                    @foreach ([['Dashboard', true], ['Students', false], ['Staff', false], ['Attendance', false], ['Exams', false], ['Fees', false]] as [$label, $active])
                    <div class="flex items-center gap-2.5 px-3 py-2 rounded-md {{ $active ? 'bg-accent-muted text-accent font-medium' : 'text-text-dark' }} text-sm">
                        <div class="w-1.5 h-1.5 rounded-full {{ $active ? 'bg-accent' : 'bg-border-muted' }}"></div>
                        {{ $label }}
                    </div>
                    @endforeach
                </div>

                {{-- Main content --}}
                <div class="flex-1 p-5 bg-background">

                    {{-- Stat cards --}}
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
                        @foreach ([
                            ['Total Students', '847', '+12 this term', 'success'],
                            ['Staff Members', '42', '8 departments', 'info'],
                            ['Attendance Today', '94.2%', '+2.1% vs yesterday', 'success'],
                            ['Fees Collected', 'GH₵ 24K', 'This term', 'accent'],
                        ] as [$label, $value, $sub, $color])
                        <div class="bg-surface border border-border rounded-xl p-3">
                            <p class="text-xs text-text-secondary mb-1">{{ $label }}</p>
                            <p class="text-xl font-semibold text-text-primary">{{ $value }}</p>
                            <p class="text-xs mt-0.5" style="color: {{ $color === 'success' ? '#009966' : ($color === 'info' ? '#0891b2' : ($color === 'accent' ? '#2563eb' : '#6a7282')) }}">{{ $sub }}</p>
                        </div>
                        @endforeach
                    </div>

                    {{-- Charts row --}}
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">

                        {{-- Fee chart --}}
                        <div class="bg-surface border border-border rounded-xl p-4">
                            <p class="text-sm font-semibold text-text-primary mb-3">Fee Collection</p>
                            <div class="flex items-end gap-1.5 h-20">
                                @foreach ([40, 65, 45, 80, 55, 90, 70, 85, 75, 95, 60, 100] as $h)
                                <div class="flex-1 rounded-sm opacity-80" style="height: {{ $h }}%; background: linear-gradient(to top, #2563eb, #93c5fd)"></div>
                                @endforeach
                            </div>
                            <div class="flex justify-between mt-1.5">
                                @foreach (['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'] as $m)
                                <span class="text-[9px] text-text-muted">{{ $m }}</span>
                                @endforeach
                            </div>
                        </div>

                        {{-- Attendance chart --}}
                        <div class="bg-surface border border-border rounded-xl p-4">
                            <p class="text-sm font-semibold text-text-primary mb-3">Attendance Rate</p>
                            <div class="flex items-end gap-1.5 h-20">
                                @foreach ([92, 88, 95, 90, 96, 85, 94] as $h)
                                <div class="flex-1 rounded-sm" style="height: {{ $h }}%; background-color: #06b6d4; opacity: 0.85"></div>
                                @endforeach
                            </div>
                            <div class="flex justify-between mt-1.5">
                                @foreach (['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $d)
                                <span class="text-[9px] text-text-muted">{{ $d }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Features --}}
<section class="bg-surface py-20 border-t border-border">
    <div class="max-w-360 mx-auto px-6">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-text-primary">Everything your school needs</h2>
            <p class="mt-3 text-text-secondary max-w-xl mx-auto">One platform, every module. From day-one registration to end-of-year reports.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

            <div class="bg-surface border border-border rounded-2xl p-6 shadow-card">
                <div class="w-10 h-10 bg-accent-light rounded-xl flex items-center justify-center mb-4">
                    <svg class="w-5 h-5 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-text-primary mb-2">Isolated Per-School Database</h3>
                <p class="text-sm text-text-secondary leading-relaxed">Every school gets its own private database. Zero data mixing, maximum security, and a unique subdomain to call their own.</p>
            </div>

            <div class="bg-surface border border-border rounded-2xl p-6 shadow-card">
                <div class="w-10 h-10 bg-success-lightest rounded-xl flex items-center justify-center mb-4">
                    <svg class="w-5 h-5 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-text-primary mb-2">Daily Attendance Tracking</h3>
                <p class="text-sm text-text-secondary leading-relaxed">Mark Present, Absent, or Late for every student in seconds. Monthly summaries and trend reports available instantly.</p>
            </div>

            <div class="bg-surface border border-border rounded-2xl p-6 shadow-card">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-4" style="background-color: #e6faff;">
                    <svg class="w-5 h-5" style="color: #00c3f7;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-text-primary mb-2">Fee Collection & Paystack</h3>
                <p class="text-sm text-text-secondary leading-relaxed">Record cash payments or let parents pay online via Paystack. Auto-generate PDF receipts and track outstanding balances.</p>
            </div>

            <div class="bg-surface border border-border rounded-2xl p-6 shadow-card">
                <div class="w-10 h-10 bg-info-lightest rounded-xl flex items-center justify-center mb-4">
                    <svg class="w-5 h-5 text-info-dark" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-text-primary mb-2">Exams & Report Cards</h3>
                <p class="text-sm text-text-secondary leading-relaxed">Schedule exams, enter marks per subject, and generate beautifully formatted PDF report cards with auto-computed grades.</p>
            </div>

            <div class="bg-surface border border-border rounded-2xl p-6 shadow-card">
                <div class="w-10 h-10 bg-accent-light rounded-xl flex items-center justify-center mb-4">
                    <svg class="w-5 h-5 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-text-primary mb-2">Timetable Builder</h3>
                <p class="text-sm text-text-secondary leading-relaxed">Build weekly timetables per class with conflict detection. Teachers see their own schedule across all classes automatically.</p>
            </div>

            <div class="bg-surface border border-border rounded-2xl p-6 shadow-card">
                <div class="w-10 h-10 bg-success-lightest rounded-xl flex items-center justify-center mb-4">
                    <svg class="w-5 h-5 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-text-primary mb-2">Auto School Public Page</h3>
                <p class="text-sm text-text-secondary leading-relaxed">Each school automatically gets a public page at their subdomain — with logo, announcements, and contact info. No extra setup.</p>
            </div>
        </div>
    </div>
</section>

{{-- Stats Banner --}}
<section class="bg-accent py-16">
    <div class="max-w-360 mx-auto px-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            @foreach ([
                ['Multi-tenant', 'Every school is isolated'],
                ['5+ Modules', 'Attendance to payments'],
                ['PDF Reports', 'Report cards & receipts'],
                ['Paystack', 'Online fee collection'],
            ] as [$stat, $label])
            <div>
                <p class="text-2xl font-bold text-accent-foreground">{{ $stat }}</p>
                <p class="text-sm text-accent-foreground opacity-75 mt-1">{{ $label }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- FAQ --}}
<section class="bg-background py-20" x-data="{ open: null }">
    <div class="max-w-360 mx-auto px-6">
        <div class="max-w-2xl mx-auto">
            <div class="text-center mb-10">
                <h2 class="text-3xl font-bold text-text-primary">Frequently Asked Questions</h2>
                <p class="mt-3 text-text-secondary">Everything you need to know about Skolet.</p>
            </div>

            @php
            $faqs = [
                ['How much does Skolet cost?', 'Skolet offers multiple plans starting from free. Our Basic plan is free for smaller schools, and our paid plans unlock advanced features like custom domains, bulk imports, and priority support. See the Pricing page for full details.'],
                ['Can I try it before paying?', 'Yes — you can register your school and use the full platform during the trial period, no credit card required. Upgrade only when you are ready.'],
                ['How does the subdomain work?', 'When you register your school, you pick a subdomain like "greenfield" and your school lives at greenfield.skolet.com. Students, staff, and parents access everything through that address.'],
                ['Can I use my own domain name?', 'Yes. Once your school is set up, you can add a custom domain (e.g. admin.yourschool.com) by pointing a CNAME record to Skolet. We handle SSL automatically.'],
                ['Is our school\'s data safe?', 'Absolutely. Every school runs on its own isolated database — your data never mixes with another school\'s. We take security seriously at every layer of the stack.'],
                ['Can I manage multiple school branches?', 'Each branch would have its own Skolet account and subdomain. Cross-branch management is on the roadmap for a future release.'],
            ];
            @endphp

            <div class="flex flex-col gap-2" itemscope itemtype="https://schema.org/FAQPage">
                @foreach ($faqs as $i => $faq)
                <div class="bg-surface border border-border rounded-xl overflow-hidden" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
                    <button @click="open = open === {{ $i }} ? null : {{ $i }}"
                            class="w-full flex items-center justify-between px-5 py-4 text-left"
                            :aria-expanded="open === {{ $i }}">
                        <span class="text-sm font-medium text-text-primary" itemprop="name">{{ $faq[0] }}</span>
                        <svg class="w-4 h-4 text-text-muted shrink-0 transition-transform duration-200"
                             :class="open === {{ $i }} ? 'rotate-180' : ''"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open === {{ $i }}"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="px-5 pb-4"
                         itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer"
                         style="display: none">
                        <p class="text-sm text-text-secondary leading-relaxed" itemprop="text">{{ $faq[1] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- Final CTA --}}
<section class="bg-surface py-20 border-t border-border">
    <div class="max-w-360 mx-auto px-6 text-center">
        <h2 class="text-3xl font-bold text-text-primary">Ready to transform how your school operates?</h2>
        <p class="mt-4 text-text-secondary max-w-lg mx-auto">Register your school today — it takes less than two minutes and you'll be live immediately.</p>
        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="{{ route('register-school') }}"
               class="w-full sm:w-auto px-8 py-3 bg-accent text-accent-foreground font-medium rounded-md hover:bg-accent-dark transition-colors text-sm">
                Register Your School
            </a>
            <a href="{{ route('pricing') }}"
               class="w-full sm:w-auto px-8 py-3 bg-surface border border-border text-text-primary font-medium rounded-md hover:bg-surface-secondary transition-colors text-sm">
                View Pricing
            </a>
        </div>
    </div>
</section>

@endsection
