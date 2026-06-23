<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\AcademicYear;
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\Section;
use App\Models\Tenant\Student;
use App\Services\StudentPromotionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class StudentPromotionController extends Controller
{
    public function __construct(
        private readonly StudentPromotionService $service,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $currentYear = AcademicYear::where('is_current', true)->first();

        if (! $currentYear) {
            return redirect(request()->getSchemeAndHttpHost() . '/students')
                ->with('error', 'No current academic year is set. Please set a current year in Settings before running promotion.');
        }

        $classes            = SchoolClass::with('sections')->orderBy('order')->get();
        $selectedClass      = null;
        $selectedSection    = null;
        $students           = collect();
        $nextClass          = null;
        $isTopClass         = false;

        if ($request->filled('class_id')) {
            $selectedClass = SchoolClass::with('sections')->find($request->class_id);

            if ($selectedClass) {
                $selectedSection = $request->filled('section_id')
                    ? Section::find($request->section_id)
                    : null;

                $query = Student::with(['schoolClass', 'section'])
                    ->where('class_id', $selectedClass->id)
                    ->where('status', 'active')
                    ->orderBy('full_name');

                if ($selectedSection) {
                    $query->where('section_id', $selectedSection->id);
                }

                $students = $query->get();

                $nextClass  = SchoolClass::where('order', '>', $selectedClass->order)->orderBy('order')->first();
                $isTopClass = $nextClass === null;
            }
        }

        return view('tenant.students.promote', compact(
            'currentYear',
            'classes',
            'selectedClass',
            'selectedSection',
            'students',
            'nextClass',
            'isTopClass',
        ));
    }

    public function execute(Request $request): RedirectResponse
    {
        $host = request()->getSchemeAndHttpHost();

        $currentYear = AcademicYear::where('is_current', true)->first();

        if (! $currentYear) {
            return redirect($host . '/students')
                ->with('error', 'No current academic year is set. Promotion aborted.');
        }

        $request->validate([
            'outcomes'   => ['required', 'array', 'min:1'],
            'outcomes.*' => ['required', 'string', 'in:promoted,retained,graduated'],
        ]);

        $result = $this->service->promote($request->input('outcomes'), $currentYear);

        if (! empty($result['errors']) && $result['promoted'] === 0 && $result['retained'] === 0 && $result['graduated'] === 0) {
            return redirect($host . '/students/promote')
                ->with('promotion_errors', $result['errors'])
                ->with('error', 'Promotion failed. No changes were made.');
        }

        return redirect($host . '/students')
            ->with('promotion_result', $result);
    }
}
