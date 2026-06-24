<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Assignment;
use App\Models\Tenant\AssignmentSubmission;
use App\Models\Tenant\Staff;
use App\Models\Tenant\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

final class SubmissionController extends Controller
{
    public function store(Request $request, Assignment $assignment): RedirectResponse
    {
        $user    = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        if (! $student) {
            return back()->with('error', 'No student record linked to your account.');
        }

        // Confirm the assignment targets this student's class
        if ($assignment->class_id !== $student->class_id) {
            abort(403);
        }
        if ($assignment->section_id && $assignment->section_id !== $student->section_id) {
            abort(403);
        }

        $data = $request->validate([
            'submission_text' => ['nullable', 'string', 'max:10000'],
            'file'            => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,txt,jpg,jpeg,png,zip'],
        ]);

        if (empty($data['submission_text']) && ! $request->hasFile('file')) {
            return back()->with('error', 'Please provide text or upload a file.');
        }

        $filePath = null;
        if ($request->hasFile('file')) {
            $tenantId = tenant('id');
            $filePath = $request->file('file')->store(
                "tenant{$tenantId}/assignments/{$assignment->id}/{$student->id}",
                'local'
            );
        }

        try {
            AssignmentSubmission::updateOrCreate(
                ['assignment_id' => $assignment->id, 'student_id' => $student->id],
                [
                    'submission_text' => $data['submission_text'] ?? null,
                    'file_path'       => $filePath,
                    'submitted_at'    => now(),
                ]
            );

            return redirect(request()->getSchemeAndHttpHost() . '/assignments')
                ->with('success', 'Assignment submitted successfully.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Could not submit assignment. Please try again.');
        }
    }

    public function grade(Request $request, AssignmentSubmission $submission): RedirectResponse
    {
        $user  = Auth::user();
        $staff = Staff::where('user_id', $user->id)->first();

        // Only the teacher who owns the assignment (or admin) can grade
        if (! $user->can('settings.manage')) {
            if (! $staff || $submission->assignment->teacher_id !== $staff->id) {
                abort(403);
            }
        }

        $data = $request->validate([
            'marks_awarded' => ['nullable', 'numeric', 'min:0'],
            'feedback'      => ['nullable', 'string', 'max:2000'],
        ]);

        // Validate marks don't exceed total_marks if set
        $totalMarks = $submission->assignment->total_marks;
        if ($totalMarks && isset($data['marks_awarded']) && $data['marks_awarded'] > $totalMarks) {
            return back()->with('error', "Marks awarded cannot exceed total marks ({$totalMarks}).");
        }

        try {
            $submission->update($data);
            return redirect(request()->getSchemeAndHttpHost() . '/assignments')
                ->with('success', 'Submission graded.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Could not save grade. Please try again.');
        }
    }
}
