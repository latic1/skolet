<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Central\BroadcastNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final class PlatformBroadcastController extends Controller
{
    public function dismiss(Request $request, string $notificationId): RedirectResponse
    {
        try {
            $notification = BroadcastNotification::on('central')
                ->where('id', $notificationId)
                ->where('tenant_id', tenant('id'))
                ->firstOrFail();

            // Critical broadcasts are non-dismissible
            if ($notification->broadcast->severity === 'critical') {
                return back();
            }

            $notification->update(['dismissed_at' => now()]);
        } catch (\Throwable $e) {
            Log::error('[PlatformBroadcastController::dismiss] ' . $e->getMessage());
        }

        return back();
    }
}
