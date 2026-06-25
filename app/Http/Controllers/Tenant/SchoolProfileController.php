<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreGradingScaleRequest;
use App\Http\Requests\Tenant\UpdateSchoolProfileRequest;
use App\Models\Tenant\SchoolProfile;
use App\Services\AdmissionNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class SchoolProfileController extends Controller
{
    public function index(): View
    {
        $profile = SchoolProfile::first();
        $logoUrl = $profile?->logo_path
            ? request()->getSchemeAndHttpHost() . '/school-logo'
            : null;

        return view('tenant.settings.school-profile', compact('profile', 'logoUrl'));
    }

    public function logo(): BinaryFileResponse
    {
        $profile = SchoolProfile::first();

        if (!$profile?->logo_path || !Storage::disk('public')->exists($profile->logo_path)) {
            abort(404);
        }

        return response()->file(
            Storage::disk('public')->path($profile->logo_path),
            ['Cache-Control' => 'public, max-age=86400']
        );
    }

    public function update(UpdateSchoolProfileRequest $request): RedirectResponse
    {
        $data = collect($request->validated())->except('logo')->toArray();
        $data['admissions_open'] = $request->boolean('admissions_open');

        $profile = SchoolProfile::first() ?? new SchoolProfile();

        if ($request->hasFile('logo')) {
            if ($profile->logo_path && Storage::disk('public')->exists($profile->logo_path)) {
                Storage::disk('public')->delete($profile->logo_path);
            }

            $tenantId = tenant('id');
            $ext      = $request->file('logo')->getClientOriginalExtension();
            $path     = $request->file('logo')->storeAs("logos/{$tenantId}", "logo.{$ext}", 'public');
            $data['logo_path'] = $path;
        }

        $profile->fill($data)->save();

        return redirect(request()->getSchemeAndHttpHost() . '/settings/profile')
            ->with('success', 'School profile updated successfully.');
    }

    public function updateGradingScale(StoreGradingScaleRequest $request): RedirectResponse
    {
        $bands   = $request->validated()['bands'];
        $profile = SchoolProfile::first() ?? new SchoolProfile();

        $scale = array_map(fn ($b) => [
            'min'    => (int) $b['min'],
            'max'    => (int) $b['max'],
            'grade'  => trim($b['grade']),
            'remark' => trim($b['remark']),
        ], $bands);

        // Sort highest-first so applyScale() works top-to-bottom
        usort($scale, fn ($a, $b) => $b['min'] <=> $a['min']);

        $profile->grading_scale = $scale;
        $profile->save();

        return redirect(request()->getSchemeAndHttpHost() . '/settings/academic-year')
            ->with('success', 'Grading scale saved successfully.');
    }

    public function resetAdmissionCounter(AdmissionNumberService $service): RedirectResponse
    {
        $service->resetCounter();

        return redirect(request()->getSchemeAndHttpHost() . '/settings/profile')
            ->with('success', 'Admission number sequence has been reset to zero.');
    }
}
