<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreSubjectRequest;
use App\Http\Requests\Tenant\UpdateSubjectRequest;
use App\Models\Tenant\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class SubjectController extends Controller
{
    public function index(): View
    {
        $subjects = Subject::orderBy('name')->get();

        return view('tenant.settings.subjects', compact('subjects'));
    }

    public function store(StoreSubjectRequest $request): RedirectResponse
    {
        try {
            Subject::create($request->validated());

            return redirect(request()->getSchemeAndHttpHost() . '/settings/subjects')
                ->with('success', 'Subject added successfully.');
        } catch (\Throwable $e) {
            \Log::error('[SubjectController::store] ' . $e->getMessage());

            return back()->with('error', 'Could not add subject. Please try again.')->withInput();
        }
    }

    public function update(UpdateSubjectRequest $request, Subject $subject): RedirectResponse
    {
        try {
            $subject->update($request->validated());

            return redirect(request()->getSchemeAndHttpHost() . '/settings/subjects')
                ->with('success', 'Subject updated successfully.');
        } catch (\Throwable $e) {
            \Log::error('[SubjectController::update] ' . $e->getMessage());

            return back()->with('error', 'Could not update subject. Please try again.')->withInput();
        }
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        try {
            $subject->delete();

            return redirect(request()->getSchemeAndHttpHost() . '/settings/subjects')
                ->with('success', 'Subject deleted.');
        } catch (\Throwable $e) {
            \Log::error('[SubjectController::destroy] ' . $e->getMessage());

            return back()->with('error', 'Could not delete subject. Please try again.');
        }
    }
}
