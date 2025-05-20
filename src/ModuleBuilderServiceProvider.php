<?php

namespace Ghazym\ModuleBuilder;

use Ghazym\ModuleBuilder\Commands\MakeModuleCommand;
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
        // Register the command
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeModuleCommand::class,
            ]);

            // Publish configuration
            $this->publishes([
                __DIR__.'/../config/module-builder.php' => config_path('module-builder.php'),
            ], 'config');
        }
    }
}