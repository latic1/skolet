@extends('layouts.tenant')

@section('title', 'Edit — ' . $staff->full_name)
@section('page-title', 'Staff')

@section('content')
@php $host = request()->getSchemeAndHttpHost(); @endphp
<div class="flex flex-col gap-6 max-w-3xl">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-text-muted">
        <a href="{{ $host }}/staff" class="hover:text-text-primary transition-colors">Staff</a>
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ $host }}/staff/{{ $staff->id }}" class="hover:text-text-primary transition-colors">{{ $staff->full_name }}</a>
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-text-primary font-medium">Edit</span>
    </div>

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

    <form method="POST" action="{{ $host }}/staff/{{ $staff->id }}" class="flex flex-col gap-6"
          x-data="{ submitting: false }" @submit="submitting = true">
        @csrf
        @method('PUT')

        {{-- Personal Information --}}
        <div class="bg-surface border border-border rounded-2xl shadow-card p-6 flex flex-col gap-5">
            <h3 class="text-base font-semibold text-text-primary">Personal Information</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-text-dark mb-1.5">Full Name <span class="text-error">*</span></label>
                    <input type="text" name="full_name" value="{{ old('full_name', $staff->full_name) }}" required
                           class="w-full px-3 py-2 bg-surface border @error('full_name') border-error @else border-border @enderror rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                    @error('full_name')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-dark mb-1.5">Role Title</label>
                    <input type="text" name="role_title" value="{{ old('role_title', $staff->role_title) }}"
                           placeholder="e.g. Class Teacher, Vice Principal"
                           class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                    @error('role_title')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-dark mb-1.5">Phone Number <span class="text-error">*</span></label>
                    <input type="text" name="phone" value="{{ old('phone', $staff->phone) }}" required
                           placeholder="e.g. 0244123456"
                           class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                    @error('phone')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-dark mb-1.5">Status <span class="text-error">*</span></label>
                    <select name="status" required
                            class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                        <option value="active"   @selected(old('status', $staff->status) === 'active')>Active</option>
                        <option value="inactive" @selected(old('status', $staff->status) === 'inactive')>Inactive</option>
                    </select>
                    @error('status')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Login Account --}}
        <div class="bg-surface border border-border rounded-2xl shadow-card p-6 flex flex-col gap-5">
            <div>
                <h3 class="text-base font-semibold text-text-primary">Login Account</h3>
                <p class="text-xs text-text-muted mt-1">Update email or system role. Leave the password fields blank to keep the current password.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-text-dark mb-1.5">Email Address <span class="text-error">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $staff->user?->email) }}" required
                           class="w-full px-3 py-2 bg-surface border @error('email') border-error @else border-border @enderror rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                    @error('email')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-dark mb-1.5">New Password</label>
                    <input type="password" name="new_password" autocomplete="new-password"
                           placeholder="Leave blank to keep unchanged"
                           class="w-full px-3 py-2 bg-surface border @error('new_password') border-error @else border-border @enderror rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                    @error('new_password')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-dark mb-1.5">Confirm New Password</label>
                    <input type="password" name="new_password_confirmation" autocomplete="new-password"
                           placeholder="Leave blank to keep unchanged"
                           class="w-full px-3 py-2 bg-surface border @error('new_password_confirmation') border-error @else border-border @enderror rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                    @error('new_password_confirmation')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-text-dark mb-1.5">System Role <span class="text-error">*</span></label>
                    @php $currentRole = $staff->user?->getRoleNames()->first(); @endphp
                    <select name="system_role" required
                            class="w-full px-3 py-2 bg-surface border @error('system_role') border-error @else border-border @enderror rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
                        <option value="">Select a role</option>
                        @foreach($roles as $role)
                        <option value="{{ $role->name }}" @selected(old('system_role', $currentRole) === $role->name)>
                            {{ ucwords(str_replace('_', ' ', $role->name)) }}
                        </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-text-muted">Controls what this staff member can access. <a href="{{ $host }}/settings/roles" class="text-accent hover:text-accent-dark transition-colors">Manage roles</a></p>
                    @error('system_role')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Form Actions --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ $host }}/staff/{{ $staff->id }}"
               class="px-4 py-2 bg-surface border border-border text-sm font-medium text-text-primary rounded-md hover:bg-surface-secondary transition-colors">
                Cancel
            </a>
            <button type="submit"
                    :disabled="submitting"
                    :class="submitting ? 'opacity-60 cursor-not-allowed' : 'hover:bg-accent-dark'"
                    class="px-6 py-2 bg-accent text-accent-foreground text-sm font-medium rounded-md transition-colors">
                <span x-show="!submitting">Save Changes</span>
                <span x-show="submitting">Saving…</span>
            </button>
        </div>

    </form>
</div>
@endsection
