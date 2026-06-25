<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\AdmissionApplication;
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\SchoolProfile;
use App\Notifications\AdmissionConfirmation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

final class PublicApplicationController extends Controller
{
    public function show(): View
    {
        $profile = SchoolProfile::first();
        $classes = SchoolClass::orderBy('order')->orderBy('name')->get(['id', 'name']);

        return view('tenant.apply', compact('profile', 'classes'));
    }

    public function store(Request $request): View|RedirectResponse
    {
        $validated = $request->validate([
            'applicant_name'    => ['required', 'string', 'max:150'],
            'date_of_birth'     => ['nullable', 'date', 'before:today'],
            'gender'            => ['nullable', 'in:male,female,other'],
            'class_applying_for'=> ['required', 'string', 'max:100'],
            'guardian_name'     => ['required', 'string', 'max:150'],
            'guardian_contact'  => ['required', 'string', 'max:30'],
            'guardian_email'    => ['nullable', 'email', 'max:150'],
            'previous_school'   => ['nullable', 'string', 'max:200'],
        ]);

        try {
            $application = AdmissionApplication::create($validated);

            if ($application->guardian_email) {
                $profile    = SchoolProfile::first();
                $schoolName = $profile?->school_name ?? config('app.name');

                Notification::route('mail', $application->guardian_email)
                    ->notify(new AdmissionConfirmation($application, $schoolName));
            }

            return view('tenant.apply-confirmation', [
                'application' => $application,
                'profile'     => SchoolProfile::first(),
            ]);
        } catch (\Throwable $e) {
            Log::error('[PublicApplicationController::store] ' . $e->getMessage());

            return back()->withInput()->with('error', 'Could not submit your application. Please try again.');
        }
    }
}
