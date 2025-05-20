<?php

namespace Ghazym\ModuleBuilder\Database\Seeders;

use Ghazym\ModuleBuilder\Models\Role;
use Ghazym\ModuleBuilder\Models\Permission;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Create super admin role
        $super_admin = Role::create([
            'name' => 'super Admin',
            'description' => 'Super Administrator with all permissions'
        ]);

        // Assign all permissions to super admin role
        $super_admin->syncPermissions(Permission::query()->get()->pluck('id')->toArray());
    }
} 