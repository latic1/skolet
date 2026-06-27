<?php

use App\Models\Tenant\Exam;
use App\Models\Tenant\ExamResult;
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\Student;
use App\Models\Tenant\Subject;
use App\Models\Tenant\Term;
use App\Models\Tenant\AcademicYear;
use App\Services\ExamAnalyticsService;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    Role::firstOrCreate(['name' => 'school_admin', 'guard_name' => 'web']);

    $this->class   = SchoolClass::create(['name' => 'EA1', 'order' => 1]);
    $this->subject = Subject::create(['name' => 'Physics', 'code' => 'PHY']);

    $this->year = AcademicYear::create([
        'name' => '2026/2027', 'start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'is_current' => true,
    ]);

    $this->term = Term::create([
        'academic_year_id' => $this->year->id,
        'name'             => 'Term 1',
        'start_date'       => '2026-01-01',
        'end_date'         => '2026-04-30',
        'is_current'       => true,
    ]);

    // Create 3 students
    $this->students = [];
    foreach (['EA0001' => 90, 'EA0002' => 60, 'EA0003' => 70] as $adm => $marks) {
        $student = Student::create([
            'admission_no'     => "2026/{$adm}",
            'full_name'        => "Student {$adm}",
            'gender'           => 'male',
            'guardian_name'    => 'Guardian',
            'guardian_contact' => '0200000099',
            'status'           => 'active',
            'class_id'         => $this->class->id,
        ]);
        $this->students[$adm] = ['student' => $student, 'marks' => $marks];
    }

    $this->exam = Exam::create([
        'name'         => 'EA Exam',
        'is_published' => true,
        'start_date'   => '2026-02-01',
        'end_date'     => '2026-02-05',
        'term_id'      => $this->term->id,
    ]);

    foreach ($this->students as ['student' => $student, 'marks' => $marks]) {
        ExamResult::create([
            'exam_id'    => $this->exam->id,
            'student_id' => $student->id,
            'subject_id' => $this->subject->id,
            'marks'      => $marks,
        ]);
    }

    $this->service = app(ExamAnalyticsService::class);
});

test('buildSubjectReport returns correct avg, min, max for a class', function (): void {
    $report = $this->service->buildSubjectReport($this->exam->id, $this->class->id, null);

    expect($report['subjects'])->toHaveCount(1);

    $physics = $report['subjects'][0];
    expect($physics['subject_name'])->toBe('Physics')
        ->and($physics['highest'])->toBe(90.0)
        ->and($physics['lowest'])->toBe(60.0)
        ->and($physics['student_count'])->toBe(3);
});

test('buildSubjectReport computes pass_rate against threshold', function (): void {
    $report = $this->service->buildSubjectReport($this->exam->id, $this->class->id, null);

    // marks: 90, 60, 70 — all pass at default threshold
    expect($report['subjects'][0]['pass_rate'])->toBe(100.0);
    expect($report['pass_threshold'])->toBeGreaterThan(0.0);
});

test('buildSubjectReport returns empty subjects when no results exist', function (): void {
    $emptyClass = SchoolClass::create(['name' => 'Empty', 'order' => 99]);

    $report = $this->service->buildSubjectReport($this->exam->id, $emptyClass->id, null);

    expect($report['subjects'])->toBeEmpty();
});

test('buildClassTrend returns one entry per exam in the term in chronological order', function (): void {
    $exam2 = Exam::create([
        'name'         => 'EA Exam 2',
        'is_published' => true,
        'start_date'   => '2026-03-01',
        'end_date'     => '2026-03-05',
        'term_id'      => $this->term->id,
    ]);

    foreach ($this->students as ['student' => $student]) {
        ExamResult::create([
            'exam_id'    => $exam2->id,
            'student_id' => $student->id,
            'subject_id' => $this->subject->id,
            'marks'      => 80.0,
        ]);
    }

    $trend = $this->service->buildClassTrend($this->class->id, null, $this->term->id);

    expect($trend)->toHaveCount(2)
        ->and($trend[0]['exam_name'])->toBe('EA Exam')
        ->and($trend[1]['exam_name'])->toBe('EA Exam 2');
});

test('buildClassTrend returns empty array when no exams in term', function (): void {
    $emptyTerm = Term::create([
        'academic_year_id' => $this->year->id,
        'name'             => 'Term 3',
        'start_date'       => '2026-09-01',
        'end_date'         => '2026-12-31',
        'is_current'       => false,
    ]);

    $trend = $this->service->buildClassTrend($this->class->id, null, $emptyTerm->id);

    expect($trend)->toBeEmpty();
});
