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
