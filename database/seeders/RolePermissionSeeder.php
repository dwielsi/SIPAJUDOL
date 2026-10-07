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
            'reports.send',
            'scans.viewAny',
            'scans.view',
            'scans.create',
            'scans.delete',
            'monitoring.view',
            'spk.view',
            'notifications.view',
            'activity_logs.viewAny',
            'settings.manage',
            'users.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Permission yang baru saja dibuat di atas belum tentu masuk ke cache
        // permission Spatie yang dipakai syncPermissions() di bawah, jadi
        // cache-nya perlu dibersihkan lagi sebelum di-sync ke role.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $kabid = Role::firstOrCreate(['name' => RoleEnum::Kabid->value, 'guard_name' => 'web']);
        $kabid->syncPermissions($permissions);
    }
}
