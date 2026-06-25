<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tenant\Exam;
use App\Models\Tenant\ExamResult;
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\SchoolProfile;
use App\Models\Tenant\Section;
use App\Models\Tenant\Student;

final class ExamAnalyticsService
{
    /**
     * Build per-subject analytics for a single exam and class.
     *
     * @return array{exam: Exam|null, class: SchoolClass|null, section: Section|null, subjects: array, pass_threshold: float}
     */
    public function buildSubjectReport(
        string $examId,
        string $classId,
        ?string $sectionId
    ): array {
        $passThreshold = $this->passThreshold();

        $exam    = Exam::with('term')->find($examId);
        $class   = SchoolClass::find($classId);
        $section = $sectionId ? Section::find($sectionId) : null;

        $studentIds = $this->studentIds($classId, $sectionId);

        if ($studentIds->isEmpty()) {
            return $this->emptyReport($exam, $class, $section, $passThreshold);
        }

        $results = ExamResult::where('exam_id', $examId)
            ->whereIn('student_id', $studentIds)
            ->whereNotNull('marks')
            ->with('subject')
            ->get();

        if ($results->isEmpty()) {
            return $this->emptyReport($exam, $class, $section, $passThreshold);
        }

        $subjects = [];
        foreach ($results->groupBy('subject_id') as $subjectId => $subjectResults) {
            $marks = $subjectResults->pluck('marks');
            $count = $marks->count();

            if ($count === 0) {
                continue;
            }

            $passCount = $marks->filter(fn ($m) => (float) $m >= $passThreshold)->count();

            $subjects[] = [
                'subject_id'    => $subjectId,
                'subject_name'  => $subjectResults->first()?->subject?->name ?? 'Unknown',
                'student_count' => $count,
                'avg_score'     => round((float) $marks->avg(), 1),
                'highest'       => (float) $marks->max(),
                'lowest'        => (float) $marks->min(),
                'pass_rate'     => $count > 0 ? round($passCount / $count * 100, 1) : 0.0,
            ];
        }

        usort($subjects, fn ($a, $b) => strcmp($a['subject_name'], $b['subject_name']));

        return [
            'exam'           => $exam,
            'class'          => $class,
            'section'        => $section,
            'subjects'       => $subjects,
            'pass_threshold' => $passThreshold,
        ];
    }

    /**
     * Build per-exam class average trend for all exams in a term.
     *
     * @return array<int, array{exam_name: string, average: float}>
     */
    public function buildClassTrend(
        string $classId,
        ?string $sectionId,
        string $termId
    ): array {
        $exams = Exam::where('term_id', $termId)
            ->orderBy('start_date')
            ->orderBy('name')
            ->get();

        if ($exams->isEmpty()) {
            return [];
        }

        $studentIds = $this->studentIds($classId, $sectionId);

        if ($studentIds->isEmpty()) {
            return [];
        }

        $trend = [];
        foreach ($exams as $exam) {
            $marks = ExamResult::where('exam_id', $exam->id)
                ->whereIn('student_id', $studentIds)
                ->whereNotNull('marks')
                ->pluck('marks');

            if ($marks->isEmpty()) {
                continue;
            }

            $trend[] = [
                'exam_name' => $exam->name,
                'average'   => round((float) $marks->avg(), 1),
            ];
        }

        return $trend;
    }

    private function passThreshold(): float
    {
        $scale = SchoolProfile::first()?->grading_scale
            ?? config('skolet.default_grading_scale', []);

        $sorted = collect($scale)->sortBy('min')->values();

        return $sorted->count() >= 2
            ? (float) $sorted->get(1)['min']
            : 40.0;
    }

    /** @return \Illuminate\Support\Collection<int, string> */
    private function studentIds(string $classId, ?string $sectionId): \Illuminate\Support\Collection
    {
        $query = Student::where('class_id', $classId)->where('status', 'active');

        if ($sectionId) {
            $query->where('section_id', $sectionId);
        }

        return $query->pluck('id');
    }

    private function emptyReport(
        ?Exam $exam,
        ?SchoolClass $class,
        ?Section $section,
        float $passThreshold
    ): array {
        return [
            'exam'           => $exam,
            'class'          => $class,
            'section'        => $section,
            'subjects'       => [],
            'pass_threshold' => $passThreshold,
        ];
    }
}
