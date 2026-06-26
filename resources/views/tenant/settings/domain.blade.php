@extends('layouts.tenant')

@section('title', 'Settings — Custom Domain')
@section('page-title', 'Settings')

@section('content')
@php $host = request()->getSchemeAndHttpHost(); @endphp
<div class="flex flex-col gap-6" x-data="{ submitting: false, showForm: false }">

    {{-- Settings Sub-Nav --}}
    <div class="flex items-center gap-1 border-b border-border pb-0 overflow-x-auto">
        <a href="{{ $host }}/settings/academic-year"
           class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors whitespace-nowrap border-transparent text-text-secondary hover:text-text-primary">
            Academic Calendar
        </a>
        <a href="{{ $host }}/settings/roles"
           class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors whitespace-nowrap border-transparent text-text-secondary hover:text-text-primary">
            Roles &amp; Permissions
        </a>
        <a href="{{ $host }}/settings/profile"
           class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors whitespace-nowrap border-transparent text-text-secondary hover:text-text-primary">
            School Profile
        </a>
        <a href="{{ $host }}/settings/domain"
           class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors whitespace-nowrap border-accent text-accent">
            Custom Domain
        </a>
        <a href="{{ $host }}/settings/notifications"
           class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors whitespace-nowrap border-transparent text-text-secondary hover:text-text-primary">
            Notifications
        </a>
        <a href="{{ $host }}/settings/audit-log"
           class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors whitespace-nowrap border-transparent text-text-secondary hover:text-text-primary">
            Audit Log
        </a>
        <a href="{{ $host }}/settings/privacy"
           class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors whitespace-nowrap border-transparent text-text-secondary hover:text-text-primary">
            Data &amp; Privacy
        </a>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="bg-success-lightest border border-success-light text-success-foreground text-sm px-4 py-3 rounded-xl flex items-start gap-3">
        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="bg-error-light border border-error text-error text-sm px-4 py-3 rounded-xl flex items-start gap-3">
        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-9a1 1 0 012 0v4a1 1 0 01-2 0V9zm1-5a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"/>
        </svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif
    @if($errors->any())
    <div class="bg-error-light border border-error text-error text-sm px-4 py-3 rounded-xl">
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Primary Domain Card --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card">
        <div class="px-6 py-4 border-b border-border">
            <h3 class="text-base font-semibold text-text-primary">Primary Subdomain</h3>
            <p class="text-xs text-text-muted mt-0.5">Your school's default Skolet address. This cannot be changed or removed.</p>
        </div>
        <div class="px-6 py-4 flex items-center justify-between gap-4 flex-wrap">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-accent-muted flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-text-primary">{{ $primaryDomain?->domain }}</p>
                    <p class="text-xs text-text-muted">Skolet subdomain</p>
                </div>
            </div>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-success-lightest text-success-foreground">
                <span class="w-1.5 h-1.5 rounded-full bg-success inline-block"></span>
                Active
            </span>
        </div>
    </div>

    {{-- Custom Domains Card --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card">
        <div class="px-6 py-4 border-b border-border flex items-center justify-between gap-4 flex-wrap">
            <div>
                <h3 class="text-base font-semibold text-text-primary">Custom Domains</h3>
                <p class="text-xs text-text-muted mt-0.5">Point your own domain to your Skolet school portal.</p>
            </div>
            <button type="button"
                    @click="showForm = !showForm"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Domain
            </button>
        </div>

        {{-- Add Domain Form --}}
        <div x-show="showForm" x-cloak class="px-6 py-5 border-b border-border bg-surface-secondary">
            <form method="POST" action="{{ $host }}/settings/domain" @submit="submitting = true" class="space-y-4">
                @csrf
                <div>
                    <label for="domain" class="block text-sm font-medium text-text-dark mb-1.5">
                        Domain Name <span class="text-error">*</span>
                    </label>
                    <input id="domain"
                           type="text"
                           name="domain"
                           value="{{ old('domain') }}"
                           required
                           placeholder="e.g. portal.yourschool.com"
                           autocomplete="off"
                           class="w-full sm:max-w-md px-3 py-2 bg-surface border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 transition-colors
                                  {{ $errors->has('domain') ? 'border-error focus:ring-error focus:border-error' : 'border-border focus:ring-accent focus:border-accent' }}">
                    @error('domain')
                    <p class="mt-1 text-xs text-error">{{ $message }}</p>
                    @enderror
                    <p class="mt-1.5 text-xs text-text-muted">Enter the full domain or subdomain you want to use (without https://).</p>
                </div>

                {{-- CNAME Instructions --}}
                <div class="bg-surface border border-border rounded-xl p-4 space-y-2">
                    <p class="text-xs font-semibold text-text-primary uppercase tracking-wide">Before adding, configure your DNS</p>
                    <p class="text-xs text-text-muted">Add the following CNAME record in your DNS provider (Cloudflare, Namecheap, GoDaddy, etc.):</p>
                    <div class="bg-surface-secondary rounded-lg px-4 py-3 font-mono text-xs text-text-primary overflow-x-auto">
                        <span class="text-text-muted">Type:</span> CNAME &nbsp;&nbsp;
                        <span class="text-text-muted">Name:</span> <span id="cname-name-preview" class="text-accent">portal</span> &nbsp;&nbsp;
                        <span class="text-text-muted">Target:</span> {{ $cnameTarget }}
                    </div>
                    <p class="text-xs text-text-muted">DNS propagation can take up to 48 hours. SSL is issued automatically once your domain is verified.</p>
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit"
                            :disabled="submitting"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
                        <span x-show="!submitting">Add Domain</span>
                        <span x-show="submitting">Adding…</span>
                    </button>
                    <button type="button" @click="showForm = false"
                            class="px-4 py-2 text-sm font-medium text-text-secondary hover:text-text-primary transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>

        {{-- Custom Domains List --}}
        @if($customDomains->isEmpty())
        <div class="px-6 py-10 text-center">
            <div class="w-10 h-10 bg-surface-secondary rounded-xl flex items-center justify-center mx-auto mb-3">
                <svg class="w-5 h-5 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                </svg>
            </div>
            <p class="text-sm text-text-muted">No custom domains added yet.</p>
            <p class="text-xs text-text-muted mt-1">Click "Add Domain" to connect your own domain.</p>
        </div>
        @else
        <div class="divide-y divide-border">
            @foreach($customDomains as $domain)
            <div class="px-6 py-4 flex items-center justify-between gap-4 flex-wrap">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-8 h-8 rounded-lg {{ $domain->isVerified() ? 'bg-success-lightest' : 'bg-warning-light' }} flex items-center justify-center shrink-0">
                        @if($domain->isVerified())
                        <svg class="w-4 h-4 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        @else
                        <svg class="w-4 h-4 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-text-primary truncate">{{ $domain->domain }}</p>
                        <p class="text-xs text-text-muted">Added {{ $domain->created_at->format('d M Y') }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 shrink-0">
                    {{-- Status badge --}}
                    @if($domain->isVerified())
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-success-lightest text-success-foreground">
                        <span class="w-1.5 h-1.5 rounded-full bg-success inline-block"></span>
                        Verified
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-warning-light text-warning">
                        <span class="w-1.5 h-1.5 rounded-full bg-warning inline-block"></span>
                        Pending DNS
                    </span>
                    @endif

                    {{-- Verify button (only for unverified) --}}
                    @if(!$domain->isVerified())
                    <form method="POST" action="{{ $host }}/settings/domain/{{ $domain->id }}/verify">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                                class="px-3 py-1.5 text-xs font-medium text-accent border border-accent rounded-md hover:bg-accent-muted transition-colors">
                            Verify DNS
                        </button>
                    </form>
                    @endif

                    {{-- Delete button --}}
                    <form method="POST" action="{{ $host }}/settings/domain/{{ $domain->id }}"
                          onsubmit="return confirm('Remove {{ $domain->domain }}? This will stop routing traffic from this domain to your school.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="p-1.5 text-text-muted hover:text-error transition-colors rounded-md hover:bg-error-light">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>

            {{-- CNAME instructions for unverified domains --}}
            @if(!$domain->isVerified())
            <div class="px-6 py-3 bg-surface-secondary border-t border-border">
                <p class="text-xs font-medium text-text-secondary mb-2">Add this CNAME record in your DNS provider:</p>
                <div class="bg-surface rounded-lg px-4 py-2.5 font-mono text-xs text-text-primary overflow-x-auto">
                    <span class="text-text-muted">Type:</span> CNAME &nbsp;&nbsp;
                    <span class="text-text-muted">Name:</span> <span class="text-accent">{{ explode('.', $domain->domain)[0] }}</span> &nbsp;&nbsp;
                    <span class="text-text-muted">Target:</span> {{ $cnameTarget }}
                </div>
            </div>
            @endif

            @endforeach
        </div>
        @endif
    </div>

    {{-- How it works --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card">
        <div class="px-6 py-4 border-b border-border">
            <h3 class="text-base font-semibold text-text-primary">How Custom Domains Work</h3>
        </div>
        <div class="px-6 py-5 space-y-4">
            <div class="flex gap-4">
                <div class="w-7 h-7 rounded-full bg-accent text-accent-foreground text-xs font-bold flex items-center justify-center shrink-0 mt-0.5">1</div>
                <div>
                    <p class="text-sm font-medium text-text-primary">Add your domain</p>
                    <p class="text-xs text-text-muted mt-0.5">Enter the domain or subdomain you own (e.g. <code class="bg-surface-secondary px-1 rounded">portal.yourschool.com</code>).</p>
                </div>
            </div>
            <div class="flex gap-4">
                <div class="w-7 h-7 rounded-full bg-accent text-accent-foreground text-xs font-bold flex items-center justify-center shrink-0 mt-0.5">2</div>
                <div>
                    <p class="text-sm font-medium text-text-primary">Configure your DNS</p>
                    <p class="text-xs text-text-muted mt-0.5">In your DNS provider, add a CNAME record pointing your domain to <code class="bg-surface-secondary px-1 rounded">{{ $cnameTarget }}</code>. This tells the internet where to send visitors.</p>
                </div>
            </div>
            <div class="flex gap-4">
                <div class="w-7 h-7 rounded-full bg-accent text-accent-foreground text-xs font-bold flex items-center justify-center shrink-0 mt-0.5">3</div>
                <div>
                    <p class="text-sm font-medium text-text-primary">Verify and go live</p>
                    <p class="text-xs text-text-muted mt-0.5">After DNS propagates (up to 48 hours), click "Verify DNS". An SSL certificate is issued automatically — no extra steps needed.</p>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    // Update CNAME name preview as user types
    document.getElementById('domain')?.addEventListener('input', function () {
        const preview = document.getElementById('cname-name-preview');
        if (preview) {
            const parts = this.value.trim().split('.');
            preview.textContent = parts[0] || 'portal';
        }
    });
</script>
@endsection
