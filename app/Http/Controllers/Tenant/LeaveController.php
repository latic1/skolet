<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\LeaveRequest;
use App\Models\Tenant\Staff;
use App\Models\Tenant\User;
use App\Notifications\LeaveRequestDecided;
use App\Notifications\LeaveRequestSubmitted;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

final class LeaveController extends Controller
{
    public function index(): View
    {
        $canManage    = Auth::user()->can('leave.manage');
        $currentStaff = Staff::where('user_id', Auth::id())->first();

        $myRequests = $currentStaff
            ? LeaveRequest::where('staff_id', $currentStaff->id)
                ->latest()
                ->paginate(10, ['*'], 'my_page')
            : collect();

        $pendingRequests = collect();
        $historyRequests = collect();

        if ($canManage) {
            $pendingRequests = LeaveRequest::with(['staff', 'approvedBy'])
                ->where('status', 'pending')
                ->oldest('start_date')
                ->get();

            $historyRequests = LeaveRequest::with(['staff', 'approvedBy'])
                ->whereIn('status', ['approved', 'rejected'])
                ->latest('approved_at')
                ->paginate(20, ['*'], 'hist_page');
        }

        return view('tenant.leave.index', compact(
            'canManage',
            'currentStaff',
            'myRequests',
            'pendingRequests',
            'historyRequests',
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'leave_type' => ['required', 'in:sick,annual,maternity,paternity,personal,other'],
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
            'reason'     => ['required', 'string', 'max:1000'],
        ]);

        $staff = Staff::where('user_id', Auth::id())->firstOrFail();

        $leaveRequest = LeaveRequest::create([...$validated, 'staff_id' => $staff->id]);

        $admins = User::role('school_admin')->get();
        Notification::send($admins, new LeaveRequestSubmitted($leaveRequest->load('staff')));

        return back()->with('success', 'Leave request submitted successfully.');
    }

    public function approve(LeaveRequest $leaveRequest): RedirectResponse
    {
        abort_unless(Auth::user()->can('leave.manage'), 403);

        $leaveRequest->update([
            'status'      => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        $leaveRequest->load('staff.user');

        if ($leaveRequest->staff?->user) {
            $leaveRequest->staff->user->notify(new LeaveRequestDecided($leaveRequest));
        }

        return back()->with('success', 'Leave request approved.');
    }

    public function reject(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        abort_unless(Auth::user()->can('leave.manage'), 403);

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $leaveRequest->update([
            'status'           => 'rejected',
            'approved_by'      => Auth::id(),
            'approved_at'      => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        $leaveRequest->load('staff.user');

        if ($leaveRequest->staff?->user) {
            $leaveRequest->staff->user->notify(new LeaveRequestDecided($leaveRequest));
        }

        return back()->with('success', 'Leave request rejected.');
    }
}
