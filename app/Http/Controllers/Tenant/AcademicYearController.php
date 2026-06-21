<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreAcademicYearRequest;
use App\Http\Requests\Tenant\StoreTermRequest;
use App\Http\Requests\Tenant\UpdateAcademicYearRequest;
use App\Http\Requests\Tenant\UpdateTermRequest;
use App\Models\Tenant\AcademicYear;
use App\Models\Tenant\SchoolProfile;
use App\Models\Tenant\Term;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Support\Str;

final class AcademicYearController extends Controller
{
    public function index(): View
    {
        $academicYears = AcademicYear::with('terms')->orderByDesc('start_date')->get();
        $yearOpen      = request()->input('year_open');
        $schoolProfile = SchoolProfile::first();

        return view('tenant.settings.academic-year', compact('academicYears', 'yearOpen', 'schoolProfile'));
    }

    public function setPeriodSystem(Request $request): RedirectResponse
    {
        $host      = request()->getSchemeAndHttpHost();
        $validated = $request->validate([
            'period_system' => ['required', 'in:3_term,2_semester'],
        ]);

        $newSystem = $validated['period_system'];
        $profile   = SchoolProfile::first();

        if ($profile && $profile->period_system !== $newSystem && Term::count() > 0) {
            return redirect($host . '/settings/academic-year')
                ->with('error', 'The period system cannot be changed after academic years with terms have been created. Delete all academic years first to reset it.');
        }

        SchoolProfile::updateOrCreate([], ['period_system' => $newSystem]);

        $label = $newSystem === '3_term' ? '3-Term System' : '2-Semester System';

        return redirect($host . '/settings/academic-year')
            ->with('success', "Period system set to {$label}.");
    }

    public function store(StoreAcademicYearRequest $request): RedirectResponse
    {
        $host        = request()->getSchemeAndHttpHost();
        $period      = SchoolProfile::value('period_system') ?? '3_term';
        $termNames   = $period === '2_semester'
            ? ['Semester 1', 'Semester 2']
            : ['Term 1', 'Term 2', 'Term 3'];

        try {
            DB::transaction(function () use ($request, $termNames): void {
                $academicYear = AcademicYear::create($request->validated());

                foreach ($termNames as $name) {
                    $academicYear->terms()->create([
                        'name'       => $name,
                        'start_date' => null,
                        'end_date'   => null,
                        'is_current' => false,
                    ]);
                }
            });

            return redirect($host . '/settings/academic-year')
                ->with('success', 'Academic year created with ' . count($termNames) . ' auto-generated ' . Str::plural('term', count($termNames)) . '.');
        } catch (\Throwable $e) {
            \Log::error('[AcademicYearController::store] ' . $e->getMessage());

            return back()->with('error', 'Could not create academic year. Please try again.')->withInput();
        }
    }

    public function update(UpdateAcademicYearRequest $request, AcademicYear $academicYear): RedirectResponse
    {
        try {
            $academicYear->update($request->validated());

            return redirect(request()->getSchemeAndHttpHost() . '/settings/academic-year')
                ->with('success', 'Academic year updated successfully.');
        } catch (\Throwable $e) {
            \Log::error('[AcademicYearController::update] ' . $e->getMessage());

            return back()->with('error', 'Could not update academic year. Please try again.')->withInput();
        }
    }

    public function destroy(AcademicYear $academicYear): RedirectResponse
    {
        try {
            $academicYear->delete();

            return redirect(request()->getSchemeAndHttpHost() . '/settings/academic-year')
                ->with('success', 'Academic year deleted.');
        } catch (\Throwable $e) {
            \Log::error('[AcademicYearController::destroy] ' . $e->getMessage());

            return back()->with('error', 'Could not delete academic year. Please try again.');
        }
    }

    public function setCurrent(AcademicYear $academicYear): RedirectResponse
    {
        try {
            DB::transaction(function () use ($academicYear) {
                AcademicYear::query()->update(['is_current' => false]);
                $academicYear->update(['is_current' => true]);
            });

            return redirect(request()->getSchemeAndHttpHost() . '/settings/academic-year')
                ->with('success', "\"{$academicYear->name}\" is now the current academic year.");
        } catch (\Throwable $e) {
            \Log::error('[AcademicYearController::setCurrent] ' . $e->getMessage());

            return back()->with('error', 'Could not update current academic year. Please try again.');
        }
    }

    public function copyTerms(AcademicYear $academicYear): RedirectResponse
    {
        $host    = request()->getSchemeAndHttpHost();
        $backUrl = $host . '/settings/academic-year?year_open=' . $academicYear->id;

        $source = AcademicYear::query()
            ->where('id', '!=', $academicYear->id)
            ->whereHas('terms')
            ->orderByDesc('start_date')
            ->with('terms')
            ->first();

        if (! $source) {
            return redirect($backUrl)
                ->with('error', 'No other academic year with terms found to copy from.');
        }

        $offsetDays = (int) $source->start_date->diffInDays($academicYear->start_date, false);

        try {
            DB::transaction(function () use ($academicYear, $source, $offsetDays): void {
                foreach ($source->terms as $term) {
                    $academicYear->terms()->create([
                        'name'       => $term->name,
                        'start_date' => $term->start_date ? $term->start_date->copy()->addDays($offsetDays) : null,
                        'end_date'   => $term->end_date ? $term->end_date->copy()->addDays($offsetDays) : null,
                        'is_current' => false,
                    ]);
                }
            });

            $count = $source->terms->count();
            $noun  = Str::plural('term', $count);

            return redirect($backUrl)
                ->with('success', "Copied {$count} {$noun} from \"{$source->name}\". Review the dates and adjust as needed.");
        } catch (\Throwable $e) {
            \Log::error('[AcademicYearController::copyTerms] ' . $e->getMessage());

            return redirect($backUrl)
                ->with('error', 'Could not copy terms. Please try again.');
        }
    }

    // -------------------------------------------------------------------------
    // Term methods
    // -------------------------------------------------------------------------

    public function storeTerm(StoreTermRequest $request, AcademicYear $academicYear): RedirectResponse
    {
        $host = request()->getSchemeAndHttpHost();

        try {
            $academicYear->terms()->create($request->validated());

            return redirect($host . '/settings/academic-year?year_open=' . $academicYear->id)
                ->with('success', 'Term added successfully.');
        } catch (\Throwable $e) {
            \Log::error('[AcademicYearController::storeTerm] ' . $e->getMessage());

            return redirect($host . '/settings/academic-year?year_open=' . $academicYear->id)
                ->with('error', 'Could not add term. Please try again.');
        }
    }

    public function updateTerm(UpdateTermRequest $request, Term $term): RedirectResponse
    {
        $host = request()->getSchemeAndHttpHost();

        try {
            $term->update($request->validated());

            return redirect($host . '/settings/academic-year?year_open=' . $term->academic_year_id)
                ->with('success', 'Term dates updated.');
        } catch (\Throwable $e) {
            \Log::error('[AcademicYearController::updateTerm] ' . $e->getMessage());

            return redirect($host . '/settings/academic-year?year_open=' . $term->academic_year_id)
                ->with('error', 'Could not update term. Please try again.');
        }
    }

    public function destroyTerm(Term $term): RedirectResponse
    {
        $host           = request()->getSchemeAndHttpHost();
        $academicYearId = $term->academic_year_id;

        try {
            $term->delete();

            return redirect($host . '/settings/academic-year?year_open=' . $academicYearId)
                ->with('success', 'Term deleted.');
        } catch (\Throwable $e) {
            \Log::error('[AcademicYearController::destroyTerm] ' . $e->getMessage());

            return redirect($host . '/settings/academic-year?year_open=' . $academicYearId)
                ->with('error', 'Could not delete term. Please try again.');
        }
    }

    public function setCurrentTerm(Term $term): RedirectResponse
    {
        $host = request()->getSchemeAndHttpHost();

        try {
            DB::transaction(function () use ($term) {
                Term::query()->update(['is_current' => false]);
                $term->update(['is_current' => true]);
            });

            return redirect($host . '/settings/academic-year?year_open=' . $term->academic_year_id)
                ->with('success', "\"{$term->name}\" is now the active term.");
        } catch (\Throwable $e) {
            \Log::error('[AcademicYearController::setCurrentTerm] ' . $e->getMessage());

            return redirect($host . '/settings/academic-year?year_open=' . $term->academic_year_id)
                ->with('error', 'Could not update active term. Please try again.');
        }
    }
}
