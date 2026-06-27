<?php

use App\Models\Tenant\Staff;
use App\Models\Tenant\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    Role::firstOrCreate(['name' => 'school_admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'teacher',      'guard_name' => 'web']);
});

test('staff can be created with a linked user account', function (): void {
    $user = User::create([
        'name'     => 'Jane Teacher',
        'email'    => 'jane@test.test',
        'password' => bcrypt('password'),
        'role'     => 'teacher',
    ]);

    $staff = Staff::create([
        'user_id'    => $user->id,
        'full_name'  => 'Jane Teacher',
        'role_title' => 'Class Teacher',
        'status'     => 'active',
    ]);

    expect($staff)->toBeInstanceOf(Staff::class)
        ->and($staff->full_name)->toBe('Jane Teacher')
        ->and($staff->user_id)->toBe($user->id);
});

test('staff user() relationship resolves the linked user', function (): void {
    $user = User::create([
        'name'     => 'Bob Staff',
        'email'    => 'bob@test.test',
        'password' => bcrypt('password'),
        'role'     => 'teacher',
    ]);

    $staff = Staff::create([
        'user_id'    => $user->id,
        'full_name'  => 'Bob Staff',
        'role_title' => 'HOD',
        'status'     => 'active',
    ]);

    expect($staff->user)->toBeInstanceOf(User::class)
        ->and($staff->user->email)->toBe('bob@test.test');
});

test('staff can be updated', function (): void {
    $user  = User::create(['name' => 'Old', 'email' => 'old@test.test', 'password' => bcrypt('pw'), 'role' => 'teacher']);
    $staff = Staff::create(['user_id' => $user->id, 'full_name' => 'Old Name', 'role_title' => 'Teacher', 'status' => 'active']);

    $staff->update(['full_name' => 'New Name', 'role_title' => 'Deputy Head']);

    $fresh = Staff::find($staff->id);
    expect($fresh->full_name)->toBe('New Name')
        ->and($fresh->role_title)->toBe('Deputy Head');
});

test('staff can be soft-deleted', function (): void {
    $user  = User::create(['name' => 'Del', 'email' => 'del@test.test', 'password' => bcrypt('pw'), 'role' => 'teacher']);
    $staff = Staff::create(['user_id' => $user->id, 'full_name' => 'Del Staff', 'role_title' => 'Teacher', 'status' => 'active']);

    $id = $staff->id;
    $staff->delete();

    expect(Staff::find($id))->toBeNull()
        ->and(Staff::withTrashed()->find($id))->not->toBeNull();
});

test('soft-deleted staff can be restored', function (): void {
    $user  = User::create(['name' => 'Res', 'email' => 'res@test.test', 'password' => bcrypt('pw'), 'role' => 'teacher']);
    $staff = Staff::create(['user_id' => $user->id, 'full_name' => 'Restore Staff', 'role_title' => 'Teacher', 'status' => 'active']);

    $id = $staff->id;
    $staff->delete();

    Staff::withTrashed()->find($id)->restore();

    expect(Staff::find($id))->not->toBeNull()
        ->and(Staff::find($id)->full_name)->toBe('Restore Staff');
});

test('staff status can be set to inactive', function (): void {
    $user  = User::create(['name' => 'Inact', 'email' => 'inact@test.test', 'password' => bcrypt('pw'), 'role' => 'teacher']);
    $staff = Staff::create(['user_id' => $user->id, 'full_name' => 'Inactive', 'role_title' => 'Teacher', 'status' => 'active']);

    $staff->update(['status' => 'inactive']);

    expect(Staff::find($staff->id)->status)->toBe('inactive');
});
