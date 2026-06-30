@extends('layouts.tenant')

@section('title', 'Staff')
@section('page-title', 'Staff')

@section('content')
@php $host = request()->getSchemeAndHttpHost(); @endphp
<div class="flex flex-col gap-6"
     x-data="staffIndex()">

    {{-- Imported credentials (shown once after a successful import) --}}
    @if(session('staff_import_credentials'))
    <div class="bg-accent-muted border border-accent-light rounded-2xl p-5">
        <div class="flex items-start gap-3 mb-4">
            <div class="w-8 h-8 rounded-lg bg-accent flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-accent mb-0.5">Temporary Passwords</p>
                <p class="text-xs text-text-secondary">Share these credentials with each staff member. They should change their password after first login.</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="border-b border-accent-light">
                        <th class="text-left py-1.5 pr-6 font-medium text-text-secondary uppercase tracking-wide">Name</th>
                        <th class="text-left py-1.5 pr-6 font-medium text-text-secondary uppercase tracking-wide">Email</th>
                        <th class="text-left py-1.5 font-medium text-text-secondary uppercase tracking-wide">Temporary Password</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(session('staff_import_credentials') as $cred)
                    <tr class="border-b border-accent-light last:border-b-0">
                        <td class="py-2 pr-6 font-medium text-text-primary">{{ $cred['name'] }}</td>
                        <td class="py-2 pr-6 text-text-secondary">{{ $cred['email'] }}</td>
                        <td class="py-2 font-mono font-semibold text-accent tracking-wider">{{ $cred['password'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Import row-level errors --}}
    @if(session('staff_import_errors'))
    <div class="bg-error-light border border-error rounded-2xl p-5">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-error shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-error mb-1">
                    Import failed &mdash; {{ count(session('staff_import_errors')) }} {{ count(session('staff_import_errors')) !== 1 ? 'errors' : 'error' }} found. No records were imported.
                </p>
                <p class="text-xs text-text-secondary mb-3">Fix these errors in your file and try again.</p>
                <ul class="space-y-1 max-h-48 overflow-y-auto">
                    @foreach(session('staff_import_errors') as $err)
                    <li class="text-xs text-error">{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-base font-semibold text-text-primary">All Staff</h2>
            <p class="text-xs text-text-muted mt-0.5">{{ $staff->total() }} staff member{{ $staff->total() !== 1 ? 's' : '' }} total</p>
        </div>
        @can('staff.create')
        <div class="flex items-center gap-2">
            <button @click="showImport = true"
                    class="flex items-center gap-2 px-4 py-2 bg-surface border border-border text-sm font-medium text-text-primary rounded-md hover:bg-surface-secondary transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Import Excel
            </button>
            <a href="{{ $host }}/staff/create"
               class="flex items-center gap-2 px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Staff
            </a>
        </div>
        @endcan
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ $host }}/staff" class="flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[200px] max-w-xs">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
            </svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email"
                   class="w-full pl-9 pr-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
        </div>

        <select name="status" onchange="this.form.submit()"
                class="px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
            <option value="">All Statuses</option>
            <option value="active"   @selected(request('status') === 'active')>Active</option>
            <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
        </select>

        @if(request()->hasAny(['search', 'status']))
        <a href="{{ $host }}/staff"
           class="px-3 py-2 text-sm text-text-secondary hover:text-text-primary transition-colors">
            Clear
        </a>
        @endif

        <button type="submit"
                class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors">
            Search
        </button>
    </form>

    {{-- Table Card --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card">
        @if($staff->isEmpty())
        <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
            <div class="w-12 h-12 rounded-xl bg-accent-muted flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            @if(request()->hasAny(['search', 'status']))
            <p class="text-sm font-medium text-text-primary mb-1">No staff members found</p>
            <p class="text-xs text-text-muted mb-4">Try adjusting your filters or search term</p>
            <a href="{{ $host }}/staff" class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors">
                Clear Filters
            </a>
            @else
            <p class="text-sm font-medium text-text-primary mb-1">No staff members yet</p>
            <p class="text-xs text-text-muted mb-4">Add your first staff member to get started</p>
            @can('staff.create')
            <a href="{{ $host }}/staff/create"
               class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors">
                Add Staff Member
            </a>
            @endcan
            @endif
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-border">
                        <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Name</th>
                        <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary whitespace-nowrap">Role Title</th>
                        <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Email</th>
                        <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary whitespace-nowrap">System Role</th>
                        <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Status</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($staff as $member)
                    @php
                        $systemRole = $member->user?->getRoleNames()->first();
                    @endphp
                    <tr class="border-b border-border last:border-b-0 hover:bg-surface-secondary transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-accent-muted flex items-center justify-center shrink-0">
                                    <span class="text-sm font-semibold text-accent">{{ mb_strtoupper(mb_substr($member->full_name, 0, 1)) }}</span>
                                </div>
                                <a href="{{ $host }}/staff/{{ $member->id }}"
                                   class="text-sm font-medium text-text-primary hover:text-accent transition-colors">
                                    {{ $member->full_name }}
                                </a>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-text-secondary">{{ $member->role_title ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm text-text-secondary">{{ $member->user?->email ?? '—' }}</td>
                        <td class="px-6 py-4">
                            @if($systemRole)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-accent-muted text-accent capitalize">
                                {{ str_replace('_', ' ', $systemRole) }}
                            </span>
                            @else
                            <span class="text-sm text-text-secondary">&mdash;</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $statusClass = $member->status === 'active'
                                    ? 'bg-success-lightest text-success-foreground'
                                    : 'bg-surface-secondary text-text-secondary';
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                                {{ ucfirst($member->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ $host }}/staff/{{ $member->id }}"
                                   class="text-xs font-medium text-text-secondary hover:text-text-primary transition-colors px-2 py-1 rounded hover:bg-surface-secondary">
                                    View
                                </a>
                                @can('staff.edit')
                                <a href="{{ $host }}/staff/{{ $member->id }}/edit"
                                   class="text-xs font-medium text-text-secondary hover:text-text-primary transition-colors px-2 py-1 rounded hover:bg-surface-secondary">
                                    Edit
                                </a>
                                @endcan
                                @can('staff.delete')
                                <form method="POST" action="{{ $host }}/staff/{{ $member->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            onclick="return confirm('Remove {{ addslashes($member->full_name) }}? Their login account will also be deleted.')"
                                            class="text-xs font-medium text-error hover:text-red-700 transition-colors px-2 py-1 rounded hover:bg-error-light">
                                        Delete
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($staff->hasPages())
        <div class="px-6 py-4 border-t border-border">
            {{ $staff->links() }}
        </div>
        @endif
        @endif
    </div>

    {{-- Import Modal --}}
    @can('staff.create')
    <div x-show="showImport"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display: none;">

        <div class="absolute inset-0 bg-overlay/40" @click="showImport = false"></div>

        <div class="relative w-full max-w-md bg-surface rounded-2xl shadow-xl border border-border p-6"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-semibold text-text-primary">Import Staff</h3>
                <button @click="showImport = false" class="p-1.5 rounded-md text-text-muted hover:bg-surface-secondary transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Step 1: Download template --}}
            <div class="mb-4 p-4 rounded-xl bg-accent-muted border border-accent-light flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-accent flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-text-primary mb-1">Step 1 &mdash; Download the template</p>
                    <p class="text-xs text-text-secondary mb-2">Fill in your staff data. Do not change the column headers. Roles must match exactly: "teacher", "accountant", or a custom role name.</p>
                    <a href="{{ $host }}/staff/import/template"
                       class="inline-flex items-center gap-1.5 text-xs font-semibold text-accent hover:text-accent-dark transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Download skolet-staff-import-template.xlsx
                    </a>
                </div>
            </div>

            {{-- Step 2: Upload --}}
            <form method="POST" action="{{ $host }}/staff/import" enctype="multipart/form-data"
                  class="flex flex-col gap-4" x-data="{ submitting: false }" @submit="submitting = true">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-text-dark mb-1.5">
                        Step 2 &mdash; Upload completed file <span class="text-error">*</span>
                    </label>
                    <input type="file" name="import_file" accept=".xlsx,.csv" required
                           class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-accent-muted file:text-accent hover:file:bg-accent-light">
                    <p class="mt-1 text-xs text-text-muted">Accepts .xlsx or .csv &mdash; max 5 MB. A temporary password will be generated for each staff member.</p>
                    @error('import_file')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>
                <div class="flex items-center justify-end gap-2 pt-1">
                    <button type="button" @click="showImport = false"
                            class="px-4 py-2 bg-surface border border-border text-sm font-medium text-text-primary rounded-md hover:bg-surface-secondary transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            :disabled="submitting"
                            :class="submitting ? 'opacity-60 cursor-not-allowed' : 'hover:bg-accent-dark'"
                            class="px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md transition-colors">
                        <span x-show="!submitting">Import Staff</span>
                        <span x-show="submitting">Importing&hellip;</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endcan

</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('staffIndex', () => ({
        showImport: {{ ($errors->has('import_file') || session('show_import_staff')) ? 'true' : 'false' }},
    }));
});
</script>
@endpush
@endsection
