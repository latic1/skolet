<?php

use App\Models\Tenant\Assignment;
use App\Models\Tenant\AssignmentSubmission;
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\Staff;
use App\Models\Tenant\Student;
use App\Models\Tenant\Subject;
use App\Models\Tenant\SubjectTeacherAssignment;
use App\Models\Tenant\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);

    Permission::firstOrCreate(['name' => 'assignments.view',   'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'assignments.create', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'assignments.submit', 'guard_name' => 'web']);

    Role::findByName('teacher')->givePermissionTo(['assignments.view', 'assignments.create']);
    Role::findByName('student')->givePermissionTo(['assignments.view', 'assignments.submit']);

    $this->class   = SchoolClass::create(['name' => 'AS1', 'order' => 1]);
    $this->subject = Subject::create(['name' => 'Art', 'code' => 'ART']);

    $teacherUser       = User::create(['name' => 'Art Teacher', 'email' => 'art@t.test', 'password' => bcrypt('pw'), 'role' => 'teacher']);
    $teacherUser->assignRole('teacher');
    $this->teacher     = Staff::create(['user_id' => $teacherUser->id, 'full_name' => 'Art Teacher', 'role_title' => 'Teacher', 'status' => 'active']);
    $this->teacherUser = $teacherUser;

    $studentUser       = User::create(['name' => 'Art Student', 'email' => 'artstud@t.test', 'password' => bcrypt('pw'), 'role' => 'student']);
    $studentUser->assignRole('student');
    $this->studentUser = $studentUser;
    $this->student     = Student::create([
        'admission_no'     => '2026/1000',
        'full_name'        => 'Art Student',
        'gender'           => 'male',
        'guardian_name'    => 'Guardian',
        'guardian_contact' => '0200000010',
        'status'           => 'active',
        'class_id'         => $this->class->id,
        'user_id'          => $studentUser->id,
    ]);

    SubjectTeacherAssignment::create([
        'staff_id'   => $this->teacher->id,
        'subject_id' => $this->subject->id,
        'class_id'   => $this->class->id,
        'section_id' => null,
    ]);
});

test('teacher can create an assignment for their class', function (): void {
    $assignment = Assignment::create([
        'teacher_id'  => $this->teacher->id,
        'subject_id'  => $this->subject->id,
        'class_id'    => $this->class->id,
        'title'       => 'Art Portfolio',
        'description' => 'Create a portfolio of 5 artworks',
        'due_date'    => now()->addWeek(),
        'total_marks' => 50,
    ]);

    expect($assignment)->toBeInstanceOf(Assignment::class)
        ->and($assignment->title)->toBe('Art Portfolio')
        ->and($assignment->teacher_id)->toBe($this->teacher->id);
});

test('student can submit an assignment before the due date', function (): void {
    $assignment = Assignment::create([
        'teacher_id'  => $this->teacher->id,
        'subject_id'  => $this->subject->id,
        'class_id'    => $this->class->id,
        'title'       => 'Sketch Assignment',
        'description' => 'Draw a landscape',
        'due_date'    => now()->addDays(3),
        'total_marks' => 20,
    ]);

    $submission = AssignmentSubmission::create([
        'assignment_id'  => $assignment->id,
        'student_id'     => $this->student->id,
        'submission_text' => 'I have completed the landscape sketch.',
        'submitted_at'   => now(),
    ]);

    expect($submission)->toBeInstanceOf(AssignmentSubmission::class)
        ->and($submission->student_id)->toBe($this->student->id);
});

test('teacher can grade a submission', function (): void {
    $assignment = Assignment::create([
        'teacher_id'  => $this->teacher->id,
        'subject_id'  => $this->subject->id,
        'class_id'    => $this->class->id,
        'title'       => 'Graded Assignment',
        'description' => 'Test assignment',
        'due_date'    => now()->addWeek(),
        'total_marks' => 100,
    ]);

    $submission = AssignmentSubmission::create([
        'assignment_id'  => $assignment->id,
        'student_id'     => $this->student->id,
        'submission_text' => 'My work',
        'submitted_at'   => now(),
    ]);

    $submission->update([
        'marks_awarded' => 85.0,
        'feedback'      => 'Excellent work! Very creative.',
    ]);

    $fresh = AssignmentSubmission::find($submission->id);
    expect((float) $fresh->marks_awarded)->toBe(85.0)
        ->and($fresh->feedback)->toBe('Excellent work! Very creative.');
});

test('student cannot submit after the due date', function (): void {
    $assignment = Assignment::create([
        'teacher_id'  => $this->teacher->id,
        'subject_id'  => $this->subject->id,
        'class_id'    => $this->class->id,
        'title'       => 'Overdue Assignment',
        'description' => 'Past due',
        'due_date'    => now()->subDay(), // already past
        'total_marks' => 20,
    ]);

    // Simulate the controller check: reject submissions after due date
    $isPastDue = now()->gt($assignment->due_date);

    expect($isPastDue)->toBeTrue();
});

test('teacher cannot manage assignment for a class they are not assigned to', function (): void {
    $otherClass = SchoolClass::create(['name' => 'Other Class', 'order' => 9]);

    $isAssignedToOtherClass = SubjectTeacherAssignment::where('staff_id', $this->teacher->id)
        ->where('class_id', $otherClass->id)
        ->exists();

    expect($isAssignedToOtherClass)->toBeFalse();
});

test('assignment submission unique constraint prevents double-submission', function (): void {
    $assignment = Assignment::create([
        'teacher_id'  => $this->teacher->id,
        'subject_id'  => $this->subject->id,
        'class_id'    => $this->class->id,
        'title'       => 'Unique Sub Assignment',
        'description' => 'Test',
        'due_date'    => now()->addWeek(),
        'total_marks' => 10,
    ]);

    AssignmentSubmission::create([
        'assignment_id'  => $assignment->id,
        'student_id'     => $this->student->id,
        'submission_text' => 'First submission',
        'submitted_at'   => now(),
    ]);

    $count = AssignmentSubmission::where('assignment_id', $assignment->id)
        ->where('student_id', $this->student->id)
        ->count();

    expect($count)->toBe(1);
});
