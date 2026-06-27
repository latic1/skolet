<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\ClassRegister;
use App\Models\Tenant\LessonPlan;
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\SchoolProfile;
use App\Models\Tenant\Staff;
use App\Models\Tenant\Subject;
use App\Models\Tenant\SubjectTeacherAssignment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class RegisterController extends Controller
{
    public function index(Request $request): View
    {
        $canManage    = Auth::user()->can('register.manage');
        $currentStaff = Staff::where('user_id', Auth::id())->first();
        $activeTab    = $request->get('tab', 'register');

        // Build available classes and subjects depending on role
        $allStaff = collect();

        if ($canManage) {
            $allStaff = Staff::with('user')->where('status', 'active')->orderBy('full_name')->get();
            $classes  = SchoolClass::with('sections')->orderBy('order')->get();
            $subjects = Subject::orderBy('name')->get();
        } else {
            $staffId     = $currentStaff?->id;
            $assignments = SubjectTeacherAssignment::where('staff_id', $staffId)
                ->with(['schoolClass.sections', 'subject'])
                ->get();

            $classIds = $assignments->pluck('class_id')->unique();
            $classes  = SchoolClass::whereIn('id', $classIds)->with('sections')->orderBy('order')->get();
            $subjects = $assignments->pluck('subject')->filter()->unique('id')->values();
        }

        // ── Class Register tab ──────────────────────────────
        $existingEntry    = null;
        $registerHistory  = collect();
        $selectedClassId   = $request->get('reg_class_id', '');
        $selectedSectionId = $request->get('reg_section_id', '');
        $selectedSubjectId = $request->get('reg_subject_id', '');
        $selectedDate      = $request->get('reg_date', now()->toDateString());
        $selectedTeacherId = $request->get('reg_teacher_id', $currentStaff?->id ?? '');

        if ($activeTab === 'register' && $selectedClassId && $selectedSubjectId) {
            $teacherId = $canManage ? ($selectedTeacherId ?: $currentStaff?->id) : $currentStaff?->id;

            if ($teacherId) {
                $baseQuery = ClassRegister::where('teacher_id', $teacherId)
                    ->where('class_id', $selectedClassId)
                    ->where('subject_id', $selectedSubjectId)
                    ->where('section_id', $selectedSectionId ?: null);

                $existingEntry   = (clone $baseQuery)->where('date', $selectedDate)->first();
                $registerHistory = (clone $baseQuery)
                    ->with(['schoolClass', 'section', 'subject'])
                    ->where('date', '!=', $selectedDate)
                    ->latest('date')
                    ->take(30)
                    ->get();
            }
        }

        // ── Lesson Plans tab ────────────────────────────────
        $weekStart    = Carbon::parse($request->get('week_start', now()->startOfWeek(Carbon::MONDAY)->toDateString()))
            ->startOfWeek(Carbon::MONDAY);
        $lessonPlans  = collect();
        $planTeacherId = $request->get('plan_teacher_id', $currentStaff?->id ?? '');

        if ($activeTab === 'plans') {
            $planQuery = LessonPlan::with(['teacher', 'subject', 'schoolClass', 'section'])
                ->where('week_start', $weekStart->toDateString());

            if ($canManage && $planTeacherId) {
                $planQuery->where('teacher_id', $planTeacherId);
            } elseif (! $canManage) {
                $planQuery->where('teacher_id', $currentStaff?->id);
            }

            $lessonPlans = $planQuery->orderBy('class_id')->get();
        }

        return view('tenant.register.index', [
            'canManage'         => $canManage,
            'currentStaff'      => $currentStaff,
            'allStaff'          => $allStaff,
            'classes'           => $classes,
            'subjects'          => $subjects,
            'activeTab'         => $activeTab,
            'existingEntry'     => $existingEntry,
            'registerHistory'   => $registerHistory,
            'selectedClassId'   => $selectedClassId,
            'selectedSectionId' => $selectedSectionId,
            'selectedSubjectId' => $selectedSubjectId,
            'selectedDate'      => $selectedDate,
            'selectedTeacherId' => $selectedTeacherId,
            'weekStart'         => $weekStart,
            'lessonPlans'       => $lessonPlans,
            'planTeacherId'     => $planTeacherId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'class_id'      => ['required', 'uuid', 'exists:school_classes,id'],
            'section_id'    => ['nullable', 'uuid', 'exists:sections,id'],
            'subject_id'    => ['required', 'uuid', 'exists:subjects,id'],
            'date'          => ['required', 'date', 'before_or_equal:today'],
            'topic_covered' => ['required', 'string', 'max:255'],
            'notes'         => ['nullable', 'string', 'max:2000'],
        ]);

        $canManage    = Auth::user()->can('register.manage');
        $currentStaff = Staff::where('user_id', Auth::id())->firstOrFail();

        ClassRegister::updateOrCreate(
            [
                'teacher_id' => $currentStaff->id,
                'class_id'   => $validated['class_id'],
                'section_id' => $validated['section_id'] ?? null,
                'subject_id' => $validated['subject_id'],
                'date'       => $validated['date'],
            ],
            [
                'topic_covered' => $validated['topic_covered'],
                'notes'         => $validated['notes'] ?? null,
            ]
        );

        return redirect()->route('tenant.register.index', [
            'tab'            => 'register',
            'reg_class_id'   => $validated['class_id'],
            'reg_section_id' => $validated['section_id'] ?? '',
            'reg_subject_id' => $validated['subject_id'],
            'reg_date'       => $validated['date'],
        ])->with('success', 'Register entry saved for ' . Carbon::parse($validated['date'])->format('d M Y') . '.');
    }

    public function exportPdf(Request $request, Staff $staff, string $month): StreamedResponse|RedirectResponse
    {
        abort_unless(
            Auth::user()->can('register.manage') || ($staff->user_id === Auth::id()),
            403
        );

        try {
            $from = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            $to   = $from->copy()->endOfMonth();
        } catch (\Throwable) {
            return back()->with('error', 'Invalid month format.');
        }

        try {
            $entries = ClassRegister::with(['schoolClass', 'section', 'subject'])
                ->where('teacher_id', $staff->id)
                ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
                ->orderBy('date')
                ->orderBy('class_id')
                ->get();

            $profile = SchoolProfile::first();

            $pdf = Pdf::loadView('tenant.register.pdf', [
                'staff'   => $staff,
                'entries' => $entries,
                'month'   => $from,
                'profile' => $profile,
            ])->setPaper('a4', 'portrait');

            $slug = $staff->full_name . '-' . $from->format('Y-m');
            $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $slug));

            return $pdf->download("register-{$slug}.pdf");
        } catch (\Throwable $e) {
            Log::error('[RegisterController::exportPdf] ' . $e->getMessage());
            return back()->with('error', 'Could not generate PDF. Please try again.');
        }
    }
}
