<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\SchoolProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

final class NotificationsController extends Controller
{
    private const EVENTS = [
        'absent_alert'           => 'Absent Alert',
        'fee_overdue_reminder'   => 'Fee Overdue Reminder',
        'exam_results_published' => 'Exam Results Published',
        'payment_confirmation'   => 'Payment Confirmation',
        'welcome_email'          => 'Welcome Email',
    ];

    public function index(): View
    {
        $profile  = SchoolProfile::first();
        $settings = $this->resolveSettings($profile?->notification_settings);

        return view('tenant.settings.notifications', [
            'settings' => $settings,
            'events'   => self::EVENTS,
        ]);
    }

    public function save(Request $request): RedirectResponse
    {
        $host = request()->getSchemeAndHttpHost();

        $settings = [];
        foreach (array_keys(self::EVENTS) as $key) {
            $settings[$key] = [
                'email' => (bool) $request->boolean("email_{$key}"),
                'sms'   => false,
            ];
        }

        try {
            $profile = SchoolProfile::first() ?? new SchoolProfile();
            $profile->fill(['notification_settings' => $settings])->save();
        } catch (\Throwable $e) {
            Log::error('[NotificationsController::save] ' . $e->getMessage());

            return redirect($host . '/settings/notifications')->with('error', 'Could not save settings. Please try again.');
        }

        return redirect($host . '/settings/notifications')->with('success', 'Notification preferences saved.');
    }

    public function test(Request $request, string $event): RedirectResponse
    {
        $host = request()->getSchemeAndHttpHost();

        if (! array_key_exists($event, self::EVENTS)) {
            return redirect($host . '/settings/notifications')->with('error', 'Unknown event type.');
        }

        $user = Auth::user();

        if (! $user?->email) {
            return redirect($host . '/settings/notifications')->with('error', 'Your account has no email address set.');
        }

        try {
            Mail::raw(
                "This is a test notification for the \"" . self::EVENTS[$event] . "\" event from Skolet.\n\nIf you received this, your email settings are working correctly.",
                fn ($m) => $m->to($user->email)->subject('[Test] ' . self::EVENTS[$event] . ' — Skolet')
            );
        } catch (\Throwable $e) {
            Log::error('[NotificationsController::test] ' . $e->getMessage());

            return redirect($host . '/settings/notifications')->with('error', 'Test email failed: ' . $e->getMessage());
        }

        return redirect($host . '/settings/notifications')->with('success', 'Test email sent to ' . $user->email . '.');
    }

    /** @param array<string, mixed>|null $stored */
    private function resolveSettings(?array $stored): array
    {
        $defaults = array_fill_keys(array_keys(self::EVENTS), ['email' => true, 'sms' => false]);

        if (! $stored) {
            return $defaults;
        }

        foreach ($defaults as $key => $default) {
            if (! isset($stored[$key])) {
                $stored[$key] = $default;
            }
        }

        return $stored;
    }
}
