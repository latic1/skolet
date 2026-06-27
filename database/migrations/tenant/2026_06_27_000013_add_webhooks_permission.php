<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permission = Permission::firstOrCreate(['name' => 'webhooks.manage', 'guard_name' => 'web']);

        $adminRole = Role::where('name', 'school_admin')->first();
        if ($adminRole && ! $adminRole->hasPermissionTo('webhooks.manage')) {
            $adminRole->givePermissionTo($permission);
        }
    }

    public function down(): void
    {
        Permission::where('name', 'webhooks.manage')->delete();
    }
};
