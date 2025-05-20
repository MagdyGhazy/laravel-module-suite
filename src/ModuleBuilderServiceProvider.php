<?php

namespace Ghazym\ModuleBuilder;

use Ghazym\ModuleBuilder\Commands\MakeModuleCommand;
use Ghazym\ModuleBuilder\Middleware\CheckPermission;
use Illuminate\Support\ServiceProvider;

class ModuleBuilderServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Merge configuration
        $this->mergeConfigFrom(__DIR__.'/../config/module-builder.php', 'module-builder');
    }

    public function boot()
    {
        // Register middleware
        $this->app['router']->aliasMiddleware('permission', CheckPermission::class);

        // Load package routes
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');

        // Register the command
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeModuleCommand::class,
            ]);

            // Publish configuration
            $this->publishes([
                __DIR__.'/../config/module-builder.php' => config_path('module-builder.php'),
            ], 'config');

            // Publish migrations
            $this->publishes([
                __DIR__.'/../database/migrations/2023_10_01_000001_create_roles_table.php' => database_path('migrations/2023_10_01_000001_create_roles_table.php'),
                __DIR__.'/../database/migrations/2023_10_01_000002_create_permissions_table.php' => database_path('migrations/2023_10_01_000002_create_permissions_table.php'),
                __DIR__.'/../database/migrations/2023_10_01_000003_create_user_role_table.php' => database_path('migrations/2023_10_01_000003_create_user_role_table.php'),
                __DIR__.'/../database/migrations/2023_10_01_000004_create_role_permission_table.php' => database_path('migrations/2023_10_01_000004_create_role_permission_table.php'),
            ], 'migrations');

            // Publish seeders
            $this->publishes([
                __DIR__.'/../database/seeders/PermissionSeeder.php' => database_path('seeders/PermissionSeeder.php'),
                __DIR__.'/../database/seeders/RoleSeeder.php' => database_path('seeders/RoleSeeder.php'),
            ], 'seeders');
        }
    }
}