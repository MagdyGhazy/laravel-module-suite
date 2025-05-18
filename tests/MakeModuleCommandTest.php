<?php

namespace Ghazym\ModuleBuilder\Tests;

use Orchestra\Testbench\TestCase;
use Illuminate\Support\Facades\File;
use Ghazym\ModuleBuilder\ModuleBuilderServiceProvider;

class MakeModuleCommandTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [ModuleBuilderServiceProvider::class];
    }

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create necessary directories if they don't exist
        File::makeDirectory(base_path('stubs'), 0755, true, true);
        
        // Copy stub files to the stubs directory
        File::copy(__DIR__ . '/../stubs/service.stub', base_path('stubs/service.stub'));
        File::copy(__DIR__ . '/../stubs/controller.module.stub', base_path('stubs/controller.module.stub'));
    }

    protected function tearDown(): void
    {
        // Clean up created files and directories
        File::deleteDirectory(base_path('stubs'));
        File::deleteDirectory(app_path('Http/Controllers/Api/Test'));
        File::deleteDirectory(app_path('Http/Services/Test'));
        File::deleteDirectory(app_path('Http/Requests/Test'));
        
        parent::tearDown();
    }

    /** @test */
    public function it_can_create_a_module()
    {
        $this->artisan('make:module Test')
            ->expectsOutput('Service, Requests, Routes, permissions, seeder, Model and migration created for Test')
            ->assertExitCode(0);

        // Assert files were created
        $this->assertTrue(File::exists(app_path('Http/Controllers/Api/Test/TestController.php')));
        $this->assertTrue(File::exists(app_path('Http/Services/Test/TestService.php')));
        $this->assertTrue(File::exists(app_path('Http/Requests/Test/StoreTestRequest.php')));
        $this->assertTrue(File::exists(app_path('Http/Requests/Test/UpdateTestRequest.php')));
        $this->assertTrue(File::exists(app_path('Models/Test.php')));
    }
}