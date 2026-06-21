<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Exports\AttendanceReportExport;
use App\Exports\FeeCollectionReportExport;
use App\Http\Controllers\Controller;
use App\Models\Tenant\FeePayment;
use App\Models\Tenant\FeeStructure;
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\SchoolProfile;
use App\Models\Tenant\Student;
use App\Models\Tenant\Term;
use App\Services\AttendanceReportService;
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
    public function __construct(private readonly AttendanceReportService $attendanceService) {}

    public function index(Request $request): View
    {
        $classes   = SchoolClass::with('sections')->orderBy('order')->get();
        $terms     = Term::with('academicYear')->latest()->get();
        $activeTab = $request->query('tab', 'attendance');

        $attendanceReport = null;
        $feeReport        = null;

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

        return view('tenant.reports.index', [
            'classes'          => $classes,
            'terms'            => $terms,
            'activeTab'        => $activeTab,
            'attendanceReport' => $attendanceReport,
            'feeReport'        => $feeReport,
            'selectedClassId'  => $request->input('class_id', ''),
            'selectedSection'  => $request->input('section_id', ''),
            'dateFrom'         => $request->input('date_from', ''),
            'dateTo'           => $request->input('date_to', ''),
            'selectedTermId'   => $request->input('term_id', ''),
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

    /** Aggregate fee payments vs expected amounts for all fee structures in a term. */
    private function buildFeeReport(string $termId): array
    {
        $term = Term::with('academicYear')->findOrFail($termId);

        $feeStructures = FeeStructure::with('schoolClass')
            ->where('term_id', $termId)
            ->orderBy('class_id')
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

        $classIds = $feeStructures->pluck('class_id')->unique();

        $studentCounts = Student::whereIn('class_id', $classIds)
            ->where('status', 'active')
            ->selectRaw('class_id, COUNT(*) as count')
            ->groupBy('class_id')
            ->pluck('count', 'class_id');

        $paymentTotals = FeePayment::whereIn('fee_structure_id', $feeStructures->pluck('id'))
            ->selectRaw('fee_structure_id, SUM(amount) as total')
            ->groupBy('fee_structure_id')
            ->pluck('total', 'fee_structure_id');

        $rows          = [];
        $totalExpected  = 0.0;
        $totalCollected = 0.0;

        foreach ($feeStructures as $fs) {
            $studentCount = (int) ($studentCounts->get($fs->class_id) ?? 0);
            $collected    = (float) ($paymentTotals->get($fs->id) ?? 0);
            $expected     = (float) $fs->amount * $studentCount;
            $outstanding  = max(0.0, $expected - $collected);

            $totalExpected  += $expected;
            $totalCollected += $collected;

            $rows[] = [
                'fee_structure' => $fs,
                'class'         => $fs->schoolClass,
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
