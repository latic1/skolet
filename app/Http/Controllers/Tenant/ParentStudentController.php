<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Student;
use App\Models\Tenant\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class ParentStudentController extends Controller
{
    public function store(Request $request, Student $student): RedirectResponse
    {
        $host = $request->getSchemeAndHttpHost();

        $data = $request->validate([
            'mode'                  => ['required', 'in:create,link'],
            'relationship'          => ['nullable', 'string', 'max:50'],
            'name'                  => ['required_if:mode,create', 'nullable', 'string', 'max:255'],
            'email'                 => ['required_if:mode,create', 'nullable', 'email', 'max:255'],
            'phone'                 => ['nullable', 'string', 'max:30'],
            'password'              => ['required_if:mode,create', 'nullable', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['nullable', 'string'],
            'parent_email'          => ['required_if:mode,link', 'nullable', 'email'],
        ]);

        try {
            DB::transaction(function () use ($data, $student): void {
                if ($data['mode'] === 'create') {
                    if (User::where('email', $data['email'])->exists()) {
                        throw new \RuntimeException('A user with that email already exists. Use "Link existing account" instead.');
                    }

                    $user = User::create([
                        'name'     => $data['name'],
                        'email'    => $data['email'],
                        'password' => $data['password'],
                        'phone'    => $data['phone'] ?? null,
                    ]);
                    $user->assignRole('parent');
                } else {
                    $user = User::where('email', $data['parent_email'])->first();
                    if (!$user) {
                        throw new \RuntimeException('No account found with that email address.');
                    }
                }

                if ($student->parents()->where('user_id', $user->id)->exists()) {
                    throw new \RuntimeException('This parent is already linked to this student.');
                }

                $student->parents()->attach($user->id, [
                    'id'           => Str::uuid()->toString(),
                    'relationship' => $data['relationship'] ?? null,
                ]);
            });

            return redirect($host . '/students/' . $student->id)
                ->with('success', 'Parent account linked to ' . $student->full_name . '.');
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('[parents.store] ' . $e->getMessage());

            return back()->withInput()->with('error', 'Could not link parent account. Please try again.');
        }
    }

    public function destroy(Student $student, User $parentUser): RedirectResponse
    {
        $host = request()->getSchemeAndHttpHost();

        try {
            DB::transaction(function () use ($student, $parentUser): void {
                $student->parents()->detach($parentUser->id);

                // Delete the parent account if they have no other linked children
                if ($parentUser->hasRole('parent') && $parentUser->linkedChildren()->count() === 0) {
                    $parentUser->delete();
                }
            });

            return redirect($host . '/students/' . $student->id)
                ->with('success', 'Parent unlinked from ' . $student->full_name . '.');
        } catch (\Throwable $e) {
            Log::error('[parents.destroy] ' . $e->getMessage());

            return back()->with('error', 'Could not unlink parent. Please try again.');
        }
    }
}
