<?php

use Ghazym\ModuleBuilder\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'role', 'middleware' => config('module-builder.auth.middleware')], function () {
    Route::controller(RoleController::class)->group(function () {
        Route::get('/', 'index')->middleware(config('module-builder.middleware.permission.name') . ':' . config('module-builder.permissions.list_roles.name'));
        Route::get('/{id}', 'show')->middleware(config('module-builder.middleware.permission.name') . ':' . config('module-builder.permissions.show_roles.name'));
        Route::post('/', 'store')->middleware(config('module-builder.middleware.permission.name') . ':' . config('module-builder.permissions.create_roles.name'));
        Route::put('/{id}', 'update')->middleware(config('module-builder.middleware.permission.name') . ':' . config('module-builder.permissions.edit_roles.name'));
        Route::delete('/{id}', 'destroy')->middleware(config('module-builder.middleware.permission.name') . ':' . config('module-builder.permissions.delete_roles.name'));
        Route::get('all/permissions', 'allPermissions')->middleware(config('module-builder.middleware.permission.name') . ':' . config('module-builder.permissions.list_permissions.name'));
        Route::put('permission/{id}', 'updatePermission')->middleware(config('module-builder.middleware.permission.name') . ':' . config('module-builder.permissions.edit_permissions.name'));
    });
}); 