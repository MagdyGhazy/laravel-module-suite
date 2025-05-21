<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roleModel = config('module-builder.roles.model');
        $permissionModel = config('module-builder.permissions.model');
        // Create super admin role
        $super_admin = $roleModel::firstOrCreate([
            'name' => config('module-builder.roles.default_roles.super_admin.name'),
            'description' => config('module-builder.roles.default_roles.super_admin.description')
        ]);

        // Assign all permissions to super admin role
        $super_admin->syncPermissions($permissionModel::query()->get()->pluck('id')->toArray());
    }
} 