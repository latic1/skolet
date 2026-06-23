<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Central\ImpersonationLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

final class ResumeImpersonation
{
    private const SESSION_LIMIT_SECONDS = 3600; // 1 hour

    /**
     * On every tenant request, if an impersonation session is active for the
     * current tenant, authenticate the school admin for this request only.
     *
     * Auth::onceUsingId() logs in without persisting to the session, so the
     * tenant's `web` guard session keys are never written. The super_admin guard
     * session on skolet.com is completely separate and remains intact.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!session('impersonating') || session('impersonating_tenant_id') !== tenant('id')) {
            return $next($request);
        }

        $startedAt = (int) session('impersonating_started_at', 0);

        if ($startedAt > 0 && (time() - $startedAt) > self::SESSION_LIMIT_SECONDS) {
            $this->expireSession();

            // Avoid redirect loop if already on /login (session write failure would
            // re-trigger this branch on the next request, bouncing forever).
            if ($request->is('login')) {
                return $next($request);
            }

            return redirect(request()->getSchemeAndHttpHost() . '/login')
                ->with('error', 'Your impersonation session expired after 1 hour.');
        }

        Auth::onceUsingId(session('impersonating_user_id'));

        return $next($request);
    }

    private function expireSession(): void
    {
        try {
            $logId = session('impersonating_log_id');

            if ($logId) {
                ImpersonationLog::on('central')->find($logId)?->update(['ended_at' => now()]);
            }
        } catch (\Throwable $e) {
            Log::error('[ResumeImpersonation] Failed to mark log ended on expiry: ' . $e->getMessage());
        }

        session()->forget([
            'impersonating',
            'impersonating_tenant_id',
            'impersonating_user_id',
            'impersonating_log_id',
            'impersonating_started_at',
        ]);
    }
}
