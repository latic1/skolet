<?php

use App\Models\Tenant\Exam;
use App\Models\Tenant\ExamResult;
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\Student;
use App\Models\Tenant\Subject;
use App\Models\Tenant\User;
use App\Services\ReportCardService;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    Role::firstOrCreate(['name' => 'school_admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'student',      'guard_name' => 'web']);

    $class = SchoolClass::create(['name' => 'Class B', 'order' => 1]);

    $this->student = Student::create([
        'admission_no'     => '2026/0200',
        'full_name'        => 'Exam Tester',
        'gender'           => 'male',
        'guardian_name'    => 'Parent',
        'guardian_contact' => '0200000002',
        'status'           => 'active',
        'class_id'         => $class->id,
    ]);

    $this->subject = Subject::create(['name' => 'Mathematics', 'code' => 'MATH']);

    $this->exam = Exam::create([
        'name'         => 'Mid-Term Test',
        'is_published' => false,
        'start_date'   => now()->toDateString(),
        'end_date'     => now()->addWeek()->toDateString(),
    ]);
});

test('exam can be created with required fields', function (): void {
    expect($this->exam)->toBeInstanceOf(Exam::class)
        ->and($this->exam->name)->toBe('Mid-Term Test')
        ->and($this->exam->is_published)->toBeFalse();
});

test('exam results can be recorded', function (): void {
    ExamResult::create([
        'exam_id'    => $this->exam->id,
        'student_id' => $this->student->id,
        'subject_id' => $this->subject->id,
        'marks'      => 75.0,
    ]);

    $result = ExamResult::where('exam_id', $this->exam->id)
        ->where('student_id', $this->student->id)
        ->first();

    expect($result->marks)->toBe('75.00');
});

test('exam result grade is computed from marks', function (): void {
    $result = ExamResult::create([
        'exam_id'    => $this->exam->id,
        'student_id' => $this->student->id,
        'subject_id' => $this->subject->id,
        'marks'      => 85.0,
    ]);

    expect(ExamResult::computeGrade(85.0, config('skolet.default_grading_scale')))->toBe('A');
    expect(ExamResult::computeGrade(55.0, config('skolet.default_grading_scale')))->toBe('C');
    expect(ExamResult::computeGrade(30.0, config('skolet.default_grading_scale')))->toBe('F');
});

test('exam can be published', function (): void {
    $this->exam->update(['is_published' => true]);

    expect(Exam::find($this->exam->id)->is_published)->toBeTrue();
});

test('report card service builds correct data for student', function (): void {
    ExamResult::create([
        'exam_id'    => $this->exam->id,
        'student_id' => $this->student->id,
        'subject_id' => $this->subject->id,
        'marks'      => 78.0,
    ]);

    $service = app(ReportCardService::class);
    $data    = $service->build($this->exam, $this->student);

    expect($data['results'])->toHaveCount(1)
        ->and($data['results']->first()['grade'])->toBe('A')
        ->and($data['average'])->toBe(78.0)
        ->and($data['average_grade'])->toBe('A');
});

test('report card has null average when no results exist', function (): void {
    $service = app(ReportCardService::class);
    $data    = $service->build($this->exam, $this->student);

    expect($data['results'])->toHaveCount(0)
        ->and($data['average'])->toBeNull();
});

test('exam result updateOrCreate is idempotent', function (): void {
    ExamResult::updateOrCreate(
        ['exam_id' => $this->exam->id, 'student_id' => $this->student->id, 'subject_id' => $this->subject->id],
        ['marks' => 60.0]
    );

    ExamResult::updateOrCreate(
        ['exam_id' => $this->exam->id, 'student_id' => $this->student->id, 'subject_id' => $this->subject->id],
        ['marks' => 72.0]
    );

    $results = ExamResult::where('exam_id', $this->exam->id)
        ->where('student_id', $this->student->id)
        ->get();

    expect($results)->toHaveCount(1)
        ->and((float) $results->first()->marks)->toBe(72.0);
});
