<?php

declare(strict_types=1);

namespace App\Services;

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
        $exam->loadMissing('academicYear');
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
