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
        // Inject $schoolProfile and $currencySymbol into every tenant view.
        // The composer covers the layout AND all child views (tenant.*) because
        // Blade captures child-section PHP before the parent layout renders, so
        // variables added only to layouts.tenant arrive too late for child views.
        // once() ensures SchoolProfile is queried at most once per request.
        View::composer(['layouts.tenant', 'tenant.*', 'tenant.*.*', 'tenant.*.*.*'], function ($view) {
            $data = once(function () {
                try {
                    $tenancy = app(\Stancl\Tenancy\Tenancy::class);

                    if ($tenancy->initialized) {
                        $profile = \App\Models\Tenant\SchoolProfile::first();

                        return [
                            'schoolProfile'  => $profile,
                            'currencySymbol' => $profile?->currency_symbol ?? '₵',
                        ];
                    }
                } catch (\Throwable) {}

                return ['schoolProfile' => null, 'currencySymbol' => '₵'];
            });

            $view->with($data);
        });

        // 5 login attempts per minute, keyed by IP + email to prevent brute-forcing.
        RateLimiter::for('tenant-login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip() . $request->input('email'));
        });
    }
}
