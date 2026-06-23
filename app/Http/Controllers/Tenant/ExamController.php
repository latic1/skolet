<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\SaveMarksRequest;
use App\Http\Requests\Tenant\StoreExamRequest;
use App\Http\Requests\Tenant\UpdateExamRequest;
use App\Models\Tenant\Exam;
use App\Models\Tenant\Term;
use App\Models\Tenant\ExamResult;
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\SchoolProfile;
use App\Models\Tenant\Section;
use App\Models\Tenant\Staff;
use App\Models\Tenant\Student;
use App\Models\Tenant\Subject;
use App\Models\Tenant\Timetable;
use App\Notifications\ExamResultsPublished;
use Illuminate\Support\Facades\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class ExamController extends Controller
{
    public function index(): View
    {
        $exams = Exam::with('term.academicYear')->latest()->get();
        $terms = Term::with('academicYear')
            ->join('academic_years', 'terms.academic_year_id', '=', 'academic_years.id')
            ->orderByDesc('academic_years.start_date')
            ->orderBy('terms.name')
            ->select('terms.*')
            ->get();

        return view('tenant.exams.index', compact('exams', 'terms'));
    }

    public function store(StoreExamRequest $request): RedirectResponse
    {
        $host = $request->getSchemeAndHttpHost();

        try {
            Exam::create($request->validated());

            return redirect($host . '/exams')->with('success', 'Exam created successfully.');
        } catch (\Throwable $e) {
            \Log::error('[exams.store] ' . $e->getMessage());

            return redirect($host . '/exams')->with('error', 'Could not create exam. Please try again.');
        }
    }

    public function update(UpdateExamRequest $request, Exam $exam): RedirectResponse
    {
        $host = $request->getSchemeAndHttpHost();

        try {
            $exam->update($request->validated());

            return redirect($host . '/exams')->with('success', 'Exam updated successfully.');
        } catch (\Throwable $e) {
            \Log::error('[exams.update] ' . $e->getMessage());

            return redirect($host . '/exams')->with('error', 'Could not update exam. Please try again.');
        }
    }

    public function destroy(Exam $exam): RedirectResponse
    {
        $host = request()->getSchemeAndHttpHost();

        try {
            $exam->delete();

            return redirect($host . '/exams')->with('success', 'Exam deleted.');
        } catch (\Throwable $e) {
            \Log::error('[exams.destroy] ' . $e->getMessage());

            return redirect($host . '/exams')->with('error', 'Could not delete exam. Please try again.');
        }
    }

    public function marks(Request $request): View
    {
        $user = Auth::user();

        $examId    = $request->input('exam_id');
        $classId   = $request->input('class_id');
        $sectionId = $request->input('section_id');
        $subjectId = $request->input('subject_id');

        $exams   = Exam::with('term.academicYear')->latest()->get();
        $classes = SchoolClass::with('sections')->orderBy('order')->get();
        $subjects = Subject::orderBy('name')->get();

        // Determine if the user has admin-level access (can manage all classes/subjects)
        $canManageAll = $user->can('settings.manage');
        $staffRecord  = Staff::where('user_id', $user->id)->first();

        // Build teacher assignment map: [{class_id, section_id, subject_id}]
        $teacherAssignments = collect();
        if (!$canManageAll && $staffRecord) {
            $teacherAssignments = Timetable::where('teacher_id', $staffRecord->id)
                ->get(['class_id', 'section_id', 'subject_id'])
                ->map(fn ($t) => [
                    'class_id'   => $t->class_id,
                    'section_id' => $t->section_id,
                    'subject_id' => $t->subject_id,
                ])
                ->values();
        }

        $students        = collect();
        $existingMarks   = collect();
        $selectedClass   = null;
        $selectedSection = null;
        $selectedExam    = null;
        $selectedSubject = null;
        $accessDenied    = false;

        if ($examId && $classId && $subjectId) {
            // Server-side access check for teachers
            if (!$canManageAll && $staffRecord) {
                $query = Timetable::where('teacher_id', $staffRecord->id)
                    ->where('class_id', $classId)
                    ->where('subject_id', $subjectId);

                if ($sectionId) {
                    $query->where('section_id', $sectionId);
                }

                $accessDenied = !$query->exists();
            }

            if (!$accessDenied) {
                $selectedExam    = $exams->firstWhere('id', $examId);
                $selectedClass   = $classes->firstWhere('id', $classId);
                $selectedSection = $sectionId ? Section::find($sectionId) : null;
                $selectedSubject = $subjects->firstWhere('id', $subjectId);

                $studentQuery = Student::where('class_id', $classId)
                    ->where('status', 'active')
                    ->orderBy('full_name');

                if ($sectionId) {
                    $studentQuery->where('section_id', $sectionId);
                } elseif ($selectedClass && $selectedClass->sections->isNotEmpty()) {
                    // Class has sections but none selected — require selection
                    $studentQuery->whereRaw('1 = 0');
                }

                $students = $studentQuery->get();

                if ($students->isNotEmpty()) {
                    $existingMarks = ExamResult::where('exam_id', $examId)
                        ->where('subject_id', $subjectId)
                        ->whereIn('student_id', $students->pluck('id'))
                        ->get()
                        ->keyBy('student_id');
                }
            }
        }

        return view('tenant.exams.marks', compact(
            'exams',
            'classes',
            'subjects',
            'students',
            'existingMarks',
            'selectedExam',
            'selectedClass',
            'selectedSection',
            'selectedSubject',
            'examId',
            'classId',
            'sectionId',
            'subjectId',
            'canManageAll',
            'teacherAssignments',
            'accessDenied',
        ));
    }

    public function publish(Exam $exam): RedirectResponse
    {
        $host = request()->getSchemeAndHttpHost();

        try {
            $exam->update(['is_published' => true]);

            $profile = SchoolProfile::first();
            if ($profile?->isNotificationEnabled('exam_results_published')) {
                $loginUrl = request()->getSchemeAndHttpHost() . '/login';

                ExamResult::where('exam_id', $exam->id)
                    ->with('student.user')
                    ->get()
                    ->each(function (ExamResult $result) use ($exam, $loginUrl): void {
                        $student = $result->student;
                        $email   = $student?->user?->email;
                        if ($email) {
                            Notification::route('mail', $email)
                                ->notify(new ExamResultsPublished($exam, $student, $loginUrl));
                        }
                    });
            }

            return redirect($host . '/exams')->with('success', "'{$exam->name}' has been published. Students and parents can now view results.");
        } catch (\Throwable $e) {
            \Log::error('[exams.publish] ' . $e->getMessage());

            return redirect($host . '/exams')->with('error', 'Could not publish exam. Please try again.');
        }
    }

    public function saveMarks(SaveMarksRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $data = $request->validated();

        try {
            // Server-side teacher restriction
            $canManageAll = $user->can('settings.manage');
            $staffRecord  = Staff::where('user_id', $user->id)->first();

            if (!$canManageAll && $staffRecord) {
                $q = Timetable::where('teacher_id', $staffRecord->id)
                    ->where('class_id', $data['class_id'])
                    ->where('subject_id', $data['subject_id']);

                if (!empty($data['section_id'])) {
                    $q->where('section_id', $data['section_id']);
                }

                if (!$q->exists()) {
                    return back()->with('error', 'You are not assigned to teach this subject for this class.');
                }
            }

            $scale = SchoolProfile::first()?->grading_scale
                ?? config('skolet.default_grading_scale', []);

            foreach ($data['marks'] as $studentId => $marks) {
                if ($marks === null || $marks === '') {
                    ExamResult::where([
                        'exam_id'    => $data['exam_id'],
                        'student_id' => $studentId,
                        'subject_id' => $data['subject_id'],
                    ])->delete();
                    continue;
                }

                $grade = ExamResult::computeGrade((float) $marks, $scale);

                ExamResult::updateOrCreate(
                    [
                        'exam_id'    => $data['exam_id'],
                        'student_id' => $studentId,
                        'subject_id' => $data['subject_id'],
                    ],
                    [
                        'marks' => $marks,
                        'grade' => $grade,
                    ]
                );
            }

            return back()->with('success', 'Marks saved successfully.');
        } catch (\Throwable $e) {
            \Log::error('[exams.saveMarks] ' . $e->getMessage());

            return back()->with('error', 'Could not save marks. Please try again.');
        }
    }
}
