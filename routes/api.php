<?php

use Ghazym\LaravelModuleSuite\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'role', 'middleware' => config('laravel-module-suite.auth.middleware')], function () {
    Route::controller(RoleController::class)->group(function () {
        Route::get('/', 'index')->middleware(config('laravel-module-suite.middleware.permission.name') . ':' . config('laravel-module-suite.permissions.default_permissions.list_roles.name'));
        Route::get('/{id}', 'show')->middleware(config('laravel-module-suite.middleware.permission.name') . ':' . config('laravel-module-suite.permissions.default_permissions.show_roles.name'));
        Route::post('/', 'store')->middleware(config('laravel-module-suite.middleware.permission.name') . ':' . config('laravel-module-suite.permissions.default_permissions.create_roles.name'));
        Route::put('/{id}', 'update')->middleware(config('laravel-module-suite.middleware.permission.name') . ':' . config('laravel-module-suite.permissions.default_permissions.edit_roles.name'));
        Route::delete('/{id}', 'destroy')->middleware(config('laravel-module-suite.middleware.permission.name') . ':' . config('laravel-module-suite.permissions.default_permissions.delete_roles.name'));
        Route::get('all/permissions', 'allPermissions')->middleware(config('laravel-module-suite.middleware.permission.name') . ':' . config('laravel-module-suite.permissions.default_permissions.list_permissions.name'));
        Route::put('permission/{id}', 'updatePermission')->middleware(config('laravel-module-suite.middleware.permission.name') . ':' . config('laravel-module-suite.permissions.default_permissions.edit_permissions.name'));
    });
}); 