<?php

use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\Section;
use App\Models\Tenant\Staff;
use App\Models\Tenant\Subject;
use App\Models\Tenant\Timetable;
use App\Models\Tenant\User;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'web']);

    $this->class   = SchoolClass::create(['name' => 'Grade 1', 'order' => 1]);
    $this->class2  = SchoolClass::create(['name' => 'Grade 2', 'order' => 2]);
    $this->subject = Subject::create(['name' => 'English', 'code' => 'ENG']);

    $user         = User::create(['name' => 'T1', 'email' => 't1@t.test', 'password' => bcrypt('pw'), 'role' => 'teacher']);
    $this->staff  = Staff::create(['user_id' => $user->id, 'full_name' => 'Teacher One', 'role_title' => 'Teacher', 'status' => 'active']);
});

test('timetable slot can be created for class, period, subject and teacher', function (): void {
    $slot = Timetable::create([
        'class_id'   => $this->class->id,
        'section_id' => null,
        'subject_id' => $this->subject->id,
        'teacher_id' => $this->staff->id,
        'day'        => 'Monday',
        'period'     => 1,
    ]);

    expect($slot)->toBeInstanceOf(Timetable::class)
        ->and($slot->day)->toBe('Monday')
        ->and($slot->period)->toBe(1);
});

test('timetable slot relationships resolve correctly', function (): void {
    $slot = Timetable::create([
        'class_id'   => $this->class->id,
        'section_id' => null,
        'subject_id' => $this->subject->id,
        'teacher_id' => $this->staff->id,
        'day'        => 'Tuesday',
        'period'     => 2,
    ]);

    expect($slot->schoolClass->name)->toBe('Grade 1')
        ->and($slot->subject->name)->toBe('English')
        ->and($slot->teacher->full_name)->toBe('Teacher One');
});

test('timetable conflict is detected when same teacher is assigned two classes in the same period', function (): void {
    // Assign teacher to Grade 1 Monday Period 1
    Timetable::create([
        'class_id'   => $this->class->id,
        'section_id' => null,
        'subject_id' => $this->subject->id,
        'teacher_id' => $this->staff->id,
        'day'        => 'Monday',
        'period'     => 1,
    ]);

    // Detect conflict: same teacher, same day+period, different class
    $conflict = Timetable::where('teacher_id', $this->staff->id)
        ->where('day', 'Monday')
        ->where('period', 1)
        ->where('class_id', '!=', $this->class2->id)
        ->exists();

    expect($conflict)->toBeTrue();
});

test('timetable slots are scoped per class', function (): void {
    Timetable::create([
        'class_id'   => $this->class->id,
        'section_id' => null,
        'subject_id' => $this->subject->id,
        'teacher_id' => $this->staff->id,
        'day'        => 'Wednesday',
        'period'     => 3,
    ]);

    $slotsForClass1 = Timetable::where('class_id', $this->class->id)->count();
    $slotsForClass2 = Timetable::where('class_id', $this->class2->id)->count();

    expect($slotsForClass1)->toBe(1)
        ->and($slotsForClass2)->toBe(0);
});

test('timetable slot can be scoped by section', function (): void {
    $section = Section::create(['class_id' => $this->class->id, 'name' => 'Section A']);

    $slot = Timetable::create([
        'class_id'   => $this->class->id,
        'section_id' => $section->id,
        'subject_id' => $this->subject->id,
        'teacher_id' => $this->staff->id,
        'day'        => 'Thursday',
        'period'     => 4,
    ]);

    expect(Timetable::where('section_id', $section->id)->count())->toBe(1)
        ->and($slot->section->name)->toBe('Section A');
});

test('timetable slot can be deleted', function (): void {
    $slot = Timetable::create([
        'class_id'   => $this->class->id,
        'section_id' => null,
        'subject_id' => $this->subject->id,
        'teacher_id' => $this->staff->id,
        'day'        => 'Friday',
        'period'     => 5,
    ]);

    $id = $slot->id;
    $slot->delete();

    expect(Timetable::find($id))->toBeNull();
});
