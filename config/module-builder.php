<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Module Builder Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration settings for the Module Builder package.
    |
    */

    // Default namespace for generated modules
    'namespace' => 'App',

    /*
    |--------------------------------------------------------------------------
    | Auth Settings
    |--------------------------------------------------------------------------
    |
    | These settings define the authentication middleware for the package.
    |
    */
    'auth' => [
        'middleware' => 'auth:sanctum',
    ],

    /*
    |--------------------------------------------------------------------------
    | Role and Permission Settings
    |--------------------------------------------------------------------------
    |
    | These settings define the role and permission system configuration.
    |
    */
    'roles' => [
        'table' => 'roles',
        'model' => \Ghazym\ModuleBuilder\Models\Role::class,
        'default_roles' => [
            'admin' => [
                'name' => 'Admin',
                'description' => 'Administrator with full access',
            ],
        ],
    ],

    'permissions' => [
        'table' => 'permissions',
        'model' => \Ghazym\ModuleBuilder\Models\Permission::class,
        'default_permissions' => [
            // User permissions
            'list users' => 'View list of users',
            'show user' => 'View user details',
            'create user' => 'Create new users',
            'edit user' => 'Edit existing users',
            'delete user' => 'Delete users',

            // Role permissions
            'list roles' => 'View list of roles',
            'show role' => 'View role details',
            'create role' => 'Create new roles',
            'edit role' => 'Edit existing roles',
            'delete role' => 'Delete roles',

            // Permission permissions
            'list permissions' => 'View list of permissions',
            'edit permission' => 'Edit existing permissions',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Middleware Settings
    |--------------------------------------------------------------------------
    |
    | These settings define the middleware configuration for the package.
    |
    */
    'middleware' => [
        'permission' => [
            'name' => 'permission',
            'class' => \Ghazym\ModuleBuilder\Middleware\CheckPermission::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Response Settings
    |--------------------------------------------------------------------------
    |
    | These settings define the default response structure for the package.
    |
    */
    'response' => [
        'success' => [
            'success' => true,
            'message' => 'Operation completed successfully',
            'data' => null,
            'errors' => null,
        ],
        'error' => [
            'success' => false,
            'message' => 'Operation failed',
            'data' => null,
            'errors' => null,
        ],
    ],
];