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

            Mail::to($data['email'])->queue(new WelcomeCredentialsMail(
                recipientName:  $data['full_name'],
                recipientEmail: $data['email'],
                plainPassword:  $plainPassword,
                loginUrl:       $loginUrl,
            ));

            return redirect(request()->getSchemeAndHttpHost() . '/staff')
                ->with('success', 'Staff member added. Login credentials sent to ' . $data['email'] . '.');
        } catch (\Throwable $e) {
            \Log::error('[staff.store] ' . $e->getMessage());

            return back()->withInput()->with('error', 'Could not add staff member. Please try again.');
        }
    }

    public function show(Staff $staff): View
    {
        $staff->load('user');

        return view('tenant.staff.show', compact('staff'));
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
            // Deleting the user cascades to the staff record via FK onDelete cascade
            $staff->user->delete();

            return redirect(request()->getSchemeAndHttpHost() . '/staff')
                ->with('success', 'Staff member removed successfully.');
        } catch (\Throwable $e) {
            \Log::error('[staff.destroy] ' . $e->getMessage());

            return back()->with('error', 'Could not remove staff member. Please try again.');
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
