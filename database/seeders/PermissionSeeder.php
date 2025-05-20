<?php

namespace Ghazym\ModuleBuilder\Database\Seeders;

use Ghazym\ModuleBuilder\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'users'              => ['list', 'show', 'create', 'edit', 'delete'],
            'roles'              => ['list', 'show', 'create', 'edit', 'delete'],
            'permissions'        => ['list', 'edit'],
        ];

        foreach ($permissions as $module => $actions) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name' => "{$action} {$module}",
                ]);
            }
        }
    }
} 