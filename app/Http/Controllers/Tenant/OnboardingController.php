<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\AcademicYear;
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\SchoolProfile;
use App\Models\Tenant\Subject;
use App\Models\Tenant\Term;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

final class OnboardingController extends Controller
{
    private const TOTAL_STEPS = 5;

    public function show(int $step = 1): View|RedirectResponse
    {
        $step = max(1, min($step, self::TOTAL_STEPS));

        $profile = SchoolProfile::first();

        // Already completed — send to dashboard
        if ($profile?->onboarding_completed) {
            return redirect(request()->getSchemeAndHttpHost() . '/dashboard');
        }

        return view('tenant.onboarding', [
            'step'    => $step,
            'total'   => self::TOTAL_STEPS,
            'profile' => $profile,
        ]);
    }

    public function store(Request $request, int $step): RedirectResponse
    {
        $host = request()->getSchemeAndHttpHost();

        return match ($step) {
            1 => $this->storeStep1($request, $host),
            2 => $this->storeStep2($request, $host),
            3 => $this->storeStep3($request, $host),
            4 => $this->storeStep4($request, $host),
            5 => $this->storeStep5($host),
            default => redirect($host . '/onboarding/1'),
        };
    }

    public function skip(Request $request): RedirectResponse
    {
        $request->session()->put('onboarding_skipped', true);

        return redirect(request()->getSchemeAndHttpHost() . '/dashboard');
    }

    // ── Step handlers ────────────────────────────────────────────────────────

    private function storeStep1(Request $request, string $host): RedirectResponse
    {
        $data = $request->validate([
            'school_name'       => ['required', 'string', 'max:150'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'logo'              => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp,svg', 'max:2048'],
        ]);

        try {
            $profile = SchoolProfile::first() ?? new SchoolProfile();

            $profile->fill([
                'school_name'       => $data['school_name'],
                'short_description' => $data['short_description'] ?? null,
                'onboarding_step'   => 2,
            ]);

            if ($request->hasFile('logo')) {
                if ($profile->logo_path && Storage::disk('public')->exists($profile->logo_path)) {
                    Storage::disk('public')->delete($profile->logo_path);
                }
                $tenantId          = tenant('id');
                $ext               = $request->file('logo')->getClientOriginalExtension();
                $profile->logo_path = $request->file('logo')->storeAs("logos/{$tenantId}", "logo.{$ext}", 'public');
            }

            $profile->save();
        } catch (\Throwable $e) {
            Log::error('[OnboardingController::storeStep1] ' . $e->getMessage());
            return back()->withInput()->with('error', 'Could not save. Please try again.');
        }

        return redirect($host . '/onboarding/2');
    }

    private function storeStep2(Request $request, string $host): RedirectResponse
    {
        $data = $request->validate([
            'year_name'     => ['required', 'string', 'max:100'],
            'start_date'    => ['required', 'date'],
            'end_date'      => ['required', 'date', 'after:start_date'],
            'period_system' => ['required', 'string', 'in:3_term,2_semester'],
        ]);

        try {
            // Mark all other years as not current, then create this one
            AcademicYear::query()->update(['is_current' => false]);

            $year = AcademicYear::create([
                'name'       => $data['year_name'],
                'start_date' => $data['start_date'],
                'end_date'   => $data['end_date'],
                'is_current' => true,
            ]);

            // Auto-create terms based on selected period system
            $termNames = $data['period_system'] === '2_semester'
                ? ['Semester 1', 'Semester 2']
                : ['Term 1', 'Term 2', 'Term 3'];

            foreach ($termNames as $i => $name) {
                Term::create([
                    'academic_year_id' => $year->id,
                    'name'             => $name,
                    'is_current'       => $i === 0, // first term is current
                ]);
            }

            $profile = SchoolProfile::first() ?? new SchoolProfile();
            $profile->fill(['period_system' => $data['period_system'], 'onboarding_step' => 3])->save();
        } catch (\Throwable $e) {
            Log::error('[OnboardingController::storeStep2] ' . $e->getMessage());
            return back()->withInput()->with('error', 'Could not save. Please try again.');
        }

        return redirect($host . '/onboarding/3');
    }

    private function storeStep3(Request $request, string $host): RedirectResponse
    {
        $data = $request->validate([
            'classes'         => ['required', 'array', 'min:1'],
            'classes.*.name'  => ['required', 'string', 'max:100'],
        ]);

        try {
            foreach ($data['classes'] as $i => $classData) {
                $name = trim($classData['name']);
                if ($name === '') {
                    continue;
                }
                SchoolClass::firstOrCreate(
                    ['name' => $name],
                    ['order' => $i + 1]
                );
            }

            SchoolProfile::first()?->fill(['onboarding_step' => 4])->save();
        } catch (\Throwable $e) {
            Log::error('[OnboardingController::storeStep3] ' . $e->getMessage());
            return back()->withInput()->with('error', 'Could not save. Please try again.');
        }

        return redirect($host . '/onboarding/4');
    }

    private function storeStep4(Request $request, string $host): RedirectResponse
    {
        $data = $request->validate([
            'subjects'        => ['required', 'array', 'min:1'],
            'subjects.*.name' => ['required', 'string', 'max:100'],
        ]);

        try {
            foreach ($data['subjects'] as $subjectData) {
                $name = trim($subjectData['name']);
                if ($name === '') {
                    continue;
                }
                Subject::firstOrCreate(['name' => $name]);
            }

            SchoolProfile::first()?->fill(['onboarding_step' => 5])->save();
        } catch (\Throwable $e) {
            Log::error('[OnboardingController::storeStep4] ' . $e->getMessage());
            return back()->withInput()->with('error', 'Could not save. Please try again.');
        }

        return redirect($host . '/onboarding/5');
    }

    private function storeStep5(string $host): RedirectResponse
    {
        $profile = SchoolProfile::first();

        if ($profile) {
            $profile->fill(['onboarding_completed' => true, 'onboarding_step' => 5])->save();
        }

        return redirect($host . '/dashboard')
            ->with('success', 'Setup complete! Welcome to Skolet.');
    }
}
