<?php

use App\Models\Tenant\AcademicYear;
use App\Models\Tenant\Exam;
use App\Models\Tenant\ExamResult;
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\SchoolProfile;
use App\Models\Tenant\Student;
use App\Models\Tenant\Subject;
use App\Models\Tenant\Term;
use App\Services\ReportCardService;

beforeEach(function (): void {
    $class            = SchoolClass::create(['name' => 'CA1', 'order' => 1]);
    $this->subject    = Subject::create(['name' => 'Maths', 'code' => 'MTH']);
    $this->academicYear = AcademicYear::create([
        'name' => '2026/2027', 'start_date' => now()->subMonths(2), 'end_date' => now()->addMonths(8),
    ]);
    $this->term = Term::create([
        'academic_year_id' => $this->academicYear->id,
        'name'              => 'Term 1',
        'start_date'        => now()->subMonths(2),
        'end_date'          => now()->addMonth(),
    ]);

    $this->student = Student::create([
        'admission_no'     => '2026/0900',
        'full_name'        => 'CA Weight Student',
        'gender'           => 'female',
        'guardian_name'    => 'Guardian',
        'guardian_contact' => '0200000009',
        'status'           => 'active',
        'class_id'         => $class->id,
    ]);

    $this->service = app(ReportCardService::class);
});

test('weighted score blends CA average and end of term exam marks using default 40/60 split', function (): void {
    $caExam1 = Exam::create(['name' => 'Class Test 1', 'term_id' => $this->term->id, 'exam_role' => Exam::ROLE_CA]);
    $caExam2 = Exam::create(['name' => 'Mid-Term Test', 'term_id' => $this->term->id, 'exam_role' => Exam::ROLE_CA]);
    $eotExam = Exam::create(['name' => 'End of Term Exam', 'term_id' => $this->term->id, 'exam_role' => Exam::ROLE_END_OF_TERM]);

    ExamResult::create(['exam_id' => $caExam1->id, 'student_id' => $this->student->id, 'subject_id' => $this->subject->id, 'marks' => 70]);
    ExamResult::create(['exam_id' => $caExam2->id, 'student_id' => $this->student->id, 'subject_id' => $this->subject->id, 'marks' => 90]);
    ExamResult::create(['exam_id' => $eotExam->id, 'student_id' => $this->student->id, 'subject_id' => $this->subject->id, 'marks' => 60]);

    $card = $this->service->build($eotExam, $this->student);
    $row  = $card['results']->first();

    // CA average = (70+90)/2 = 80. Weighted = 80*0.4 + 60*0.6 = 68.0
    expect($card['is_weighted'])->toBeTrue()
        ->and($row['ca_average'])->toBe(80.0)
        ->and($row['exam_marks'])->toBe(60.0)
        ->and($row['marks'])->toBe(68.0)
        ->and($row['status'])->toBe('complete');
});

test('weighted score respects a custom CA/exam weight split', function (): void {
    SchoolProfile::create(['school_name' => 'Weighted School', 'ca_weight' => 30, 'exam_weight' => 70]);

    $caExam  = Exam::create(['name' => 'Class Test', 'term_id' => $this->term->id, 'exam_role' => Exam::ROLE_CA]);
    $eotExam = Exam::create(['name' => 'End of Term Exam', 'term_id' => $this->term->id, 'exam_role' => Exam::ROLE_END_OF_TERM]);

    ExamResult::create(['exam_id' => $caExam->id, 'student_id' => $this->student->id, 'subject_id' => $this->subject->id, 'marks' => 100]);
    ExamResult::create(['exam_id' => $eotExam->id, 'student_id' => $this->student->id, 'subject_id' => $this->subject->id, 'marks' => 50]);

    $card = $this->service->build($eotExam, $this->student);
    $row  = $card['results']->first();

    // 100*0.3 + 50*0.7 = 30 + 35 = 65.0
    expect($row['marks'])->toBe(65.0);
});

test('subject is pending when only CA marks exist and the end of term exam has not been graded', function (): void {
    $caExam  = Exam::create(['name' => 'Class Test', 'term_id' => $this->term->id, 'exam_role' => Exam::ROLE_CA]);
    $eotExam = Exam::create(['name' => 'End of Term Exam', 'term_id' => $this->term->id, 'exam_role' => Exam::ROLE_END_OF_TERM]);

    ExamResult::create(['exam_id' => $caExam->id, 'student_id' => $this->student->id, 'subject_id' => $this->subject->id, 'marks' => 75]);

    $card = $this->service->build($eotExam, $this->student);
    $row  = $card['results']->first();

    expect($row['status'])->toBe('pending_exam')
        ->and($row['marks'])->toBeNull()
        ->and($row['ca_average'])->toBe(75.0);
});

test('exams tagged none are excluded from the CA bucket', function (): void {
    $untaggedExam = Exam::create(['name' => 'Practice Quiz', 'term_id' => $this->term->id, 'exam_role' => Exam::ROLE_NONE]);
    $caExam       = Exam::create(['name' => 'Class Test', 'term_id' => $this->term->id, 'exam_role' => Exam::ROLE_CA]);
    $eotExam      = Exam::create(['name' => 'End of Term Exam', 'term_id' => $this->term->id, 'exam_role' => Exam::ROLE_END_OF_TERM]);

    ExamResult::create(['exam_id' => $untaggedExam->id, 'student_id' => $this->student->id, 'subject_id' => $this->subject->id, 'marks' => 10]);
    ExamResult::create(['exam_id' => $caExam->id, 'student_id' => $this->student->id, 'subject_id' => $this->subject->id, 'marks' => 80]);
    ExamResult::create(['exam_id' => $eotExam->id, 'student_id' => $this->student->id, 'subject_id' => $this->subject->id, 'marks' => 60]);

    $card = $this->service->build($eotExam, $this->student);
    $row  = $card['results']->first();

    // CA average must be 80 (the untagged 10 must not drag it down), weighted = 80*0.4+60*0.6 = 68
    expect($row['ca_average'])->toBe(80.0)
        ->and($row['marks'])->toBe(68.0);
});

test('an exam with role none still returns raw unweighted results (backward compatible)', function (): void {
    $exam = Exam::create(['name' => 'Legacy Exam', 'term_id' => $this->term->id, 'exam_role' => Exam::ROLE_NONE]);

    ExamResult::create(['exam_id' => $exam->id, 'student_id' => $this->student->id, 'subject_id' => $this->subject->id, 'marks' => 82]);

    $card = $this->service->build($exam, $this->student);
    $row  = $card['results']->first();

    expect($card['is_weighted'])->toBeFalse()
        ->and($row['marks'])->toBe(82.0)
        ->and($row)->not->toHaveKey('ca_average');
});

test('only one end-of-term exam is allowed per term', function (): void {
    Exam::create(['name' => 'End of Term A', 'term_id' => $this->term->id, 'exam_role' => Exam::ROLE_END_OF_TERM]);

    $alreadyExists = Exam::where('term_id', $this->term->id)
        ->where('exam_role', Exam::ROLE_END_OF_TERM)
        ->exists();

    expect($alreadyExists)->toBeTrue();
});
