<?php

use App\Models\Tenant\AcademicYear;
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\Staff;
use App\Models\Tenant\Subject;
use App\Models\Tenant\SubjectTeacherAssignment;
use App\Models\Tenant\User;
use Illuminate\Database\QueryException;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'web']);

    $this->class   = SchoolClass::create(['name' => 'Class A', 'order' => 1]);
    $this->subject = Subject::create(['name' => 'Science', 'code' => 'SCI']);

    $user        = User::create(['name' => 'Sci Teacher', 'email' => 'sci@t.test', 'password' => bcrypt('pw'), 'role' => 'teacher']);
    $this->staff = Staff::create(['user_id' => $user->id, 'full_name' => 'Science Teacher', 'role_title' => 'Teacher', 'status' => 'active']);
});

test('subject-teacher assignment can be created', function (): void {
    $assignment = SubjectTeacherAssignment::create([
        'staff_id'   => $this->staff->id,
        'subject_id' => $this->subject->id,
        'class_id'   => $this->class->id,
        'section_id' => null,
    ]);

    expect($assignment)->toBeInstanceOf(SubjectTeacherAssignment::class)
        ->and($assignment->staff_id)->toBe($this->staff->id)
        ->and($assignment->subject_id)->toBe($this->subject->id);
});

test('assignment relationships resolve correctly', function (): void {
    $assignment = SubjectTeacherAssignment::create([
        'staff_id'   => $this->staff->id,
        'subject_id' => $this->subject->id,
        'class_id'   => $this->class->id,
        'section_id' => null,
    ]);

    expect($assignment->staff->full_name)->toBe('Science Teacher')
        ->and($assignment->subject->name)->toBe('Science')
        ->and($assignment->schoolClass->name)->toBe('Class A');
});

test('teacher is scoped to only their assigned classes for marks entry', function (): void {
    SubjectTeacherAssignment::create([
        'staff_id'   => $this->staff->id,
        'subject_id' => $this->subject->id,
        'class_id'   => $this->class->id,
        'section_id' => null,
    ]);

    $class2 = SchoolClass::create(['name' => 'Class B', 'order' => 2]);

    $hasAccessToClass1 = SubjectTeacherAssignment::where('staff_id', $this->staff->id)
        ->where('class_id', $this->class->id)
        ->where('subject_id', $this->subject->id)
        ->exists();

    $hasAccessToClass2 = SubjectTeacherAssignment::where('staff_id', $this->staff->id)
        ->where('class_id', $class2->id)
        ->where('subject_id', $this->subject->id)
        ->exists();

    expect($hasAccessToClass1)->toBeTrue()
        ->and($hasAccessToClass2)->toBeFalse();
});

test('teacher only sees assigned classes on attendance page', function (): void {
    SubjectTeacherAssignment::create([
        'staff_id'   => $this->staff->id,
        'subject_id' => $this->subject->id,
        'class_id'   => $this->class->id,
        'section_id' => null,
    ]);

    $class2 = SchoolClass::create(['name' => 'Class C', 'order' => 3]);

    $assignedClassIds = SubjectTeacherAssignment::where('staff_id', $this->staff->id)
        ->pluck('class_id')
        ->unique()
        ->values();

    expect($assignedClassIds)->toContain($this->class->id)
        ->and($assignedClassIds)->not->toContain($class2->id);
});

test('assignment can be deleted', function (): void {
    $assignment = SubjectTeacherAssignment::create([
        'staff_id'   => $this->staff->id,
        'subject_id' => $this->subject->id,
        'class_id'   => $this->class->id,
        'section_id' => null,
    ]);

    $id = $assignment->id;
    $assignment->delete();

    expect(SubjectTeacherAssignment::find($id))->toBeNull();
});
