<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\Staff;
use App\Models\Tenant\Subject;
use App\Models\Tenant\Timetable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class TimetableController extends Controller
{
    private const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    private const PERIODS = [1, 2, 3, 4, 5, 6, 7, 8];

    public function index(Request $request): View
    {
        $classes  = SchoolClass::with('sections')->orderBy('order')->get();
        $subjects = Subject::orderBy('name')->get();
        $staff    = Staff::where('status', 'active')->orderBy('full_name')->get();

        $classId   = $request->query('class_id', '');
        $sectionId = $request->query('section_id', '');

        $entries         = collect();
        $selectedClass   = null;
        $selectedSection = null;

        if ($classId) {
            $selectedClass = $classes->firstWhere('id', $classId);

            if ($selectedClass && $sectionId) {
                $selectedSection = $selectedClass->sections->firstWhere('id', $sectionId);
            }

            $query = Timetable::with(['subject', 'teacher'])
                ->where('class_id', $classId);

            if ($sectionId) {
                $query->where('section_id', $sectionId);
            } else {
                $query->whereNull('section_id');
            }

            $entries = $query->get()->keyBy(fn ($e) => $e->day . '-' . $e->period);
        }

        $entriesForView = $entries->map(fn ($e) => [
            'id'           => $e->id,
            'subject_id'   => $e->subject_id,
            'teacher_id'   => $e->teacher_id,
            'subject_name' => $e->subject?->name ?? '',
            'teacher_name' => $e->teacher?->full_name ?? '',
        ])->toArray();

        return view('tenant.timetable.index', [
            'classes'         => $classes,
            'subjects'        => $subjects,
            'staff'           => $staff,
            'classId'         => $classId,
            'sectionId'       => $sectionId,
            'selectedClass'   => $selectedClass,
            'selectedSection' => $selectedSection,
            'entriesForView'  => $entriesForView,
            'days'            => self::DAYS,
            'periods'         => self::PERIODS,
            'host'            => $request->getSchemeAndHttpHost(),
        ]);
    }

    public function save(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'class_id'   => ['required', 'uuid', 'exists:school_classes,id'],
            'section_id' => ['nullable', 'uuid', 'exists:sections,id'],
            'subject_id' => ['required', 'uuid', 'exists:subjects,id'],
            'teacher_id' => ['required', 'uuid', 'exists:staff,id'],
            'day'        => ['required', 'in:' . implode(',', self::DAYS)],
            'period'     => ['required', 'integer', 'min:1', 'max:8'],
        ]);

        $classId   = $validated['class_id'];
        $sectionId = $validated['section_id'] ?? null;
        $teacherId = $validated['teacher_id'];
        $day       = $validated['day'];
        $period    = (int) $validated['period'];

        // Find the existing cell for this slot (used to exclude from conflict check below)
        $existingForSlot = Timetable::where('class_id', $classId)
            ->where(function ($q) use ($sectionId) {
                $sectionId ? $q->where('section_id', $sectionId) : $q->whereNull('section_id');
            })
            ->where('day', $day)
            ->where('period', $period)
            ->first();

        // Conflict: same teacher already assigned to a DIFFERENT class/section in this slot
        $conflict = Timetable::where('teacher_id', $teacherId)
            ->where('day', $day)
            ->where('period', $period)
            ->when($existingForSlot, fn ($q) => $q->where('id', '!=', $existingForSlot->id))
            ->with(['schoolClass', 'section'])
            ->first();

        $conflictMessage = null;

        if ($conflict) {
            $teacher       = Staff::find($teacherId);
            $conflictClass   = $conflict->schoolClass?->name ?? 'another class';
            $conflictSection = $conflict->section?->name ? ' — ' . $conflict->section->name : '';
            $conflictMessage = ($teacher?->full_name ?? 'This teacher') . ' is already assigned to '
                . $conflictClass . $conflictSection . ' on ' . $day . ', Period ' . $period;
        }

        try {
            $entry = Timetable::updateOrCreate(
                [
                    'class_id'   => $classId,
                    'section_id' => $sectionId,
                    'day'        => $day,
                    'period'     => $period,
                ],
                [
                    'subject_id' => $validated['subject_id'],
                    'teacher_id' => $teacherId,
                ]
            );

            $entry->load(['subject', 'teacher']);

            return response()->json([
                'success'  => true,
                'entry'    => [
                    'id'           => $entry->id,
                    'subject_id'   => $entry->subject_id,
                    'teacher_id'   => $entry->teacher_id,
                    'subject_name' => $entry->subject?->name ?? '',
                    'teacher_name' => $entry->teacher?->full_name ?? '',
                ],
                'conflict' => $conflictMessage,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => 'Failed to save. Please try again.'], 500);
        }
    }

    public function destroy(Timetable $timetable): JsonResponse
    {
        try {
            $timetable->delete();
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => 'Failed to clear cell.'], 500);
        }
    }

    public function teacher(Request $request): View
    {
        $isAdmin   = auth()->user()->can('settings.manage');
        $allStaff  = Staff::where('status', 'active')->orderBy('full_name')->get();
        $teacherId = $request->query('teacher_id', '');

        // Non-admins always see their own timetable
        if (! $isAdmin) {
            $myStaff   = Staff::where('user_id', auth()->id())->first();
            $teacherId = $myStaff?->id ?? '';
        }

        $selectedTeacher = null;
        $entries         = collect();

        if ($teacherId) {
            $selectedTeacher = $allStaff->firstWhere('id', $teacherId);
            $entries = Timetable::with(['subject', 'schoolClass', 'section'])
                ->where('teacher_id', $teacherId)
                ->get()
                ->keyBy(fn ($e) => $e->day . '-' . $e->period);
        }

        $entriesForView = $entries->map(fn ($e) => [
            'id'            => $e->id,
            'subject_name'  => $e->subject?->name ?? '',
            'class_name'    => $e->schoolClass?->name ?? '',
            'section_name'  => $e->section?->name ?? '',
        ])->toArray();

        return view('tenant.timetable.teacher', [
            'allStaff'        => $allStaff,
            'teacherId'       => $teacherId,
            'selectedTeacher' => $selectedTeacher,
            'entriesForView'  => $entriesForView,
            'days'            => self::DAYS,
            'periods'         => self::PERIODS,
            'isAdmin'         => $isAdmin,
            'host'            => $request->getSchemeAndHttpHost(),
        ]);
    }
}
