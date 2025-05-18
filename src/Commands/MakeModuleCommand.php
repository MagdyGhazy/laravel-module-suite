<?php

namespace Ghazym\ModuleBuilder\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Foundation\Application;

class MakeModuleCommand extends Command
{
    protected $signature = 'make:module {name}';

    protected $description = 'Generate a new migration, seeder, model, service, controller, requests, permissions, and routs';

    protected array $paths = [];
    protected array $names = [];

    protected array $stubs = [
        'service.stub',
        'controller.module.stub'
    ];

    public function handle()
    {
        if (!$this->checkAndSetupPrerequisites()) {
            return;
        }

        $this->initializeNames();
        $this->initializePaths();

        if (!$this->checkExistingFiles()) {
            return;
        }

        $this->createDirectories();
        
        if (!$this->checkStubFiles()) {
            return;
        }

        $this->generateArtisanFiles();
        $this->generateServiceAndController();
        $this->appendRoutes();
        $this->updatePermissionSeeder();
        $this->updateDatabaseSeeder();
        $this->updateModuleSeeder();

        $this->info("Service, Requests, Routes, permissions, seeder, Model and migration created for {$this->names['name']}");
    }

    protected function checkAndSetupPrerequisites(): bool
    {
        if (!$this->checkAndPublishStubs()) {
            return false;
        }

        if (!$this->checkAndSetupApiRoute()) {
            return false;
        }

        return true;
    }

    protected function checkAndPublishStubs(): bool
    {
        $stubsPath = base_path('stubs');
        $packageStubsPath = __DIR__ . '/../../stubs';

        if (!File::exists($stubsPath)) {
            $this->info('Publishing Laravel stubs...');
            try {
                Artisan::call('stub:publish');
                $this->info('Laravel stubs published successfully.');
            } catch (\Exception $e) {
                $this->error('Failed to publish stubs: ' . $e->getMessage());
                return false;
            }
        }

        // Copy custom stubs
        try {
            if (!File::exists($packageStubsPath)) {
                $this->error("Package stubs directory not found at: {$packageStubsPath}");
                return false;
            }

            foreach ($this->stubs as $stub) {
                $source = $packageStubsPath . '/' . $stub;
                $destination = $stubsPath . '/' . $stub;

                if (!File::exists($source)) {
                    $this->error("Stub file not found: {$source}");
                    return false;
                }

                File::copy($source, $destination);
                $this->info("Copied {$stub} to application stubs directory.");
            }

            $this->info('Custom stubs copied successfully.');
        } catch (\Exception $e) {
            $this->error('Failed to copy custom stubs: ' . $e->getMessage());
            return false;
        }

        return true;
    }

    protected function checkAndSetupApiRoute(): bool
    {
        $laravelVersion = Application::VERSION;
        
        if (version_compare($laravelVersion, '11.0.0', '>=')) {
            if (!File::exists(base_path('routes/api.php'))) {
                $this->info('Setting up API routes...');
                try {
                    Artisan::call('install:api');
                    $this->info('API routes setup completed.');
                } catch (\Exception $e) {
                    $this->error('Failed to setup API routes: ' . $e->getMessage());
                    return false;
                }
            }
        }
        return true;
    }

    protected function initializeNames(): void
    {
        $this->names = [
            'name' => ucfirst($this->argument('name')),
            'small_name' => strtolower($this->argument('name')),
            'class_name' => ucfirst($this->argument('name')) . 'Service',
            'controller_name' => ucfirst($this->argument('name')) . 'Controller',
            'seeder_name' => ucfirst($this->argument('name')) . 'Seeder',
            'store_request' => 'Store' . ucfirst($this->argument('name')) . 'Request',
            'update_request' => 'Update' . ucfirst($this->argument('name')) . 'Request',
            'plural_name' => Str::plural(ucfirst($this->argument('name'))),
            'small_plural_name' => Str::plural(strtolower($this->argument('name'))),
        ];
    }

    protected function initializePaths(): void
    {
        $this->paths = [
            'service_directory' => app_path("Http/Services/{$this->names['name']}"),
            'service_path' => app_path("Http/Services/{$this->names['name']}/{$this->names['class_name']}.php"),
            'controller_directory' => app_path("Http/Controllers/Api/{$this->names['name']}"),
            'controller_path' => app_path("Http/Controllers/Api/{$this->names['name']}/{$this->names['controller_name']}.php"),
            'service_stub' => base_path('stubs/service.stub'),
            'controller_stub' => base_path('stubs/controller.module.stub'),
        ];
    }

    protected function checkExistingFiles(): bool
    {
        if (File::exists($this->paths['service_path'])) {
            $this->error("Service {$this->names['class_name']} already exists!");
            return false;
        }

        if (File::exists($this->paths['controller_path'])) {
            $this->error("Controller {$this->names['controller_name']} already exists!");
            return false;
        }

        return true;
    }

    protected function createDirectories(): void
    {
        File::makeDirectory($this->paths['service_directory'], 0755, true, true);
        File::makeDirectory($this->paths['controller_directory'], 0755, true, true);
    }

    protected function checkStubFiles(): bool
    {
        if (!File::exists($this->paths['service_stub'])) {
            $this->error("Stub file not found at: stubs/service.stub");
            return false;
        }

        if (!File::exists($this->paths['controller_stub'])) {
            $this->error("Stub file not found at: stubs/controller.module.stub");
            return false;
        }

        return true;
    }

    protected function generateArtisanFiles(): void
    {
        Artisan::call("make:model", ['name' => $this->names['name'], '--migration' => true]);
        Artisan::call("make:request", ['name' => "{$this->names['name']}/{$this->names['store_request']}"]);
        Artisan::call("make:request", ['name' => "{$this->names['name']}/{$this->names['update_request']}"]);
        Artisan::call("make:seeder", ['name' => "{$this->names['seeder_name']}"]);
    }

    protected function generateServiceAndController(): void
    {
        $replacements = [
            '{{ name }}' => $this->names['name'],
            '{{ service_name }}' => $this->names['class_name'],
            '{{ controller_name }}' => $this->names['controller_name'],
            '{{ store_request }}' => $this->names['store_request'],
            '{{ update_request }}' => $this->names['update_request']
        ];

        $service_content = str_replace(
            array_keys($replacements),
            array_values($replacements),
            File::get($this->paths['service_stub'])
        );

        $controller_content = str_replace(
            array_keys($replacements),
            array_values($replacements),
            File::get($this->paths['controller_stub'])
        );

        File::put($this->paths['service_path'], $service_content);
        File::put($this->paths['controller_path'], $controller_content);
    }

    protected function appendRoutes(): void
    {
        $date = now()->format('Y-m-d');
        $routePath = base_path('routes/api.php');
        $routeContent = <<<ROUTE



             /** ===========| {$this->names['name']} |============================| {$date} |================= **/
             Route::group(['prefix' => '{$this->names['small_name']}', 'middleware' => 'auth:sanctum'], function () {
                 Route::controller(\\App\\Http\\Controllers\\Api\\{$this->names['name']}\\{$this->names['controller_name']}::class)->group(function () {
                     Route::get('/', 'index')->middleware('permission:list {$this->names['small_plural_name']}');
                     Route::get('/{id}', 'show')->middleware('permission:show {$this->names['small_plural_name']}');
                     Route::post('/', 'store')->middleware('permission:create {$this->names['small_plural_name']}');
                     Route::put('/{id}', 'update')->middleware('permission:edit {$this->names['small_plural_name']}');
                     Route::delete('/{id}', 'destroy')->middleware('permission:delete {$this->names['small_plural_name']}');
                 });
             });
        ROUTE;

        File::append($routePath, $routeContent);
    }

    protected function updatePermissionSeeder(): void
    {
        $seederPath = database_path('seeders/PermissionSeeder.php');
        $permissionLine = "            '{$this->names['small_plural_name']}' => ['list', 'show', 'create', 'edit', 'delete'],\n        ";

        if (File::exists($seederPath)) {
            $seederContent = File::get($seederPath);

            if (strpos($seederContent, $permissionLine) === false) {
                $seederContent = preg_replace(
                    '/(\$permissions\s*=\s*\[)(.*?)(\];)/s',
                    "$1$2\n{$permissionLine}$3",
                    $seederContent
                );

                File::put($seederPath, $seederContent);
            }
        }
    }

    protected function updateDatabaseSeeder(): void
    {
        $databaseSeederPath = database_path('seeders/DatabaseSeeder.php');
        $seeder_call_line = "        \$this->call({$this->names['seeder_name']}::class);";

        if (File::exists($databaseSeederPath)) {
            $dbSeederContent = File::get($databaseSeederPath);

            if (strpos($dbSeederContent, $seeder_call_line) === false) {
                $dbSeederContent = preg_replace(
                    '/(public function run\(\): void\s*\{\n)(.*?)(\n\s*\})/s',
                    "$1$2\n{$seeder_call_line}$3",
                    $dbSeederContent
                );

                File::put($databaseSeederPath, $dbSeederContent);
            }
        }
    }

    protected function updateModuleSeeder(): void
    {
        $created_seeder_path = database_path("seeders/{$this->names['seeder_name']}.php");
        $seeder_insert_line = "        {$this->names['name']}::create([\n            '' => ''\n        ]);";

        if (File::exists($created_seeder_path)) {
            $seeder_content = File::get($created_seeder_path);

            $model_import_line = "use App\\Models\\{$this->names['name']};";

            if (!Str::contains($seeder_content, $model_import_line)) {
                $seeder_content = preg_replace(
                    '/(namespace\s+Database\\\\Seeders;)/',
                    "$1\n\n{$model_import_line}",
                    $seeder_content
                );
            }

            $seeder_content = preg_replace(
                '/(public function run\(\): void\s*\{\n)(\s*\/\/)/',
                "$1{$seeder_insert_line}",
                $seeder_content
            );

            File::put($created_seeder_path, $seeder_content);
        }
    }
}