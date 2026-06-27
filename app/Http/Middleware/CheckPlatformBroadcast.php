<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Central\BroadcastNotification;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

final class CheckPlatformBroadcast
{
    public function handle(Request $request, Closure $next): Response
    {
        $activeBroadcast  = null;
        $criticalBroadcast = null;

        try {
            if (tenancy()->initialized && auth()->check()) {
                $tenantId = tenant('id');

                // Find undismissed broadcast notifications for this tenant where send_at has passed
                $notification = BroadcastNotification::on('central')
                    ->with('broadcast')
                    ->where('tenant_id', $tenantId)
                    ->whereNull('dismissed_at')
                    ->whereHas('broadcast', function ($q) {
                        $q->where(function ($q2) {
                            $q2->whereNull('send_at')
                               ->orWhere('send_at', '<=', now());
                        })->whereNotNull('sent_at');
                    })
                    ->orderByDesc('created_at')
                    ->first();

                if ($notification) {
                    $broadcast = $notification->broadcast;

                    if ($broadcast->severity === 'critical') {
                        $criticalBroadcast = [
                            'notification_id' => $notification->id,
                            'subject'         => $broadcast->subject,
                            'message'         => $broadcast->message,
                            'severity'        => $broadcast->severity,
                            'sent_at'         => $broadcast->sent_at,
                        ];
                    } else {
                        $activeBroadcast = [
                            'notification_id' => $notification->id,
                            'subject'         => $broadcast->subject,
                            'message'         => $broadcast->message,
                            'severity'        => $broadcast->severity,
                            'sent_at'         => $broadcast->sent_at,
                        ];
                    }
                }
            }
        } catch (\Throwable) {
            // Silently ignore — broadcast is non-critical infrastructure
        }

        View::share('activeBroadcast', $activeBroadcast);
        View::share('criticalBroadcast', $criticalBroadcast);

        return $next($request);
    }
}
