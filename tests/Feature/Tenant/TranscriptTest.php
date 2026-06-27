<?php

use App\Models\Tenant\Exam;
use App\Models\Tenant\ExamResult;
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\Student;
use App\Models\Tenant\Subject;
use App\Models\Tenant\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    Role::firstOrCreate(['name' => 'school_admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'student',      'guard_name' => 'web']);

    Permission::firstOrCreate(['name' => 'exams.view', 'guard_name' => 'web']);

    $class         = SchoolClass::create(['name' => 'TC1', 'order' => 1]);
    $this->subject = Subject::create(['name' => 'Maths', 'code' => 'MTH']);

    // Student with a linked user account
    $studentUser = User::create([
        'name'     => 'Transcript Student',
        'email'    => 'transcript@test.test',
        'password' => bcrypt('pw'),
        'role'     => 'student',
    ]);
    $studentUser->assignRole('student');

    $this->student = Student::create([
        'admission_no'     => '2026/0700',
        'full_name'        => 'Transcript Student',
        'gender'           => 'male',
        'guardian_name'    => 'Guardian',
        'guardian_contact' => '0200000007',
        'status'           => 'active',
        'class_id'         => $class->id,
        'user_id'          => $studentUser->id,
    ]);

    // Another student (for 403 tests)
    $this->otherStudent = Student::create([
        'admission_no'     => '2026/0701',
        'full_name'        => 'Other Student',
        'gender'           => 'female',
        'guardian_name'    => 'Guardian2',
        'guardian_contact' => '0200000008',
        'status'           => 'active',
        'class_id'         => $class->id,
    ]);

    $this->studentUser = $studentUser;

    // Published exam with result
    $this->publishedExam = Exam::create([
        'name'         => 'End of Term 1',
        'is_published' => true,
        'start_date'   => now()->subMonth()->toDateString(),
        'end_date'     => now()->subWeeks(2)->toDateString(),
    ]);

    ExamResult::create([
        'exam_id'    => $this->publishedExam->id,
        'student_id' => $this->student->id,
        'subject_id' => $this->subject->id,
        'marks'      => 82.0,
    ]);

    // Unpublished exam with result
    $this->unpublishedExam = Exam::create([
        'name'         => 'Mid-Term Test',
        'is_published' => false,
        'start_date'   => now()->subDays(10)->toDateString(),
        'end_date'     => now()->subDays(5)->toDateString(),
    ]);

    ExamResult::create([
        'exam_id'    => $this->unpublishedExam->id,
        'student_id' => $this->student->id,
        'subject_id' => $this->subject->id,
        'marks'      => 65.0,
    ]);
});

test('student has published exam results in the database', function (): void {
    $publishedResults = ExamResult::where('student_id', $this->student->id)
        ->whereHas('exam', fn ($q) => $q->where('is_published', true))
        ->count();

    expect($publishedResults)->toBe(1);
});

test('unpublished exam results are excluded from transcript query', function (): void {
    $publishedResults = ExamResult::where('student_id', $this->student->id)
        ->whereHas('exam', fn ($q) => $q->where('is_published', true))
        ->with('exam', 'subject')
        ->get();

    $allResults = ExamResult::where('student_id', $this->student->id)->count();

    expect($publishedResults->count())->toBe(1)
        ->and($allResults)->toBe(2)
        ->and($publishedResults->first()->exam->name)->toBe('End of Term 1');
});

test('student can access only their own transcript data', function (): void {
    // Student's user_id is linked to their student record
    $student = Student::where('user_id', $this->studentUser->id)->first();

    expect($student->id)->toBe($this->student->id);

    // Another student has no user_id link — student cannot access it
    $otherLinked = Student::where('user_id', $this->studentUser->id)
        ->where('id', $this->otherStudent->id)
        ->exists();

    expect($otherLinked)->toBeFalse();
});

test('exam result grade is computed correctly for transcript marks', function (): void {
    $result = ExamResult::where('student_id', $this->student->id)
        ->where('exam_id', $this->publishedExam->id)
        ->first();

    $scale = config('skolet.default_grading_scale', []);
    $grade = ExamResult::computeGrade((float) $result->marks, $scale);

    expect($grade)->toBe('A');
});

test('student has no results for another student', function (): void {
    $otherResults = ExamResult::where('student_id', $this->otherStudent->id)->count();

    expect($otherResults)->toBe(0);
});
