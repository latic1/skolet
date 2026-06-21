<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreSchoolClassRequest;
use App\Http\Requests\Tenant\UpdateSchoolClassRequest;
use App\Models\Tenant\SchoolClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class SchoolClassController extends Controller
{
    public function index(): View
    {
        $classes = SchoolClass::with('sections')
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        $classesJson = $classes->map(fn (SchoolClass $c) => [
            'id'       => $c->id,
            'name'     => $c->name,
            'order'    => $c->order,
            'sections' => $c->sections->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])->values()->toArray(),
        ])->values()->toArray();

        return view('tenant.settings.classes', compact('classes', 'classesJson'));
    }

    public function store(StoreSchoolClassRequest $request): RedirectResponse
    {
        try {
            $data = $request->validated();
            if (empty($data['order'])) {
                $data['order'] = SchoolClass::max('order') + 1;
            }
            SchoolClass::create($data);

            return redirect(request()->getSchemeAndHttpHost() . '/settings/classes')
                ->with('success', 'Class created successfully.');
        } catch (\Throwable $e) {
            \Log::error('[SchoolClassController::store] ' . $e->getMessage());

            return back()->with('error', 'Could not create class. Please try again.')->withInput();
        }
    }

    public function update(UpdateSchoolClassRequest $request, SchoolClass $schoolClass): RedirectResponse
    {
        try {
            $schoolClass->update($request->validated());

            return redirect(request()->getSchemeAndHttpHost() . '/settings/classes')
                ->with('success', 'Class updated successfully.');
        } catch (\Throwable $e) {
            \Log::error('[SchoolClassController::update] ' . $e->getMessage());

            return back()->with('error', 'Could not update class. Please try again.')->withInput();
        }
    }

    public function destroy(SchoolClass $schoolClass): RedirectResponse
    {
        try {
            $schoolClass->delete();

            return redirect(request()->getSchemeAndHttpHost() . '/settings/classes')
                ->with('success', 'Class deleted (sections removed automatically).');
        } catch (\Throwable $e) {
            \Log::error('[SchoolClassController::destroy] ' . $e->getMessage());

            return back()->with('error', 'Could not delete class. Please try again.');
        }
    }
}
