<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\AcademicYear;
use App\Models\Tenant\Announcement;
use App\Models\Tenant\Attendance;
use App\Models\Tenant\ExamResult;
use App\Models\Tenant\FeePayment;
use App\Models\Tenant\FeeStructure;
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\SchoolProfile;
use App\Models\Tenant\Staff;
use App\Models\Tenant\Student;
use App\Models\Tenant\Subject;
use App\Models\Tenant\Term;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $can = [
            'students'   => $user->can('students.view'),
            'staff'      => $user->can('staff.view'),
            'attendance' => $user->can('attendance.view'),
            'exams'      => $user->can('exams.view'),
            'fees'       => $user->can('fees.view'),
            'reports'    => $user->can('reports.view'),
            'settings'   => $user->can('settings.manage'),
        ];

        // ── Real counts ──────────────────────────────────────────────────────
        $totalStudents = Student::count();
        $totalStaff    = Staff::count();

        // ── Attendance today ─────────────────────────────────────────────────
        $today          = Carbon::today()->toDateString();
        $markedToday    = Attendance::where('date', $today)->count();
        $presentToday   = Attendance::where('date', $today)->where('status', 'present')->count();
        $attendanceToday = $markedToday > 0 ? (int) round(($presentToday / $markedToday) * 100) : 0;

        // ── Fees this term ───────────────────────────────────────────────────
        $currentTerm     = Term::where('is_current', true)->first();
        $feesThisTerm    = 0.0;
        $feesOutstanding = 0.0;
        $overdueCount    = 0;

        if ($currentTerm) {
            $feesThisTerm = (float) FeePayment::whereHas(
                'feeStructure',
                fn ($q) => $q->where('term_id', $currentTerm->id)
            )->sum('amount');

            $termStructures = FeeStructure::where('term_id', $currentTerm->id)->get();

            if ($termStructures->isNotEmpty()) {
                $structureIds     = $termStructures->pluck('id');
                $paidPerStructure = FeePayment::whereIn('fee_structure_id', $structureIds)
                    ->selectRaw('fee_structure_id, SUM(amount) as total_paid')
                    ->groupBy('fee_structure_id')
                    ->pluck('total_paid', 'fee_structure_id');

                foreach ($termStructures as $fs) {
                    $paid        = (float) ($paidPerStructure[$fs->id] ?? 0);
                    $outstanding = max(0.0, (float) $fs->amount - $paid);
                    $feesOutstanding += $outstanding;

                    if ($outstanding > 0 && $fs->due_date instanceof Carbon && $fs->due_date->isPast()) {
                        $overdueCount++;
                    }
                }
            }
        }

        $stats = [
            'total_students'   => $totalStudents,
            'total_staff'      => $totalStaff,
            'attendance_today' => $attendanceToday,
            'fees_this_term'   => $feesThisTerm,
            'fees_outstanding' => $feesOutstanding,
            'overdue_count'    => $overdueCount,
        ];

        // ── Recent activity ──────────────────────────────────────────────────
        $recentAnnouncements = Announcement::with('postedBy')->latest()->limit(3)->get();
        $recentPayments      = FeePayment::with(['student', 'recordedBy'])->latest()->limit(2)->get();

        $activity = collect();

        foreach ($recentAnnouncements as $ann) {
            $activity->push([
                'type' => 'announce',
                'text' => 'Announcement: ' . $ann->title,
                'time' => $ann->created_at->diffForHumans(),
                'ts'   => $ann->created_at,
            ]);
        }

        foreach ($recentPayments as $pmt) {
            $studentName = $pmt->student?->full_name ?? 'Unknown student';
            $activity->push([
                'type' => 'fee',
                'text' => 'Fee payment received — ' . $studentName,
                'time' => $pmt->created_at->diffForHumans(),
                'ts'   => $pmt->created_at,
            ]);
        }

        $activity = $activity->sortByDesc('ts')->values()->toArray();

        // ── Setup checklist ──────────────────────────────────────────────────
        $host      = request()->getSchemeAndHttpHost();
        $checklist = [
            [
                'label' => 'School profile set up',
                'done'  => DB::table('school_profile')->exists(),
                'link'  => $host . '/settings/profile',
            ],
            [
                'label' => 'Academic year configured',
                'done'  => AcademicYear::exists(),
                'link'  => $host . '/settings/academic-year',
            ],
            [
                'label' => 'Classes created',
                'done'  => SchoolClass::exists(),
                'link'  => $host . '/settings/classes',
            ],
            [
                'label' => 'Subjects added',
                'done'  => Subject::exists(),
                'link'  => $host . '/settings/subjects',
            ],
        ];

        // ── Fee collection chart — last 6 months (real data) ─────────────────
        $feeChartRaw = FeePayment::selectRaw(
            "DATE_FORMAT(paid_at, '%b') as month_label,
             YEAR(paid_at) as yr,
             MONTH(paid_at) as mo,
             SUM(amount) as total"
        )
            ->where('paid_at', '>=', now()->subMonths(5)->startOfMonth())
            ->groupByRaw("YEAR(paid_at), MONTH(paid_at), DATE_FORMAT(paid_at, '%b')")
            ->orderByRaw('YEAR(paid_at) ASC, MONTH(paid_at) ASC')
            ->get();

        $feeChartLabels = [];
        $feeChartValues = [];
        for ($i = 5; $i >= 0; $i--) {
            $month            = now()->subMonths($i);
            $feeChartLabels[] = $month->format('M');
            $row              = $feeChartRaw->first(
                fn ($r) => (int) $r->yr === (int) $month->year && (int) $r->mo === (int) $month->month
            );
            $feeChartValues[] = $row ? round((float) $row->total, 2) : 0;
        }
        $feeChart = ['labels' => $feeChartLabels, 'values' => $feeChartValues];

        // ── Attendance chart — last 7 days (real data) ───────────────────────
        $attChartLabels = [];
        $attChartValues = [];
        for ($i = 6; $i >= 0; $i--) {
            $day              = Carbon::today()->subDays($i);
            $attChartLabels[] = $day->format('D');
            $dayStr           = $day->toDateString();
            $dayMarked        = Attendance::where('date', $dayStr)->count();
            $dayPresent       = Attendance::where('date', $dayStr)->where('status', 'present')->count();
            $attChartValues[] = $dayMarked > 0 ? (int) round(($dayPresent / $dayMarked) * 100) : 0;
        }
        $attendanceChart = ['labels' => $attChartLabels, 'values' => $attChartValues];

        // ── Grade distribution chart (real data) ─────────────────────────────
        $gradeCounts  = ExamResult::selectRaw('grade, COUNT(*) as cnt')
            ->whereNotNull('grade')
            ->groupBy('grade')
            ->pluck('cnt', 'grade');
        $totalResults = (int) $gradeCounts->sum();
        $gradeOrder   = ['A', 'B', 'C', 'D', 'F'];
        $gradeLabels  = ['A (70-100)', 'B (60-69)', 'C (50-59)', 'D (40-49)', 'F (<40)'];
        $gradeValues  = [];
        foreach ($gradeOrder as $g) {
            $cnt           = (int) ($gradeCounts[$g] ?? 0);
            $gradeValues[] = $totalResults > 0 ? round(($cnt / $totalResults) * 100) : 0;
        }
        $gradeChart = ['labels' => $gradeLabels, 'values' => $gradeValues];

        $schoolProfile = SchoolProfile::first();

        return view('tenant.dashboard', compact(
            'user', 'can', 'stats', 'activity', 'recentAnnouncements', 'checklist',
            'feeChart', 'attendanceChart', 'gradeChart', 'schoolProfile'
        ));
    }
}
