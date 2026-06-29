@extends('layouts.central')

@section('title', 'Skolet &mdash; The Complete School Management Platform')
@section('meta_description', 'Skolet helps schools manage attendance, exams, fee collection, and communication &mdash; all in one place. Every school gets its own isolated database and free subdomain.')

@push('og_tags')
<meta property="og:type" content="website">
<meta property="og:title" content="Skolet &mdash; The Complete School Management Platform">
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
<style>
    @keyframes float-up-down {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-8px); }
    }
    @keyframes float-down-up {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(8px); }
    }
    .animate-float { animation: float-up-down 4s ease-in-out infinite; }
    .animate-float-delay { animation: float-down-up 4.5s ease-in-out infinite; animation-delay: 0.8s; }
    .gradient-text {
        background: linear-gradient(90deg, #93c5fd 0%, #ffffff 50%, #67e8f9 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .hero-pillar {
        position: absolute;
        border-radius: 9999px;
        border: 1px solid rgba(255,255,255,0.05);
        pointer-events: none;
    }
</style>
@endpush

@section('content')

{{-- ═══════════════════════════════════════════
     HERO SECTION &mdash; dark purple-black background
     ═══════════════════════════════════════════ --}}
<section class="relative w-full overflow-hidden px-6 md:px-12 pt-16 pb-24" style="background: #1a0d32;">

    {{-- Abstract background pillars --}}
    <div class="absolute inset-0 z-0 pointer-events-none">
        <div class="hero-pillar" style="top:40px; left:10%; height:500px; width:128px; transform:rotate(35deg); background:rgba(120,60,220,0.18); backdrop-filter:blur(40px); border:1px solid rgba(255,255,255,0.10);"></div>
        <div class="hero-pillar" style="top:-50px; right:15%; height:600px; width:192px; transform:rotate(35deg); background:rgba(100,40,200,0.22); backdrop-filter:blur(40px); border:1px solid rgba(255,255,255,0.08);"></div>
        <div class="hero-pillar" style="bottom:-100px; left:30%; height:400px; width:96px; transform:rotate(35deg); background:rgba(80,30,160,0.15); backdrop-filter:blur(30px); border:1px solid rgba(255,255,255,0.06);"></div>
        <div class="absolute top-1/4 left-1/3 w-96 h-96 rounded-full blur-[120px]" style="background: rgba(37,99,235,0.25);"></div>
        <div class="absolute bottom-1/3 right-1/4 w-80 h-80 rounded-full blur-[100px]" style="background: rgba(6,182,212,0.15);"></div>
    </div>

    <div class="relative z-10 mx-auto max-w-7xl">
        <div class="mx-auto max-w-4xl text-center flex flex-col items-center">

            {{-- Eyebrow badge --}}
            <div class="inline-flex items-center gap-2 rounded-full px-4 py-1.5 mb-6"
                 style="border: 1px solid rgba(37,99,235,0.3); background: rgba(37,99,235,0.1); backdrop-filter: blur(8px);">
                <svg class="h-4 w-4" style="color:#67e8f9" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 010-2h1V3a1 1 0 011-1zm0 10a1 1 0 011 1v1h1a1 1 0 110 2H6v1a1 1 0 11-2 0v-1H3a1 1 0 110-2h1v-1a1 1 0 011-1zM12 2a1 1 0 01.967.744L14.146 7.2 17.5 9.134a1 1 0 010 1.732l-3.354 1.935-1.18 4.455a1 1 0 01-1.933 0L9.854 12.8 6.5 10.866a1 1 0 010-1.732l3.354-1.935 1.18-4.455A1 1 0 0112 2z" clip-rule="evenodd"/>
                </svg>
                <span class="text-xs font-semibold tracking-wider uppercase" style="color: #93c5fd;">
                    Multi-tenant &middot; Per-school isolated database
                </span>
            </div>

            {{-- Main headline --}}
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-extrabold tracking-tight text-white leading-[1.1] mb-6">
                Powerful Tools for Complete<br class="hidden md:inline">
                <span class="gradient-text">School Management.</span>
            </h1>

            {{-- Subtext --}}
            <p class="text-base sm:text-lg leading-relaxed max-w-2xl mb-10 font-light" style="color: #d1d5db;">
                Your all-in-one SaaS solution for effortless school administration and academic
                excellence. Attendance, exams, fee collection, timetables, and communication &mdash;
                every school gets its own secure database and subdomain.
            </p>

            {{-- CTA buttons --}}
            <div class="flex flex-wrap items-center justify-center gap-4 mb-20">
                <a href="{{ route('register-school') }}"
                   class="flex items-center gap-2 rounded-full px-8 py-4 font-semibold text-white transition-all hover:-translate-y-0.5 active:scale-95"
                   style="background: #2563eb; box-shadow: 0 8px 32px rgba(37,99,235,0.25);">
                    <span>Register Your School Free</span>
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </a>
                <a href="{{ route('pricing') }}"
                   class="flex items-center gap-2 rounded-full px-8 py-4 font-semibold text-white transition-all hover:bg-white/10 active:scale-95"
                   style="border: 1px solid rgba(255,255,255,0.2); background: rgba(255,255,255,0.05);">
                    <span>View Pricing</span>
                    <div class="flex h-6 w-6 items-center justify-center rounded-full" style="background: rgba(255,255,255,0.2);">
                        <svg class="h-3 w-3 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                    </div>
                </a>
            </div>
        </div>

        {{-- Floating stats cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:flex lg:items-stretch justify-center gap-6 mt-8">

            {{-- Card 1: Mesh/wave card --}}
            <div class="flex-1 min-w-[220px] rounded-3xl bg-white p-6 flex flex-col justify-between overflow-hidden relative group" style="box-shadow: 0 25px 50px rgba(0,0,0,0.3);">
                <div class="absolute top-0 right-0 left-0 h-32 flex items-center justify-center border-b overflow-hidden" style="background: #f9fafb; border-color: #e7eaf3;">
                    <svg class="w-full h-full absolute inset-0" viewBox="0 0 100 100" preserveAspectRatio="none" style="color: rgba(37,99,235,0.08);">
                        <path d="M0,50 C20,20 40,80 60,30 C80,10 90,90 100,50 L100,100 L0,100 Z" fill="currentColor"/>
                        <path d="M0,60 C30,40 50,70 70,20 C90,30 95,80 100,60 L100,100 L0,100 Z" fill="rgba(37,99,235,0.04)"/>
                        <line x1="0" y1="20" x2="100" y2="20" stroke="rgba(37,99,235,0.05)" stroke-width="0.5"/>
                        <line x1="0" y1="40" x2="100" y2="40" stroke="rgba(37,99,235,0.05)" stroke-width="0.5"/>
                        <line x1="0" y1="60" x2="100" y2="60" stroke="rgba(37,99,235,0.05)" stroke-width="0.5"/>
                        <line x1="0" y1="80" x2="100" y2="80" stroke="rgba(37,99,235,0.05)" stroke-width="0.5"/>
                    </svg>
                    <div class="absolute flex flex-col items-center gap-1">
                        <div class="h-1.5 w-12 rounded-full" style="background: rgba(37,99,235,0.2);"></div>
                        <div class="h-1.5 w-8 rounded-full" style="background: rgba(37,99,235,0.1);"></div>
                    </div>
                </div>
                <div class="pt-32 flex flex-col justify-between h-full">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full transition-transform group-hover:translate-x-1" style="background: #f3f4f6;">
                        <svg class="h-5 w-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </div>
                    <p class="mt-4 font-bold text-gray-900 leading-snug text-sm">
                        Manage your entire school<br>in one place, from day one.
                    </p>
                </div>
            </div>

            {{-- Card 2: Stat &mdash; attendance rate --}}
            <div class="lg:w-[15%] min-w-[160px] rounded-3xl bg-white p-6 flex flex-col justify-between" style="box-shadow: 0 25px 50px rgba(0,0,0,0.3);">
                <div>
                    <div class="h-10 w-10 rounded-full flex items-center justify-center mb-6" style="background: #ecfdf5;">
                        <svg class="h-5 w-5" style="color: #10b981;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-4xl font-extrabold text-gray-900 mb-2 tracking-tight">94%</h3>
                </div>
                <p class="text-xs text-gray-500 leading-relaxed mt-4 font-medium">Average daily attendance rate across schools.</p>
            </div>

            {{-- Card 3: School photo --}}
            <div class="lg:w-[32%] min-w-[280px] rounded-3xl bg-white p-2 flex flex-col relative group overflow-hidden" style="box-shadow: 0 25px 50px rgba(0,0,0,0.3);">
                <div class="h-48 sm:h-full w-full rounded-[20px] overflow-hidden relative">
                    <img src="https://images.unsplash.com/photo-1580582932707-520aed937b7b?q=80&w=600&auto=format&fit=crop"
                         alt="Teachers and students in a classroom"
                         class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                    <div class="absolute inset-0 flex items-end p-4"
                         style="background: linear-gradient(to top, rgba(0,0,0,0.6), rgba(0,0,0,0.1) 50%, transparent);">
                        <span class="text-[10px] uppercase tracking-wider font-bold text-white px-2.5 py-1 rounded-full" style="background: rgba(255,255,255,0.2); backdrop-filter: blur(8px);">
                            Modern Classrooms
                        </span>
                    </div>
                </div>
            </div>

            {{-- Card 4: Stat &mdash; modules --}}
            <div class="lg:w-[15%] min-w-[160px] rounded-3xl bg-white p-6 flex flex-col justify-between" style="box-shadow: 0 25px 50px rgba(0,0,0,0.3);">
                <div>
                    <div class="h-10 w-10 rounded-full flex items-center justify-center mb-6" style="background: #eff6ff;">
                        <svg class="h-5 w-5" style="color: #2563eb;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <h3 class="text-4xl font-extrabold text-gray-900 mb-2 tracking-tight">10+</h3>
                </div>
                <p class="text-xs text-gray-500 leading-relaxed mt-4 font-medium">Powerful modules from attendance to payroll.</p>
            </div>

            {{-- Card 5: Secure infrastructure --}}
            <div class="flex-1 min-w-[220px] bg-white rounded-3xl p-2 overflow-hidden relative group" style="box-shadow: 0 25px 50px rgba(0,0,0,0.3);">
                <div class="h-64 sm:h-full w-full relative overflow-hidden rounded-[20px]">
                    <img src="https://images.unsplash.com/photo-1551434678-e076c223a692?q=80&w=600&auto=format&fit=crop"
                         alt="School administrator working on laptop"
                         class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                    <div class="absolute inset-0 flex flex-col justify-end p-4"
                         style="background: linear-gradient(to top, rgba(15,5,29,0.85), transparent 60%);">
                        <span class="text-xs font-bold uppercase tracking-wider mb-1" style="color: #67e8f9;">
                            Secure & Isolated
                        </span>
                        <p class="text-white text-xs font-medium leading-relaxed">
                            Every school's data lives in its own private database.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>


{{-- ═══════════════════════════════════════════
     FEATURE ONE &mdash; attendance & records
     bg-slate-50 / left: mockup / right: copy
     ═══════════════════════════════════════════ --}}
<section class="relative w-full py-24 px-6 md:px-12 overflow-hidden bg-surface-secondary">
    <div class="mx-auto max-w-7xl grid grid-cols-1 lg:grid-cols-12 gap-16 items-center">

        {{-- Left: Attendance mockup card --}}
        <div class="lg:col-span-6 relative flex justify-center">
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-72 h-72 rounded-full blur-[80px] -z-10" style="background: rgba(37,99,235,0.08);"></div>

            <div class="relative w-full max-w-md bg-white rounded-3xl p-6 border border-border" style="box-shadow: 0 25px 50px rgba(0,0,0,0.08);">
                <div class="flex items-center justify-between mb-6">
                    <h4 class="font-bold text-gray-900 text-lg">Today's Attendance</h4>
                    <span class="h-2 w-2 rounded-full bg-success animate-pulse"></span>
                </div>

                {{-- Attendance card: Class 6A --}}
                <div class="rounded-2xl border p-5 mb-4 relative overflow-hidden" style="border-color: #dbeafe; background: rgba(239,246,255,0.4);">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex-1">
                            <h5 class="font-bold text-gray-900 text-lg mb-4">Class 6A</h5>
                            <div class="grid grid-cols-2 gap-x-4 gap-y-2 text-xs text-gray-600">
                                <div class="flex items-center gap-1.5">
                                    <svg class="h-3.5 w-3.5" style="color: #2563eb;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span>42 students</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <svg class="h-3.5 w-3.5" style="color: #10b981;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>38 present</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <svg class="h-3.5 w-3.5" style="color: #ef4444;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>4 absent</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <svg class="h-3.5 w-3.5" style="color: #ff8904;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>2 late</span>
                                </div>
                            </div>
                        </div>
                        {{-- Circular progress --}}
                        <div class="relative flex items-center justify-center self-center shrink-0 h-24 w-24">
                            <svg class="w-20 h-20" style="transform: rotate(-90deg);">
                                <circle cx="40" cy="40" r="34" stroke="#EFF6FF" stroke-width="6" fill="transparent"/>
                                <circle cx="40" cy="40" r="34" stroke="#2563eb" stroke-width="6" fill="transparent"
                                        stroke-dasharray="{{ 2 * M_PI * 34 }}" stroke-dashoffset="{{ 2 * M_PI * 34 * (1 - 0.905) }}"
                                        stroke-linecap="round"/>
                            </svg>
                            <span class="absolute text-center font-extrabold text-lg" style="color: #1e3a8a;">90%</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-3 mt-5 pt-4 border-t" style="border-color: rgba(219,234,254,0.6);">
                        <button class="text-xs font-semibold px-4 py-2 rounded-xl border transition-colors hover:bg-gray-50" style="color: #6a7282; border-color: #e7eaf3;">Export PDF</button>
                        <button class="text-xs font-semibold text-white px-5 py-2 rounded-xl transition-colors" style="background: #2563eb;">View Report</button>
                    </div>
                </div>

                {{-- Secondary card: Class 5B --}}
                <div class="rounded-2xl border p-4 opacity-70 flex items-center justify-between" style="border-color: #e7eaf3; background: #f9fafb;">
                    <div>
                        <h5 class="font-bold text-gray-800 text-sm mb-1">Class 5B</h5>
                        <span class="text-xs text-gray-500">35 of 38 students marked</span>
                    </div>
                    <div class="h-10 w-10 rounded-full border-2 flex items-center justify-center text-xs font-bold" style="border-color: #e7eaf3; color: #6a7282;">92%</div>
                </div>
            </div>

            {{-- Floating badge --}}
            <div class="animate-float absolute top-12 -left-6 bg-white rounded-2xl p-4 border border-border hidden sm:flex items-center gap-3" style="box-shadow: 0 8px 30px rgba(0,0,0,0.1);">
                <div class="h-10 w-10 rounded-xl flex items-center justify-center" style="background: #eff6ff;">
                    <svg class="h-5 w-5" style="color: #2563eb;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                <div>
                    <span class="text-[10px] text-gray-400 font-medium">This Month</span>
                    <p class="font-bold text-gray-900 text-sm">96.4% Avg Rate</p>
                </div>
            </div>
        </div>

        {{-- Right: Copy --}}
        <div class="lg:col-span-6 flex flex-col justify-center">
            <span class="text-xs font-bold uppercase tracking-widest mb-4 block" style="color: #2563eb;">Institutional Efficiency</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight leading-tight mb-6">
                Empower Your School<br>with Complete Operational Control
            </h2>
            <p class="text-gray-600 leading-relaxed font-light mb-8 max-w-xl">
                Track attendance for every class and student in seconds. Mark Present, Absent, or Late,
                view monthly summaries, and generate automated reports &mdash; all without spreadsheets.
                Skolet keeps your records accurate and your admin team free to focus on what matters.
            </p>
            <div class="flex flex-wrap items-center gap-4">
                <a href="{{ route('register-school') }}"
                   class="rounded-full px-7 py-3.5 font-semibold text-white transition-all hover:-translate-y-px active:scale-95"
                   style="background: #2563eb; box-shadow: 0 4px 16px rgba(37,99,235,0.2);">
                    Get started
                </a>
                <a href="{{ route('pricing') }}"
                   class="rounded-full border px-7 py-3.5 font-semibold text-gray-700 transition-all hover:bg-gray-50 active:scale-95"
                   style="border-color: #d1d5db; background: white;">
                    See pricing
                </a>
            </div>
        </div>

    </div>
</section>


{{-- ═══════════════════════════════════════════
     FEATURE TWO &mdash; fee & analytics
     bg-white / left: copy / right: chart card
     ═══════════════════════════════════════════ --}}
<section class="relative w-full bg-white py-24 px-6 md:px-12 overflow-hidden">
    <div class="mx-auto max-w-7xl grid grid-cols-1 lg:grid-cols-12 gap-16 items-center">

        {{-- Left: Copy --}}
        <div class="lg:col-span-6 flex flex-col justify-center">
            <span class="text-xs font-bold uppercase tracking-widest mb-4 block" style="color: #06b6d4;">Financial Intelligence</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight leading-tight mb-6">
                Track Fees, Payments,<br>and Academic Progress
            </h2>
            <p class="text-gray-600 leading-relaxed font-light mb-8 max-w-xl">
                Record cash payments or let parents pay online via Paystack. Auto-generate PDF receipts,
                track outstanding balances, and get powerful analytics on collection rates and exam
                results &mdash; all from one unified dashboard.
            </p>
            <div>
                <a href="{{ route('register-school') }}"
                   class="inline-block rounded-full px-8 py-3.5 font-semibold text-white transition-all hover:-translate-y-px active:scale-95"
                   style="background: #2563eb; box-shadow: 0 4px 16px rgba(37,99,235,0.2);">
                    Learn More
                </a>
            </div>
        </div>

        {{-- Right: Analytics chart card --}}
        <div class="lg:col-span-6 relative flex justify-center">
            <div class="absolute inset-0 rounded-3xl blur-3xl -z-10" style="background: linear-gradient(135deg, rgba(6,182,212,0.05), rgba(37,99,235,0.05));"></div>

            <div class="w-full max-w-md bg-white rounded-3xl p-6 border" style="border-color: #e7eaf3; box-shadow: 0 25px 50px rgba(0,0,0,0.08);">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h4 class="font-bold text-gray-900 text-base">Fee Collection</h4>
                        <p class="text-xs text-gray-400 mt-0.5">Monthly collection rate</p>
                    </div>
                    <div class="flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold" style="background: #ecfdf5; color: #059669;">
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        <span>+18.3%</span>
                    </div>
                </div>

                {{-- Chart --}}
                <div class="relative h-64 w-full">
                    {{-- Tooltip badge --}}
                    <div class="absolute top-[28%] right-[10%] z-10 flex flex-col items-center">
                        <div class="text-white text-[11px] font-bold px-3 py-1.5 rounded-lg shadow-md" style="background: #0F051D;">
                            GH₵ 24K
                        </div>
                        <div class="h-2 w-2 rounded-full border-2 border-white mt-1" style="background: #2563eb;"></div>
                    </div>

                    {{-- Grid labels --}}
                    <div class="absolute inset-0 flex flex-col justify-between text-[11px] text-gray-400 font-mono pr-4 pb-6">
                        @foreach ([100, 75, 50, 25, 0] as $val)
                        <div class="flex items-center w-full">
                            <span class="w-8 text-right mr-3">{{ $val }}%</span>
                            <div class="flex-1 border-t border-dashed" style="border-color: #f3f4f6;"></div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Curves --}}
                    <div class="absolute top-2 bottom-6 overflow-visible" style="left: 44px; right: 16px;">
                        <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                            <line x1="10" y1="0" x2="10" y2="100" stroke="#f3f4f6" stroke-width="0.5"/>
                            <line x1="50" y1="0" x2="50" y2="100" stroke="#f3f4f6" stroke-width="0.5"/>
                            <line x1="90" y1="0" x2="90" y2="100" stroke="#f3f4f6" stroke-width="0.5"/>
                            {{-- Fee collection line --}}
                            <path d="M 10 70 Q 30 40, 50 55 T 90 25" fill="none" stroke="#2563eb" stroke-width="2.5" stroke-linecap="round"/>
                            {{-- Exam score line --}}
                            <path d="M 10 55 Q 35 30, 50 42 T 90 60" fill="none" stroke="#06b6d4" stroke-width="2.5" stroke-linecap="round"/>
                        </svg>
                    </div>

                    {{-- X-axis labels --}}
                    <div class="absolute bottom-0 flex justify-between text-[11px] text-gray-400 font-medium" style="left: 44px; right: 16px;">
                        <span>Sep</span><span>Oct</span><span>Nov</span>
                    </div>
                </div>

                {{-- Legend --}}
                <div class="mt-4 pt-4 border-t flex items-center gap-6" style="border-color: #f3f4f6;">
                    <div class="flex items-center gap-2 text-xs text-gray-600">
                        <span class="h-2 w-4 rounded-full inline-block" style="background: #2563eb;"></span>Fees Collected
                    </div>
                    <div class="flex items-center gap-2 text-xs text-gray-600">
                        <span class="h-2 w-4 rounded-full inline-block" style="background: #06b6d4;"></span>Exam Scores
                    </div>
                </div>
            </div>

            {{-- Floating mini stat --}}
            <div class="animate-float-delay absolute -bottom-6 -right-4 bg-white rounded-2xl p-4 border border-border hidden sm:flex items-center gap-3" style="box-shadow: 0 8px 30px rgba(0,0,0,0.1);">
                <div class="h-2 w-2 rounded-full" style="background: #2563eb;"></div>
                <span class="text-xs font-semibold text-gray-800">Paystack Online Payments</span>
            </div>
        </div>

    </div>
</section>


{{-- ═══════════════════════════════════════════
     DASHBOARD SHOWCASE
     bg-slate-50 / full-width mockup
     ═══════════════════════════════════════════ --}}
<section class="relative w-full bg-surface-secondary py-24 px-6 md:px-12 overflow-hidden">
    <div class="mx-auto max-w-7xl">

        <div class="mx-auto max-w-3xl text-center mb-16">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight leading-tight mb-4">
                Everything Your School Needs,<br>One Intuitive Dashboard
            </h2>
            <p class="text-gray-600 leading-relaxed font-light max-w-2xl mx-auto">
                Streamline operations, enhance communication, and empower educators. Discover seamless
                efficiency and innovation in school management.
            </p>
        </div>

        {{-- Dashboard mockup --}}
        <div class="mx-auto max-w-6xl rounded-3xl p-3 md:p-6 border" style="background: #f8fafc; box-shadow: 0 25px 60px rgba(0,0,0,0.12); border-color: #e7eaf3;">
            <div class="bg-white rounded-[20px] overflow-hidden border border-border flex flex-col">

                {{-- Mock browser bar --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-border flex-wrap gap-4" style="background: #f9fafb;">
                    <div class="flex items-center gap-2">
                        <div class="flex gap-1.5">
                            <div class="w-3 h-3 rounded-full" style="background: #ef4444; opacity: 0.6;"></div>
                            <div class="w-3 h-3 rounded-full" style="background: #ff8904; opacity: 0.6;"></div>
                            <div class="w-3 h-3 rounded-full" style="background: #10b981; opacity: 0.6;"></div>
                        </div>
                        <div class="ml-3 px-4 py-1.5 rounded-full text-xs text-gray-400 border border-border" style="background: white;">
                            greenfield.skolet.com/dashboard
                        </div>
                    </div>
                    {{-- Nav tabs --}}
                    <div class="flex items-center gap-1">
                        @foreach (['Overview', 'Students', 'Attendance', 'Fees'] as $tab)
                        <button class="px-4 py-1.5 rounded-full text-xs font-semibold transition-colors {{ $loop->first ? 'text-accent' : 'text-gray-500 hover:text-gray-800' }}"
                                style="{{ $loop->first ? 'background: #eff6ff;' : '' }}">{{ $tab }}</button>
                        @endforeach
                    </div>
                </div>

                {{-- Main workspace --}}
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 p-6">

                    {{-- Left col --}}
                    <div class="lg:col-span-8 flex flex-col gap-6">

                        {{-- Welcome banner --}}
                        <div class="rounded-2xl p-6 text-white relative overflow-hidden" style="background: linear-gradient(135deg, #2563eb, #1e3a8a);">
                            <div class="absolute right-0 top-0 bottom-0 w-1/3 opacity-10 pointer-events-none" style="background: radial-gradient(ellipse at right, white, transparent);"></div>
                            <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-6">
                                <div>
                                    <div class="flex items-center gap-2 mb-2">
                                        <svg class="h-4 w-4" style="color: #fbbf24;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 010-2h1V3a1 1 0 011-1zm0 10a1 1 0 011 1v1h1a1 1 0 110 2H6v1a1 1 0 11-2 0v-1H3a1 1 0 110-2h1v-1a1 1 0 011-1zM12 2a1 1 0 01.967.744L14.146 7.2 17.5 9.134a1 1 0 010 1.732l-3.354 1.935-1.18 4.455a1 1 0 01-1.933 0L9.854 12.8 6.5 10.866a1 1 0 010-1.732l3.354-1.935 1.18-4.455A1 1 0 0112 2z" clip-rule="evenodd"/></svg>
                                        <span class="text-xs font-bold uppercase tracking-widest text-blue-200">Welcome Back</span>
                                    </div>
                                    <h3 class="text-xl md:text-2xl font-extrabold mb-1">Greenfield Academy</h3>
                                    <p class="text-xs leading-relaxed font-light text-blue-100 max-w-md">Term 2 exams are scheduled for next week. 3 fee reminders were sent to parents automatically this morning.</p>
                                </div>
                                <button class="rounded-xl px-4 py-2 text-xs font-semibold text-white transition-colors flex items-center gap-1.5 self-start sm:self-auto" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);">
                                    View Schedule
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                                </button>
                            </div>
                        </div>

                        {{-- Stats row --}}
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            @foreach ([
                                ['Total Students', '847', '#fff7ed', '#ff8904', 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
                                ['Staff Members', '42', '#eff6ff', '#2563eb', 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                                ['Attendance Today', '94.2%', '#ecfdf5', '#10b981', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                                ['Fees Collected', 'GH₵ 24K', '#ecfeff', '#06b6d4', 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],
                            ] as [$label, $value, $bg, $color, $path])
                            <div class="rounded-2xl border border-border p-4 hover:shadow-md transition-shadow bg-white">
                                <div class="h-8 w-8 rounded-xl flex items-center justify-center mb-3" style="background: {{ $bg }};">
                                    <svg class="h-4 w-4" style="color: {{ $color }};" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/></svg>
                                </div>
                                <p class="text-2xl font-extrabold text-gray-900 leading-none">{{ $value }}</p>
                                <span class="text-[11px] text-gray-500 font-medium mt-1 block">{{ $label }}</span>
                            </div>
                            @endforeach
                        </div>

                        {{-- Fee collection chart --}}
                        <div class="rounded-2xl border border-border bg-white p-5">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="font-bold text-gray-900 text-sm">Fee Collection &mdash; This Year</h4>
                                <div class="flex items-center gap-3 text-xs text-gray-400 font-medium">
                                    <div class="flex items-center gap-1"><span class="h-2 w-2 rounded-full inline-block" style="background: #2563eb;"></span>Collected</div>
                                    <div class="flex items-center gap-1"><span class="h-2 w-2 rounded-full inline-block" style="background: #06b6d4;"></span>Expected</div>
                                </div>
                            </div>
                            <div class="flex items-end gap-1.5 h-20">
                                @foreach ([40, 65, 45, 80, 55, 90, 70, 85, 75, 95, 60, 100] as $h)
                                <div class="flex-1 rounded-sm" style="height: {{ $h }}%; background: linear-gradient(to top, #2563eb, #93c5fd); opacity: 0.8;"></div>
                                @endforeach
                            </div>
                            <div class="flex justify-between mt-2">
                                @foreach (['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'] as $m)
                                <span class="text-[9px] text-gray-400">{{ $m }}</span>
                                @endforeach
                            </div>
                        </div>

                    </div>

                    {{-- Right col --}}
                    <div class="lg:col-span-4 flex flex-col gap-6">

                        {{-- Progress ring --}}
                        <div class="rounded-2xl border border-border bg-white p-5">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="font-bold text-gray-900 text-sm">Term Progress</h4>
                                <span class="text-[11px] font-bold px-2 py-0.5 rounded-md" style="color: #2563eb; background: #eff6ff;">Term 2</span>
                            </div>
                            <div class="relative flex items-center justify-center h-40">
                                <svg class="w-32 h-32" style="transform: rotate(-90deg);">
                                    <circle cx="64" cy="64" r="50" stroke="#EFF6FF" stroke-width="6" fill="transparent"/>
                                    <circle cx="64" cy="64" r="50" stroke="#2563eb" stroke-width="6" fill="transparent"
                                            stroke-dasharray="{{ 2 * M_PI * 50 }}" stroke-dashoffset="{{ 2 * M_PI * 50 * 0.32 }}" stroke-linecap="round"/>
                                    <circle cx="64" cy="64" r="38" stroke="#EFF6FF" stroke-width="6" fill="transparent"/>
                                    <circle cx="64" cy="64" r="38" stroke="#06b6d4" stroke-width="6" fill="transparent"
                                            stroke-dasharray="{{ 2 * M_PI * 38 }}" stroke-dashoffset="{{ 2 * M_PI * 38 * 0.42 }}" stroke-linecap="round"/>
                                    <circle cx="64" cy="64" r="26" stroke="#EFF6FF" stroke-width="6" fill="transparent"/>
                                    <circle cx="64" cy="64" r="26" stroke="#10b981" stroke-width="6" fill="transparent"
                                            stroke-dasharray="{{ 2 * M_PI * 26 }}" stroke-dashoffset="{{ 2 * M_PI * 26 * 0.55 }}" stroke-linecap="round"/>
                                </svg>
                                <div class="absolute flex flex-col items-center">
                                    <span class="text-[10px] text-gray-400 uppercase tracking-widest font-bold">Week</span>
                                    <span class="font-extrabold text-gray-900 text-lg leading-none">6 / 14</span>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-x-2 gap-y-3 mt-4 pt-4 border-t border-gray-50 text-xs">
                                <div class="flex items-center gap-1.5 text-gray-600"><span class="h-2 w-2 rounded-full shrink-0" style="background: #2563eb;"></span>Exams: <strong class="text-gray-900 ml-1">3/8</strong></div>
                                <div class="flex items-center gap-1.5 text-gray-600"><span class="h-2 w-2 rounded-full shrink-0" style="background: #06b6d4;"></span>Reports: <strong class="text-gray-900 ml-1">1/3</strong></div>
                            </div>
                        </div>

                        {{-- Upcoming event --}}
                        <div class="rounded-2xl border border-border bg-white p-5">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="font-bold text-gray-900 text-sm">Upcoming</h4>
                                <span class="text-[10px] text-gray-400 font-medium">This Week</span>
                            </div>
                            <div class="flex items-center gap-3.5 p-3 rounded-xl hover:bg-gray-50 transition-colors cursor-pointer border border-transparent hover:border-border">
                                <div class="h-10 w-10 rounded-lg flex items-center justify-center font-bold text-xs shrink-0" style="background: #eff6ff; color: #2563eb;">
                                    EXM
                                </div>
                                <div class="flex-1 text-left min-w-0">
                                    <p class="text-xs font-bold text-gray-900 truncate">Mathematics Mid-Term Exam</p>
                                    <span class="text-[10px] text-gray-400 block mt-0.5">Thursday &middot; Classes 5&ndash;6 &middot; 2 Hours</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


{{-- ═══════════════════════════════════════════
     STEP BY STEP &mdash; onboarding in 3 steps
     bg-white / interactive
     ═══════════════════════════════════════════ --}}
<section class="relative w-full bg-white py-24 px-6 md:px-12 overflow-hidden" x-data="{ activeStep: 1 }">
    <div class="mx-auto max-w-7xl">

        <div class="mx-auto max-w-3xl text-center mb-16">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight leading-tight mb-4">
                Your School Live in 3 Simple Steps
            </h2>
            <p class="text-gray-600 leading-relaxed font-light max-w-2xl mx-auto">
                No technical setup required. From registration to a fully operational school platform
                in minutes &mdash; not days.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-16 items-center">

            {{-- Left: Visual cards --}}
            <div class="lg:col-span-6 flex justify-center relative min-h-[360px] items-center">
                <div class="absolute w-72 h-72 rounded-full blur-3xl -z-10" style="background: rgba(37,99,235,0.06);"></div>

                {{-- Step 1 visual --}}
                <div x-show="activeStep === 1"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="w-full max-w-sm rounded-3xl p-6 flex flex-col gap-4"
                     style="background: #faf8ff; border: 1px solid #e9d5ff; box-shadow: 0 20px 40px rgba(0,0,0,0.08);">
                    <div class="flex items-center justify-between border-b pb-3" style="border-color: #f3e8ff;">
                        <h4 class="font-bold" style="color: #581c87;">Register Your School</h4>
                        <span class="text-xs px-2 py-0.5 rounded-full font-bold" style="background: #f3e8ff; color: #7c3aed;">STEP 1</span>
                    </div>
                    <div class="flex flex-col gap-3">
                        @foreach ([['School name & subdomain', true], ['Admin account created', true], ['Your database provisioned', false]] as [$item, $done])
                        <div class="flex items-center gap-3 bg-white p-3.5 rounded-xl border shadow-sm" style="border-color: rgba(233,213,255,0.5);">
                            @if ($done)
                            <svg class="h-5 w-5 shrink-0" style="color: #7c3aed;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @else
                            <svg class="h-5 w-5 shrink-0 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>
                            @endif
                            <p class="text-xs font-bold {{ $done ? 'line-through text-gray-400' : 'text-gray-900' }}">{{ $item }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Step 2 visual --}}
                <div x-show="activeStep === 2"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="w-full max-w-sm rounded-3xl p-6"
                     style="background: rgba(239,246,255,0.5); border: 1px solid #dbeafe; box-shadow: 0 20px 40px rgba(0,0,0,0.08); display:none;">
                    <h4 class="font-bold mb-4 text-left" style="color: #1e3a8a;">Configure Your Modules</h4>
                    <div class="grid grid-cols-2 gap-4">
                        @foreach ([['Attendance', '#2563eb', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'], ['Fee Collection', '#06b6d4', 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'], ['Exams & Grades', '#7c3aed', 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'], ['Timetable', '#10b981', 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z']] as [$name, $color, $path])
                        <div class="bg-white p-4 rounded-xl border shadow-sm text-left" style="border-color: #dbeafe;">
                            <svg class="h-5 w-5 mb-2" style="color: {{ $color }};" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/></svg>
                            <p class="text-xs font-bold text-gray-900">{{ $name }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Step 3 visual --}}
                <div x-show="activeStep === 3"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="w-full max-w-sm rounded-3xl p-6 flex flex-col gap-4 text-left"
                     style="background: rgba(236,253,245,0.4); border: 1px solid #a7f3d0; box-shadow: 0 20px 40px rgba(0,0,0,0.08); display:none;">
                    <h4 class="font-bold mb-1" style="color: #064e3b;">You're Live!</h4>
                    <p class="text-xs text-gray-500 leading-relaxed mb-3">Your school is now accessible at your subdomain. Invite staff, add students, and start tracking from day one.</p>
                    <div class="rounded-xl bg-white p-3.5 border flex items-center justify-between shadow-sm" style="border-color: #a7f3d0;">
                        <div class="flex items-center gap-3">
                            <div class="h-2 w-2 rounded-full animate-pulse" style="background: #10b981;"></div>
                            <span class="text-xs font-bold text-gray-800">greenfield.skolet.com is live</span>
                        </div>
                        <span class="text-[10px] font-extrabold px-2 py-0.5 rounded" style="color: #059669; background: #d1fae5;">ACTIVE</span>
                    </div>
                    <div class="rounded-xl bg-white p-3.5 border flex items-center justify-between shadow-sm" style="border-color: #a7f3d0;">
                        <div class="flex items-center gap-3">
                            <div class="h-2 w-2 rounded-full" style="background: #10b981;"></div>
                            <span class="text-xs font-bold text-gray-800">First attendance marked</span>
                        </div>
                        <span class="text-[10px] font-extrabold px-2 py-0.5 rounded" style="color: #059669; background: #d1fae5;">DONE</span>
                    </div>
                </div>
            </div>

            {{-- Right: Step list --}}
            <div class="lg:col-span-6 flex flex-col gap-4">
                @foreach ([
                    [1, 'Register Your School', 'Pick a subdomain, set up your admin account, and your isolated private database is provisioned automatically &mdash; in seconds.', 'border-purple-600 bg-purple-50/50 text-purple-700'],
                    [2, 'Configure Your Modules', 'Enable the modules your school needs &mdash; attendance, exams, fees, timetable, HR, payroll &mdash; and invite your staff to get started.', 'border-blue-600 bg-blue-50/50 text-blue-700'],
                    [3, 'Go Live Instantly', 'Your school is immediately accessible at yourschool.skolet.com. Add students, take attendance, collect fees &mdash; everything from day one.', 'border-emerald-600 bg-emerald-50/50 text-emerald-700'],
                ] as [$id, $title, $desc, $activeClass])
                <div @click="activeStep = {{ $id }}"
                     class="group text-left p-6 rounded-2xl border-l-4 cursor-pointer transition-all duration-200"
                     :class="activeStep === {{ $id }} ? '{{ $activeClass }} shadow-md translate-x-1' : 'border-gray-200 bg-transparent hover:bg-gray-50 hover:border-gray-300'">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="h-6 w-6 rounded-full flex items-center justify-center text-xs font-bold border transition-colors shrink-0"
                              :class="activeStep === {{ $id }} ? 'bg-current text-white border-transparent' : 'border-gray-300 text-gray-500 group-hover:border-gray-400 group-hover:text-gray-700'">
                            {{ $id }}
                        </span>
                        <h3 class="font-extrabold text-gray-900 text-lg">{{ $title }}</h3>
                    </div>
                    <p class="text-sm text-gray-600 leading-relaxed pl-9 font-light">{{ $desc }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>


{{-- ═══════════════════════════════════════════
     FEATURES GRID &mdash; all modules at a glance
     bg-slate-50
     ═══════════════════════════════════════════ --}}
<section class="bg-surface-secondary py-20 px-6 md:px-12">
    <div class="mx-auto max-w-7xl">
        <div class="text-center mb-12">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">Everything your school needs</h2>
            <p class="mt-3 text-gray-600 font-light max-w-xl mx-auto">One platform, every module. From day-one registration to end-of-year reports.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ([
                ['Isolated Per-School Database', 'Every school gets its own private database. Zero data mixing, maximum security, and a unique subdomain to call their own.', '#eff6ff', '#2563eb', 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
                ['Daily Attendance Tracking', 'Mark Present, Absent, or Late for every student in seconds. Monthly summaries and trend reports available instantly.', '#ecfdf5', '#10b981', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
                ['Fee Collection & Paystack', 'Record cash payments or let parents pay online via Paystack. Auto-generate PDF receipts and track outstanding balances.', '#ecfeff', '#06b6d4', 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],
                ['Exams & Report Cards', 'Schedule exams, enter marks per subject, and generate beautifully formatted PDF report cards with auto-computed grades.', '#f5f3ff', '#7c3aed', 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                ['Timetable Builder', 'Build weekly timetables per class with conflict detection. Teachers see their own schedule across all classes automatically.', '#eff6ff', '#2563eb', 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                ['Auto School Public Page', 'Each school automatically gets a public page at their subdomain &mdash; with logo, announcements, and contact info. No extra setup.', '#ecfdf5', '#10b981', 'M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9'],
            ] as [$title, $desc, $bg, $color, $path])
            <div class="bg-white rounded-3xl p-6 border border-border hover:-translate-y-1 transition-transform duration-200" style="box-shadow: 0 4px 16px rgba(0,0,0,0.04);">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-4" style="background: {{ $bg }};">
                    <svg class="w-5 h-5" style="color: {{ $color }};" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-gray-900 mb-2">{{ $title }}</h3>
                <p class="text-sm text-gray-600 leading-relaxed font-light">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>


{{-- ═══════════════════════════════════════════
     TESTIMONIALS &mdash; school leader reviews
     bg-white
     ═══════════════════════════════════════════ --}}
<section class="relative w-full bg-white py-24 px-6 md:px-12 overflow-hidden">
    <div class="mx-auto max-w-7xl">

        <div class="mx-auto max-w-3xl text-center mb-16">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight leading-tight mb-4">
                Trusted by School Leaders
            </h2>
            <p class="text-gray-600 leading-relaxed font-light max-w-2xl mx-auto">
                Hear firsthand how Skolet has transformed school management for administrators,
                teachers, and entire institutions.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach ([
                ['Skolet completely transformed how we run our school. Attendance is instant, fees are tracked to the last pesewa, and parents get notified automatically. I can\'t imagine going back to spreadsheets.', 'Akosua Mensah', 'Head Mistress, Greenfield Academy', 'AM'],
                ['The isolated database feature was the selling point for us. Every school\'s data is completely private. We\'ve seen zero mixing of records since we switched, and the setup literally took 5 minutes.', 'Kwame Asante', 'IT Administrator, Sunrise International School', 'KA'],
                ['Our teachers love that they can mark attendance on their phones and generate report cards with one click. The exam grading module alone saved us two full weeks of manual work this term.', 'Grace Ofori', 'Principal, New Hope Community School', 'GO'],
            ] as [$quote, $name, $role, $initials])
            <div class="bg-white rounded-3xl p-8 border border-border flex flex-col justify-between hover:-translate-y-1 transition-transform duration-300 relative group"
                 style="box-shadow: 0 20px 40px rgba(0,0,0,0.06);">
                <div class="absolute top-6 right-8 text-gray-50 group-hover:text-blue-50 transition-colors duration-300">
                    <svg class="h-10 w-10 fill-current" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg>
                </div>

                <div class="relative z-10">
                    <div class="flex items-center gap-0.5 mb-6" style="color: #fbbf24;">
                        @for ($i = 0; $i < 5; $i++)
                        <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @endfor
                    </div>
                    <p class="text-gray-600 leading-relaxed font-light text-sm italic mb-8">"{{ $quote }}"</p>
                </div>

                <div class="flex items-center gap-3 pt-6 border-t border-gray-50 relative z-10">
                    <div class="h-11 w-11 rounded-full flex items-center justify-center font-bold text-sm text-white shrink-0"
                         style="background: linear-gradient(135deg, #2563eb, #1e3a8a);">
                        {{ $initials }}
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900 text-sm leading-tight">{{ $name }}</h4>
                        <span class="text-[11px] text-gray-400 font-medium block mt-0.5">{{ $role }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>


{{-- ═══════════════════════════════════════════
     STATS BANNER
     bg-accent
     ═══════════════════════════════════════════ --}}
<section class="bg-accent py-16 px-6 md:px-12">
    <div class="mx-auto max-w-7xl">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            @foreach ([
                ['Multi-tenant', 'Every school is isolated'],
                ['10+ Modules', 'From attendance to payroll'],
                ['PDF Reports', 'Report cards & receipts'],
                ['Paystack', 'Secure online fee collection'],
            ] as [$stat, $label])
            <div>
                <p class="text-2xl font-bold text-white">{{ $stat }}</p>
                <p class="text-sm text-white/75 mt-1">{{ $label }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>


{{-- ═══════════════════════════════════════════
     GET STARTED CTA
     bg-white / centered
     ═══════════════════════════════════════════ --}}
<section class="relative w-full bg-white py-24 px-6 md:px-12 overflow-hidden text-center">
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] rounded-full blur-[120px] pointer-events-none" style="background: rgba(37,99,235,0.05);"></div>

    <div class="relative z-10 mx-auto max-w-4xl flex flex-col items-center">
        <h2 class="text-4xl sm:text-5xl font-extrabold text-gray-900 tracking-tight leading-tight mb-4">
            It's easy to get started
        </h2>

        <div class="flex flex-wrap items-center justify-center gap-2 mb-10 text-sm text-gray-600 font-medium">
            <span class="font-bold text-gray-900">No credit card required</span>
            <span class="text-gray-300 hidden sm:inline">&middot;</span>
            <div class="flex items-center" style="color: #fbbf24;">
                @for ($i = 0; $i < 5; $i++)
                <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                @endfor
            </div>
            <span>Your school live in minutes</span>
        </div>

        <div class="flex flex-wrap items-center justify-center gap-4">
            <a href="{{ route('register-school') }}"
               class="flex items-center gap-2 rounded-full px-8 py-4 font-semibold text-white transition-all hover:-translate-y-px active:scale-95"
               style="background: #2563eb; box-shadow: 0 8px 32px rgba(37,99,235,0.2);">
                <span>Register Your School Free</span>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
            <a href="{{ route('pricing') }}"
               class="flex items-center gap-2 rounded-full border px-8 py-4 font-semibold text-gray-700 transition-all hover:bg-gray-50 active:scale-95"
               style="border-color: #d1d5db; background: white;">
                <span>View Pricing</span>
                <div class="flex h-5 w-5 items-center justify-center rounded-full" style="background: #f3f4f6;">
                    <svg class="h-2.5 w-2.5 text-gray-600" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                </div>
            </a>
        </div>
    </div>
</section>


{{-- ═══════════════════════════════════════════
     FAQ
     bg-surface-secondary
     ═══════════════════════════════════════════ --}}
<section class="bg-surface-secondary py-20 px-6 md:px-12" x-data="{ open: null }">
    <div class="mx-auto max-w-7xl">
        <div class="max-w-2xl mx-auto">
            <div class="text-center mb-10">
                <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Frequently Asked Questions</h2>
                <p class="mt-3 text-gray-600 font-light">Everything you need to know about Skolet.</p>
            </div>

            @php
            $faqs = [
                ['How much does Skolet cost?', 'Skolet offers multiple plans starting from free. Our Basic plan is free for smaller schools, and our paid plans unlock advanced features like custom domains, bulk imports, and priority support. See the Pricing page for full details.'],
                ['Can I try it before paying?', 'Yes &mdash; you can register your school and use the full platform during the trial period, no credit card required. Upgrade only when you are ready.'],
                ['How does the subdomain work?', 'When you register your school, you pick a subdomain like "greenfield" and your school lives at greenfield.skolet.com. Students, staff, and parents access everything through that address.'],
                ['Can I use my own domain name?', 'Yes. Once your school is set up, you can add a custom domain (e.g. admin.yourschool.com) by pointing a CNAME record to Skolet. We handle SSL automatically.'],
                ['Is our school\'s data safe?', 'Absolutely. Every school runs on its own isolated database &mdash; your data never mixes with another school\'s. We take security seriously at every layer of the stack.'],
                ['Can I manage multiple school branches?', 'Each branch would have its own Skolet account and subdomain. Cross-branch management is on the roadmap for a future release.'],
            ];
            @endphp

            <div class="flex flex-col gap-2" itemscope itemtype="https://schema.org/FAQPage">
                @foreach ($faqs as $i => $faq)
                <div class="bg-white border border-border rounded-2xl overflow-hidden" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
                    <button @click="open = open === {{ $i }} ? null : {{ $i }}"
                            class="w-full flex items-center justify-between px-5 py-4 text-left"
                            :aria-expanded="open === {{ $i }}">
                        <span class="text-sm font-semibold text-gray-900" itemprop="name">{{ $faq[0] }}</span>
                        <svg class="w-4 h-4 text-gray-400 shrink-0 transition-transform duration-200"
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
                        <p class="text-sm text-gray-600 leading-relaxed font-light" itemprop="text">{{ $faq[1] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

@endsection
