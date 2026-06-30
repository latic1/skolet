@extends('layouts.tenant')

@section('title', $student->full_name . ' &mdash; Student Profile')
@section('page-title', 'Students')

@section('content')
@php $host = request()->getSchemeAndHttpHost(); @endphp
<div class="flex flex-col gap-6 max-w-3xl">

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="bg-success-lightest border border-success-light text-success-foreground text-sm px-4 py-3 rounded-xl flex items-start gap-3">
        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-text-muted">
        <a href="{{ $host }}/students" class="hover:text-text-primary transition-colors">Students</a>
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-text-primary font-medium">{{ $student->full_name }}</span>
    </div>

    {{-- Profile Header Card --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card p-6">
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-accent-muted flex items-center justify-center shrink-0">
                    <span class="text-xl font-semibold text-accent">{{ mb_strtoupper(mb_substr($student->full_name, 0, 1)) }}</span>
                </div>
                <div>
                    <h2 class="text-base font-semibold text-text-primary">{{ $student->full_name }}</h2>
                    <p class="text-sm text-text-muted mt-0.5">{{ $student->admission_no }}</p>
                    <div class="flex items-center gap-2 mt-2">
                        @php
                            $statusClass = match($student->status) {
                                'active'    => 'bg-success-lightest text-success-foreground',
                                'inactive'  => 'bg-surface-secondary text-text-secondary',
                                'graduated' => 'bg-accent-muted text-accent',
                                default     => 'bg-surface-secondary text-text-secondary',
                            };
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                            {{ ucfirst($student->status) }}
                        </span>
                        @if($student->schoolClass)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-accent-muted text-accent">
                            {{ $student->schoolClass->name }}{{ $student->section ? ' &mdash; ' . $student->section->name : '' }}
                        </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                @if($canDownloadTranscript)
                <a href="{{ $host }}/students/{{ $student->id }}/transcript"
                   class="flex items-center gap-2 px-4 py-2 bg-surface border border-border text-sm font-medium text-text-primary rounded-md hover:bg-surface-secondary transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Transcript
                </a>
                @endif
                @can('students.edit')
                <a href="{{ $host }}/students/{{ $student->id }}/edit"
                   class="flex items-center gap-2 px-4 py-2 bg-surface border border-border text-sm font-medium text-text-primary rounded-md hover:bg-surface-secondary transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit
                </a>
                <form method="POST" action="{{ $host }}/students/{{ $student->id }}/export">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-2 px-4 py-2 bg-surface border border-border text-sm font-medium text-text-primary rounded-md hover:bg-surface-secondary transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Export Data
                    </button>
                </form>
                <form method="POST" action="{{ $host }}/students/{{ $student->id }}/anonymize"
                      onsubmit="return confirm('Anonymise personal data for {{ addslashes($student->full_name) }}? Their name and contact info will be replaced with placeholders. Academic records are preserved. This cannot be undone.')">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-2 px-4 py-2 bg-surface border border-border text-sm font-medium text-text-secondary rounded-md hover:bg-surface-secondary transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                        Anonymise
                    </button>
                </form>
                @endcan
                @can('students.delete')
                <form method="POST" action="{{ $host }}/students/{{ $student->id }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            onclick="return confirm('Move {{ addslashes($student->full_name) }} to trash?')"
                            class="flex items-center gap-2 px-4 py-2 bg-error-light text-error text-sm font-medium rounded-md hover:bg-red-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Delete
                    </button>
                </form>
                @endcan
            </div>
        </div>
    </div>

    {{-- Personal Details --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card p-6">
        <h3 class="text-base font-semibold text-text-primary mb-5">Personal Details</h3>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
            <div>
                <dt class="text-xs font-medium text-text-muted uppercase tracking-wide mb-1">Date of Birth</dt>
                <dd class="text-sm text-text-primary">{{ $student->date_of_birth?->format('d M Y') ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-text-muted uppercase tracking-wide mb-1">Gender</dt>
                <dd class="text-sm text-text-primary">{{ $student->gender ? ucfirst($student->gender) : '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-text-muted uppercase tracking-wide mb-1">Address</dt>
                <dd class="text-sm text-text-primary">{{ $student->address ?? '—' }}</dd>
            </div>
        </dl>
    </div>

    {{-- Guardian Details --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card p-6">
        <h3 class="text-base font-semibold text-text-primary mb-5">Guardian Details</h3>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
            <div>
                <dt class="text-xs font-medium text-text-muted uppercase tracking-wide mb-1">Guardian Name</dt>
                <dd class="text-sm text-text-primary">{{ $student->guardian_name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-text-muted uppercase tracking-wide mb-1">Contact</dt>
                <dd class="text-sm text-text-primary">{{ $student->guardian_contact ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-text-muted uppercase tracking-wide mb-1">Email</dt>
                <dd class="text-sm text-text-primary">
                    @if($student->guardian_email)
                    <a href="mailto:{{ $student->guardian_email }}" class="text-accent hover:text-accent-dark transition-colors">{{ $student->guardian_email }}</a>
                    @else
                    &mdash;
                    @endif
                </dd>
            </div>
        </dl>
    </div>

    {{-- Academic --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card p-6">
        <h3 class="text-base font-semibold text-text-primary mb-5">Academic Details</h3>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
            <div>
                <dt class="text-xs font-medium text-text-muted uppercase tracking-wide mb-1">Class</dt>
                <dd class="text-sm text-text-primary">{{ $student->schoolClass?->name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-text-muted uppercase tracking-wide mb-1">Section</dt>
                <dd class="text-sm text-text-primary">{{ $student->section?->name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-text-muted uppercase tracking-wide mb-1">Admission Number</dt>
                <dd class="text-sm font-medium text-text-primary">{{ $student->admission_no }}</dd>
            </div>
        </dl>
    </div>

    {{-- Fee Discounts --}}
    @canany(['fees.edit', 'fees.view'])
    <div class="bg-surface border border-border rounded-2xl shadow-card p-6"
         x-data="{
            showModal: false,
            form: { fee_structure_id: '', discount_type: 'percentage', discount_value: '', reason: '', valid_from: '', valid_until: '' },
            init() {
                @if($errors->hasAny(['discount_type','discount_value','reason','fee_structure_id','valid_from','valid_until']))
                this.showModal = true;
                @endif
            }
         }">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-base font-semibold text-text-primary">Fee Discounts</h3>
            @can('fees.edit')
            <button type="button" @click="showModal = true"
                    class="flex items-center gap-1.5 px-3 py-1.5 bg-accent text-accent-foreground text-xs font-medium rounded-md hover:bg-accent-dark transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Discount
            </button>
            @endcan
        </div>

        @if($feeDiscounts->isEmpty())
        <div class="flex flex-col items-center justify-center py-8 text-center">
            <div class="w-10 h-10 rounded-xl bg-surface-secondary flex items-center justify-center mx-auto mb-3">
                <svg class="w-5 h-5 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
            </div>
            <p class="text-sm text-text-muted">No fee discounts applied.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm" style="min-width: 540px">
                <thead>
                    <tr class="border-b border-border">
                        <th class="text-left text-xs font-medium text-text-muted uppercase tracking-wide pb-2 pr-4">Type</th>
                        <th class="text-left text-xs font-medium text-text-muted uppercase tracking-wide pb-2 pr-4">Value</th>
                        <th class="text-left text-xs font-medium text-text-muted uppercase tracking-wide pb-2 pr-4">Applies To</th>
                        <th class="text-left text-xs font-medium text-text-muted uppercase tracking-wide pb-2 pr-4">Reason</th>
                        <th class="text-left text-xs font-medium text-text-muted uppercase tracking-wide pb-2 pr-4">Expiry</th>
                        @can('fees.edit')<th class="pb-2"></th>@endcan
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($feeDiscounts as $discount)
                    @php
                        $today   = today();
                        $expired = $discount->valid_until && $discount->valid_until->lt($today);
                        $pending = $discount->valid_from && $discount->valid_from->gt($today);
                    @endphp
                    <tr class="{{ $expired ? 'opacity-50' : '' }}">
                        <td class="py-2.5 pr-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $discount->discount_type === 'percentage' ? 'bg-accent-muted text-accent' : 'bg-warning-light text-warning' }}">
                                {{ $discount->discount_type === 'percentage' ? 'Percentage' : 'Fixed' }}
                            </span>
                        </td>
                        <td class="py-2.5 pr-4 font-medium text-text-primary">
                            {{ $discount->discount_type === 'percentage'
                                ? number_format((float)$discount->discount_value, 0) . '%'
                                : format_money((float)$discount->discount_value, $currencySymbol) }}
                        </td>
                        <td class="py-2.5 pr-4 text-text-secondary">
                            {{ $discount->feeStructure ? $discount->feeStructure->fee_item : 'All fees' }}
                        </td>
                        <td class="py-2.5 pr-4 text-text-secondary max-w-xs truncate" title="{{ $discount->reason }}">
                            {{ $discount->reason }}
                        </td>
                        <td class="py-2.5 pr-4 text-text-secondary">
                            @if($expired)
                                <span class="text-error text-xs">Expired {{ $discount->valid_until->format('M j, Y') }}</span>
                            @elseif($pending)
                                <span class="text-text-muted text-xs">From {{ $discount->valid_from->format('M j, Y') }}</span>
                            @elseif($discount->valid_until)
                                {{ $discount->valid_until->format('M j, Y') }}
                            @else
                                <span class="text-text-muted text-xs">No expiry</span>
                            @endif
                        </td>
                        @can('fees.edit')
                        <td class="py-2.5 text-right">
                            <form method="POST" action="{{ $host }}/students/{{ $student->id }}/discounts/{{ $discount->id }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        onclick="return confirm('Remove this discount from {{ addslashes($student->full_name) }}?')"
                                        class="text-xs text-error hover:text-red-700 transition-colors">
                                    Remove
                                </button>
                            </form>
                        </td>
                        @endcan
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Add Discount Modal --}}
        @can('fees.edit')
        <div x-show="showModal" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             @keydown.escape.window="showModal = false">
            <div class="absolute inset-0 bg-black/50" @click="showModal = false"></div>
            <div class="relative bg-surface rounded-2xl shadow-2xl w-full max-w-md z-10"
                 @click.stop>
                <div class="flex items-center justify-between px-6 py-4 border-b border-border">
                    <h4 class="text-sm font-semibold text-text-primary">Add Fee Discount</h4>
                    <button type="button" @click="showModal = false"
                            class="p-1 rounded-md text-text-muted hover:bg-surface-secondary hover:text-text-primary transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                @if($errors->hasAny(['discount_type','discount_value','reason','fee_structure_id','valid_from','valid_until']))
                <div class="mx-6 mt-4 p-3 bg-error-light border border-error rounded-xl text-xs text-error">
                    <ul class="space-y-0.5">
                        @foreach($errors->only(['discount_type','discount_value','reason','fee_structure_id','valid_from','valid_until']) as $msgs)
                            @foreach($msgs as $msg)
                            <li>{{ $msg }}</li>
                            @endforeach
                        @endforeach
                    </ul>
                </div>
                @endif

                <form method="POST" action="{{ $host }}/students/{{ $student->id }}/discounts"
                      class="px-6 py-5 flex flex-col gap-4">
                    @csrf

                    {{-- Discount Type --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="block text-sm font-medium text-text-dark">Discount Type <span class="text-error">*</span></label>
                        <select name="discount_type" x-model="form.discount_type" required
                                class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent {{ $errors->has('discount_type') ? 'border-error' : '' }}">
                            <option value="percentage">Percentage (%)</option>
                            <option value="fixed">Fixed Amount</option>
                        </select>
                    </div>

                    {{-- Value --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="block text-sm font-medium text-text-dark">
                            Value <span class="text-error">*</span>
                            <span class="text-xs text-text-muted font-normal" x-show="form.discount_type === 'percentage'">(max 100)</span>
                        </label>
                        <input type="number" name="discount_value" x-model="form.discount_value"
                               required min="0.01" step="0.01"
                               :max="form.discount_type === 'percentage' ? 100 : undefined"
                               placeholder="e.g. 50"
                               class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent {{ $errors->has('discount_value') ? 'border-error' : '' }}">
                        @error('discount_value')
                        <p class="text-xs text-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Applies To --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="block text-sm font-medium text-text-dark">Applies To</label>
                        <select name="fee_structure_id" x-model="form.fee_structure_id"
                                class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent {{ $errors->has('fee_structure_id') ? 'border-error' : '' }}">
                            <option value="">All fees (blanket discount)</option>
                            @foreach($studentFeeStructures as $fs)
                            <option value="{{ $fs->id }}">{{ $fs->fee_item }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Reason --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="block text-sm font-medium text-text-dark">Reason <span class="text-error">*</span></label>
                        <input type="text" name="reason" x-model="form.reason" required maxlength="500"
                               placeholder="e.g. Scholarship, Financial hardship waiver"
                               class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent {{ $errors->has('reason') ? 'border-error' : '' }}">
                        @error('reason')
                        <p class="text-xs text-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Valid From + Valid Until --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="block text-sm font-medium text-text-dark">Valid From <span class="text-xs text-text-muted font-normal">(optional)</span></label>
                            <input type="date" name="valid_from" x-model="form.valid_from"
                                   class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent {{ $errors->has('valid_from') ? 'border-error' : '' }}">
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="block text-sm font-medium text-text-dark">Valid Until <span class="text-xs text-text-muted font-normal">(optional)</span></label>
                            <input type="date" name="valid_until" x-model="form.valid_until"
                                   class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent {{ $errors->has('valid_until') ? 'border-error' : '' }}">
                            @error('valid_until')
                            <p class="text-xs text-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-1">
                        <button type="button" @click="showModal = false"
                                class="px-4 py-2 text-sm font-medium text-text-secondary border border-border rounded-md hover:bg-surface-secondary transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors">
                            Add Discount
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endcan
    </div>
    @endcanany

    {{-- Login Account --}}
    @can('students.edit')
    <div class="bg-surface border border-border rounded-2xl shadow-card p-6"
         x-data="{ submitting: false }">
        <h3 class="text-base font-semibold text-text-primary mb-5">Login Account</h3>

        @if($student->user)
        {{-- Account exists --}}
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-success-lightest flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-success-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-text-primary">{{ $student->user->email }}</p>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-accent-muted text-accent capitalize mt-1">
                        {{ $student->user->getRoleNames()->first() ?? 'No role' }}
                    </span>
                </div>
            </div>
            <form method="POST" action="{{ $host }}/students/{{ $student->id }}/login"
                  @submit="submitting = true"
                  onsubmit="return confirm('Remove login access for {{ addslashes($student->full_name) }}? They will no longer be able to log in.')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        :disabled="submitting"
                        :class="submitting ? 'opacity-60 cursor-not-allowed' : 'hover:bg-red-100'"
                        class="px-3 py-1.5 bg-error-light text-error text-xs font-medium rounded-md transition-colors">
                    <span x-show="!submitting">Revoke Access</span>
                    <span x-show="submitting">Revoking&hellip;</span>
                </button>
            </form>
        </div>
        @else
        {{-- No account --}}
        <div class="mb-5 flex items-start gap-3 p-4 bg-surface-secondary rounded-xl">
            <svg class="w-4 h-4 text-text-muted mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-xs text-text-secondary">No login account yet. Create one so this student or their parent can log in to view fees, report cards, and announcements.</p>
        </div>

        @if($errors->hasAny(['email', 'password', 'password_confirmation', 'role']))
        <div class="mb-4 p-3 bg-error-light border border-error rounded-xl text-xs text-error">
            <ul class="space-y-0.5">
                @foreach($errors->only(['email', 'password', 'password_confirmation', 'role']) as $msgs)
                    @foreach($msgs as $msg)
                    <li>{{ $msg }}</li>
                    @endforeach
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ $host }}/students/{{ $student->id }}/login"
              class="flex flex-col gap-4"
              @submit="submitting = true">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-text-dark mb-1.5">Email Address <span class="text-error">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           placeholder="{{ $student->guardian_email ?? 'student@example.com' }}"
                           class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors @error('email') border-error @enderror">
                </div>
                <div>
                    <label class="block text-sm font-medium text-text-dark mb-1.5">Password <span class="text-error">*</span></label>
                    <input type="password" name="password" required minlength="8"
                           class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors @error('password') border-error @enderror">
                </div>
                <div>
                    <label class="block text-sm font-medium text-text-dark mb-1.5">Confirm Password <span class="text-error">*</span></label>
                    <input type="password" name="password_confirmation" required
                           class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                </div>
                <div>
                    <label class="block text-sm font-medium text-text-dark mb-1.5">Account Role</label>
                    <input type="hidden" name="role" value="student">
                    <p class="text-sm text-text-muted py-2">Student &mdash; logs in as themselves</p>
                    <p class="text-xs text-text-muted">To give parents access, use the <strong>Parent Accounts</strong> section below.</p>
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit"
                        :disabled="submitting"
                        :class="submitting ? 'opacity-60 cursor-not-allowed' : 'hover:bg-accent-dark'"
                        class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md transition-colors">
                    <span x-show="!submitting">Create Login Account</span>
                    <span x-show="submitting">Creating&hellip;</span>
                </button>
            </div>
        </form>
        @endif
    </div>
    @endcan

    {{-- Parent Accounts --}}
    @can('students.edit')
    <div class="bg-surface border border-border rounded-2xl shadow-card p-6"
         x-data="{ mode: 'create', submitting: false }">
        <h3 class="text-base font-semibold text-text-primary mb-5">Parent Accounts</h3>

        {{-- Linked parents list --}}
        @if($student->parents->isEmpty())
        <div class="flex items-center justify-center py-6 text-center mb-5">
            <div>
                <div class="w-10 h-10 rounded-xl bg-surface-secondary flex items-center justify-center mx-auto mb-3">
                    <svg class="w-5 h-5 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <p class="text-sm text-text-muted">No parent accounts linked yet.</p>
            </div>
        </div>
        @else
        <div class="overflow-x-auto mb-5">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border">
                        <th class="text-left text-xs font-medium text-text-muted uppercase tracking-wide pb-2 pr-4">Name</th>
                        <th class="text-left text-xs font-medium text-text-muted uppercase tracking-wide pb-2 pr-4">Email</th>
                        <th class="text-left text-xs font-medium text-text-muted uppercase tracking-wide pb-2 pr-4">Relationship</th>
                        <th class="pb-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($student->parents as $parent)
                    <tr>
                        <td class="py-2.5 pr-4 font-medium text-text-primary">{{ $parent->name }}</td>
                        <td class="py-2.5 pr-4 text-text-secondary">{{ $parent->email }}</td>
                        <td class="py-2.5 pr-4 text-text-secondary capitalize">{{ $parent->pivot->relationship ?? '—' }}</td>
                        <td class="py-2.5 text-right">
                            <form method="POST" action="{{ $host }}/students/{{ $student->id }}/parents/{{ $parent->id }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        onclick="return confirm('Remove {{ addslashes($parent->name) }} as a parent of {{ addslashes($student->full_name) }}?')"
                                        class="text-xs text-error hover:text-red-700 transition-colors">
                                    Remove
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Add parent form --}}
        <div class="border-t border-border pt-5">
            <p class="text-xs font-medium text-text-muted uppercase tracking-wide mb-3">Link Parent Account</p>

            {{-- Mode toggle --}}
            <div class="flex gap-2 mb-4">
                <button type="button" @click="mode = 'create'"
                        :class="mode === 'create' ? 'bg-accent text-accent-foreground' : 'bg-surface border border-border text-text-primary hover:bg-surface-secondary'"
                        class="px-3 py-1.5 text-xs font-medium rounded-md transition-colors">
                    Create new account
                </button>
                <button type="button" @click="mode = 'link'"
                        :class="mode === 'link' ? 'bg-accent text-accent-foreground' : 'bg-surface border border-border text-text-primary hover:bg-surface-secondary'"
                        class="px-3 py-1.5 text-xs font-medium rounded-md transition-colors">
                    Link existing account
                </button>
            </div>

            @if($errors->hasAny(['name', 'email', 'phone', 'password', 'password_confirmation', 'parent_email', 'relationship', 'mode']))
            <div class="mb-4 p-3 bg-error-light border border-error rounded-xl text-xs text-error">
                <ul class="space-y-0.5">
                    @foreach($errors->only(['name', 'email', 'phone', 'password', 'password_confirmation', 'parent_email', 'relationship', 'mode']) as $msgs)
                        @foreach($msgs as $msg)
                        <li>{{ $msg }}</li>
                        @endforeach
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ $host }}/students/{{ $student->id }}/parents"
                  class="flex flex-col gap-4"
                  @submit="submitting = true">
                @csrf
                <input type="hidden" name="mode" :value="mode">

                {{-- Create new account fields --}}
                <div x-show="mode === 'create'" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs text-text-muted mb-1">Full Name <span class="text-error">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}"
                               placeholder="Kwame Mensah"
                               class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs text-text-muted mb-1">Email Address <span class="text-error">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}"
                               placeholder="parent@example.com"
                               class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs text-text-muted mb-1">Phone Number</label>
                        <input type="text" name="phone" value="{{ old('phone') }}"
                               placeholder="0244123456"
                               class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs text-text-muted mb-1">Password <span class="text-error">*</span></label>
                        <input type="password" name="password" minlength="8"
                               class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs text-text-muted mb-1">Confirm Password <span class="text-error">*</span></label>
                        <input type="password" name="password_confirmation"
                               class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs text-text-muted mb-1">Relationship</label>
                        <select name="relationship"
                                class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                            <option value="">Select&hellip;</option>
                            <option value="father"   @selected(old('relationship') === 'father')>Father</option>
                            <option value="mother"   @selected(old('relationship') === 'mother')>Mother</option>
                            <option value="guardian" @selected(old('relationship') === 'guardian')>Guardian</option>
                        </select>
                    </div>
                </div>

                {{-- Link existing account fields --}}
                <div x-show="mode === 'link'" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs text-text-muted mb-1">Parent Email Address <span class="text-error">*</span></label>
                        <input type="email" name="parent_email" value="{{ old('parent_email') }}"
                               placeholder="existing.parent@example.com"
                               class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs text-text-muted mb-1">Relationship</label>
                        <select name="relationship"
                                class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                            <option value="">Select&hellip;</option>
                            <option value="father"   @selected(old('relationship') === 'father')>Father</option>
                            <option value="mother"   @selected(old('relationship') === 'mother')>Mother</option>
                            <option value="guardian" @selected(old('relationship') === 'guardian')>Guardian</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                            :disabled="submitting"
                            :class="submitting ? 'opacity-60 cursor-not-allowed' : 'hover:bg-accent-dark'"
                            class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md transition-colors">
                        <span x-show="!submitting" x-text="mode === 'create' ? 'Create & Link Parent' : 'Link Parent'"></span>
                        <span x-show="submitting">Saving&hellip;</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endcan

    {{-- Attendance Summary (Phase 3 placeholder) --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-base font-semibold text-text-primary">Attendance History</h3>
            <span class="text-xs text-text-muted bg-surface-secondary px-2 py-1 rounded-md">Available in Phase 3</span>
        </div>
        <div class="flex items-center justify-center py-8 text-center">
            <div>
                <div class="w-10 h-10 rounded-xl bg-surface-secondary flex items-center justify-center mx-auto mb-3">
                    <svg class="w-5 h-5 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <p class="text-sm text-text-muted">Attendance records will appear here once daily attendance is set up.</p>
            </div>
        </div>
    </div>

    {{-- Exam Results (Phase 4 placeholder) --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-base font-semibold text-text-primary">Exam Results</h3>
            <span class="text-xs text-text-muted bg-surface-secondary px-2 py-1 rounded-md">Available in Phase 4</span>
        </div>
        <div class="flex items-center justify-center py-8 text-center">
            <div>
                <div class="w-10 h-10 rounded-xl bg-surface-secondary flex items-center justify-center mx-auto mb-3">
                    <svg class="w-5 h-5 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <p class="text-sm text-text-muted">Exam results will appear here once exams are set up.</p>
            </div>
        </div>
    </div>

    {{-- Fee Status (Phase 5 placeholder) --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-base font-semibold text-text-primary">Fee Status</h3>
            <span class="text-xs text-text-muted bg-surface-secondary px-2 py-1 rounded-md">Available in Phase 5</span>
        </div>
        <div class="flex items-center justify-center py-8 text-center">
            <div>
                <div class="w-10 h-10 rounded-xl bg-surface-secondary flex items-center justify-center mx-auto mb-3">
                    <svg class="w-5 h-5 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <p class="text-sm text-text-muted">Fee payments will appear here once fee structures are set up.</p>
            </div>
        </div>
    </div>

    {{-- Behavior / Discipline --}}
    @can('behavior.view')
    <div class="bg-surface border border-border rounded-2xl shadow-card overflow-hidden"
         x-data="studentBehavior()">

        <div class="flex items-center justify-between px-6 py-4 border-b border-border">
            <div>
                <h3 class="text-base font-semibold text-text-primary">Behavior & Discipline</h3>
                <p class="text-xs text-text-muted mt-0.5">{{ $disciplinaryRecords->count() }} {{ Str::plural('record', $disciplinaryRecords->count()) }}</p>
            </div>
            @can('behavior.create')
            <button @click="showModal = true"
                    class="flex items-center gap-1.5 px-3 py-1.5 bg-accent text-accent-foreground text-xs font-medium rounded-md hover:bg-accent-dark transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Log Incident
            </button>
            @endcan
        </div>

        @if($disciplinaryRecords->isEmpty())
        <div class="flex items-center justify-center py-10 text-center px-6">
            <p class="text-sm text-text-muted">No behavior records for this student.</p>
        </div>
        @else
        <div class="divide-y divide-border">
            @foreach($disciplinaryRecords as $record)
            @php
                $typeBadge = match($record->incident_type) {
                    'warning'      => 'bg-warning-light text-warning',
                    'detention'    => 'bg-error-light text-error',
                    'suspension'   => 'bg-error-light text-error',
                    'expulsion'    => 'bg-error text-white',
                    'commendation' => 'bg-success-lightest text-success-foreground',
                    default        => 'bg-surface-secondary text-text-secondary',
                };
            @endphp
            <div class="px-6 py-4" x-data="{ expanded: false }">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-start gap-3 min-w-0">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium shrink-0 {{ $typeBadge }}">
                            {{ ucfirst($record->incident_type) }}
                        </span>
                        <div class="min-w-0">
                            <p class="text-sm text-text-primary leading-snug line-clamp-2"
                               x-show="!expanded">{{ $record->description }}</p>
                            <p class="text-sm text-text-primary leading-snug whitespace-pre-line"
                               x-show="expanded" x-cloak>{{ $record->description }}</p>
                            @if($record->action_taken)
                            <p class="text-xs text-text-muted mt-1"
                               x-show="expanded" x-cloak>
                                <span class="font-medium">Action:</span> {{ $record->action_taken }}
                            </p>
                            @endif
                            <div class="flex items-center gap-3 mt-1.5 flex-wrap">
                                <span class="text-xs text-text-muted">{{ $record->date->format('d M Y') }}</span>
                                <span class="text-xs text-text-muted">by {{ $record->reportedBy?->name ?? '—' }}</span>
                                @if($record->parent_notified)
                                <span class="text-xs text-success-foreground font-medium">Parent notified</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-1 shrink-0">
                        @if(strlen($record->description) > 120 || $record->action_taken)
                        <button @click="expanded = !expanded"
                                class="text-xs text-accent hover:text-accent-dark transition-colors"
                                x-text="expanded ? 'Less' : 'More'"></button>
                        @endif
                        @can('behavior.delete')
                        <form method="POST" action="{{ $host }}/behavior/{{ $record->id }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    onclick="return confirm('Delete this record?')"
                                    class="p-1 rounded text-text-muted hover:text-error hover:bg-error-light transition-colors ml-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                        @endcan
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Log Incident Modal (student pre-filled) --}}
        <div x-show="showModal"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-start justify-center p-4 pt-10 sm:items-center sm:pt-4"
             style="display: none;">

            <div class="absolute inset-0 bg-overlay/40" @click="showModal = false"></div>

            <div class="relative w-full max-w-lg bg-surface rounded-2xl shadow-xl border border-border max-h-[90vh] flex flex-col"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">

                <div class="flex items-center justify-between px-6 py-4 border-b border-border shrink-0">
                    <h3 class="text-base font-semibold text-text-primary">Log Incident &mdash; {{ $student->full_name }}</h3>
                    <button @click="showModal = false" class="p-1.5 rounded-md text-text-muted hover:bg-surface-secondary transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="overflow-y-auto flex-1">
                    <form method="POST" action="{{ $host }}/behavior"
                          class="flex flex-col gap-4 px-6 py-5"
                          @submit="submitting = true">
                        @csrf
                        <input type="hidden" name="student_id" value="{{ $student->id }}">

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-text-dark mb-1.5">Type <span class="text-error">*</span></label>
                                <select name="incident_type" x-model="form.incident_type" required
                                        class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                                    <option value="">Select&hellip;</option>
                                    @foreach(['warning','detention','suspension','expulsion','commendation'] as $type)
                                    <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-text-dark mb-1.5">Date <span class="text-error">*</span></label>
                                <input type="date" name="date" x-model="form.date" required
                                       max="{{ date('Y-m-d') }}"
                                       class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-text-dark mb-1.5">Description <span class="text-error">*</span></label>
                            <textarea name="description" x-model="form.description" rows="3"
                                      placeholder="What happened?"
                                      maxlength="2000" required
                                      class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors resize-y"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-text-dark mb-1.5">Action Taken</label>
                            <textarea name="action_taken" x-model="form.action_taken" rows="2"
                                      placeholder="Steps taken to address the incident (optional)"
                                      maxlength="1000"
                                      class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors resize-y"></textarea>
                        </div>

                        <label class="flex items-center gap-3 cursor-pointer select-none">
                            <input type="hidden" name="parent_notified" value="0">
                            <input type="checkbox" name="parent_notified" value="1"
                                   x-model="form.parent_notified"
                                   class="w-4 h-4 rounded border-border text-accent focus:ring-accent focus:ring-1">
                            <span class="text-sm text-text-primary">Notify parent by email</span>
                            @if(!$student->guardian_email)
                            <span class="text-xs text-text-muted">(no guardian email on file)</span>
                            @endif
                        </label>

                        <div class="flex justify-end gap-3 pt-1">
                            <button type="button" @click="showModal = false"
                                    class="px-4 py-2 bg-surface border border-border text-sm font-medium text-text-primary rounded-md hover:bg-surface-secondary transition-colors">
                                Cancel
                            </button>
                            <button type="submit"
                                    :disabled="submitting"
                                    :class="submitting ? 'opacity-60 cursor-not-allowed' : 'hover:bg-accent-dark'"
                                    class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md transition-colors">
                                <span x-show="!submitting">Log Incident</span>
                                <span x-show="submitting">Saving&hellip;</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endcan

</div>
@endsection

@push('scripts')
<script>
function studentBehavior() {
    return {
        showModal: false,
        submitting: false,
        form: { incident_type: '', description: '', action_taken: '', date: '{{ date('Y-m-d') }}', parent_notified: false },
    };
}
</script>
@endpush
