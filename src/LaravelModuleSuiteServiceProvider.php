<?php

namespace Ghazym\LaravelModuleSuite;

use Ghazym\LaravelModuleSuite\Commands\MakeModuleCommand;
use Ghazym\LaravelModuleSuite\Middleware\CheckPermission;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

class LaravelModuleSuiteServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Merge configuration
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-module-suite.php', 'laravel-module-suite');
    }

    public function boot(): void
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
                __DIR__.'/../config/laravel-module-suite.php' => config_path('laravel-module-suite.php'),
            ], 'config');

            // Publish migrations
            $this->publishes([
                __DIR__.'/../database/migrations/2023_10_01_000001_create_roles_table.php' => database_path('migrations/2023_10_01_000001_create_roles_table.php'),
                __DIR__.'/../database/migrations/2023_10_01_000002_create_permissions_table.php' => database_path('migrations/2023_10_01_000002_create_permissions_table.php'),
                __DIR__.'/../database/migrations/2023_10_01_000004_create_role_permission_table.php' => database_path('migrations/2023_10_01_000004_create_role_permission_table.php'),
                __DIR__.'/../database/migrations/2023_10_01_000006_create_roleables_table.php' => database_path('migrations/2023_10_01_000006_create_roleables_table.php'),
                __DIR__.'/../database/migrations/2023_10_01_000005_create_media_table.php' => database_path('migrations/2023_10_01_000005_create_media_table.php'),
            ], 'migrations');

            // Publish seeders
            $this->publishes([
                __DIR__.'/../database/seeders/PermissionSeeder.php' => database_path('seeders/PermissionSeeder.php'),
                __DIR__.'/../database/seeders/RoleSeeder.php' => database_path('seeders/RoleSeeder.php'),
            ], 'seeders');

            // Register seeders in DatabaseSeeder
            $this->registerSeeders();
        }

        // Register media disk configuration
        $this->app['config']->set('filesystems.disks.media', [
            'driver' => 'local',
            'root' => storage_path('app/media'),
            'url' => env('APP_URL').'/storage/media',
            'visibility' => 'public',
        ]);

        // Create symbolic link for storage
        if (!file_exists(public_path('storage'))) {
            $this->app->make('files')->link(
                storage_path('app/public'),
                public_path('storage')
            );
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