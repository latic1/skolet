<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Inject $schoolProfile into the tenant layout and login view.
        // Wraps in try/catch so requests that hit the route before tenancy is
        // initialized (e.g. central-domain 404s) don't blow up.
        View::composer(['layouts.tenant', 'tenant.auth.login'], function ($view) {
            try {
                $tenancy = app(\Stancl\Tenancy\Tenancy::class);

                if ($tenancy->initialized) {
                    $profile = \App\Models\Tenant\SchoolProfile::first();
                    $view->with('schoolProfile', $profile);
                    $view->with('currencySymbol', $profile?->currency_symbol ?? '₵');
                    return;
                }
            } catch (\Throwable) {}

            $view->with('schoolProfile', null);
            $view->with('currencySymbol', '₵');
        });

        // 5 login attempts per minute, keyed by IP + email to prevent brute-forcing.
        RateLimiter::for('tenant-login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip() . $request->input('email'));
        });
    }
}
