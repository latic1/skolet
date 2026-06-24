<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Staff;
use App\Models\Tenant\SubjectTeacherAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class SubjectAssignmentController extends Controller
{
    public function store(Request $request, Staff $staff): RedirectResponse
    {
        $host = $request->getSchemeAndHttpHost();

        $data = $request->validate([
            'subject_id' => ['required', 'uuid', 'exists:subjects,id'],
            'class_id'   => ['required', 'uuid', 'exists:school_classes,id'],
            'section_id' => ['nullable', 'uuid', 'exists:sections,id'],
        ]);

        $sectionId = $data['section_id'] ?: null;

        $exists = SubjectTeacherAssignment::where('staff_id', $staff->id)
            ->where('subject_id', $data['subject_id'])
            ->where('class_id', $data['class_id'])
            ->where(fn ($q) => $sectionId
                ? $q->where('section_id', $sectionId)
                : $q->whereNull('section_id'))
            ->exists();

        if ($exists) {
            return redirect("{$host}/staff/{$staff->id}")
                ->with('error', 'This assignment already exists.');
        }

        try {
            SubjectTeacherAssignment::create([
                'staff_id'   => $staff->id,
                'subject_id' => $data['subject_id'],
                'class_id'   => $data['class_id'],
                'section_id' => $sectionId,
            ]);

            return redirect("{$host}/staff/{$staff->id}")
                ->with('success', 'Assignment added.');
        } catch (\Throwable $e) {
            \Log::error('[SubjectAssignment.store] ' . $e->getMessage());

            return redirect("{$host}/staff/{$staff->id}")
                ->with('error', 'Could not add assignment. Please try again.');
        }
    }

    public function destroy(SubjectTeacherAssignment $assignment): RedirectResponse
    {
        $host    = request()->getSchemeAndHttpHost();
        $staffId = $assignment->staff_id;

        try {
            $assignment->delete();

            return redirect("{$host}/staff/{$staffId}")
                ->with('success', 'Assignment removed.');
        } catch (\Throwable $e) {
            \Log::error('[SubjectAssignment.destroy] ' . $e->getMessage());

            return redirect("{$host}/staff/{$staffId}")
                ->with('error', 'Could not remove assignment. Please try again.');
        }
    }
}
