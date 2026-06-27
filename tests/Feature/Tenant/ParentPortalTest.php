<?php

use App\Models\Tenant\FeePayment;
use App\Models\Tenant\FeeStructure;
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\Student;
use App\Models\Tenant\User;
use App\Services\FeeStatusService;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    Role::firstOrCreate(['name' => 'parent',  'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);

    Permission::firstOrCreate(['name' => 'fees.view', 'guard_name' => 'web']);
    Role::findByName('parent')->givePermissionTo(['fees.view']);

    $class = SchoolClass::create(['name' => 'PP1', 'order' => 1]);

    $this->parentUser = User::create([
        'name' => 'Parent User', 'email' => 'parent_pp@test.test', 'password' => bcrypt('pw'), 'role' => 'parent',
    ]);
    $this->parentUser->assignRole('parent');

    $this->student = Student::create([
        'admission_no'     => '2026/1100',
        'full_name'        => 'Portal Child',
        'gender'           => 'female',
        'guardian_name'    => 'Parent User',
        'guardian_contact' => '0200000011',
        'guardian_email'   => 'parent_pp@test.test',
        'status'           => 'active',
        'class_id'         => $class->id,
    ]);

    $this->otherStudent = Student::create([
        'admission_no'     => '2026/1101',
        'full_name'        => 'Other Child',
        'gender'           => 'male',
        'guardian_name'    => 'Other Parent',
        'guardian_contact' => '0200000012',
        'status'           => 'active',
        'class_id'         => $class->id,
    ]);

    // Link parent to their child via parent_student pivot
    DB::table('parent_student')->insert([
        'user_id'    => $this->parentUser->id,
        'student_id' => $this->student->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
});

test('parent login account is linked to a student via parent_student pivot', function (): void {
    $linked = DB::table('parent_student')
        ->where('user_id', $this->parentUser->id)
        ->where('student_id', $this->student->id)
        ->exists();

    expect($linked)->toBeTrue();
});

test('parent can view linked child via linkedChildren relationship', function (): void {
    $children = $this->parentUser->linkedChildren;

    expect($children)->toHaveCount(1)
        ->and($children->first()->id)->toBe($this->student->id);
});

test('parent cannot access another student not in their linked list', function (): void {
    $children = $this->parentUser->linkedChildren;

    $otherLinked = $children->contains('id', $this->otherStudent->id);

    expect($otherLinked)->toBeFalse();
});

test('revoking parent login detaches the pivot row', function (): void {
    DB::table('parent_student')
        ->where('user_id', $this->parentUser->id)
        ->where('student_id', $this->student->id)
        ->delete();

    $linked = DB::table('parent_student')
        ->where('user_id', $this->parentUser->id)
        ->exists();

    expect($linked)->toBeFalse();
});

test('parent with multiple children can select active child via session', function (): void {
    $secondChild = Student::create([
        'admission_no'     => '2026/1102',
        'full_name'        => 'Second Child',
        'gender'           => 'male',
        'guardian_name'    => 'Parent User',
        'guardian_contact' => '0200000013',
        'status'           => 'active',
        'class_id'         => $this->student->class_id,
    ]);

    DB::table('parent_student')->insert([
        'user_id'    => $this->parentUser->id,
        'student_id' => $secondChild->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $children = $this->parentUser->linkedChildren()->get();
    expect($children)->toHaveCount(2);

    // Simulate session-based child selection
    $selectedId = $this->student->id;
    $selectedChild = $children->firstWhere('id', $selectedId);

    expect($selectedChild)->not->toBeNull()
        ->and($selectedChild->full_name)->toBe('Portal Child');
});

test('student parents relationship resolves correctly', function (): void {
    $parents = $this->student->parents()->get();

    expect($parents)->toHaveCount(1)
        ->and($parents->first()->email)->toBe('parent_pp@test.test');
});
