<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Central\ImpersonationLog;
use App\Models\Central\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class ImpersonationController extends Controller
{
    /**
     * Start a Super Admin impersonation session for a tenant's School Admin.
     *
     * Uses a short-lived cache token as a one-time handshake to pass the session
     * data across the central → tenant subdomain boundary, since the two domains
     * maintain separate session cookies. The token is consumed in ImpersonateController::handle().
     */
    public function start(Tenant $tenant): RedirectResponse
    {
        try {
            $schoolAdminId = null;

            $tenant->run(function () use (&$schoolAdminId): void {
                $user          = \App\Models\Tenant\User::role('school_admin')->first();
                $schoolAdminId = $user?->id;
            });

            if (!$schoolAdminId) {
                return back()->with('error', "No school admin account found for {$tenant->name}.");
            }

            $log = ImpersonationLog::create([
                'super_admin_id'       => Auth::guard('super_admin')->id(),
                'tenant_id'            => $tenant->id,
                'impersonated_user_id' => $schoolAdminId,
                'started_at'           => now(),
            ]);

            // 90-second TTL is sufficient to cover any redirect latency.
            // Uses the 'central' store explicitly to bypass CacheTenancyBootstrapper,
            // which wraps the default store with tenant tags (not supported by the file driver).
            $token = Str::uuid()->toString();

            Cache::store('central')->put(
                "impersonate:{$token}",
                [
                    'tenant_id' => $tenant->id,
                    'user_id'   => $schoolAdminId,
                    'log_id'    => $log->id,
                ],
                now()->addSeconds(90)
            );

            $domain = $tenant->domains->first()?->domain
                ?? $tenant->subdomain . '.' . (parse_url(config('app.url'), PHP_URL_HOST) ?? 'schoolflow.com');
            $scheme = request()->isSecure() ? 'https' : 'http';
            $port   = (int) request()->getPort();
            $stdPort = ($scheme === 'https') ? 443 : 80;
            $portStr = ($port !== $stdPort) ? ':' . $port : '';

            return redirect()->away("{$scheme}://{$domain}{$portStr}/impersonate/{$token}");
        } catch (\Throwable $e) {
            Log::error('[ImpersonationController::start] ' . $e->getMessage());

            return back()->with('error', 'Could not initiate impersonation session. Please try again.');
        }
    }
}
