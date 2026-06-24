<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

final class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = Activity::with('causer')->latest();

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('causer_id')) {
            $query->where('causer_type', User::class)
                  ->where('causer_id', $request->causer_id);
        }

        if ($request->filled('log_name')) {
            $query->where('log_name', $request->log_name);
        }

        $logs = $query->paginate(25)->withQueryString();

        $users = User::orderBy('name')->get(['id', 'name', 'email']);

        $logNames = [
            'student'        => 'Student',
            'staff'          => 'Staff',
            'exam'           => 'Exam',
            'exam_result'    => 'Exam Result',
            'fee_structure'  => 'Fee Structure',
            'fee_payment'    => 'Fee Payment',
            'announcement'   => 'Announcement',
            'school_profile' => 'School Profile',
            'academic_year'  => 'Academic Year',
            'term'           => 'Term',
            'school_class'   => 'Class',
        ];

        return view('tenant.settings.audit-log', compact('logs', 'users', 'logNames'));
    }
}
