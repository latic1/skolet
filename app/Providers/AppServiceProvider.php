<?php

namespace App\Providers;

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
                    $view->with('schoolProfile', \App\Models\Tenant\SchoolProfile::first());
                    return;
                }
            } catch (\Throwable) {}

            $view->with('schoolProfile', null);
        });
    }
}
