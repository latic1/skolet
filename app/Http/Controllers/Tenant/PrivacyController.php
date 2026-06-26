<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Jobs\ExportAllSchoolDataJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class PrivacyController extends Controller
{
    public function index(): View
    {
        return view('tenant.settings.privacy');
    }

    public function requestFullExport(Request $request): RedirectResponse
    {
        $user       = Auth::user();
        $tenantId   = tenant()->getTenantKey();
        $tenantHost = $request->getSchemeAndHttpHost();

        ExportAllSchoolDataJob::dispatch(
            tenantId: $tenantId,
            tenantHost: $tenantHost,
            adminEmail: $user->email,
            adminName: $user->name,
        );

        return back()->with('success', 'Export request received. We will email you at ' . $user->email . ' when it\'s ready (within a few minutes).');
    }

    public function download(Request $request, string $token): BinaryFileResponse|RedirectResponse
    {
        abort_unless($request->user()?->can('settings.manage'), 403);

        if (!preg_match('/^[0-9a-f\-]{36}$/', $token)) {
            abort(404);
        }

        $tenantId = tenant()->getTenantKey();
        $path     = storage_path("app/{$tenantId}/exports/{$token}.zip");

        if (!file_exists($path)) {
            return back()->with('error', 'Export file not found or has expired.');
        }

        // Expire after 24 hours
        if (filemtime($path) < time() - 86400) {
            @unlink($path);

            return back()->with('error', 'This export link has expired. Please request a new export.');
        }

        return response()->download($path, 'schoolflow-export.zip', ['Content-Type' => 'application/zip']);
    }
}
