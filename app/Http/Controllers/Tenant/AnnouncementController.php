<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreAnnouncementRequest;
use App\Http\Requests\Tenant\UpdateAnnouncementRequest;
use App\Models\Tenant\Announcement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

final class AnnouncementController extends Controller
{
    public function index(): View
    {
        $announcements = Announcement::with('postedBy')
            ->latest()
            ->get();

        return view('tenant.announcements.index', compact('announcements'));
    }

    public function store(StoreAnnouncementRequest $request): RedirectResponse
    {
        $host = request()->getSchemeAndHttpHost();

        try {
            Announcement::create([
                'title'     => $request->validated()['title'],
                'body'      => $request->validated()['body'],
                'is_public' => (bool) ($request->validated()['is_public'] ?? false),
                'posted_by' => Auth::id(),
            ]);

            return redirect($host . '/announcements')
                ->with('success', 'Announcement posted successfully.');
        } catch (\Throwable $e) {
            Log::error('[AnnouncementController::store] ' . $e->getMessage());

            return back()
                ->with('error', 'Could not post announcement. Please try again.')
                ->withInput();
        }
    }

    public function update(UpdateAnnouncementRequest $request, Announcement $announcement): RedirectResponse
    {
        $host = request()->getSchemeAndHttpHost();

        try {
            $announcement->update([
                'title'     => $request->validated()['title'],
                'body'      => $request->validated()['body'],
                'is_public' => (bool) ($request->validated()['is_public'] ?? false),
            ]);

            return redirect($host . '/announcements')
                ->with('success', 'Announcement updated.');
        } catch (\Throwable $e) {
            Log::error('[AnnouncementController::update] ' . $e->getMessage());

            return back()
                ->with('error', 'Could not update announcement. Please try again.')
                ->withInput();
        }
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $host = request()->getSchemeAndHttpHost();

        try {
            $announcement->delete();

            return redirect($host . '/announcements')
                ->with('success', 'Announcement deleted.');
        } catch (\Throwable $e) {
            Log::error('[AnnouncementController::destroy] ' . $e->getMessage());

            return back()->with('error', 'Could not delete announcement. Please try again.');
        }
    }
}
