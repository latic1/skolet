<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Assignment;
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\Section;
use App\Models\Tenant\Staff;
use App\Models\Tenant\Student;
use App\Models\Tenant\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class AssignmentController extends Controller
{
    public function index(Request $request): View
    {
        $user         = Auth::user();
        $canManageAll = $user->can('settings.manage');

        // Teacher: only their own assignments; admin: all assignments
        $query = Assignment::with(['teacher', 'subject', 'schoolClass', 'section'])
            ->withCount(['submissions'])
            ->orderByDesc('due_date');

        if (! $canManageAll && $user->can('assignments.create')) {
            // Teacher — filter to own assignments
            $staff = Staff::where('user_id', $user->id)->first();
            if ($staff) {
                $query->where('teacher_id', $staff->id);
            }
        }

        // Admin class filter
        $filterClassId = $request->input('class_id');
        if ($filterClassId) {
            $query->where('class_id', $filterClassId);
        }

        // Admin teacher filter
        $filterTeacherId = $request->input('teacher_id');
        if ($filterTeacherId && $canManageAll) {
            $query->where('teacher_id', $filterTeacherId);
        }

        $assignments = $query->get();
        $classes     = SchoolClass::orderBy('order')->get();
        $subjects    = Subject::orderBy('name')->get();
        $sections    = Section::all();
        $staff       = $canManageAll ? Staff::orderBy('full_name')->get() : collect();

        // Student view data
        $studentAssignments    = collect();
        $submittedAssignments  = collect();
        $studentRecord         = null;

        if ($user->can('assignments.submit') && ! $user->can('assignments.create')) {
            $studentRecord = Student::where('user_id', $user->id)->first();
            if ($studentRecord) {
                $now = now();

                $studentAssignments = Assignment::with(['teacher', 'subject',
                    'submissions' => fn ($q) => $q->where('student_id', $studentRecord->id),
                ])
                    ->where('class_id', $studentRecord->class_id)
                    ->where(function ($q) use ($studentRecord) {
                        $q->whereNull('section_id')->orWhere('section_id', $studentRecord->section_id);
                    })
                    ->orderBy('due_date')
                    ->get();

                $pendingAssignments   = $studentAssignments->filter(
                    fn ($a) => $a->submissions->isEmpty() && $a->due_date >= $now
                );
                $submittedAssignments = $studentAssignments->filter(
                    fn ($a) => $a->submissions->isNotEmpty()
                );
                $overdueAssignments   = $studentAssignments->filter(
                    fn ($a) => $a->submissions->isEmpty() && $a->due_date < $now
                );

                return view('tenant.assignments.index', compact(
                    'pendingAssignments',
                    'submittedAssignments',
                    'overdueAssignments',
                    'studentRecord',
                ));
            }
        }

        // Parent view — show children's assignments (read-only)
        if ($user->hasRole('parent') && ! $user->can('assignments.submit')) {
            return view('tenant.assignments.index', [
                'assignments'         => collect(),
                'classes'             => $classes,
                'subjects'            => $subjects,
                'sections'            => $sections,
                'staff'               => $staff,
                'canManageAll'        => false,
                'filterClassId'       => null,
                'filterTeacherId'     => null,
                'pendingAssignments'  => collect(),
                'submittedAssignments'=> collect(),
                'overdueAssignments'  => collect(),
                'studentRecord'       => null,
                'isParent'            => true,
            ]);
        }

        return view('tenant.assignments.index', compact(
            'assignments',
            'classes',
            'subjects',
            'sections',
            'staff',
            'canManageAll',
            'filterClassId',
            'filterTeacherId',
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $user  = Auth::user();
        $staff = Staff::where('user_id', $user->id)->first();

        $data = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'subject_id'  => ['required', 'uuid', 'exists:subjects,id'],
            'class_id'    => ['required', 'uuid', 'exists:school_classes,id'],
            'section_id'  => ['nullable', 'uuid', 'exists:sections,id'],
            'due_date'    => ['required', 'date'],
            'total_marks' => ['nullable', 'numeric', 'min:0'],
        ]);

        // Admin can assign to any teacher via teacher_id; teacher defaults to themselves
        $canManageAll = $user->can('settings.manage');
        if ($canManageAll && $request->filled('teacher_id')) {
            $data['teacher_id'] = $request->input('teacher_id');
        } elseif ($staff) {
            $data['teacher_id'] = $staff->id;
        } else {
            return back()->withErrors(['error' => 'No staff record found for your account.']);
        }

        try {
            Assignment::create($data);
            return redirect(request()->getSchemeAndHttpHost() . '/assignments')
                ->with('success', 'Assignment created successfully.');
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Could not create assignment. Please try again.');
        }
    }

    public function update(Request $request, Assignment $assignment): RedirectResponse
    {
        $user = Auth::user();

        // Teachers can only edit their own assignments
        if (! $user->can('settings.manage')) {
            $staff = Staff::where('user_id', $user->id)->first();
            if (! $staff || $assignment->teacher_id !== $staff->id) {
                abort(403);
            }
        }

        $data = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'subject_id'  => ['required', 'uuid', 'exists:subjects,id'],
            'class_id'    => ['required', 'uuid', 'exists:school_classes,id'],
            'section_id'  => ['nullable', 'uuid', 'exists:sections,id'],
            'due_date'    => ['required', 'date'],
            'total_marks' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            $assignment->update($data);
            return redirect(request()->getSchemeAndHttpHost() . '/assignments')
                ->with('success', 'Assignment updated.');
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Could not update assignment. Please try again.');
        }
    }

    public function destroy(Assignment $assignment): RedirectResponse
    {
        $user = Auth::user();

        // Teachers can only delete their own assignments
        if (! $user->can('settings.manage')) {
            $staff = Staff::where('user_id', $user->id)->first();
            if (! $staff || $assignment->teacher_id !== $staff->id) {
                abort(403);
            }
        }

        try {
            $assignment->delete();
            return redirect(request()->getSchemeAndHttpHost() . '/assignments')
                ->with('success', 'Assignment deleted.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Could not delete assignment.');
        }
    }

    public function submissionFile(Request $request, Assignment $assignment): mixed
    {
        $user  = Auth::user();
        $staff = Staff::where('user_id', $user->id)->first();

        // Allow teacher who owns the assignment or admin
        $canView = $user->can('settings.manage')
            || ($staff && $assignment->teacher_id === $staff->id);

        if (! $canView) {
            // Students may download their own submission file
            $student = Student::where('user_id', $user->id)->first();
            $submission = $student
                ? $assignment->submissions()->where('student_id', $student->id)->first()
                : null;
            abort_unless($submission && $submission->file_path, 403);

            return response()->file(storage_path('app/' . $submission->file_path));
        }

        $submissionId = $request->input('submission_id');
        $submission   = $assignment->submissions()->findOrFail($submissionId);
        abort_unless($submission->file_path, 404);

        return response()->file(storage_path('app/' . $submission->file_path));
    }
}
