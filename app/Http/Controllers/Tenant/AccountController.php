<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\UpdateAccountRequest;
use App\Http\Requests\Tenant\UpdatePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class AccountController extends Controller
{
    public function edit(): View
    {
        $user      = auth()->user();
        $avatarUrl = $user->avatar_path && Storage::disk('public')->exists($user->avatar_path)
            ? request()->getSchemeAndHttpHost() . '/account/avatar'
            : null;

        return view('tenant.account.edit', compact('user', 'avatarUrl'));
    }

    public function update(UpdateAccountRequest $request): RedirectResponse
    {
        $user = auth()->user();
        $data = collect($request->validated())->except('avatar')->toArray();

        try {
            if ($request->hasFile('avatar')) {
                if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
                    Storage::disk('public')->delete($user->avatar_path);
                }

                $tenantId = tenant('id');
                $ext      = $request->file('avatar')->getClientOriginalExtension();
                $path     = $request->file('avatar')->storeAs(
                    "avatars/{$tenantId}/{$user->id}",
                    "avatar.{$ext}",
                    'public'
                );
                $data['avatar_path'] = $path;
            }

            $user->fill($data)->save();

            return redirect(request()->getSchemeAndHttpHost() . '/account')
                ->with('success', 'Profile updated successfully.');
        } catch (\Throwable) {
            return back()->withInput()->with('error', 'Failed to update profile. Please try again.');
        }
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        try {
            auth()->user()->update(['password' => $request->validated('new_password')]);

            return redirect(request()->getSchemeAndHttpHost() . '/account')
                ->with('success', 'Password changed successfully.');
        } catch (\Throwable) {
            return back()->with('error', 'Failed to update password. Please try again.');
        }
    }

    public function avatar(): BinaryFileResponse
    {
        $user = auth()->user();

        if (!$user->avatar_path || !Storage::disk('public')->exists($user->avatar_path)) {
            abort(404);
        }

        return response()->file(
            Storage::disk('public')->path($user->avatar_path),
            ['Cache-Control' => 'private, max-age=3600']
        );
    }
}
