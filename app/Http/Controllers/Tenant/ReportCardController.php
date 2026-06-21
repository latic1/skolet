<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Exam;
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\Student;
use App\Services\ReportCardService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class ReportCardController extends Controller
{
    public function __construct(private readonly ReportCardService $service) {}

    /**
     * Report card preview page.
     *
     * Access rules:
     *  - School admin / teacher (settings.manage or exams.edit): sees all exams + all students.
     *  - Student / parent: sees only published exams; student is auto-resolved from their linked user.
     */
    public function preview(Request $request): View
    {
        $user         = Auth::user();
        $canManageAll = $user->can('settings.manage') || $user->can('exams.edit');

        // Build exam list scoped by role
        $examsQuery = Exam::with('term.academicYear')->latest();
        if (!$canManageAll) {
            $examsQuery->where('is_published', true);
        }
        $exams = $examsQuery->get();

        $classes   = SchoolClass::with('sections')->orderBy('order')->get();
        $students  = collect();
        $cardData  = null;
        $accessError = null;

        $examId    = $request->input('exam_id');
        $classId   = $request->input('class_id');
        $sectionId = $request->input('section_id');
        $studentId = $request->input('student_id');

        // For student/parent roles: auto-resolve the student record tied to this user
        $linkedStudent = null;
        if (!$canManageAll) {
            $linkedStudent = Student::where('user_id', $user->id)->first();
            if ($linkedStudent) {
                $studentId = $linkedStudent->id;
                $classId   = $linkedStudent->class_id;
            }
        }

        // Populate student dropdown for admins once class is chosen
        if ($canManageAll && $classId) {
            $studentQuery = Student::where('class_id', $classId)
                ->where('status', 'active')
                ->orderBy('full_name');

            if ($sectionId) {
                $studentQuery->where('section_id', $sectionId);
            }
            $students = $studentQuery->get();
        }

        // Build card when both exam + student are selected
        if ($examId && $studentId) {
            $exam    = Exam::find($examId);
            $student = Student::with(['schoolClass', 'section'])->find($studentId);

            if (!$exam || !$student) {
                $accessError = 'The selected exam or student could not be found.';
            } elseif (!$canManageAll && !$exam->is_published) {
                // Students/parents cannot see unpublished results
                $accessError = 'Results for this exam have not been published yet.';
            } elseif (!$canManageAll && $linkedStudent?->id !== $student->id) {
                // Students/parents can only see their own report card
                $accessError = 'You can only view your own report card.';
            } else {
                $cardData = $this->service->build($exam, $student);
            }
        }

        return view('tenant.exams.report-card', compact(
            'exams',
            'classes',
            'students',
            'cardData',
            'accessError',
            'examId',
            'classId',
            'sectionId',
            'studentId',
            'canManageAll',
            'linkedStudent',
        ));
    }

    /**
     * Download report card as PDF.
     * Same access rules as preview(); PDF is generated and streamed.
     */
    public function download(Request $request): Response|\Illuminate\Http\RedirectResponse
    {
        $user         = Auth::user();
        $canManageAll = $user->can('settings.manage') || $user->can('exams.edit');

        $examId    = $request->input('exam_id');
        $studentId = $request->input('student_id');

        if (!$examId || !$studentId) {
            return back()->with('error', 'Please select an exam and student first.');
        }

        $exam    = Exam::find($examId);
        $student = Student::with(['schoolClass', 'section'])->find($studentId);

        if (!$exam || !$student) {
            return back()->with('error', 'The selected exam or student could not be found.');
        }

        if (!$canManageAll && !$exam->is_published) {
            return back()->with('error', 'Results for this exam have not been published yet.');
        }

        if (!$canManageAll) {
            $linkedStudent = Student::where('user_id', $user->id)->first();
            if (!$linkedStudent || $linkedStudent->id !== $student->id) {
                return back()->with('error', 'You can only download your own report card.');
            }
        }

        try {
            $pdfPath  = $this->service->generatePdf($exam, $student);
            $filename = "report-card-{$student->admission_no}-{$exam->name}.pdf";
            $filename = preg_replace('/[^A-Za-z0-9\-_.]/', '-', $filename);

            return response()->file($pdfPath, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        } catch (\Throwable $e) {
            return back()->with('error', 'Could not generate the report card PDF. Please try again.');
        }
    }
}
