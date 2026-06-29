<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Central\Tenant;
use App\Support\TenantPermissions;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class SeedTenantPermissions extends Command
{
    protected $signature = 'tenants:seed-permissions
                            {subdomain? : The tenant subdomain (e.g. accra)}
                            {--all : Run against every active tenant}';

    protected $description = 'Backfill roles and permissions for one tenant or all tenants. Safe to re-run.';

    public function handle(): int
    {
        $tenants = $this->resolveTenants();

        if ($tenants->isEmpty()) {
            $this->error('No matching tenant(s) found.');
            return self::FAILURE;
        }

        foreach ($tenants as $tenant) {
            $this->info("  → {$tenant->name} ({$tenant->subdomain})");

            $tenant->run(function () {
                app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

                foreach (TenantPermissions::all() as $name) {
                    Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
                }

                foreach (TenantPermissions::byRole() as $roleName => $permissions) {
                    $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
                    $role->syncPermissions($permissions);
                }
            });
        }

        $this->info('Done.');
        return self::SUCCESS;
    }

    private function resolveTenants()
    {
        if ($this->option('all')) {
            return Tenant::all();
        }

        $subdomain = $this->argument('subdomain');

        if (! $subdomain) {
            $this->error('Provide a subdomain or pass --all.');
            return collect();
        }

        $tenant = Tenant::where('subdomain', $subdomain)->first();

        if (! $tenant) {
            $this->error("No tenant found with subdomain \"{$subdomain}\".");
            return collect();
        }

        return collect([$tenant]);
    }
}
