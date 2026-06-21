<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\UpdateSchoolProfileRequest;
use App\Models\Tenant\SchoolProfile;
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
}
