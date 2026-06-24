<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Attendance;
use App\Models\Tenant\Exam;
use App\Models\Tenant\ExamResult;
use App\Models\Tenant\Student;
use App\Models\Tenant\Term;
use App\Services\FeeStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class ParentPortalController extends Controller
{
    public function __construct(private readonly FeeStatusService $feeStatusService) {}

    public function index(Request $request): View
    {
        $user = Auth::user();

        $children = Student::with(['schoolClass', 'section'])
            ->whereHas('parents', fn ($q) => $q->where('user_id', $user->id))
            ->orderBy('full_name')
            ->get();

        $childId       = $request->input('child_id', $children->first()?->id);
        $selectedChild = $children->firstWhere('id', $childId);

        $feeItems          = [];
        $currentTerm       = null;
        $publishedExams    = collect();
        $attendanceSummary = collect();

        if ($selectedChild) {
            $currentTerm = Term::where('is_current', true)->first();
            $feeItems    = $this->feeStatusService->getStudentFeeItems($selectedChild, $currentTerm?->id);

            // Group published exam results by exam
            $publishedExams = Exam::with('term.academicYear')
                ->where('is_published', true)
                ->whereHas('results', fn ($q) => $q->where('student_id', $selectedChild->id))
                ->latest()
                ->get()
                ->map(function (Exam $exam) use ($selectedChild) {
                    $exam->studentResults = ExamResult::with('subject')
                        ->where('exam_id', $exam->id)
                        ->where('student_id', $selectedChild->id)
                        ->orderBy('subject_id')
                        ->get();

                    return $exam;
                });

            // Attendance this month
            $year     = now()->year;
            $monthNum = now()->month;
            $attendanceSummary = Attendance::where('student_id', $selectedChild->id)
                ->whereYear('date', $year)
                ->whereMonth('date', $monthNum)
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status');
        }

        return view('tenant.parents.portal', compact(
            'children',
            'selectedChild',
            'childId',
            'feeItems',
            'currentTerm',
            'publishedExams',
            'attendanceSummary',
        ));
    }
}
