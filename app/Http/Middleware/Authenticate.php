<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }
        // Super admin routes have their own login page.
        if (str_starts_with($request->path(), 'super-admin')) {
            return route('super-admin.login');
        }
        // Use host-based URL so unauthenticated tenant users land on the tenant login
        // page, not the central domain. route() cannot auto-bind {subdomain} from
        // domain-parameterised routes, so we build the URL from the request host.
        return $request->getSchemeAndHttpHost() . '/login';
    }
}
