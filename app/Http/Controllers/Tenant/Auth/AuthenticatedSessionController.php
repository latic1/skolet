<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Central\SubscriptionPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('tenant.auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        // Honeypot: any non-empty submission is a bot.
        if ($request->filled('hp_check')) {
            return back()->withErrors(['email' => 'Invalid credentials.']);
        }

        $request->authenticate();

        // Block suspended tenants (manual Super Admin disable)
        $tenant = tenant();
        if ($tenant !== null && $tenant->status === 'suspended') {
            Auth::logout();
            $request->session()->invalidate();
            return back()->withErrors([
                'email' => 'This school account has been suspended. Please contact Skolet support.',
            ]);
        }

        // Block expired subscriptions
        if ($tenant !== null) {
            $plan = SubscriptionPlan::where('tenant_id', $tenant->id)->first();
            if ($plan !== null && $plan->status === 'expired') {
                Auth::logout();
                $request->session()->invalidate();
                return back()->withErrors([
                    'email' => 'Your subscription has expired. Please contact Skolet support to renew.',
                ]);
            }
        }

        $request->session()->regenerate();

        return redirect()->intended($request->getSchemeAndHttpHost() . '/dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect($request->getSchemeAndHttpHost() . '/login');
    }
}
