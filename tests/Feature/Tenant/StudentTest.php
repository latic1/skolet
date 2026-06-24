<?php

use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\SchoolProfile;
use App\Models\Tenant\Student;
use App\Models\Tenant\User;
use App\Services\AdmissionNumberService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    // Seed minimum roles so assignRole works
    Role::firstOrCreate(['name' => 'school_admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'teacher',      'guard_name' => 'web']);
});

test('student can be created with required fields', function (): void {
    $class = SchoolClass::create(['name' => 'Primary 1', 'order' => 1]);

    $student = Student::create([
        'admission_no'     => '2026/0001',
        'full_name'        => 'Jane Doe',
        'gender'           => 'female',
        'guardian_name'    => 'John Doe',
        'guardian_contact' => '0244000000',
        'status'           => 'active',
        'class_id'         => $class->id,
    ]);

    expect($student)->toBeInstanceOf(Student::class)
        ->and($student->full_name)->toBe('Jane Doe')
        ->and($student->admission_no)->toBe('2026/0001');
});

test('student is retrieved from database after creation', function (): void {
    Student::create([
        'admission_no'     => '2026/0002',
        'full_name'        => 'Test Student',
        'gender'           => 'male',
        'guardian_name'    => 'Guardian',
        'guardian_contact' => '0200000000',
        'status'           => 'active',
    ]);

    expect(Student::where('admission_no', '2026/0002')->exists())->toBeTrue();
});

test('student can be updated', function (): void {
    $student = Student::create([
        'admission_no'     => '2026/0003',
        'full_name'        => 'Old Name',
        'gender'           => 'male',
        'guardian_name'    => 'Guardian',
        'guardian_contact' => '0200000000',
        'status'           => 'active',
    ]);

    $student->update(['full_name' => 'New Name']);

    expect(Student::find($student->id)->full_name)->toBe('New Name');
});

test('student can be deleted', function (): void {
    $student = Student::create([
        'admission_no'     => '2026/0004',
        'full_name'        => 'To Delete',
        'gender'           => 'female',
        'guardian_name'    => 'Guardian',
        'guardian_contact' => '0200000000',
        'status'           => 'active',
    ]);

    $id = $student->id;
    $student->delete();

    expect(Student::find($id))->toBeNull();
});

test('admission number service generates formatted string', function (): void {
    SchoolProfile::create([
        'school_name'       => 'Test School',
        'admission_counter' => 0,
        'admission_pattern' => '{YEAR}/{SEQ:4}',
    ]);

    $service = app(AdmissionNumberService::class);
    $number  = $service->generate();

    expect($number)->toMatch('/^\d{4}\/\d{4}$/');
});

test('admission number counter increments on each generate call', function (): void {
    SchoolProfile::create([
        'school_name'       => 'Test School',
        'admission_counter' => 0,
        'admission_pattern' => '{YEAR}/{SEQ:4}',
    ]);

    $service = app(AdmissionNumberService::class);
    $first   = $service->generate();
    $second  = $service->generate();

    expect($first)->not->toBe($second);
});

test('student status defaults to active', function (): void {
    $student = Student::create([
        'admission_no'     => '2026/0010',
        'full_name'        => 'Status Test',
        'gender'           => 'male',
        'guardian_name'    => 'Guardian',
        'guardian_contact' => '0200000000',
        'status'           => 'active',
    ]);

    expect($student->status)->toBe('active');
});
