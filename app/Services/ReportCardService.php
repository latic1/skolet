<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tenant\Attendance;
use App\Models\Tenant\Exam;
use App\Models\Tenant\ExamResult;
use App\Models\Tenant\SchoolProfile;
use App\Models\Tenant\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

final class ReportCardService
{
    /**
     * Build the report card data array for one student + exam.
     *
     * Returns:
     *   student        — Student model with schoolClass + section loaded
     *   exam           — Exam model with academicYear loaded
     *   results        — collection of arrays with subject, marks, grade, remark, bar_width, bar_color
     *   average        — float|null  (null when no marks exist)
     *   average_grade  — string|null
     *   average_remark — string|null
     *   scale          — the grading scale array from config
     */
    public function build(Exam $exam, Student $student): array
    {
        $exam->loadMissing('term.academicYear');
        $student->loadMissing(['schoolClass', 'section']);

        $scale = SchoolProfile::first()?->grading_scale
            ?? config('skolet.default_grading_scale', []);

        $rawResults = ExamResult::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->with('subject')
            ->orderBy('created_at')
            ->get();

        $results = $rawResults->map(function (ExamResult $r) use ($scale) {
            [$grade, $remark] = $this->applyScale((float) $r->marks, $scale);
            return [
                'subject'    => $r->subject?->name ?? '—',
                'marks'      => $r->marks,
                'grade'      => $grade,
                'remark'     => $remark,
                'bar_width'  => min(100, (int) round($r->marks)),
                'bar_color'  => $this->barColor($grade),
            ];
        });

        $average      = $results->isNotEmpty() ? round($results->avg('marks'), 1) : null;
        [$avgGrade, $avgRemark] = $average !== null
            ? $this->applyScale($average, $scale)
            : [null, null];

        return [
            'student'        => $student,
            'exam'           => $exam,
            'results'        => $results,
            'average'        => $average,
            'average_grade'  => $avgGrade,
            'average_remark' => $avgRemark,
            'scale'          => $scale,
        ];
    }

    /**
     * Generate (or retrieve a cached) PDF for a student's report card.
     * Returns the absolute local storage path, or throws on failure.
     *
     * Saved to: storage/{tenant}/report-cards/{student_id}/{exam_id}.pdf
     */
    public function generatePdf(Exam $exam, Student $student): string
    {
        $tenantId  = tenant('id');
        $disk      = Storage::disk('local');
        $directory = "{$tenantId}/report-cards/{$student->id}";
        $filename  = "{$exam->id}.pdf";
        $path      = "{$directory}/{$filename}";

        try {
            $data = $this->build($exam, $student);

            $schoolProfile = SchoolProfile::first();
            $logoBase64    = null;

            if ($schoolProfile?->logo_path) {
                try {
                    $content = Storage::disk('public')->get($schoolProfile->logo_path);
                    if ($content) {
                        $ext      = strtolower(pathinfo($schoolProfile->logo_path, PATHINFO_EXTENSION));
                        $mime     = match ($ext) {
                            'jpg', 'jpeg' => 'image/jpeg',
                            'png'         => 'image/png',
                            'gif'         => 'image/gif',
                            'webp'        => 'image/webp',
                            'svg'         => 'image/svg+xml',
                            default       => 'image/png',
                        };
                        $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode($content);
                    }
                } catch (\Throwable) {
                    // Logo not critical — PDF still generates without it
                }
            }

            $data['schoolProfile'] = $schoolProfile;
            $data['logoBase64']    = $logoBase64;

            $pdf = Pdf::loadView('tenant.exams.report-card-pdf', $data)
                ->setPaper('a4', 'portrait');

            $disk->makeDirectory($directory);
            $disk->put($path, $pdf->output());

            return storage_path("app/{$path}");
        } catch (\Throwable $e) {
            Log::error('[ReportCardService.generatePdf] ' . $e->getMessage(), [
                'exam_id'    => $exam->id,
                'student_id' => $student->id,
            ]);
            throw $e;
        }
    }

    /**
     * Generate a cumulative transcript PDF for a student across all published exams.
     * Saves to storage/{tenantId}/transcripts/{student_id}.pdf and returns the absolute path.
     */
    public function generateTranscript(Student $student): string
    {
        $tenantId  = tenant('id');
        $disk      = Storage::disk('local');
        $directory = "{$tenantId}/transcripts";
        $path      = "{$directory}/{$student->id}.pdf";

        try {
            $scale = SchoolProfile::first()?->grading_scale
                ?? config('skolet.default_grading_scale', []);

            $rawResults = ExamResult::where('student_id', $student->id)
                ->whereHas('exam', fn ($q) => $q->where('is_published', true))
                ->with(['exam.term.academicYear', 'subject'])
                ->get();

            // Group: academicYear → term → exam
            $groupedByYear = $rawResults
                ->filter(fn ($r) => $r->exam?->term?->academicYear !== null)
                ->groupBy(fn ($r) => $r->exam->term->academicYear->id)
                ->map(function ($yearResults) use ($student, $scale) {
                    $academicYear = $yearResults->first()->exam->term->academicYear;

                    $termGroups = $yearResults
                        ->groupBy(fn ($r) => $r->exam->term->id)
                        ->map(function ($termResults) use ($student, $scale) {
                            $term = $termResults->first()->exam->term;

                            // Attendance % for this student during this term
                            $attendancePct = null;
                            if ($term->start_date) {
                                $termTo = ($term->end_date && $term->end_date->isPast())
                                    ? $term->end_date->toDateString()
                                    : now()->toDateString();
                                $att = Attendance::where('student_id', $student->id)
                                    ->whereBetween('date', [$term->start_date->toDateString(), $termTo])
                                    ->get();
                                if ($att->isNotEmpty()) {
                                    $attendancePct = round($att->where('status', 'present')->count() / $att->count() * 100, 1);
                                }
                            }

                            // Group by exam
                            $examGroups = $termResults
                                ->groupBy(fn ($r) => $r->exam_id)
                                ->map(function ($examResults) use ($scale) {
                                    $exam = $examResults->first()->exam;
                                    $rows = $examResults->map(function ($r) use ($scale) {
                                        [$grade, $remark] = $this->applyScale((float) $r->marks, $scale);
                                        return [
                                            'subject' => $r->subject?->name ?? '—',
                                            'marks'   => (float) $r->marks,
                                            'grade'   => $grade,
                                            'remark'  => $remark,
                                        ];
                                    })->sortBy('subject')->values();

                                    $average = $rows->isNotEmpty() ? round($rows->avg('marks'), 1) : null;
                                    [$avgGrade, $avgRemark] = $average !== null
                                        ? $this->applyScale($average, $scale)
                                        : [null, null];

                                    return [
                                        'exam'           => $exam,
                                        'results'        => $rows,
                                        'average'        => $average,
                                        'average_grade'  => $avgGrade,
                                        'average_remark' => $avgRemark,
                                    ];
                                })
                                ->sortBy(fn ($e) => optional($e['exam']->start_date)->toDateString() ?? $e['exam']->created_at->toDateString())
                                ->values();

                            $termAvg = $termResults->pluck('marks')->isNotEmpty()
                                ? round($termResults->pluck('marks')->avg(), 1)
                                : null;
                            [$termGrade, $termRemark] = $termAvg !== null
                                ? $this->applyScale($termAvg, $scale)
                                : [null, null];

                            return [
                                'term'           => $term,
                                'attendance_pct' => $attendancePct,
                                'exams'          => $examGroups,
                                'term_average'   => $termAvg,
                                'term_grade'     => $termGrade,
                                'term_remark'    => $termRemark,
                            ];
                        })
                        ->sortBy(fn ($t) => optional($t['term']->start_date)->toDateString() ?? $t['term']->name)
                        ->values();

                    $yearAvg = $yearResults->pluck('marks')->isNotEmpty()
                        ? round($yearResults->pluck('marks')->avg(), 1)
                        : null;
                    [$yearGrade, $yearRemark] = $yearAvg !== null
                        ? $this->applyScale($yearAvg, $scale)
                        : [null, null];

                    return [
                        'academic_year' => $academicYear,
                        'terms'         => $termGroups,
                        'year_average'  => $yearAvg,
                        'year_grade'    => $yearGrade,
                        'year_remark'   => $yearRemark,
                    ];
                })
                ->sortBy(fn ($y) => optional($y['academic_year']->start_date ?? null)->toDateString() ?? $y['academic_year']->name)
                ->values();

            $allMarks = $rawResults->pluck('marks');
            $cumulativeAvg = $allMarks->isNotEmpty() ? round($allMarks->avg(), 1) : null;
            [$cumGrade, $cumRemark] = $cumulativeAvg !== null
                ? $this->applyScale($cumulativeAvg, $scale)
                : [null, null];

            $schoolProfile = SchoolProfile::first();
            $logoBase64    = null;

            if ($schoolProfile?->logo_path) {
                try {
                    $content = Storage::disk('public')->get($schoolProfile->logo_path);
                    if ($content) {
                        $ext      = strtolower(pathinfo($schoolProfile->logo_path, PATHINFO_EXTENSION));
                        $mime     = match ($ext) {
                            'jpg', 'jpeg' => 'image/jpeg',
                            'png'         => 'image/png',
                            'gif'         => 'image/gif',
                            'webp'        => 'image/webp',
                            'svg'         => 'image/svg+xml',
                            default       => 'image/png',
                        };
                        $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode($content);
                    }
                } catch (\Throwable) {
                    // Logo not critical
                }
            }

            $student->loadMissing(['schoolClass', 'section']);

            $pdf = Pdf::loadView('tenant.exams.transcript-pdf', [
                'student'            => $student,
                'years'              => $groupedByYear,
                'cumulative_average' => $cumulativeAvg,
                'cumulative_grade'   => $cumGrade,
                'cumulative_remark'  => $cumRemark,
                'scale'              => $scale,
                'schoolProfile'      => $schoolProfile,
                'logoBase64'         => $logoBase64,
            ])->setPaper('a4', 'portrait');

            $disk->makeDirectory($directory);
            $disk->put($path, $pdf->output());

            return storage_path("app/{$path}");
        } catch (\Throwable $e) {
            Log::error('[ReportCardService.generateTranscript] ' . $e->getMessage(), [
                'student_id' => $student->id,
            ]);
            throw $e;
        }
    }

    // -------------------------------------------------------------------------

    /** Returns [grade, remark] for a numeric score against the given scale. */
    private function applyScale(float $marks, array $scale): array
    {
        foreach ($scale as $band) {
            if ($marks >= $band['min'] && $marks <= $band['max']) {
                return [$band['grade'], $band['remark']];
            }
        }

        // Fallback — should not happen with a well-configured scale
        return ['F', 'Fail'];
    }

    /** CSS color token matching the grade band (used in progress bar fills). */
    private function barColor(string $grade): string
    {
        return match ($grade) {
            'A'     => '#10b981',
            'B'     => '#61a8ff',
            'C'     => '#ff8904',
            default => '#ef4444',
        };
    }
}
