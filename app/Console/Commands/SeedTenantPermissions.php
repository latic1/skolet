<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Central\Tenant;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class SeedTenantPermissions extends Command
{
    protected $signature = 'tenants:seed-permissions {subdomain : The tenant subdomain (e.g. accra)}';

    protected $description = 'Seed (or re-seed) roles and permissions for a tenant. Safe to run on existing tenants.';

    public function handle(): int
    {
        $subdomain = $this->argument('subdomain');

        $tenant = Tenant::where('subdomain', $subdomain)->first();

        if (! $tenant) {
            $this->error("No tenant found with subdomain \"{$subdomain}\".");

            return self::FAILURE;
        }

        $this->info("Seeding permissions for tenant: {$tenant->name} ({$subdomain})");

        $tenant->run(function () {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            $all = [
                'students.view', 'students.create', 'students.edit', 'students.delete',
                'staff.view', 'staff.create', 'staff.edit', 'staff.delete',
                'attendance.view', 'attendance.edit',
                'timetable.view', 'timetable.edit',
                'exams.view', 'exams.create', 'exams.edit', 'exams.delete',
                'fees.view', 'fees.create', 'fees.edit',
                'announcements.view', 'announcements.create', 'announcements.edit', 'announcements.delete',
                'admissions.view', 'admissions.manage',
                'reports.view',
                'settings.manage',
            ];

            foreach ($all as $name) {
                Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
            }

            $rolePermissions = [
                'school_admin' => $all,
                'teacher' => [
                    'students.view',
                    'attendance.view', 'attendance.edit',
                    'timetable.view', 'timetable.edit',
                    'exams.view', 'exams.create', 'exams.edit',
                    'announcements.view', 'announcements.create', 'announcements.edit',
                ],
                'accountant' => [
                    'fees.view', 'fees.create', 'fees.edit',
                    'reports.view',
                    'announcements.view',
                ],
                'student' => [
                    'attendance.view',
                    'exams.view',
                    'fees.view',
                    'announcements.view',
                ],
                'parent' => [
                    'attendance.view',
                    'exams.view',
                    'fees.view',
                    'announcements.view',
                ],
            ];

            foreach ($rolePermissions as $roleName => $permissions) {
                $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
                $role->syncPermissions($permissions);
            }
        });

        $this->info('Done. Roles and permissions seeded successfully.');
        $this->line('');
        $this->line('If the admin user is not assigned the school_admin role, run:');
        $this->line("  php artisan tinker");
        $this->line("  >>> \$t = App\\Models\\Central\\Tenant::where('subdomain','$subdomain')->first();");
        $this->line("  >>> \$t->run(fn() => App\\Models\\Tenant\\User::where('email','john@accraacademy.com')->first()->assignRole('school_admin'));");

        return self::SUCCESS;
    }
}
