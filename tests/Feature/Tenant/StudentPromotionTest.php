<?php

use App\Models\Tenant\AcademicYear;
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\Student;
use App\Models\Tenant\StudentClassHistory;
use App\Services\StudentPromotionService;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    Role::firstOrCreate(['name' => 'school_admin', 'guard_name' => 'web']);

    $this->class1 = SchoolClass::create(['name' => 'Grade 1', 'order' => 1]);
    $this->class2 = SchoolClass::create(['name' => 'Grade 2', 'order' => 2]);
    $this->class3 = SchoolClass::create(['name' => 'Grade 3', 'order' => 3]);

    $this->year = AcademicYear::create([
        'name'       => '2025/2026',
        'start_date' => '2025-09-01',
        'end_date'   => '2026-07-31',
        'is_current' => true,
    ]);

    $this->student1 = Student::create([
        'admission_no'     => '2026/1200',
        'full_name'        => 'Promo Student 1',
        'gender'           => 'male',
        'guardian_name'    => 'Guardian',
        'guardian_contact' => '0200000012',
        'status'           => 'active',
        'class_id'         => $this->class1->id,
    ]);

    $this->student2 = Student::create([
        'admission_no'     => '2026/1201',
        'full_name'        => 'Promo Student 2',
        'gender'           => 'female',
        'guardian_name'    => 'Guardian',
        'guardian_contact' => '0200000013',
        'status'           => 'active',
        'class_id'         => $this->class2->id,
    ]);

    $this->service = app(StudentPromotionService::class);
});

test('promoting a student updates class_id to the next class in order', function (): void {
    $result = $this->service->promote(
        [$this->student1->id => 'promoted'],
        $this->year,
    );

    expect($result['promoted'])->toBe(1)
        ->and($result['errors'])->toBeEmpty();

    $this->student1->refresh();
    expect($this->student1->class_id)->toBe($this->class2->id);
});

test('retaining a student leaves class_id unchanged', function (): void {
    $originalClassId = $this->student1->class_id;

    $result = $this->service->promote(
        [$this->student1->id => 'retained'],
        $this->year,
    );

    expect($result['retained'])->toBe(1);

    $this->student1->refresh();
    expect($this->student1->class_id)->toBe($originalClassId);
});

test('graduating a student sets status to graduated', function (): void {
    $result = $this->service->promote(
        [$this->student2->id => 'graduated'],
        $this->year,
    );

    expect($result['graduated'])->toBe(1);

    $this->student2->refresh();
    expect($this->student2->status)->toBe('graduated');
});

test('promotion creates a student_class_history row for each student', function (): void {
    $this->service->promote(
        [
            $this->student1->id => 'promoted',
            $this->student2->id => 'retained',
        ],
        $this->year,
    );

    expect(StudentClassHistory::where('student_id', $this->student1->id)->count())->toBe(1)
        ->and(StudentClassHistory::where('student_id', $this->student2->id)->count())->toBe(1);
});

test('student_class_history records correct outcome', function (): void {
    $this->service->promote(
        [$this->student1->id => 'promoted'],
        $this->year,
    );

    $history = StudentClassHistory::where('student_id', $this->student1->id)->first();

    expect($history->outcome)->toBe('promoted')
        ->and($history->academic_year_id)->toBe($this->year->id)
        ->and($history->class_id)->toBe($this->class1->id); // original class before promotion
});

test('attempting to promote student in highest class returns an error', function (): void {
    $topStudent = Student::create([
        'admission_no'     => '2026/1202',
        'full_name'        => 'Top Class Student',
        'gender'           => 'male',
        'guardian_name'    => 'Guardian',
        'guardian_contact' => '0200000014',
        'status'           => 'active',
        'class_id'         => $this->class3->id,
    ]);

    $result = $this->service->promote(
        [$topStudent->id => 'promoted'],
        $this->year,
    );

    expect($result['errors'])->not->toBeEmpty()
        ->and($result['promoted'])->toBe(0);
});
