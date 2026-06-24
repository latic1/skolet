<?php

use App\Models\Tenant\Attendance;
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\Student;
use App\Models\Tenant\User;
use App\Services\AttendanceReportService;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    Role::firstOrCreate(['name' => 'school_admin', 'guard_name' => 'web']);

    $class = SchoolClass::create(['name' => 'Class A', 'order' => 1]);

    $this->student = Student::create([
        'admission_no'     => '2026/0100',
        'full_name'        => 'Attend Test',
        'gender'           => 'male',
        'guardian_name'    => 'Parent',
        'guardian_contact' => '0200000001',
        'status'           => 'active',
        'class_id'         => $class->id,
    ]);

    $this->classId = $class->id;
    $this->date    = now()->toDateString();
});

test('attendance record is created for a student', function (): void {
    Attendance::create([
        'student_id' => $this->student->id,
        'date'       => $this->date,
        'status'     => 'present',
    ]);

    expect(
        Attendance::where('student_id', $this->student->id)
            ->where('date', $this->date)
            ->exists()
    )->toBeTrue();
});

test('attendance can be marked as absent', function (): void {
    Attendance::create([
        'student_id' => $this->student->id,
        'date'       => $this->date,
        'status'     => 'absent',
    ]);

    $record = Attendance::where('student_id', $this->student->id)
        ->where('date', $this->date)
        ->first();

    expect($record->status)->toBe('absent');
});

test('re-marking attendance is idempotent via updateOrCreate', function (): void {
    // First mark: present
    Attendance::updateOrCreate(
        ['student_id' => $this->student->id, 'date' => $this->date],
        ['status' => 'present']
    );

    // Re-mark: absent
    Attendance::updateOrCreate(
        ['student_id' => $this->student->id, 'date' => $this->date],
        ['status' => 'absent']
    );

    $records = Attendance::where('student_id', $this->student->id)
        ->where('date', $this->date)
        ->get();

    // Only one record should exist, with the latest status
    expect($records)->toHaveCount(1)
        ->and($records->first()->status)->toBe('absent');
});

test('attendance report service builds summary for a class', function (): void {
    Attendance::create(['student_id' => $this->student->id, 'date' => now()->subDays(2)->toDateString(), 'status' => 'present']);
    Attendance::create(['student_id' => $this->student->id, 'date' => now()->subDays(1)->toDateString(), 'status' => 'absent']);
    Attendance::create(['student_id' => $this->student->id, 'date' => $this->date, 'status' => 'present']);

    $service = app(AttendanceReportService::class);
    $result  = $service->build(
        classId:   $this->classId,
        sectionId: null,
        dateFrom:  now()->subDays(3),
        dateTo:    now()
    );

    expect($result['success'])->toBeTrue()
        ->and($result['data'])->not->toBeEmpty();

    $studentRow = collect($result['data'])->firstWhere('student_id', $this->student->id);
    expect($studentRow)->not->toBeNull()
        ->and($studentRow['present'])->toBe(2)
        ->and($studentRow['absent'])->toBe(1);
});

test('attendance report returns empty data when no records exist', function (): void {
    $service = app(AttendanceReportService::class);
    $result  = $service->build(
        classId:   $this->classId,
        sectionId: null,
        dateFrom:  now()->subMonth(),
        dateTo:    now()
    );

    expect($result['success'])->toBeTrue()
        ->and($result['data'])->toBeArray();
});
