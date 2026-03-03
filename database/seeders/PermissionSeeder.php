<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        try {
            $permissionModel = config('laravel-module-suite.permissions.model');
            $defaultPermissions = config('laravel-module-suite.permissions.default_permissions', []);

            foreach ($defaultPermissions as $action) {
                $permissionModel::firstOrCreate(
                    ['name' => $action['name']],
                    [
                        'type'          => $action['type'] ?? null,
                        'description'   => $action['description'] ?? null,
                    ]
                );
            }

            $permissions = [
                // 'examplemodules' => ['list', 'show', 'create', 'edit', 'delete'],
            ];

            if (!empty($permissions)) {
                foreach ($permissions as $module => $actions) {
                    foreach ($actions as $action) {
                        $permissionModel::firstOrCreate([
                            'name' => "{$action} {$module}",
                        ]);
                    }
                }
            }

        } catch (\Exception $e) {
            Log::error('Failed to seed permissions: ' . $e->getMessage());
        }
    }
} 