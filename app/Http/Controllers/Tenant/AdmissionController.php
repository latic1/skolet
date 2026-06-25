<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\AdmissionApplication;
use App\Models\Tenant\SchoolProfile;
use App\Notifications\AdmissionRejected;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

final class AdmissionController extends Controller
{
    public function index(Request $request): View
    {
        $query = AdmissionApplication::latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('class')) {
            $query->where('class_applying_for', 'like', '%' . $request->input('class') . '%');
        }

        $applications = $query->paginate(25)->withQueryString();

        $classes = AdmissionApplication::select('class_applying_for')
            ->distinct()
            ->orderBy('class_applying_for')
            ->pluck('class_applying_for');

        return view('tenant.admissions.index', compact('applications', 'classes'));
    }

    public function accept(Request $request, AdmissionApplication $application): RedirectResponse
    {
        abort_unless($request->user()->can('admissions.manage'), 403);

        if (! $application->isPending()) {
            return back()->with('error', 'This application has already been reviewed.');
        }

        $host = request()->getSchemeAndHttpHost();

        return redirect("{$host}/students/create?from_application={$application->id}");
    }

    public function reject(Request $request, AdmissionApplication $application): RedirectResponse
    {
        abort_unless($request->user()->can('admissions.manage'), 403);

        if (! $application->isPending()) {
            return back()->with('error', 'This application has already been reviewed.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        try {
            $application->update([
                'status'           => 'rejected',
                'rejection_reason' => $validated['rejection_reason'],
                'reviewed_by'      => Auth::id(),
                'reviewed_at'      => now(),
            ]);

            if ($application->guardian_email) {
                $profile    = SchoolProfile::first();
                $schoolName = $profile?->school_name ?? config('app.name');

                Notification::route('mail', $application->guardian_email)
                    ->notify(new AdmissionRejected($application, $schoolName));
            }

            return back()->with('success', "Application for {$application->applicant_name} has been rejected.");
        } catch (\Throwable $e) {
            Log::error('[AdmissionController::reject] ' . $e->getMessage());

            return back()->with('error', 'Could not reject application. Please try again.');
        }
    }
}
