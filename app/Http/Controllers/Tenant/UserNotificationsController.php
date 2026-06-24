<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\TenantNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class UserNotificationsController extends Controller
{
    public function index(): View
    {
        $notifications = TenantNotification::where('user_id', Auth::id())
            ->with('announcement')
            ->latest()
            ->paginate(20);

        return view('tenant.notifications.index', compact('notifications'));
    }

    public function markRead(TenantNotification $notification): JsonResponse|RedirectResponse
    {
        abort_if($notification->user_id !== Auth::id(), 403);

        $notification->update(['read_at' => now()]);

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back();
    }

    public function markAllRead(): RedirectResponse
    {
        TenantNotification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }
}
