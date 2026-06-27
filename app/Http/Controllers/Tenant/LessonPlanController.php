<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\LessonPlan;
use App\Models\Tenant\Staff;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

final class LessonPlanController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'class_id'   => ['required', 'uuid', 'exists:school_classes,id'],
            'section_id' => ['nullable', 'uuid', 'exists:sections,id'],
            'subject_id' => ['required', 'uuid', 'exists:subjects,id'],
            'week_start' => ['required', 'date'],
            'objectives' => ['nullable', 'string', 'max:2000'],
            'content'    => ['required', 'string', 'max:5000'],
        ]);

        $staff = Staff::where('user_id', Auth::id())->firstOrFail();

        $weekStart = Carbon::parse($validated['week_start'])->startOfWeek(Carbon::MONDAY)->toDateString();

        LessonPlan::updateOrCreate(
            [
                'teacher_id' => $staff->id,
                'subject_id' => $validated['subject_id'],
                'class_id'   => $validated['class_id'],
                'section_id' => $validated['section_id'] ?? null,
                'week_start' => $weekStart,
            ],
            [
                'objectives' => $validated['objectives'] ?? null,
                'content'    => $validated['content'],
            ]
        );

        return redirect()->route('tenant.register.index', [
            'tab'        => 'plans',
            'week_start' => $weekStart,
        ])->with('success', 'Lesson plan saved.');
    }

    public function update(Request $request, LessonPlan $lessonPlan): RedirectResponse
    {
        $canManage = Auth::user()->can('register.manage');
        $staff     = Staff::where('user_id', Auth::id())->first();

        abort_unless($canManage || $lessonPlan->teacher_id === $staff?->id, 403);

        $validated = $request->validate([
            'objectives' => ['nullable', 'string', 'max:2000'],
            'content'    => ['required', 'string', 'max:5000'],
        ]);

        $lessonPlan->update($validated);

        return redirect()->route('tenant.register.index', [
            'tab'        => 'plans',
            'week_start' => $lessonPlan->week_start->toDateString(),
        ])->with('success', 'Lesson plan updated.');
    }

    public function destroy(LessonPlan $lessonPlan): RedirectResponse
    {
        $canManage = Auth::user()->can('register.manage');
        $staff     = Staff::where('user_id', Auth::id())->first();

        abort_unless($canManage || $lessonPlan->teacher_id === $staff?->id, 403);

        $weekStart = $lessonPlan->week_start->toDateString();
        $lessonPlan->delete();

        return redirect()->route('tenant.register.index', [
            'tab'        => 'plans',
            'week_start' => $weekStart,
        ])->with('success', 'Lesson plan deleted.');
    }
}
