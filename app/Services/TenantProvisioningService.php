<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Central\Domain;
use App\Models\Central\SubscriptionPlan;
use App\Models\Central\Tenant;
use App\Models\Tenant\ExpenseCategory;
use App\Models\Tenant\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class TenantProvisioningService
{
    public function provision(
        string $schoolName,
        string $subdomain,
        string $adminName,
        string $adminEmail,
        string $adminPassword,
        ?string $adminPhone = null,
    ): array {
        $tenantDomain = $this->buildTenantDomain($subdomain);

        if (Domain::where('domain', $tenantDomain)->exists()) {
            return ['success' => false, 'error' => 'That subdomain is already taken. Please choose another.'];
        }

        $tenant = null;

        try {
            // Creating the Tenant record fires TenantCreated → CreateDatabase + MigrateDatabase (synchronous)
            $tenant = Tenant::create([
                'name'      => $schoolName,
                'subdomain' => $subdomain,
                'status'    => 'active',
            ]);

            $tenant->domains()->create(['domain' => $tenantDomain]);

            // Create the initial subscription_plans row (central DB, trial status)
            SubscriptionPlan::create([
                'tenant_id'        => $tenant->id,
                'rate_per_student' => config('skolet.default_rate_per_student', 5.00),
                'student_count'    => 0,
                'amount_due'       => 0,
                'payment_status'   => 'unpaid',
                'status'           => 'trial',
                'cycle_start'      => now()->toDateString(),
                'cycle_end'        => now()->addYear()->toDateString(),
            ]);

            // Switch to tenant DB context: seed permissions, roles, create School Admin
            $tenant->run(function () use ($adminName, $adminEmail, $adminPassword): void {
                $this->seedPermissions();
                $this->seedExpenseCategories();

                $user = User::create([
                    'name'     => $adminName,
                    'email'    => $adminEmail,
                    'password' => Hash::make($adminPassword),
                    'role'     => 'school_admin',
                ]);

                $user->assignRole('school_admin');
            });

            return [
                'success' => true,
                'data'    => [
                    'tenant'         => $tenant,
                    'domain'         => $tenantDomain,
                    'admin_email'    => $adminEmail,
                    'admin_name'     => $adminName,
                    'admin_password' => $adminPassword,
                    'admin_phone'    => $adminPhone,
                ],
                'error'   => null,
            ];
        } catch (\Throwable $e) {
            Log::error('[TenantProvisioningService::provision] ' . $e->getMessage(), [
                'subdomain'   => $subdomain,
                'school_name' => $schoolName,
            ]);

            // TenantDeleted event fires DeleteDatabase — drops the provisioned DB
            if ($tenant !== null) {
                try {
                    $tenant->delete();
                } catch (\Throwable $cleanupException) {
                    Log::error('[TenantProvisioningService::provision] cleanup failed: ' . $cleanupException->getMessage());
                }
            }

            return [
                'success' => false,
                'data'    => null,
                'error'   => 'Could not register your school. Please try again or contact support.',
            ];
        }
    }

    private function seedPermissions(): void
    {
        $all = [
            'students.view', 'students.create', 'students.edit', 'students.delete',
            'staff.view', 'staff.create', 'staff.edit', 'staff.delete',
            'attendance.view', 'attendance.edit',
            'timetable.view', 'timetable.edit',
            'exams.view', 'exams.create', 'exams.edit', 'exams.delete',
            'fees.view', 'fees.create', 'fees.edit',
            'announcements.view', 'announcements.create', 'announcements.edit', 'announcements.delete',
            'assignments.view', 'assignments.create', 'assignments.edit', 'assignments.delete', 'assignments.submit',
            'behavior.view', 'behavior.create', 'behavior.edit', 'behavior.delete',
            'expenses.view', 'expenses.create', 'expenses.edit', 'expenses.delete',
            'admissions.view', 'admissions.manage',
            'payroll.view', 'payroll.create', 'payroll.edit',
            'leave.view', 'leave.manage',
            'register.view', 'register.create', 'register.manage',
            'reports.view',
            'settings.manage',
        ];

        foreach ($all as $name) {
            Permission::create(['name' => $name, 'guard_name' => 'web']);
        }

        $rolePermissions = [
            'school_admin' => $all,
            'teacher'      => [
                'students.view',
                'attendance.view', 'attendance.edit',
                'timetable.view', 'timetable.edit',
                'exams.view', 'exams.create', 'exams.edit',
                'announcements.view', 'announcements.create', 'announcements.edit',
                'assignments.view', 'assignments.create', 'assignments.edit', 'assignments.delete',
                'behavior.view', 'behavior.create',
                'leave.view',
                'register.view', 'register.create',
            ],
            'accountant' => [
                'fees.view', 'fees.create', 'fees.edit',
                'expenses.view', 'expenses.create', 'expenses.edit', 'expenses.delete',
                'payroll.view', 'payroll.create', 'payroll.edit',
                'leave.view',
                'reports.view',
                'announcements.view',
            ],
            'student' => [
                'attendance.view',
                'exams.view',
                'fees.view',
                'announcements.view',
                'assignments.view', 'assignments.submit',
            ],
            'parent' => [
                'attendance.view',
                'exams.view',
                'fees.view',
                'announcements.view',
                'assignments.view',
            ],
        ];

        foreach ($rolePermissions as $roleName => $permissions) {
            $role = Role::create(['name' => $roleName, 'guard_name' => 'web']);
            $role->givePermissionTo($permissions);
        }
    }

    private function seedExpenseCategories(): void
    {
        $defaults = ['Salaries', 'Utilities', 'Supplies', 'Maintenance', 'Events', 'Other'];

        foreach ($defaults as $name) {
            ExpenseCategory::firstOrCreate(['name' => $name]);
        }
    }

    private function buildTenantDomain(string $subdomain): string
    {
        $appHost = parse_url(config('app.url'), PHP_URL_HOST) ?? 'skolet.com';

        // Strip 'www.' so demo.www.skolet.com is never produced
        $baseHost = (string) preg_replace('/^www\./i', '', $appHost);

        return $subdomain . '.' . $baseHost;
    }
}
