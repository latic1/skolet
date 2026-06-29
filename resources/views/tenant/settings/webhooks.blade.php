@extends('layouts.tenant')

@section('title', 'Settings â€” Webhooks')
@section('page-title', 'Settings')

@section('content')
@php $host = request()->getSchemeAndHttpHost(); @endphp

<div class="flex flex-col gap-6" x-data="webhooksPage()">

    @include('partials.settings-tabs')

    {{-- Flash --}}
    @if(session('success'))
    <div class="flex items-center gap-3 bg-success-lightest border border-success-light text-success-foreground text-sm px-4 py-3 rounded-xl">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="bg-error-light border border-error/20 text-error text-sm px-4 py-3 rounded-xl">{{ session('error') }}</div>
    @endif

    {{-- Page header --}}
    <div class="flex items-center justify-between gap-4">
        <div>
            <h2 class="text-base font-semibold text-text-primary">Outbound Webhooks</h2>
            <p class="text-sm text-text-muted mt-0.5">Send real-time event notifications to external services when things happen in your school.</p>
        </div>
        <button @click="openAdd()"
                class="flex items-center gap-2 px-4 py-2 text-sm font-medium bg-accent text-accent-foreground rounded-xl hover:bg-accent-dark transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Endpoint
        </button>
    </div>

    {{-- Webhooks table --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card overflow-hidden">

        @if ($webhooks->isEmpty())
            <div class="flex flex-col items-center justify-center py-16 text-center px-6">
                <div class="w-12 h-12 rounded-xl bg-surface-secondary flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-text-primary mb-1">No webhook endpoints configured</p>
                <p class="text-xs text-text-muted mb-4">Add an HTTPS endpoint to receive real-time event notifications.</p>
                <button @click="openAdd()"
                        class="px-4 py-2 text-sm font-medium bg-accent text-accent-foreground rounded-xl hover:bg-accent-dark transition-colors">
                    Add Endpoint
                </button>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm" style="min-width: 700px">
                    <thead>
                        <tr class="border-b border-border bg-surface-secondary">
                            <th class="px-5 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wide">Endpoint URL</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wide">Events</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wide">Status</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wide">Last Delivery</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach ($webhooks as $webhook)
                        @php $latest = $webhook->latestDeliveryRecord; @endphp
                        <tr class="hover:bg-surface-secondary/50 transition-colors">
                            {{-- URL --}}
                            <td class="px-5 py-4">
                                <span class="font-mono text-xs text-text-primary break-all">{{ $webhook->url }}</span>
                            </td>

                            {{-- Events --}}
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($webhook->events as $ev)
                                        <span class="inline-flex px-1.5 py-0.5 rounded text-xs bg-surface-secondary text-text-secondary border border-border">
                                            {{ str_replace('_', ' ', $ev) }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>

                            {{-- Active toggle --}}
                            <td class="px-5 py-4">
                                <form method="POST" action="{{ $host }}/settings/webhooks/{{ $webhook->id }}/toggle">
                                    @csrf @method('PATCH')
                                    <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-medium transition-colors
                                                   {{ $webhook->active
                                                       ? 'bg-success-lightest text-success-foreground hover:opacity-80'
                                                       : 'bg-surface-secondary text-text-muted border border-border hover:opacity-80' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $webhook->active ? 'bg-success-foreground' : 'bg-text-muted' }}"></span>
                                        {{ $webhook->active ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>

                            {{-- Last delivery --}}
                            <td class="px-5 py-4">
                                @if ($latest)
                                    @if ($latest->isSuccess())
                                        <span class="inline-flex items-center gap-1 text-xs text-success-foreground">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            {{ $latest->response_status }} &middot; {{ $latest->attempted_at->diffForHumans() }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs text-error">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            {{ $latest->response_status ?? 'Error' }} &middot; {{ $latest->attempted_at->diffForHumans() }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-xs text-text-muted">No deliveries yet</span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <a href="{{ $host }}/settings/webhooks/{{ $webhook->id }}/deliveries"
                                       class="px-2.5 py-1 text-xs font-medium rounded-md border border-border text-text-secondary bg-surface hover:bg-surface-secondary transition-colors">
                                        Deliveries
                                        @if ($webhook->deliveries_count > 0)
                                            <span class="ml-1 text-text-muted">({{ $webhook->deliveries_count }})</span>
                                        @endif
                                    </a>
                                    <form method="POST" action="{{ $host }}/settings/webhooks/{{ $webhook->id }}"
                                          onsubmit="return confirm('Remove this webhook endpoint?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="px-2.5 py-1 text-xs font-medium rounded-md border border-error/30 text-error bg-error-light/40 hover:bg-error-light transition-colors">
                                            Remove
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

    </div>

    {{-- Info card --}}
    <div class="bg-surface-secondary border border-border rounded-xl p-4 flex items-start gap-3">
        <svg class="w-4 h-4 text-text-muted shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
            <p class="text-xs font-medium text-text-primary mb-0.5">Webhook security</p>
            <p class="text-xs text-text-muted leading-relaxed">
                Every request includes an <code class="bg-surface border border-border px-1 rounded">X-Skolet-Signature: sha256=&lt;HMAC&gt;</code> header.
                Verify this against your secret using HMAC-SHA256 to confirm requests come from Skolet.
                Failed deliveries are automatically retried after 1 min, 5 min, and 30 min.
            </p>
        </div>
    </div>

    {{-- Add Webhook Modal --}}
    <div x-show="addOpen" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="bg-surface rounded-2xl shadow-xl border border-border w-full max-w-lg p-6"
             @click.outside="addOpen = false">
            <h3 class="font-semibold text-text-darkest mb-1">Add Webhook Endpoint</h3>
            <p class="text-sm text-text-muted mb-5">Configure an HTTPS endpoint to receive event notifications.</p>

            <form method="POST" action="{{ $host }}/settings/webhooks">
                @csrf

                {{-- URL --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-text-dark mb-1.5">
                        Endpoint URL <span class="text-error">*</span>
                    </label>
                    <input type="url" name="url" placeholder="https://your-server.com/webhook"
                           value="{{ old('url') }}"
                           class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors"
                           required>
                    <p class="text-xs text-text-muted mt-1">Must use HTTPS. Skolet will POST JSON payloads here.</p>
                </div>

                {{-- Secret --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-text-dark mb-1.5">
                        Secret Key <span class="text-error">*</span>
                    </label>
                    <div class="flex gap-2">
                        <input type="text" name="secret" x-model="secret"
                               class="flex-1 px-3 py-2 bg-surface border border-border rounded-md text-sm font-mono focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors"
                               required minlength="16">
                        <button type="button" @click="generateSecret()"
                                class="px-3 py-2 text-xs font-medium bg-surface border border-border text-text-secondary rounded-md hover:bg-surface-secondary transition-colors whitespace-nowrap">
                            Generate
                        </button>
                    </div>
                    <p class="text-xs text-text-muted mt-1">Used to verify the <code class="bg-surface border border-border px-0.5 rounded">X-Skolet-Signature</code> header.</p>
                </div>

                {{-- Events --}}
                <div class="mb-5">
                    <label class="block text-sm font-medium text-text-dark mb-2">
                        Events to subscribe <span class="text-error">*</span>
                    </label>
                    <div class="flex flex-col gap-2">
                        @foreach ([
                            'student_enrolled'   => 'Student Enrolled â€” when a new student is added',
                            'payment_received'   => 'Payment Received â€” when a fee payment is recorded',
                            'attendance_marked'  => 'Attendance Marked â€” when a class attendance session is saved',
                            'exam_published'     => 'Exam Published â€” when exam results are made visible to students',
                            'announcement_posted'=> 'Announcement Posted â€” when a new announcement is created',
                        ] as $value => $label)
                        <label class="flex items-start gap-3 cursor-pointer group">
                            <input type="checkbox" name="events[]" value="{{ $value }}"
                                   class="mt-0.5 h-4 w-4 rounded border-border text-accent focus:ring-accent">
                            <div>
                                <p class="text-sm font-medium text-text-primary group-hover:text-accent transition-colors">
                                    {{ Str::before($label, ' â€”') }}
                                </p>
                                <p class="text-xs text-text-muted">{{ Str::after($label, 'â€” ') }}</p>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex gap-3 justify-end">
                    <button type="button" @click="addOpen = false"
                            class="px-4 py-2 text-sm font-medium text-text-secondary bg-surface border border-border rounded-md hover:bg-surface-secondary transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium bg-accent text-accent-foreground rounded-md hover:bg-accent-dark transition-colors">
                        Add Webhook
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function webhooksPage() {
    return {
        addOpen: {{ $errors->isNotEmpty() ? 'true' : 'false' }},
        secret: '{{ old('secret') ?? '' }}',

        openAdd() {
            if (!this.secret) this.generateSecret();
            this.addOpen = true;
        },
        generateSecret() {
            const arr = new Uint8Array(24);
            window.crypto.getRandomValues(arr);
            this.secret = Array.from(arr).map(b => b.toString(16).padStart(2, '0')).join('');
        },
    };
}
</script>
@endpush
