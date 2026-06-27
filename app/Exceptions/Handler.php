<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            if (app()->bound('sentry')) {
                app('sentry')->captureException($e);
            }
        });

        // Return JSON error responses for all /api/* routes
        $this->renderable(function (AuthenticationException $e, Request $request): ?JsonResponse {
            if ($this->isApiRequest($request)) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return null;
        });

        $this->renderable(function (AuthorizationException $e, Request $request): ?JsonResponse {
            if ($this->isApiRequest($request)) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }
            return null;
        });

        $this->renderable(function (ValidationException $e, Request $request): ?JsonResponse {
            if ($this->isApiRequest($request)) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors'  => $e->errors(),
                ], 422);
            }
            return null;
        });

        $this->renderable(function (HttpExceptionInterface $e, Request $request): ?JsonResponse {
            if ($this->isApiRequest($request)) {
                return response()->json(['message' => $e->getMessage() ?: 'HTTP error.'], $e->getStatusCode());
            }
            return null;
        });

        $this->renderable(function (Throwable $e, Request $request): ?JsonResponse {
            if ($this->isApiRequest($request) && app()->environment('production')) {
                return response()->json(['message' => 'Server error.'], 500);
            }
            return null;
        });
    }

    private function isApiRequest(Request $request): bool
    {
        return $request->is('api/*') || $request->expectsJson();
    }
}
