<?php

use App\Models\Tenant\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Seeds all roles and permissions — mirrors TenantProvisioningService::seedPermissions().
 */
function seedRolesAndPermissions(): void
{
    $all = [
        'students.view', 'students.create', 'students.edit', 'students.delete',
        'staff.view', 'staff.create', 'staff.edit', 'staff.delete',
        'attendance.view', 'attendance.edit',
        'timetable.view', 'timetable.edit',
        'exams.view', 'exams.create', 'exams.edit', 'exams.delete',
        'fees.view', 'fees.create', 'fees.edit',
        'announcements.view', 'announcements.create', 'announcements.edit', 'announcements.delete',
        'reports.view',
        'settings.manage',
    ];

    foreach ($all as $name) {
        Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
    }

    $rolePermissions = [
        'school_admin' => $all,
        'teacher'      => ['students.view', 'attendance.view', 'attendance.edit', 'timetable.view', 'timetable.edit', 'exams.view', 'exams.create', 'exams.edit', 'announcements.view', 'announcements.create', 'announcements.edit'],
        'accountant'   => ['fees.view', 'fees.create', 'fees.edit', 'reports.view', 'announcements.view'],
        'student'      => ['attendance.view', 'exams.view', 'fees.view', 'announcements.view'],
        'parent'       => ['attendance.view', 'exams.view', 'fees.view', 'announcements.view'],
    ];

    foreach ($rolePermissions as $roleName => $permissions) {
        $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        $role->givePermissionTo($permissions);
    }
}

function makeUser(string $role): User
{
    $user = User::create([
        'name'     => ucfirst($role),
        'email'    => $role . '_' . uniqid() . '@test.test',
        'password' => bcrypt('password'),
        'role'     => $role,
    ]);
    $user->assignRole($role);
    return $user;
}

beforeEach(function (): void {
    seedRolesAndPermissions();
});

// --- school_admin: can access everything ---

test('school_admin can access students index', function (): void {
    $admin = makeUser('school_admin');
    expect($admin->can('students.view'))->toBeTrue();
});

test('school_admin can access settings', function (): void {
    $admin = makeUser('school_admin');
    expect($admin->can('settings.manage'))->toBeTrue();
});

// --- teacher: limited access ---

test('teacher can view students', function (): void {
    $teacher = makeUser('teacher');
    expect($teacher->can('students.view'))->toBeTrue();
});

test('teacher cannot delete students', function (): void {
    $teacher = makeUser('teacher');
    expect($teacher->can('students.delete'))->toBeFalse();
});

test('teacher cannot access settings', function (): void {
    $teacher = makeUser('teacher');
    expect($teacher->can('settings.manage'))->toBeFalse();
});

test('teacher cannot manage fees', function (): void {
    $teacher = makeUser('teacher');
    expect($teacher->can('fees.view'))->toBeFalse();
});

// --- accountant: fee focused ---

test('accountant can view and record fees', function (): void {
    $accountant = makeUser('accountant');
    expect($accountant->can('fees.view'))->toBeTrue()
        ->and($accountant->can('fees.create'))->toBeTrue();
});

test('accountant cannot manage students', function (): void {
    $accountant = makeUser('accountant');
    expect($accountant->can('students.view'))->toBeFalse()
        ->and($accountant->can('students.create'))->toBeFalse();
});

test('accountant cannot access settings', function (): void {
    $accountant = makeUser('accountant');
    expect($accountant->can('settings.manage'))->toBeFalse();
});

// --- student role: read-only limited access ---

test('student role can view exams', function (): void {
    $student = makeUser('student');
    expect($student->can('exams.view'))->toBeTrue();
});

test('student role cannot create exams', function (): void {
    $student = makeUser('student');
    expect($student->can('exams.create'))->toBeFalse();
});

test('student role cannot view staff', function (): void {
    $student = makeUser('student');
    expect($student->can('staff.view'))->toBeFalse();
});

test('student role cannot access settings', function (): void {
    $student = makeUser('student');
    expect($student->can('settings.manage'))->toBeFalse();
});

// --- parent role mirrors student ---

test('parent role has same permissions as student role', function (): void {
    $parent  = makeUser('parent');
    $student = makeUser('student');

    $parentPerms  = $parent->getAllPermissions()->pluck('name')->sort()->values();
    $studentPerms = $student->getAllPermissions()->pluck('name')->sort()->values();

    expect($parentPerms->toArray())->toBe($studentPerms->toArray());
});
