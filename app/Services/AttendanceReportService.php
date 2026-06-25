<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tenant\Attendance;
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\Section;
use App\Models\Tenant\Student;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

final class AttendanceReportService
{
    /**
     * Returns students below the given attendance threshold for a term.
     * Queries attendances within the term's start_date→end_date (or today if end_date is future).
     *
     * @return array{success: bool, data: mixed, error: ?string}
     */
    public function buildChronicAbsentees(
        string $termId,
        string $classId,
        ?string $sectionId,
        int $threshold = 80,
    ): array {
        try {
            $term    = \App\Models\Tenant\Term::with('academicYear')->findOrFail($termId);
            $class   = SchoolClass::findOrFail($classId);
            $section = $sectionId ? Section::find($sectionId) : null;

            $from = $term->start_date;
            $to   = $term->end_date && $term->end_date->isPast() ? $term->end_date : now()->toDateObject();

            $studentQuery = Student::where('class_id', $classId)->where('status', 'active');
            if ($sectionId) {
                $studentQuery->where('section_id', $sectionId);
            }
            $students = $studentQuery->orderBy('full_name')->get();

            if ($students->isEmpty()) {
                return ['success' => true, 'data' => [
                    'term'      => $term,
                    'class'     => $class,
                    'section'   => $section,
                    'threshold' => $threshold,
                    'rows'      => [],
                ], 'error' => null];
            }

            $attendances = Attendance::whereBetween('date', [$from->toDateString(), $to->toDateString()])
                ->whereIn('student_id', $students->pluck('id'))
                ->get()
                ->groupBy('student_id');

            $rows = [];
            foreach ($students as $student) {
                $records     = $attendances->get($student->id, collect());
                $present     = $records->where('status', 'present')->count();
                $absent      = $records->where('status', 'absent')->count();
                $total       = $records->count();
                $pctPresent  = $total > 0 ? round(($present / $total) * 100, 1) : 0.0;

                if ($pctPresent < $threshold) {
                    $rows[] = [
                        'student'          => $student,
                        'absent'           => $absent,
                        'days_marked'      => $total,
                        'percent_present'  => $pctPresent,
                        'guardian_name'    => $student->guardian_name,
                        'guardian_contact' => $student->guardian_contact,
                        'guardian_email'   => $student->guardian_email,
                    ];
                }
            }

            usort($rows, fn ($a, $b) => $a['percent_present'] <=> $b['percent_present']);

            return ['success' => true, 'data' => [
                'term'      => $term,
                'class'     => $class,
                'section'   => $section,
                'threshold' => $threshold,
                'rows'      => $rows,
            ], 'error' => null];
        } catch (\Throwable $e) {
            Log::error('[AttendanceReportService::buildChronicAbsentees] ' . $e->getMessage());

            return ['success' => false, 'data' => null, 'error' => 'Could not build absentee report.'];
        }
    }

    /**
     * Aggregates attendance records for a class (and optionally a section)
     * within a date range. Returns per-student present/absent/late counts
     * and the percentage of marked days the student was present.
     *
     * @return array{success: bool, data: mixed, error: ?string}
     */
    public function build(
        string $classId,
        ?string $sectionId,
        Carbon $dateFrom,
        Carbon $dateTo
    ): array {
        try {
            $class   = SchoolClass::findOrFail($classId);
            $section = $sectionId ? Section::find($sectionId) : null;

            $studentQuery = Student::where('class_id', $classId);
            if ($sectionId) {
                $studentQuery->where('section_id', $sectionId);
            }
            $students = $studentQuery->orderBy('full_name')->get();

            if ($students->isEmpty()) {
                return ['success' => true, 'data' => [
                    'class'     => $class,
                    'section'   => $section,
                    'date_from' => $dateFrom,
                    'date_to'   => $dateTo,
                    'rows'      => [],
                ], 'error' => null];
            }

            $attendances = Attendance::whereBetween('date', [$dateFrom->toDateString(), $dateTo->toDateString()])
                ->whereIn('student_id', $students->pluck('id'))
                ->get()
                ->groupBy('student_id');

            $rows = $students->map(function (Student $student) use ($attendances): array {
                $records = $attendances->get($student->id, collect());
                $present = $records->where('status', 'present')->count();
                $absent  = $records->where('status', 'absent')->count();
                $late    = $records->where('status', 'late')->count();
                $total   = $records->count();

                return [
                    'student'         => $student,
                    'present'         => $present,
                    'absent'          => $absent,
                    'late'            => $late,
                    'total_marked'    => $total,
                    'percent_present' => $total > 0 ? round(($present / $total) * 100, 1) : 0.0,
                ];
            })->all();

            return ['success' => true, 'data' => [
                'class'     => $class,
                'section'   => $section,
                'date_from' => $dateFrom,
                'date_to'   => $dateTo,
                'rows'      => $rows,
            ], 'error' => null];
        } catch (\Throwable $e) {
            Log::error('[AttendanceReportService::build] ' . $e->getMessage());

            return ['success' => false, 'data' => null, 'error' => 'Could not generate attendance report.'];
        }
    }
}
