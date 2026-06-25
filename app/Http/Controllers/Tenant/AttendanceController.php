<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\SaveAttendanceRequest;
use App\Http\Requests\Tenant\SaveStaffAttendanceRequest;
use App\Models\Tenant\Attendance;
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\Section;
use App\Models\Tenant\Staff;
use App\Models\Tenant\StaffAttendance;
use App\Models\Tenant\SchoolProfile;
use App\Models\Tenant\SubjectTeacherAssignment;
use App\Models\Tenant\Student;
use App\Models\Tenant\Term;
use App\Notifications\AbsenceAlert;
use App\Notifications\LowAttendanceAlert;
use App\Services\AttendanceReportService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

final class AttendanceController extends Controller
{
    public function __construct(
        private readonly AttendanceReportService $attendanceReportService,
    ) {}

    public function index(): View
    {
        $user        = Auth::user();
        $staffRecord = Staff::where('user_id', $user->id)->first();

        if ($user->can('settings.manage')) {
            $classes = SchoolClass::with('sections')->orderBy('order')->get();
        } else {
            $assignedClassIds = SubjectTeacherAssignment::where('staff_id', $staffRecord?->id)
                ->pluck('class_id')
                ->unique();
            $classes = SchoolClass::with('sections')
                ->whereIn('id', $assignedClassIds)
                ->orderBy('order')
                ->get();
        }

        $classId   = request('class_id');
        $sectionId = request('section_id');
        $date      = request('date', now()->toDateString());

        $students        = collect();
        $existingRecords = collect();
        $selectedClass   = null;
        $selectedSection = null;

        if ($classId) {
            $selectedClass   = $classes->firstWhere('id', $classId);
            $selectedSection = $sectionId ? Section::find($sectionId) : null;

            $query = Student::where('class_id', $classId)
                ->where('status', 'active')
                ->orderBy('full_name');

            if ($sectionId) {
                $query->where('section_id', $sectionId);
            } elseif ($selectedClass && $selectedClass->sections->isNotEmpty()) {
                // Class has sections but none chosen — require section selection first
                $query->whereRaw('1 = 0');
            }

            $students = $query->get();

            if ($students->isNotEmpty()) {
                $existingRecords = Attendance::where('date', $date)
                    ->whereIn('student_id', $students->pluck('id'))
                    ->get()
                    ->keyBy('student_id');
            }
        }

        return view('tenant.attendance.index', compact(
            'classes',
            'students',
            'existingRecords',
            'selectedClass',
            'selectedSection',
            'date',
            'classId',
            'sectionId',
        ));
    }

    public function save(SaveAttendanceRequest $request): RedirectResponse
    {
        try {
            $data    = $request->validated();
            $userId  = Auth::id();
            $profile = SchoolProfile::first();

            foreach ($data['statuses'] as $studentId => $status) {
                if (!$status) {
                    continue;
                }

                Attendance::updateOrCreate(
                    ['student_id' => $studentId, 'date' => $data['date']],
                    ['status' => $status, 'marked_by' => $userId]
                );

                if ($status === 'absent' && $profile?->isNotificationEnabled('absent_alert')) {
                    $student = Student::find($studentId);
                    if ($student?->guardian_email) {
                        Notification::route('mail', $student->guardian_email)
                            ->notify(new AbsenceAlert($student, $data['date']));
                    }
                }
            }

            return back()->with('success', 'Attendance saved for ' . Carbon::parse($data['date'])->format('d M Y') . '.');
        } catch (\Throwable $e) {
            \Log::error('[attendance.save] ' . $e->getMessage());

            return back()->with('error', 'Could not save attendance. Please try again.');
        }
    }

    public function report(): View
    {
        $classes = SchoolClass::with('sections')->orderBy('order')->get();

        $classId   = request('class_id');
        $sectionId = request('section_id');
        $studentId = request('student_id');
        $month     = request('month', now()->format('Y-m'));

        [$year, $monthNum] = explode('-', $month);

        $studentsForDropdown = collect();
        $selectedStudent     = null;
        $daysInMonth         = [];
        $summary             = ['present' => 0, 'absent' => 0, 'late' => 0, 'unmarked' => 0];

        if ($classId) {
            $q = Student::where('class_id', $classId)->where('status', 'active')->orderBy('full_name');
            if ($sectionId) {
                $q->where('section_id', $sectionId);
            }
            $studentsForDropdown = $q->get();
        }

        if ($studentId) {
            $selectedStudent = Student::with(['schoolClass', 'section'])->find($studentId);

            if ($selectedStudent) {
                $attendanceByDate = Attendance::where('student_id', $studentId)
                    ->whereYear('date', $year)
                    ->whereMonth('date', $monthNum)
                    ->get()
                    ->keyBy(fn ($a) => $a->date->format('Y-m-d'));

                $startOfMonth = Carbon::create((int) $year, (int) $monthNum, 1);
                $endOfMonth   = $startOfMonth->copy()->endOfMonth();
                $today        = now()->toDateString();

                $current = $startOfMonth->copy();
                while ($current->lte($endOfMonth) && $current->toDateString() <= $today) {
                    $dateStr = $current->toDateString();
                    $record  = $attendanceByDate->get($dateStr);

                    $daysInMonth[] = [
                        'date'   => $dateStr,
                        'label'  => $current->format('d M'),
                        'day'    => $current->format('D'),
                        'status' => $record ? $record->status : null,
                    ];

                    if ($record) {
                        $summary[$record->status]++;
                    } else {
                        $summary['unmarked']++;
                    }

                    $current->addDay();
                }
            }
        }

        $prevMonth = Carbon::create((int) $year, (int) $monthNum, 1)->subMonth()->format('Y-m');
        $nextMonth = Carbon::create((int) $year, (int) $monthNum, 1)->addMonth()->format('Y-m');

        return view('tenant.attendance.report', compact(
            'classes',
            'studentsForDropdown',
            'selectedStudent',
            'classId',
            'sectionId',
            'studentId',
            'month',
            'prevMonth',
            'nextMonth',
            'daysInMonth',
            'summary',
        ));
    }

    public function staff(): View
    {
        $date = request('date', now()->toDateString());

        $staffList = Staff::with('user')
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get();

        $existingRecords = collect();

        if ($staffList->isNotEmpty()) {
            $existingRecords = StaffAttendance::where('date', $date)
                ->whereIn('staff_id', $staffList->pluck('id'))
                ->get()
                ->keyBy('staff_id');
        }

        return view('tenant.attendance.staff', compact(
            'staffList',
            'existingRecords',
            'date',
        ));
    }

    public function saveStaff(SaveStaffAttendanceRequest $request): RedirectResponse
    {
        try {
            $data   = $request->validated();
            $userId = Auth::id();

            foreach ($data['statuses'] as $staffId => $status) {
                if (!$status) {
                    continue;
                }

                StaffAttendance::updateOrCreate(
                    ['staff_id' => $staffId, 'date' => $data['date']],
                    ['status' => $status, 'marked_by' => $userId]
                );
            }

            return back()->with('success', 'Staff attendance saved for ' . Carbon::parse($data['date'])->format('d M Y') . '.');
        } catch (\Throwable $e) {
            \Log::error('[attendance.saveStaff] ' . $e->getMessage());

            return back()->with('error', 'Could not save staff attendance. Please try again.');
        }
    }

    public function notifyGuardian(Request $request, Student $student): RedirectResponse
    {
        abort_unless($request->user()->can('attendance.view'), 403);

        $validated = $request->validate([
            'term_id'         => ['required', 'uuid', 'exists:terms,id'],
            'percent_present' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        if (! $student->guardian_email) {
            return back()->with('error', "No guardian email on file for {$student->full_name}.");
        }

        $key = 'low-attendance-alert:' . $student->id . ':' . Auth::id();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            return back()->with('error', 'Too many notifications sent for this student. Please wait before retrying.');
        }
        RateLimiter::hit($key, 3600);

        try {
            $term = Term::findOrFail($validated['term_id']);
            Notification::route('mail', $student->guardian_email)
                ->notify(new LowAttendanceAlert($student, (float) $validated['percent_present'], $term->name));

            return back()->with('success', "Low attendance alert sent to guardian of {$student->full_name}.");
        } catch (\Throwable $e) {
            \Log::error('[AttendanceController::notifyGuardian] ' . $e->getMessage());

            return back()->with('error', 'Could not send notification. Please try again.');
        }
    }

    public function notifyAll(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('attendance.view'), 403);

        $validated = $request->validate([
            'term_id'    => ['required', 'uuid', 'exists:terms,id'],
            'class_id'   => ['required', 'string'],
            'section_id' => ['nullable', 'string'],
            'threshold'  => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $bulkKey = 'low-attendance-bulk:' . Auth::id();
        if (RateLimiter::tooManyAttempts($bulkKey, 5)) {
            return back()->with('error', 'Bulk notification rate limit reached. Please wait before sending again.');
        }
        RateLimiter::hit($bulkKey, 3600);

        try {
            $result = $this->attendanceReportService->buildChronicAbsentees(
                $validated['term_id'],
                $validated['class_id'],
                $validated['section_id'] ?? null,
                (int) $validated['threshold'],
            );

            if (! $result['success']) {
                return back()->with('error', $result['error']);
            }

            $term  = $result['data']['term'];
            $sent  = 0;
            foreach ($result['data']['rows'] as $row) {
                $student = $row['student'];
                if ($student->guardian_email) {
                    Notification::route('mail', $student->guardian_email)
                        ->notify(new LowAttendanceAlert($student, $row['percent_present'], $term->name));
                    $sent++;
                }
            }

            return back()->with('success', "Low attendance alerts sent to {$sent} guardian(s).");
        } catch (\Throwable $e) {
            \Log::error('[AttendanceController::notifyAll] ' . $e->getMessage());

            return back()->with('error', 'Could not send bulk notifications. Please try again.');
        }
    }
}
