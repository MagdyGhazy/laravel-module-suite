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

    protected function initializeNames(): void
    {
        $name = ucfirst($this->argument('name'));
        $small_name = strtolower($name);
        $plural_name = Str::plural($name);
        $small_plural_name = Str::plural($small_name);
        
        $this->names = [
            'name' => $name,
            'small_name' => $small_name,
            'class_name' => $name . 'Service',
            'controller_name' => $name . 'Controller',
            'seeder_name' => $name . 'Seeder',
            'store_request' => 'Store' . $name . 'Request',
            'update_request' => 'Update' . $name . 'Request',
            'plural_name' => $plural_name,
            'small_plural_name' => $small_plural_name,
        ];
    }

    protected function initializePaths(): void
    {
        $this->paths = [
            'service_directory' => app_path("Http/Services/{$this->names['name']}"),
            'service_path' => app_path("Http/Services/{$this->names['name']}/{$this->names['class_name']}.php"),
            'controller_directory' => app_path("Http/Controllers/Api/{$this->names['name']}"),
            'controller_path' => app_path("Http/Controllers/Api/{$this->names['name']}/{$this->names['controller_name']}.php"),
            'service_stub' => __DIR__ . '/../../stubs/service.stub',
            'controller_stub' => __DIR__ . '/../../stubs/controller.module.stub',
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
            $this->error("Stub file not found at: {$this->paths['service_stub']}");
            return false;
        }

        if (!File::exists($this->paths['controller_stub'])) {
            $this->error("Stub file not found at: {$this->paths['controller_stub']}");
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
        $serviceContent = File::get($this->paths['service_stub']);
        $serviceContent = $this->replacePlaceholders($serviceContent);
        File::put($this->paths['service_path'], $serviceContent);

        $controllerContent = File::get($this->paths['controller_stub']);
        $controllerContent = $this->replacePlaceholders($controllerContent);
        File::put($this->paths['controller_path'], $controllerContent);
    }

    protected function replacePlaceholders(string $content): string
    {
        $replacements = [
            '{{ name }}' => $this->names['name'],
            '{{ service_name }}' => $this->names['class_name'],
            '{{ controller_name }}' => $this->names['controller_name'],
            '{{ store_request }}' => $this->names['store_request'],
            '{{ update_request }}' => $this->names['update_request'],
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $content);
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