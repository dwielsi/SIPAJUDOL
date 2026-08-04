<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'dashboard.view',
            'websites.viewAny',
            'websites.view',
            'websites.create',
            'websites.update',
            'websites.delete',
            'websites.restore',
            'websites.forceDelete',
            'reports.viewAny',
            'reports.view',
            'reports.create',
            'reports.update',
            'reports.delete',
            'reports.print',
            'scans.viewAny',
            'scans.view',
            'scans.create',
            'scans.delete',
            'monitoring.view',
            'notifications.view',
            'keywords.viewAny',
            'keywords.create',
            'keywords.update',
            'keywords.delete',
            'activity_logs.viewAny',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $kabid = Role::firstOrCreate(['name' => RoleEnum::Kabid->value, 'guard_name' => 'web']);
        $kabid->syncPermissions($permissions);

        $operator = Role::firstOrCreate(['name' => RoleEnum::Operator->value, 'guard_name' => 'web']);
        $operator->syncPermissions([
            'dashboard.view',
            'websites.viewAny',
            'websites.view',
            'reports.viewAny',
            'reports.view',
            'reports.create',
            'reports.print',
            'scans.viewAny',
            'scans.view',
            'scans.create',
            'monitoring.view',
            'notifications.view',
        ]);
    }
}
