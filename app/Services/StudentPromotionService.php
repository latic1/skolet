<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tenant\AcademicYear;
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\Section;
use App\Models\Tenant\Student;
use App\Models\Tenant\StudentClassHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class StudentPromotionService
{
    /**
     * Execute end-of-year promotion for a batch of students.
     *
     * @param  array<string, string>  $outcomes  [student_id => 'promoted'|'retained'|'graduated']
     * @param  AcademicYear           $fromYear  The year being closed out
     * @return array{promoted: int, retained: int, graduated: int, errors: list<string>}
     */
    public function promote(array $outcomes, AcademicYear $fromYear): array
    {
        $promoted  = 0;
        $retained  = 0;
        $graduated = 0;
        $errors    = [];

        try {
            DB::transaction(function () use ($outcomes, $fromYear, &$promoted, &$retained, &$graduated, &$errors): void {
                foreach ($outcomes as $studentId => $outcome) {
                    $student = Student::find($studentId);

                    if (! $student) {
                        $errors[] = "Student {$studentId} not found — skipped.";
                        continue;
                    }

                    // Record history row for this year
                    StudentClassHistory::create([
                        'student_id'       => $student->id,
                        'academic_year_id' => $fromYear->id,
                        'class_id'         => $student->class_id,
                        'section_id'       => $student->section_id,
                        'outcome'          => $outcome,
                    ]);

                    match ($outcome) {
                        'promoted'  => $this->handlePromoted($student, $errors, $promoted),
                        'retained'  => $this->handleRetained($retained),
                        'graduated' => $this->handleGraduated($student, $graduated),
                        default     => $errors[] = "Unknown outcome '{$outcome}' for {$student->full_name} — skipped.",
                    };
                }
            });
        } catch (\Throwable $e) {
            Log::error('[StudentPromotionService::promote] ' . $e->getMessage());

            return [
                'promoted'  => 0,
                'retained'  => 0,
                'graduated' => 0,
                'errors'    => ['An unexpected error occurred. No changes were saved. Please try again.'],
            ];
        }

        return compact('promoted', 'retained', 'graduated', 'errors');
    }

    private function handlePromoted(Student $student, array &$errors, int &$promoted): void
    {
        $nextClass = SchoolClass::where('order', '>', $student->schoolClass->order)
            ->orderBy('order')
            ->first();

        if (! $nextClass) {
            $errors[] = "{$student->full_name} cannot be promoted — they are already in the highest class. Change their outcome to Graduate or Retain.";
            return;
        }

        // Match section by name in the next class
        $nextSection = $student->section_id
            ? Section::where('class_id', $nextClass->id)
                ->where('name', $student->section?->name)
                ->first()
            : null;

        $student->update([
            'class_id'   => $nextClass->id,
            'section_id' => $nextSection?->id,
        ]);

        $promoted++;
    }

    private function handleRetained(int &$retained): void
    {
        $retained++;
    }

    private function handleGraduated(Student $student, int &$graduated): void
    {
        $student->update(['status' => 'graduated']);
        $graduated++;
    }
}
