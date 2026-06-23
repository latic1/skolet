<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSchoolRegistrationRequest;
use App\Mail\WelcomeCredentialsMail;
use App\Services\TenantProvisioningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
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
            schoolName: $request->validated('school_name'),
            subdomain:  $request->validated('subdomain'),
            adminName:  $request->validated('admin_name'),
            adminEmail: $request->validated('admin_email'),
        );

        if (! $result['success']) {
            return back()
                ->withInput()
                ->withErrors(['subdomain' => $result['error']]);
        }

        $port       = request()->getPort();
        $portSuffix = in_array($port, [80, 443]) ? '' : ':' . $port;
        $loginUrl   = request()->getScheme() . '://' . $result['data']['domain'] . $portSuffix . '/login';

        Mail::to($result['data']['admin_email'])->queue(new WelcomeCredentialsMail(
            recipientName:  $result['data']['admin_name'],
            recipientEmail: $result['data']['admin_email'],
            plainPassword:  $result['data']['admin_password'],
            loginUrl:       $loginUrl,
        ));

        return redirect($loginUrl)
            ->with('success', 'School registered! Login credentials have been sent to ' . $result['data']['admin_email'] . '.');
    }
}
