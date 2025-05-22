<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roleModel = config('laravel-module-suite.roles.model');
        $permissionModel = config('laravel-module-suite.permissions.model');
        // Create super admin role
        $super_admin = $roleModel::firstOrCreate([
            'name' => config('laravel-module-suite.roles.default_roles.super_admin.name'),
            'description' => config('laravel-module-suite.roles.default_roles.super_admin.description')
        ]);

        // Assign all permissions to super admin role
        $super_admin->syncPermissions($permissionModel::query()->get()->pluck('id')->toArray());
    }
} 