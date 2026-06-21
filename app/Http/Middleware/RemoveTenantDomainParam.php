<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RemoveTenantDomainParam
{
    public function handle(Request $request, Closure $next): Response
    {
        // Strip the {subdomain} domain capture from route parameters so it
        // doesn't end up shifted into controller method argument slots when
        // ControllerDispatcher splices in a FormRequest at position 0.
        $request->route()?->forgetParameter('subdomain');

        return $next($request);
    }
}
