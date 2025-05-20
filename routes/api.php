<?php

use Ghazym\ModuleBuilder\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'role', 'middleware' => 'auth:sanctum'], function () {
    Route::controller(RoleController::class)->group(function () {
        Route::get('/', 'index')->middleware('permission:list roles');
        Route::get('/{id}', 'show')->middleware('permission:show roles');
        Route::post('/', 'store')->middleware('permission:create roles');
        Route::put('/{id}', 'update')->middleware('permission:edit roles');
        Route::delete('/{id}', 'destroy')->middleware('permission:delete roles');
        Route::get('all/permissions', 'allPermissions')->middleware('permission:list permissions');
        Route::put('permission/{id}', 'updatePermission')->middleware('permission:edit permissions');
    });
}); 