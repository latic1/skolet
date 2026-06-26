<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Exports\StaffImportTemplate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\ImportStaffRequest;
use App\Http\Requests\Tenant\StoreStaffRequest;
use App\Http\Requests\Tenant\UpdateStaffRequest;
use App\Imports\StaffImport;
use App\Mail\WelcomeCredentialsMail;
use App\Models\Tenant\Staff;
use App\Models\Tenant\User;
use App\Services\SmsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class StaffController extends Controller
{
    public function index(): View
    {
        $query = Staff::with('user')->orderBy('full_name');

        if (request()->filled('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($u) => $u->where('email', 'like', "%{$search}%"));
            });
        }

        if (request()->filled('status')) {
            $query->where('status', request('status'));
        }

        $staff = $query->paginate(25)->withQueryString();

        return view('tenant.staff.index', compact('staff'));
    }

    public function create(): View
    {
        $roles = Role::whereNotIn('name', ['school_admin', 'student', 'parent'])->orderBy('name')->get();

        return view('tenant.staff.create', compact('roles'));
    }

    public function store(StoreStaffRequest $request): RedirectResponse
    {
        try {
            $data          = $request->validated();
            $plainPassword = Str::password(12);

            DB::transaction(function () use ($data, $plainPassword): void {
                $user = User::create([
                    'name'     => $data['full_name'],
                    'email'    => $data['email'],
                    'password' => $plainPassword,
                    'role'     => $data['system_role'],
                ]);

                $user->assignRole($data['system_role']);

                Staff::create([
                    'user_id'    => $user->id,
                    'full_name'  => $data['full_name'],
                    'role_title' => $data['role_title'] ?? null,
                    'phone'      => $data['phone'] ?? null,
                    'status'     => $data['status'],
                ]);
            });

            $loginUrl = request()->getSchemeAndHttpHost() . '/login';

            try {
                Mail::to($data['email'])->queue(new WelcomeCredentialsMail(
                    recipientName:  $data['full_name'],
                    recipientEmail: $data['email'],
                    plainPassword:  $plainPassword,
                    loginUrl:       $loginUrl,
                ));
            } catch (\Throwable) {}

            $phone = $data['phone'] ?? null;
            if ($phone) {
                $smsBody = "Skolet: Your account is ready!\nLogin: {$loginUrl}\nEmail: {$data['email']}\nPassword: {$plainPassword}";
                app(SmsService::class)->send($phone, $smsBody);
            }

            return redirect(request()->getSchemeAndHttpHost() . '/staff')
                ->with('success', 'Staff member added. Login credentials sent to ' . $data['email'] . ($phone ? ' and via SMS.' : '.'));
        } catch (\Throwable $e) {
            \Log::error('[staff.store] ' . $e->getMessage());

            return back()->withInput()->with('error', 'Could not add staff member. Please try again.');
        }
    }

    public function show(Staff $staff): View
    {
        $staff->load(['user', 'assignments.subject', 'assignments.schoolClass', 'assignments.section']);

        $classes  = \App\Models\Tenant\SchoolClass::with('sections')->orderBy('order')->get();
        $subjects = \App\Models\Tenant\Subject::orderBy('name')->get();

        return view('tenant.staff.show', compact('staff', 'classes', 'subjects'));
    }

    public function edit(Staff $staff): View
    {
        $staff->load('user');
        $roles = Role::whereNotIn('name', ['school_admin', 'student', 'parent'])->orderBy('name')->get();

        return view('tenant.staff.edit', compact('staff', 'roles'));
    }

    public function update(UpdateStaffRequest $request, Staff $staff): RedirectResponse
    {
        try {
            $data = $request->validated();

            DB::transaction(function () use ($data, $staff): void {
                $staff->update([
                    'full_name'  => $data['full_name'],
                    'role_title' => $data['role_title'] ?? null,
                    'phone'      => $data['phone'] ?? null,
                    'status'     => $data['status'],
                ]);

                $userUpdate = [
                    'name'  => $data['full_name'],
                    'email' => $data['email'],
                    'role'  => $data['system_role'],
                ];

                if (!empty($data['new_password'])) {
                    $userUpdate['password'] = $data['new_password'];
                }

                $staff->user->update($userUpdate);
                $staff->user->syncRoles([$data['system_role']]);
            });

            return redirect(request()->getSchemeAndHttpHost() . '/staff/' . $staff->id)
                ->with('success', 'Staff member updated successfully.');
        } catch (\Throwable $e) {
            \Log::error('[staff.update] ' . $e->getMessage());

            return back()->withInput()->with('error', 'Could not update staff member. Please try again.');
        }
    }

    public function destroy(Staff $staff): RedirectResponse
    {
        try {
            $name = $staff->full_name;
            $staff->delete();
            $staff->user?->delete();

            return redirect(request()->getSchemeAndHttpHost() . '/staff')
                ->with('success', $name . ' moved to trash.');
        } catch (\Throwable $e) {
            \Log::error('[staff.destroy] ' . $e->getMessage());

            return back()->with('error', 'Could not remove staff member. Please try again.');
        }
    }

    public function trash(): View
    {
        $staff = Staff::onlyTrashed()->latest('deleted_at')->get();

        return view('tenant.staff.trash', compact('staff'));
    }

    public function restore(string $id): RedirectResponse
    {
        try {
            $member = Staff::onlyTrashed()->findOrFail($id);
            $member->restore();
            User::onlyTrashed()->where('id', $member->user_id)->restore();

            return redirect(request()->getSchemeAndHttpHost() . '/staff')
                ->with('success', $member->full_name . ' restored successfully.');
        } catch (\Throwable $e) {
            \Log::error('[staff.restore] ' . $e->getMessage());

            return back()->with('error', 'Could not restore staff member. Please try again.');
        }
    }

    public function forceDelete(string $id): RedirectResponse
    {
        try {
            $member = Staff::onlyTrashed()->findOrFail($id);
            $member->forceDelete();
            User::onlyTrashed()->where('id', $member->user_id)->forceDelete();

            return redirect(request()->getSchemeAndHttpHost() . '/staff/trash')
                ->with('success', 'Staff member permanently deleted.');
        } catch (\Throwable $e) {
            \Log::error('[staff.forceDelete] ' . $e->getMessage());

            return back()->with('error', 'Could not permanently delete staff member. Please try again.');
        }
    }

    public function resendCredentials(Staff $staff): RedirectResponse
    {
        $phone = $staff->phone;

        if (empty($phone)) {
            return back()->with('error', 'This staff member has no phone number on record. Update their profile first.');
        }

        try {
            $plainPassword = Str::password(12);

            $staff->user->update(['password' => $plainPassword]);

            $loginUrl = request()->getSchemeAndHttpHost() . '/login';
            $smsBody  = "Skolet: Your login credentials have been reset.\nLogin: {$loginUrl}\nEmail: {$staff->user->email}\nPassword: {$plainPassword}";

            $sent = app(SmsService::class)->send($phone, $smsBody);

            if (! $sent) {
                return back()->with('error', 'Password was reset but SMS could not be delivered. Check the phone number.');
            }

            return back()->with('success', 'New credentials sent via SMS to ' . $phone . '.');
        } catch (\Throwable $e) {
            \Log::error('[staff.resendCredentials] ' . $e->getMessage());

            return back()->with('error', 'Could not resend credentials. Please try again.');
        }
    }

    public function import(ImportStaffRequest $request): RedirectResponse
    {
        try {
            $import = new StaffImport();
            Excel::import($import, $request->file('import_file'));

            if (!empty($import->errors)) {
                return back()->with('staff_import_errors', $import->errors);
            }

            $count = $import->imported;
            $noun  = $count !== 1 ? 'staff members' : 'staff member';

            $redirect = redirect(request()->getSchemeAndHttpHost() . '/staff')
                ->with('success', "{$count} {$noun} imported successfully.");

            if (!empty($import->credentials)) {
                $redirect = $redirect->with('staff_import_credentials', $import->credentials);
            }

            return $redirect;
        } catch (\Throwable $e) {
            \Log::error('[staff.import] ' . $e->getMessage());

            return back()->with('error', 'Could not process the file. Make sure it matches the template format.');
        }
    }

    public function downloadTemplate(): BinaryFileResponse
    {
        return Excel::download(new StaffImportTemplate(), 'skolet-staff-import-template.xlsx');
    }
}
