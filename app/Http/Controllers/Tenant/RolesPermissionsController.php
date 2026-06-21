<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

final class RolesPermissionsController extends Controller
{
    private const FIXED_ROLES = ['school_admin', 'teacher', 'accountant', 'student', 'parent'];

    private const PERMISSION_MODULES = [
        'Students'      => ['students.view', 'students.create', 'students.edit', 'students.delete'],
        'Staff'         => ['staff.view', 'staff.create', 'staff.edit', 'staff.delete'],
        'Attendance'    => ['attendance.view', 'attendance.edit'],
        'Timetable'     => ['timetable.view', 'timetable.edit'],
        'Exams'         => ['exams.view', 'exams.create', 'exams.edit', 'exams.delete'],
        'Fees'          => ['fees.view', 'fees.create', 'fees.edit'],
        'Announcements' => ['announcements.view', 'announcements.create', 'announcements.edit', 'announcements.delete'],
        'Reports'       => ['reports.view'],
    ];

    public function index(): View
    {
        $roles = Role::with('permissions')->orderBy('name')->get();

        return view('tenant.settings.roles', [
            'roles'             => $roles,
            'fixedRoles'        => self::FIXED_ROLES,
            'permissionModules' => self::PERMISSION_MODULES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'          => ['required', 'string', 'max:50', 'unique:roles,name', 'regex:/^[a-z][a-z0-9_]*$/'],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ], [
            'name.regex' => 'Role name must start with a letter and contain only lowercase letters, numbers, and underscores.',
        ]);

        try {
            $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);
            $role->givePermissionTo($request->input('permissions', []));

            return redirect(request()->getSchemeAndHttpHost() . '/settings/roles')
                ->with('success', "Role '{$request->name}' created successfully.");
        } catch (\Throwable $e) {
            Log::error('[roles.store] ' . $e->getMessage());

            return back()->withInput()->with('error', 'Could not create role. Please try again.');
        }
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        if (in_array($role->name, self::FIXED_ROLES, true)) {
            return back()->with('error', 'Fixed roles cannot be modified.');
        }

        $request->validate([
            'name'          => ['required', 'string', 'max:50', Rule::unique('roles', 'name')->ignore($role->id), 'regex:/^[a-z][a-z0-9_]*$/'],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ], [
            'name.regex' => 'Role name must start with a letter and contain only lowercase letters, numbers, and underscores.',
        ]);

        try {
            $role->update(['name' => $request->name]);
            $role->syncPermissions($request->input('permissions', []));

            return redirect(request()->getSchemeAndHttpHost() . '/settings/roles')
                ->with('success', 'Role updated successfully.');
        } catch (\Throwable $e) {
            Log::error('[roles.update] ' . $e->getMessage());

            return back()->withInput()->with('error', 'Could not update role. Please try again.');
        }
    }

    public function destroy(Role $role): RedirectResponse
    {
        if (in_array($role->name, self::FIXED_ROLES, true)) {
            return back()->with('error', 'Fixed roles cannot be deleted.');
        }

        try {
            $role->delete();

            return redirect(request()->getSchemeAndHttpHost() . '/settings/roles')
                ->with('success', 'Role deleted successfully.');
        } catch (\Throwable $e) {
            Log::error('[roles.destroy] ' . $e->getMessage());

            return back()->with('error', 'Could not delete role. Please try again.');
        }
    }
}
