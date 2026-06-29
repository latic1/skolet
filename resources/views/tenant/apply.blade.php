<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $schoolName = $profile?->school_name ?? tenant('name') ?? config('app.name');
        $logoUrl    = $profile?->logo_path ? request()->getSchemeAndHttpHost() . '/school-logo' : null;
    @endphp
    <title>Apply &mdash; {{ $schoolName }}</title>
    <meta name="description" content="Submit an online admission application to {{ $schoolName }}.">
    <meta name="robots" content="index, follow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans bg-background min-h-screen">

    {{-- Navbar --}}
    <header class="bg-surface border-b border-border sticky top-0 z-50">
        <div class="max-w-2xl mx-auto flex items-center justify-between h-16 px-4 lg:px-8">
            <a href="{{ request()->getSchemeAndHttpHost() }}/" class="flex items-center gap-2.5">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $schoolName }} logo" class="w-9 h-9 rounded-[10px] object-contain">
                @else
                    <div class="w-9 h-9 rounded-[10px] flex items-center justify-center shrink-0"
                         style="background: linear-gradient(45deg, #2563eb 0%, #1e3a8a 100%)">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                @endif
                <span class="text-[19px] font-bold leading-7 text-text-darkest tracking-tight">{{ $schoolName }}</span>
            </a>
            <a href="{{ request()->getSchemeAndHttpHost() . '/login' }}"
               class="px-4 py-2 text-sm font-medium bg-accent text-accent-foreground rounded-md hover:bg-accent-dark transition-colors">
                Login
            </a>
        </div>
    </header>

    <main class="max-w-2xl mx-auto px-4 lg:px-8 py-10 flex flex-col gap-6">

        {{-- Page heading --}}
        <div>
            <h1 class="text-2xl font-bold text-text-primary tracking-tight">Admission Application</h1>
            <p class="text-sm text-text-muted mt-1">Fill in the form below to apply for admission to {{ $schoolName }}.</p>
        </div>

        {{-- Flash error --}}
        @if(session('error'))
        <div class="bg-error-light border border-error text-error text-sm px-4 py-3 rounded-xl flex items-start gap-3">
            <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        {{-- Validation errors --}}
        @if($errors->any())
        <div class="bg-error-light border border-error text-error text-sm px-4 py-3 rounded-xl flex items-start gap-3">
            <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            <div>
                <p class="font-medium mb-1">Please fix the following errors:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        <form method="POST" action="{{ request()->getSchemeAndHttpHost() }}/apply"
              x-data="{ submitting: false }" @submit="submitting = true"
              class="flex flex-col gap-6">
            @csrf

            {{-- Student Information --}}
            <div class="bg-surface border border-border rounded-2xl shadow-card p-6 flex flex-col gap-5">
                <div>
                    <h2 class="text-base font-semibold text-text-primary">Student Information</h2>
                    <p class="text-xs text-text-muted mt-0.5">Details about the student applying for admission.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-text-dark mb-1.5">Full Name <span class="text-error">*</span></label>
                        <input type="text" name="applicant_name" value="{{ old('applicant_name') }}" required
                               placeholder="e.g. Jane Doe"
                               class="w-full px-3 py-2 bg-surface border @error('applicant_name') border-error @else border-border @enderror rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                        @error('applicant_name')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-text-dark mb-1.5">Date of Birth</label>
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}"
                               class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                        @error('date_of_birth')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-text-dark mb-1.5">Gender</label>
                        <select name="gender"
                                class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                            <option value="">Not specified</option>
                            <option value="male"   @selected(old('gender') === 'male')>Male</option>
                            <option value="female" @selected(old('gender') === 'female')>Female</option>
                            <option value="other"  @selected(old('gender') === 'other')>Other</option>
                        </select>
                        @error('gender')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-text-dark mb-1.5">Class Applying For <span class="text-error">*</span></label>
                        @if($classes->isNotEmpty())
                        <select name="class_applying_for" required
                                class="w-full px-3 py-2 bg-surface border @error('class_applying_for') border-error @else border-border @enderror rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                            <option value="">Select a class&hellip;</option>
                            @foreach($classes as $cls)
                            <option value="{{ $cls->name }}" @selected(old('class_applying_for') === $cls->name)>{{ $cls->name }}</option>
                            @endforeach
                        </select>
                        @else
                        <input type="text" name="class_applying_for" value="{{ old('class_applying_for') }}" required
                               placeholder="e.g. Grade 7, JSS 1"
                               class="w-full px-3 py-2 bg-surface border @error('class_applying_for') border-error @else border-border @enderror rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                        @endif
                        @error('class_applying_for')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            {{-- Guardian Information --}}
            <div class="bg-surface border border-border rounded-2xl shadow-card p-6 flex flex-col gap-5">
                <div>
                    <h2 class="text-base font-semibold text-text-primary">Guardian Information</h2>
                    <p class="text-xs text-text-muted mt-0.5">Parent or guardian who will be contacted about this application.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-text-dark mb-1.5">Guardian Name <span class="text-error">*</span></label>
                        <input type="text" name="guardian_name" value="{{ old('guardian_name') }}" required
                               placeholder="e.g. John Doe"
                               class="w-full px-3 py-2 bg-surface border @error('guardian_name') border-error @else border-border @enderror rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                        @error('guardian_name')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-text-dark mb-1.5">Contact Number <span class="text-error">*</span></label>
                        <input type="text" name="guardian_contact" value="{{ old('guardian_contact') }}" required
                               placeholder="e.g. 0244123456"
                               class="w-full px-3 py-2 bg-surface border @error('guardian_contact') border-error @else border-border @enderror rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                        @error('guardian_contact')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-text-dark mb-1.5">Email Address</label>
                        <input type="email" name="guardian_email" value="{{ old('guardian_email') }}"
                               placeholder="e.g. guardian@example.com"
                               class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                        <p class="mt-1 text-xs text-text-muted">A confirmation email will be sent to this address if provided.</p>
                        @error('guardian_email')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            {{-- Previous School --}}
            <div class="bg-surface border border-border rounded-2xl shadow-card p-6">
                <div class="mb-4">
                    <h2 class="text-base font-semibold text-text-primary">Previous School <span class="text-text-muted font-normal text-sm">(optional)</span></h2>
                </div>
                <input type="text" name="previous_school" value="{{ old('previous_school') }}"
                       placeholder="e.g. Accra Primary School"
                       class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                @error('previous_school')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
            </div>

            {{-- Submit --}}
            <div class="flex items-center justify-end gap-3">
                <a href="{{ request()->getSchemeAndHttpHost() }}/"
                   class="px-4 py-2 bg-surface border border-border text-sm font-medium text-text-primary rounded-md hover:bg-surface-secondary transition-colors">
                    Cancel
                </a>
                <button type="submit"
                        :disabled="submitting"
                        :class="submitting ? 'opacity-60 cursor-not-allowed' : 'hover:bg-accent-dark'"
                        class="px-6 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md transition-colors">
                    <span x-show="!submitting">Submit Application</span>
                    <span x-show="submitting">Submitting&hellip;</span>
                </button>
            </div>

        </form>
    </main>

</body>
</html>
