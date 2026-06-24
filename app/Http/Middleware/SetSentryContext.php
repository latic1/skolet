<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class SetSentryContext
{
    public function handle(Request $request, Closure $next): Response
    {
        if (function_exists('app') && app()->bound('sentry') && tenancy()->initialized) {
            \Sentry\configureScope(function (\Sentry\State\Scope $scope): void {
                $scope->setTag('tenant_id', tenant('id'));

                if (Auth::check()) {
                    $scope->setUser([
                        'id'    => Auth::id(),
                        'email' => Auth::user()?->email,
                    ]);
                }
            });
        }

        return $next($request);
    }
}
