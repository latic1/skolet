@extends('layouts.tenant')

@section('title', 'Settings &mdash; Roles & Permissions')
@section('page-title', 'Settings')

@section('content')
@php $host = request()->getSchemeAndHttpHost(); @endphp
<div class="flex flex-col gap-6" x-data="{
    showModal: false,
    submitting: false,
    mode: 'add',
    form: { id: '', name: '', permissions: [] },

    openAdd() {
        this.mode = 'add';
        this.form = { id: '', name: '', permissions: [] };
        this.showModal = true;
    },

    openEdit(role) {
        this.mode = 'edit';
        this.form = { id: role.id, name: role.name, permissions: role.permissions };
        this.showModal = true;
    },

    close() {
        this.showModal = false;
        this.submitting = false;
    },

    isModuleAllChecked(perms) {
        return perms.length > 0 && perms.every(p => this.form.permissions.includes(p));
    },

    toggleModule(perms) {
        if (this.isModuleAllChecked(perms)) {
            this.form.permissions = this.form.permissions.filter(p => !perms.includes(p));
        } else {
            const toAdd = perms.filter(p => !this.form.permissions.includes(p));
            this.form.permissions = [...this.form.permissions, ...toAdd];
        }
    }
}">

    @include('partials.settings-tabs')

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="flex items-start gap-3 bg-success-lightest border border-success-light text-success-foreground text-sm px-4 py-3 rounded-xl">
        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="flex items-start gap-3 bg-error-light border border-error text-error text-sm px-4 py-3 rounded-xl">
        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- Page Header --}}
    <div class="flex items-center justify-between gap-4">
        <div>
            <h2 class="text-base font-semibold text-text-primary">Roles &amp; Permissions</h2>
            <p class="text-xs text-text-muted mt-0.5">Define who can access each module. Fixed roles cannot be modified.</p>
        </div>
        <button @click="openAdd()"
                class="flex items-center gap-2 px-4 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md hover:bg-accent-dark transition-colors shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Create Role
        </button>
    </div>

    {{-- Roles Table --}}
    <div class="bg-surface border border-border rounded-2xl shadow-card">
        <div class="flex items-center justify-between px-6 py-4 border-b border-border">
            <h3 class="text-sm font-semibold text-text-primary">All Roles</h3>
            <span class="text-xs text-text-muted">{{ $roles->count() }} {{ Str::plural('role', $roles->count()) }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-border bg-surface-secondary">
                        <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Role Name</th>
                        <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Permissions</th>
                        <th class="text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary">Type</th>
                        <th class="px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $role)
                    @php
                        $isFixed = in_array($role->name, $fixedRoles);
                        $permCount = $role->permissions->count();
                        $roleData = [
                            'id'          => $role->id,
                            'name'        => $role->name,
                            'permissions' => $role->permissions->pluck('name')->values()->toArray(),
                        ];
                    @endphp
                    <tr class="border-b border-border last:border-b-0 hover:bg-surface-secondary transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0
                                    {{ $isFixed ? 'bg-surface-secondary' : 'bg-accent-muted' }}">
                                    <svg class="w-4 h-4 {{ $isFixed ? 'text-text-muted' : 'text-accent' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-text-primary">
                                    {{ ucwords(str_replace('_', ' ', $role->name)) }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($isFixed && $role->name === 'school_admin')
                                <span class="text-sm text-text-secondary">All permissions</span>
                            @else
                                <span class="text-sm text-text-secondary">{{ $permCount }} {{ Str::plural('permission', $permCount) }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($isFixed)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-surface-secondary text-text-secondary">
                                    Fixed
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-accent-muted text-accent">
                                    Custom
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                @if(!$isFixed)
                                    <button @click="openEdit({{ Js::from($roleData) }})"
                                            class="text-xs font-medium text-text-secondary hover:text-text-primary transition-colors px-2 py-1 rounded hover:bg-surface-secondary">
                                        Edit
                                    </button>
                                    <form method="POST" action="{{ $host }}/settings/roles/{{ $role->id }}"
                                          onsubmit="return confirm('Delete the \'{{ addslashes(ucwords(str_replace('_', ' ', $role->name))) }}\' role? Staff assigned this role will lose their permissions.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-xs font-medium text-error hover:text-red-700 transition-colors px-2 py-1 rounded hover:bg-error-light">
                                            Delete
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-text-muted italic">Cannot modify</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach

                    @if($roles->isEmpty())
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-sm text-text-muted">No roles found.</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    {{-- Info Card --}}
    <div class="bg-accent-muted border border-accent-light rounded-2xl p-5 flex gap-4">
        <div class="w-8 h-8 rounded-lg bg-accent-light flex items-center justify-center shrink-0">
            <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <p class="text-sm font-medium text-text-primary mb-1">How roles work</p>
            <p class="text-xs text-text-secondary leading-relaxed">
                Assign roles to staff members when adding or editing them. Each role's permissions control which modules and actions are visible to that staff member.
                Custom roles you create here appear in the <a href="{{ $host }}/staff/create" class="text-accent hover:text-accent-dark transition-colors font-medium">Add Staff form</a>.
            </p>
        </div>
    </div>


    {{-- Create / Edit Modal --}}
    <div x-show="showModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-overlay/40" @click="close()"></div>

        {{-- Panel --}}
        <div class="relative w-full max-w-2xl bg-surface rounded-2xl shadow-xl border border-border flex flex-col"
             style="max-height: 90vh"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            {{-- Modal Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-border shrink-0">
                <h3 class="text-base font-semibold text-text-primary"
                    x-text="mode === 'add' ? 'Create Role' : 'Edit Role'"></h3>
                <button @click="close()"
                        class="p-1.5 rounded-md text-text-muted hover:bg-surface-secondary transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Scrollable body + single form --}}
            <form method="POST"
                  :action="mode === 'add' ? '{{ $host }}/settings/roles' : '{{ $host }}/settings/roles/' + form.id"
                  class="flex flex-col min-h-0 flex-1"
                  @submit="submitting = true">
                @csrf
                {{-- Empty string is falsy in PHP so Laravel ignores it and uses the real POST method --}}
                <input type="hidden" name="_method" :value="mode === 'edit' ? 'PUT' : ''">

                <div class="overflow-y-auto flex-1 px-6 py-5 flex flex-col gap-6">

                    {{-- Role Name --}}
                    <div>
                        <label class="block text-sm font-medium text-text-dark mb-1.5">
                            Role Name <span class="text-error">*</span>
                        </label>
                        <input type="text" name="name" x-model="form.name" required
                               placeholder="e.g. librarian, head_teacher"
                               class="w-full px-3 py-2 bg-surface border @error('name') border-error @else border-border @enderror rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                        <p class="mt-1 text-xs text-text-muted">Lowercase letters, numbers, and underscores only (e.g. <code class="bg-surface-secondary px-1 rounded">head_teacher</code>).</p>
                        @error('name')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>

                    {{-- Permissions --}}
                    <div>
                        <div class="mb-4">
                            <p class="text-sm font-semibold text-text-primary">Permissions</p>
                            <p class="text-xs text-text-muted mt-0.5">Select the modules and actions this role can access.</p>
                        </div>

                        <div class="flex flex-col gap-3">
                            @foreach($permissionModules as $module => $permissions)
                            @php $permsJson = Js::from($permissions); @endphp
                            <div class="border border-border rounded-xl overflow-hidden">
                                {{-- Module header --}}
                                <div class="flex items-center justify-between px-4 py-2.5 bg-surface-secondary border-b border-border">
                                    <span class="text-xs font-semibold uppercase tracking-wide text-text-secondary">{{ $module }}</span>
                                    <button type="button"
                                            @click="toggleModule({{ $permsJson }})"
                                            class="text-xs font-medium transition-colors hover:underline"
                                            :class="isModuleAllChecked({{ $permsJson }}) ? 'text-accent' : 'text-text-muted hover:text-text-primary'"
                                            x-text="isModuleAllChecked({{ $permsJson }}) ? 'Deselect all' : 'Select all'">
                                    </button>
                                </div>

                                {{-- Permission checkboxes --}}
                                <div class="flex flex-wrap gap-x-6 gap-y-3 px-4 py-3">
                                    @foreach($permissions as $perm)
                                    @php $action = ucfirst(explode('.', $perm)[1]); @endphp
                                    <label class="flex items-center gap-2 cursor-pointer select-none group">
                                        <input type="checkbox"
                                               name="permissions[]"
                                               value="{{ $perm }}"
                                               x-model="form.permissions"
                                               class="w-4 h-4 rounded border-border accent-accent cursor-pointer">
                                        <span class="text-sm text-text-primary group-hover:text-accent transition-colors">{{ $action }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>

                        @error('permissions')<p class="mt-2 text-xs text-error">{{ $message }}</p>@enderror
                        @error('permissions.*')<p class="mt-2 text-xs text-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-border shrink-0">
                    <button type="button" @click="close()"
                            class="px-4 py-2 bg-surface border border-border text-sm font-medium text-text-primary rounded-md hover:bg-surface-secondary transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            :disabled="submitting"
                            :class="submitting ? 'opacity-60 cursor-not-allowed' : 'hover:bg-accent-dark'"
                            class="px-5 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md transition-colors">
                        <span x-show="!submitting" x-text="mode === 'add' ? 'Create Role' : 'Save Changes'"></span>
                        <span x-show="submitting">Saving&hellip;</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
