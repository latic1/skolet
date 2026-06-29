<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Exports\AttendanceReportExport;
use App\Exports\FeeCollectionReportExport;
use App\Http\Controllers\Controller;
use App\Models\Tenant\AcademicYear;
use App\Models\Tenant\Exam;
use App\Models\Tenant\FeePayment;
use App\Models\Tenant\FeeStructure;
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\SchoolProfile;
use App\Models\Tenant\Student;
use App\Models\Tenant\Term;
use App\Services\AttendanceReportService;
use App\Services\ExamAnalyticsService;
use App\Services\FinancialSummaryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ReportController extends Controller
{
    public function __construct(
        private readonly AttendanceReportService $attendanceService,
        private readonly ExamAnalyticsService $examAnalyticsService,
        private readonly FinancialSummaryService $financialSummaryService,
    ) {}

    private const DEFAULT_THRESHOLD = 80;

    public function index(Request $request): View
    {
        $classes       = SchoolClass::with('sections')->orderBy('order')->get();
        $terms         = Term::with('academicYear')->latest()->get();
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $activeTab     = $request->query('tab', 'attendance');

        $attendanceReport  = null;
        $feeReport         = null;
        $analyticsReport   = null;
        $financialReport   = null;
        $trendData         = [];
        $exams             = collect();
        $absenteesReport   = null;
        $selectedThreshold         = (int) $request->input('threshold', self::DEFAULT_THRESHOLD);
        $selectedFinancialYearId   = $request->input('financial_year_id', '');
        $selectedFinancialTermId   = $request->input('financial_term_id', '');

        if (
            $activeTab === 'attendance'
            && $request->filled('class_id')
            && $request->filled('date_from')
            && $request->filled('date_to')
        ) {
            $dateFrom = Carbon::parse($request->input('date_from'));
            $dateTo   = Carbon::parse($request->input('date_to'));

            if ($dateFrom->lte($dateTo)) {
                $result = $this->attendanceService->build(
                    $request->input('class_id'),
                    $request->input('section_id') ?: null,
                    $dateFrom,
                    $dateTo
                );

                if ($result['success']) {
                    $attendanceReport = $result['data'];
                } else {
                    session()->flash('error', $result['error']);
                }
            }
        }

        if ($activeTab === 'fees' && $request->filled('term_id')) {
            try {
                $feeReport = $this->buildFeeReport($request->input('term_id'));
            } catch (\Throwable $e) {
                Log::error('[ReportController::index/fees] ' . $e->getMessage());
                session()->flash('error', 'Could not load fee report.');
            }
        }

        if ($activeTab === 'alerts' && $request->filled('class_id') && $request->filled('term_id')) {
            $result = $this->attendanceService->buildChronicAbsentees(
                $request->input('term_id'),
                $request->input('class_id'),
                $request->input('section_id') ?: null,
                $selectedThreshold,
            );

            if ($result['success']) {
                $absenteesReport = $result['data'];
            } else {
                session()->flash('error', $result['error']);
            }
        }

        if ($activeTab === 'financial' && $request->filled('financial_year_id')) {
            try {
                $financialReport = $this->financialSummaryService->build(
                    $request->input('financial_year_id'),
                    $request->input('financial_term_id') ?: null,
                );
            } catch (\Throwable $e) {
                Log::error('[ReportController::index/financial] ' . $e->getMessage());
                session()->flash('error', 'Could not load financial summary.');
            }
        }

        if ($activeTab === 'academic') {
            $exams = Exam::with('term')->orderBy('name')->get();

            if ($request->filled('class_id')) {
                $classId   = $request->input('class_id');
                $sectionId = $request->input('section_id') ?: null;
                $examId    = $request->input('exam_id');
                $termId    = $request->input('term_id');

                try {
                    if ($examId) {
                        $analyticsReport = $this->examAnalyticsService->buildSubjectReport($examId, $classId, $sectionId);
                        // Infer term from exam if no term_id supplied
                        if (! $termId) {
                            $termId = $analyticsReport['exam']?->term_id;
                        }
                    }

                    if ($termId) {
                        $trendData = $this->examAnalyticsService->buildClassTrend($classId, $sectionId, $termId);
                    }
                } catch (\Throwable $e) {
                    Log::error('[ReportController::index/academic] ' . $e->getMessage());
                    session()->flash('error', 'Could not load academic analytics.');
                }
            }
        }

        return view('tenant.reports.index', [
            'classes'                  => $classes,
            'terms'                    => $terms,
            'academicYears'            => $academicYears,
            'exams'                    => $exams,
            'activeTab'                => $activeTab,
            'attendanceReport'         => $attendanceReport,
            'feeReport'                => $feeReport,
            'analyticsReport'          => $analyticsReport,
            'financialReport'          => $financialReport,
            'trendData'                => $trendData,
            'absenteesReport'          => $absenteesReport,
            'selectedThreshold'        => $selectedThreshold,
            'selectedClassId'          => $request->input('class_id', ''),
            'selectedSection'          => $request->input('section_id', ''),
            'dateFrom'                 => $request->input('date_from', ''),
            'dateTo'                   => $request->input('date_to', ''),
            'selectedTermId'           => $request->input('term_id', ''),
            'selectedExamId'           => $request->input('exam_id', ''),
            'selectedFinancialYearId'  => $selectedFinancialYearId,
            'selectedFinancialTermId'  => $selectedFinancialTermId,
        ]);
    }

    public function attendancePdf(Request $request): StreamedResponse|RedirectResponse
    {
        $validated = $request->validate([
            'class_id'   => ['required', 'string'],
            'section_id' => ['nullable', 'string'],
            'date_from'  => ['required', 'date'],
            'date_to'    => ['required', 'date', 'after_or_equal:date_from'],
        ]);

        $result = $this->attendanceService->build(
            $validated['class_id'],
            $validated['section_id'] ?? null,
            Carbon::parse($validated['date_from']),
            Carbon::parse($validated['date_to'])
        );

        if (! $result['success']) {
            return back()->with('error', $result['error']);
        }

        try {
            $profile    = SchoolProfile::first();
            $logoBase64 = $this->encodeLogoBase64($profile);

            $pdf = Pdf::loadView('tenant.reports.attendance-pdf', [
                'report'     => $result['data'],
                'profile'    => $profile,
                'logoBase64' => $logoBase64,
            ])->setPaper('a4', 'portrait');

            $slug = $this->slugify($result['data']['class']->name);

            return $pdf->download("attendance-report-{$slug}.pdf");
        } catch (\Throwable $e) {
            Log::error('[ReportController::attendancePdf] ' . $e->getMessage());
            return back()->with('error', 'Could not generate PDF. Please try again.');
        }
    }

    public function attendanceExcel(Request $request): BinaryFileResponse|RedirectResponse
    {
        $validated = $request->validate([
            'class_id'   => ['required', 'string'],
            'section_id' => ['nullable', 'string'],
            'date_from'  => ['required', 'date'],
            'date_to'    => ['required', 'date', 'after_or_equal:date_from'],
        ]);

        $result = $this->attendanceService->build(
            $validated['class_id'],
            $validated['section_id'] ?? null,
            Carbon::parse($validated['date_from']),
            Carbon::parse($validated['date_to'])
        );

        if (! $result['success']) {
            return back()->with('error', $result['error']);
        }

        $slug = $this->slugify($result['data']['class']->name);

        return Excel::download(new AttendanceReportExport($result['data']), "attendance-{$slug}.xlsx");
    }

    public function feesPdf(Request $request): StreamedResponse|RedirectResponse
    {
        $validated = $request->validate(['term_id' => ['required', 'string']]);

        try {
            $report     = $this->buildFeeReport($validated['term_id']);
            $profile    = SchoolProfile::first();
            $logoBase64 = $this->encodeLogoBase64($profile);

            $pdf = Pdf::loadView('tenant.reports.fees-pdf', [
                'report'     => $report,
                'profile'    => $profile,
                'logoBase64' => $logoBase64,
            ])->setPaper('a4', 'landscape');

            $slug = $this->slugify($report['term']->name);

            return $pdf->download("fee-collection-{$slug}.pdf");
        } catch (\Throwable $e) {
            Log::error('[ReportController::feesPdf] ' . $e->getMessage());
            return back()->with('error', 'Could not generate PDF. Please try again.');
        }
    }

    public function feesExcel(Request $request): BinaryFileResponse|RedirectResponse
    {
        $validated = $request->validate(['term_id' => ['required', 'string']]);

        try {
            $report = $this->buildFeeReport($validated['term_id']);
            $slug   = $this->slugify($report['term']->name);

            return Excel::download(new FeeCollectionReportExport($report), "fee-collection-{$slug}.xlsx");
        } catch (\Throwable $e) {
            Log::error('[ReportController::feesExcel] ' . $e->getMessage());
            return back()->with('error', 'Could not generate Excel file. Please try again.');
        }
    }

    public function academicPdf(Request $request): StreamedResponse|RedirectResponse
    {
        $validated = $request->validate([
            'exam_id'    => ['required', 'uuid', 'exists:exams,id'],
            'class_id'   => ['required', 'string'],
            'section_id' => ['nullable', 'string'],
        ]);

        try {
            $report     = $this->examAnalyticsService->buildSubjectReport(
                $validated['exam_id'],
                $validated['class_id'],
                $validated['section_id'] ?? null,
            );
            $profile    = SchoolProfile::first();
            $logoBase64 = $this->encodeLogoBase64($profile);

            $pdf = Pdf::loadView('tenant.reports.academic-analytics-pdf', [
                'report'     => $report,
                'profile'    => $profile,
                'logoBase64' => $logoBase64,
            ])->setPaper('a4', 'portrait');

            $className = $report['class']?->name ?? 'class';
            $examName  = $report['exam']?->name ?? 'exam';
            $slug      = $this->slugify($className . '-' . $examName);

            return $pdf->download("academic-analytics-{$slug}.pdf");
        } catch (\Throwable $e) {
            Log::error('[ReportController::academicPdf] ' . $e->getMessage());

            return back()->with('error', 'Could not generate PDF. Please try again.');
        }
    }

    public function financialPdf(Request $request): StreamedResponse|RedirectResponse
    {
        $validated = $request->validate([
            'financial_year_id' => ['required', 'uuid', 'exists:academic_years,id'],
            'financial_term_id' => ['nullable', 'uuid', 'exists:terms,id'],
        ]);

        try {
            $report     = $this->financialSummaryService->build(
                $validated['financial_year_id'],
                $validated['financial_term_id'] ?? null,
            );
            $profile    = SchoolProfile::first();
            $logoBase64 = $this->encodeLogoBase64($profile);

            $label = $report['term']
                ? $report['term']->name
                : $report['academic_year']->name;

            $pdf = Pdf::loadView('tenant.reports.financial-pdf', [
                'report'     => $report,
                'profile'    => $profile,
                'logoBase64' => $logoBase64,
            ])->setPaper('a4', 'landscape');

            return $pdf->download('financial-summary-' . $this->slugify($label) . '.pdf');
        } catch (\Throwable $e) {
            Log::error('[ReportController::financialPdf] ' . $e->getMessage());
            return back()->with('error', 'Could not generate PDF. Please try again.');
        }
    }

    /** Aggregate fee payments vs expected amounts for all fee structures in a term. */
    private function buildFeeReport(string $termId): array
    {
        $term = Term::with('academicYear')->findOrFail($termId);

        $academicYearId = $term->academic_year_id;

        $feeStructures = FeeStructure::where(function ($q) use ($termId, $academicYearId) {
            $q->where(function ($q2) use ($termId) {
                $q2->where('billing_cycle', 'term')->where('term_id', $termId);
            });
            if ($academicYearId) {
                $q->orWhere(function ($q2) use ($academicYearId) {
                    $q2->where('billing_cycle', 'annual')->where('academic_year_id', $academicYearId);
                });
            }
            $q->orWhere(function ($q2) use ($termId) {
                $q2->whereNull('billing_cycle')->where('term_id', $termId);
            });
        })
        ->orderBy('billing_cycle')
        ->orderBy('target_class')
        ->orderBy('fee_item')
        ->get();

        if ($feeStructures->isEmpty()) {
            return [
                'term'              => $term,
                'rows'              => [],
                'total_expected'    => 0.0,
                'total_collected'   => 0.0,
                'total_outstanding' => 0.0,
            ];
        }

        $specificClassIds = $feeStructures->pluck('target_class')
            ->filter(fn ($v) => $v !== 'all')
            ->unique();

        $classes = SchoolClass::whereIn('id', $specificClassIds)->get()->keyBy('id');

        $totalStudentCount  = Student::where('status', 'active')->count();
        $classStudentCounts = Student::whereIn('class_id', $specificClassIds)
            ->where('status', 'active')
            ->selectRaw('class_id, COUNT(*) as count')
            ->groupBy('class_id')
            ->pluck('count', 'class_id');

        $paymentTotals = FeePayment::whereIn('fee_structure_id', $feeStructures->pluck('id'))
            ->selectRaw('fee_structure_id, SUM(amount) as total')
            ->groupBy('fee_structure_id')
            ->pluck('total', 'fee_structure_id');

        $rows           = [];
        $totalExpected  = 0.0;
        $totalCollected = 0.0;

        foreach ($feeStructures as $fs) {
            $isAll = $fs->target_class === 'all';

            $studentCount = $isAll
                ? $totalStudentCount
                : (int) ($classStudentCounts->get($fs->target_class) ?? 0);

            $collected   = (float) ($paymentTotals->get($fs->id) ?? 0);
            $expected    = (float) $fs->amount * $studentCount;
            $outstanding = max(0.0, $expected - $collected);

            $totalExpected  += $expected;
            $totalCollected += $collected;

            $rows[] = [
                'fee_structure' => $fs,
                'class'         => $isAll ? null : $classes->get($fs->target_class),
                'class_label'   => $isAll ? 'All Classes' : ($classes->get($fs->target_class)?->name ?? '—'),
                'student_count' => $studentCount,
                'expected'      => $expected,
                'collected'     => $collected,
                'outstanding'   => $outstanding,
            ];
        }

        return [
            'term'              => $term,
            'rows'              => $rows,
            'total_expected'    => $totalExpected,
            'total_collected'   => $totalCollected,
            'total_outstanding' => max(0.0, $totalExpected - $totalCollected),
        ];
    }

    private function encodeLogoBase64(?SchoolProfile $profile): ?string
    {
        if (! $profile?->logo_path) {
            return null;
        }

        try {
            $ext  = pathinfo($profile->logo_path, PATHINFO_EXTENSION);
            $data = Storage::disk('public')->get($profile->logo_path);

            return "data:image/{$ext};base64," . base64_encode($data);
        } catch (\Throwable) {
            return null;
        }
    }

    private function slugify(string $name): string
    {
        return strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
    }
}
