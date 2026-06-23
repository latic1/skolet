<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Tenant\SchoolProfile;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class TenantOnboardingMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Only redirect school admins (anyone with settings.manage)
        if (! $user || ! $user->can('settings.manage')) {
            return $next($request);
        }

        // Respect explicit skip — user chose to bypass wizard
        if ($request->session()->get('onboarding_skipped')) {
            return $next($request);
        }

        $profile = SchoolProfile::first();

        // No profile row or onboarding not yet complete → send to wizard
        if (! $profile || ! $profile->onboarding_completed) {
            $step = $profile?->onboarding_step ?? 1;

            return redirect(
                $request->getSchemeAndHttpHost() . '/onboarding/' . $step
            );
        }

        return $next($request);
    }
}
