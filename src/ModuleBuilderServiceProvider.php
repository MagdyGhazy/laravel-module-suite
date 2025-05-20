<?php

namespace Ghazym\ModuleBuilder;

use Ghazym\ModuleBuilder\Commands\MakeModuleCommand;
use Ghazym\ModuleBuilder\Middleware\CheckPermission;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

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

        // Load package routes with api prefix
        Route::prefix('api')->group(function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        });

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

            // Register seeders in DatabaseSeeder
            $this->registerSeeders();
        }
    }

    protected function registerSeeders(): void
    {
        $databaseSeederPath = database_path('seeders/DatabaseSeeder.php');
        
        if (!File::exists($databaseSeederPath)) {
            return;
        }

        $content = File::get($databaseSeederPath);
        
        // Check if seeders are already registered
        if (str_contains($content, 'PermissionSeeder') && str_contains($content, 'RoleSeeder')) {
            return;
        }

        // Add use statements if they don't exist
        if (!str_contains($content, 'use Ghazym\ModuleBuilder\Database\Seeders\PermissionSeeder;')) {
            $content = str_replace(
                '<?php',
                "<?php\n\nuse Ghazym\ModuleBuilder\Database\Seeders\PermissionSeeder;\nuse Ghazym\ModuleBuilder\Database\Seeders\RoleSeeder;",
                $content
            );
        }

        // Add seeder calls in the run method
        if (str_contains($content, 'public function run(): void')) {
            $content = preg_replace(
                '/(public function run\(\): void\s*{)/',
                "$1\n        \$this->call(PermissionSeeder::class);\n        \$this->call(RoleSeeder::class);",
                $content
            );
        }

        File::put($databaseSeederPath, $content);
    }
}