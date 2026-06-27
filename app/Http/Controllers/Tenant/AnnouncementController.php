<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreAnnouncementRequest;
use App\Http\Requests\Tenant\UpdateAnnouncementRequest;
use App\Jobs\SendAnnouncementNotifications;
use App\Jobs\SendWebhookPayload;
use App\Models\Tenant\Announcement;
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

final class AnnouncementController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        if ($user->can('settings.manage')) {
            $announcements = Announcement::with('postedBy')->latest()->get();
        } else {
            $userRoles     = $user->getRoleNames()->toArray();
            $announcements = Announcement::with('postedBy')
                ->where(function ($q) use ($user, $userRoles) {
                    $q->where('audience_type', 'all');

                    if ($user->hasRole('student')) {
                        $q->orWhere('audience_type', 'all_students');
                        $student = Student::where('user_id', $user->id)->first();
                        if ($student) {
                            $q->orWhere(function ($sq) use ($student) {
                                $sq->where('audience_type', 'class')
                                   ->whereJsonContains('audience_ids', $student->class_id);
                            });
                        }
                    }

                    if ($user->hasRole('parent')) {
                        $q->orWhere('audience_type', 'all_parents');
                    }

                    foreach ($userRoles as $role) {
                        $q->orWhere(function ($sq) use ($role) {
                            $sq->where('audience_type', 'role')
                               ->whereJsonContains('audience_ids', $role);
                        });
                    }
                })
                ->latest()
                ->get();
        }

        $classes = SchoolClass::orderBy('order')->get(['id', 'name']);
        $roles   = Role::orderBy('name')->pluck('name')->toArray();

        return view('tenant.announcements.index', compact('announcements', 'classes', 'roles'));
    }

    public function store(StoreAnnouncementRequest $request): RedirectResponse
    {
        $host     = request()->getSchemeAndHttpHost();
        $validated = $request->validated();

        try {
            $audienceType = $validated['audience_type'] ?? 'all';
            $audienceIds  = in_array($audienceType, ['class', 'role'])
                ? ($validated['audience_ids'] ?? null)
                : null;

            $announcement = Announcement::create([
                'title'         => $validated['title'],
                'body'          => $validated['body'],
                'is_public'     => (bool) ($validated['is_public'] ?? false),
                'audience_type' => $audienceType,
                'audience_ids'  => $audienceIds,
                'posted_by'     => Auth::id(),
            ]);

            SendAnnouncementNotifications::dispatch($announcement->id, tenant('id'));

            SendWebhookPayload::dispatch(tenant('id'), 'announcement_posted', [
                'event'     => 'announcement_posted',
                'tenant'    => tenant('id'),
                'timestamp' => now()->toIso8601String(),
                'data'      => [
                    'announcement_id' => $announcement->id,
                    'title'           => $announcement->title,
                    'audience_type'   => $audienceType,
                ],
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
        $host      = request()->getSchemeAndHttpHost();
        $validated = $request->validated();

        try {
            $audienceType = $validated['audience_type'] ?? 'all';
            $audienceIds  = in_array($audienceType, ['class', 'role'])
                ? ($validated['audience_ids'] ?? null)
                : null;

            $announcement->update([
                'title'         => $validated['title'],
                'body'          => $validated['body'],
                'is_public'     => (bool) ($validated['is_public'] ?? false),
                'audience_type' => $audienceType,
                'audience_ids'  => $audienceIds,
            ]);

            SendAnnouncementNotifications::dispatch($announcement->id, tenant('id'));

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
