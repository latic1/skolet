<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSchoolRegistrationRequest;
use App\Mail\WelcomeCredentialsMail;
use App\Services\SmsService;
use App\Services\TenantProvisioningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

final class SchoolRegistrationController extends Controller
{
    public function __construct(
        private readonly TenantProvisioningService $provisioningService,
        private readonly SmsService $smsService,
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
            adminPhone: $request->validated('admin_phone'),
        );

        if (! $result['success']) {
            return back()
                ->withInput()
                ->withErrors(['subdomain' => $result['error']]);
        }

        $port       = request()->getPort();
        $portSuffix = in_array($port, [80, 443]) ? '' : ':' . $port;
        $loginUrl   = request()->getScheme() . '://' . $result['data']['domain'] . $portSuffix . '/login';

        $mailSent = true;
        try {
            Mail::to($result['data']['admin_email'])->queue(new WelcomeCredentialsMail(
                recipientName:  $result['data']['admin_name'],
                recipientEmail: $result['data']['admin_email'],
                plainPassword:  $result['data']['admin_password'],
                loginUrl:       $loginUrl,
            ));
        } catch (\Throwable) {
            $mailSent = false;
        }

        // Send SMS if a phone was provided and email failed (or always if phone given)
        $smsSent = false;
        $phone   = $result['data']['admin_phone'] ?? null;
        if ($phone !== null) {
            $smsBody = "Skolet: Your school is live!\nLogin: {$loginUrl}\nEmail: {$result['data']['admin_email']}\nPassword: {$result['data']['admin_password']}";
            $smsSent = $this->smsService->send($phone, $smsBody);
        }

        if ($mailSent || $smsSent) {
            $via     = array_filter(['email' => $mailSent, 'SMS' => $smsSent]);
            $message = 'School registered! Login credentials sent via ' . implode(' and ', array_keys($via)) . '.';
        } else {
            $message = 'School registered! (Notifications failed — save these credentials now) '
                . 'Email: ' . $result['data']['admin_email']
                . ' | Password: ' . $result['data']['admin_password'];
        }

        return redirect($loginUrl)->with('success', $message);
    }
}
