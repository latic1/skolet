<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Central\ImpersonationLog;
use App\Models\Tenant\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

final class ImpersonateController extends Controller
{
    /**
     * Validate a one-time impersonation token and establish the tenant-side session.
     *
     * Token is placed by ImpersonationController::start() with a 90-second TTL.
     * Cache::pull() consumes the token (single-use). The session keys written here
     * are read by ResumeImpersonation middleware on every subsequent tenant request
     * to re-authenticate as the school admin without persisting auth state.
     */
    public function handle(string $token): RedirectResponse
    {
        // Use the 'central' store — bypasses CacheTenancyBootstrapper tenant tag wrapping.
        $data = Cache::store('central')->pull("impersonate:{$token}");

        if (!$data || ($data['tenant_id'] ?? null) !== tenant('id')) {
            abort(403, 'Invalid or expired impersonation token.');
        }

        $user = User::find($data['user_id']);

        if (!$user) {
            abort(404, 'School admin account not found.');
        }

        session([
            'impersonating'            => true,
            'impersonating_tenant_id'  => $data['tenant_id'],
            'impersonating_user_id'    => $data['user_id'],
            'impersonating_log_id'     => $data['log_id'],
            'impersonating_started_at' => now()->timestamp,
        ]);

        return redirect(request()->getSchemeAndHttpHost() . '/dashboard');
    }

    /**
     * End the impersonation session and return to the Super Admin dashboard.
     *
     * Never calls Auth::logout() — only the impersonation session keys are
     * cleared. The super_admin guard on schoolflow.com is completely unaffected
     * because it lives in a separate session (separate subdomain cookie).
     */
    public function exit(): RedirectResponse
    {
        try {
            $logId = session('impersonating_log_id');

            if ($logId) {
                ImpersonationLog::on('central')->find($logId)?->update(['ended_at' => now()]);
            }
        } catch (\Throwable $e) {
            Log::error('[ImpersonateController::exit] ' . $e->getMessage());
        }

        session()->forget([
            'impersonating',
            'impersonating_tenant_id',
            'impersonating_user_id',
            'impersonating_log_id',
            'impersonating_started_at',
        ]);

        return redirect()->away(config('app.url') . '/super-admin');
    }
}
