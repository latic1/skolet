<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSchoolRegistrationRequest;
use App\Services\TenantProvisioningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class SchoolRegistrationController extends Controller
{
    public function __construct(
        private readonly TenantProvisioningService $provisioningService,
    ) {}

    public function create(): View
    {
        return view('central.register-school');
    }

    public function store(StoreSchoolRegistrationRequest $request): RedirectResponse
    {
        $result = $this->provisioningService->provision(
            schoolName:    $request->validated('school_name'),
            subdomain:     $request->validated('subdomain'),
            adminName:     $request->validated('admin_name'),
            adminEmail:    $request->validated('admin_email'),
            adminPassword: $request->validated('admin_password'),
        );

        if (! $result['success']) {
            return back()
                ->withInput()
                ->withErrors(['subdomain' => $result['error']]);
        }

        $port = request()->getPort();
        $portSuffix = in_array($port, [80, 443]) ? '' : ':' . $port;
        $loginUrl = request()->getScheme() . '://' . $result['data']['domain'] . $portSuffix . '/login';

        return redirect($loginUrl)
            ->with('success', 'Your school has been registered. Please log in to get started.');
    }
}
