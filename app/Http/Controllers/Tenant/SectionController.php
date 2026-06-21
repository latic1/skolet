<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreSectionRequest;
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\Section;
use Illuminate\Http\RedirectResponse;

final class SectionController extends Controller
{
    public function store(StoreSectionRequest $request, SchoolClass $schoolClass): RedirectResponse
    {
        try {
            $schoolClass->sections()->create($request->validated());

            return redirect(request()->getSchemeAndHttpHost() . '/settings/classes?class_open=' . $schoolClass->id)
                ->with('success', "Section \"{$request->name}\" added to {$schoolClass->name}.");
        } catch (\Throwable $e) {
            \Log::error('[SectionController::store] ' . $e->getMessage());

            return back()->with('error', 'Could not add section. Please try again.');
        }
    }

    public function destroy(Section $section): RedirectResponse
    {
        $classId = $section->class_id;

        try {
            $section->delete();

            return redirect(request()->getSchemeAndHttpHost() . '/settings/classes?class_open=' . $classId)
                ->with('success', 'Section deleted.');
        } catch (\Throwable $e) {
            \Log::error('[SectionController::destroy] ' . $e->getMessage());

            return back()->with('error', 'Could not delete section. Please try again.');
        }
    }
}
