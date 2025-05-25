<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Module Builder Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains all configurations for the module builder package.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Media Configuration
    |--------------------------------------------------------------------------
    */
    'media' => [
        // Default maximum file size in bytes (10MB)
        'max_size' => 10 * 1024 * 1024,

        // Maximum file sizes by type (in bytes)
        'max_sizes' => [
            // Images
            'image/jpeg' => 5 * 1024 * 1024,    // 5MB
            'image/png' => 5 * 1024 * 1024,     // 5MB
            'image/gif' => 5 * 1024 * 1024,     // 5MB
            'image/webp' => 5 * 1024 * 1024,    // 5MB

            // Documents
            'application/pdf' => 20 * 1024 * 1024,    // 20MB
            'application/msword' => 15 * 1024 * 1024, // 15MB
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 15 * 1024 * 1024, // 15MB
            'application/vnd.ms-excel' => 15 * 1024 * 1024, // 15MB
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 15 * 1024 * 1024, // 15MB

            // Videos
            'video/mp4' => 100 * 1024 * 1024,   // 100MB
            'video/mpeg' => 100 * 1024 * 1024,  // 100MB
            'video/quicktime' => 100 * 1024 * 1024, // 100MB
            'video/x-msvideo' => 100 * 1024 * 1024, // 100MB

            // Audio
            'audio/mpeg' => 20 * 1024 * 1024,   // 20MB
            'audio/wav' => 20 * 1024 * 1024,    // 20MB
            'audio/ogg' => 20 * 1024 * 1024,    // 20MB

            // Archives
            'application/zip' => 50 * 1024 * 1024,    // 50MB
            'application/x-rar-compressed' => 50 * 1024 * 1024, // 50MB
        ],

        // Allowed MIME types
        'allowed_mimes' => [
            // Images
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            // Documents
            'application/pdf', 'application/msword', 
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            // Videos
            'video/mp4', 'video/mpeg', 'video/quicktime', 'video/x-msvideo',
            // Audio
            'audio/mpeg', 'audio/wav', 'audio/ogg',
            // Archives
            'application/zip', 'application/x-rar-compressed',
        ],

        // Storage disk configuration
        'disk' => [
            'default' => 'public',
            'types' => [
                // Images stored in public disk
                'image' => 'public',
                
                // Documents stored in local disk (private)
                'document' => 'local',
                
                // Videos stored in public disk use s3 (if configured)
                'video' => 'public',
                
                // Audio files in public disk
                'audio' => 'public',
                
                // Archives in local disk
                'archive' => 'local',
            ],
        ],

        // Default folder for media storage
        'default_folder' => 'media',
    ],


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
        'model' => \Ghazym\LaravelModuleSuite\Models\Role::class,
        'default_roles' => [
            'super_admin' => [
                'name' => 'Super Admin',
                'description' => 'Administrator with full access',
            ],
        ],
    ],

    'permissions' => [
        'model' => \Ghazym\LaravelModuleSuite\Models\Permission::class,
        'default_permissions' => [
            // User permissions
            'list_users' => [
                'name' => 'list users',
                'description' => 'View list of users',
            ],      
            'show_users' => [
                'name' => 'show users',
                'description' => 'View user details',
            ],
            'create_users' => [
                'name' => 'create users',
                'description' => 'Create new users',
            ],
            'edit_users'   => [
                'name' => 'edit users',
                'description' => 'Edit existing users',
            ],
            'delete_users' => [
                'name' => 'delete users',
                'description' => 'Delete users',
            ],

            // Role permissions
            'list_roles'   => [
                'name' => 'list roles',
                'description' => 'View list of roles',
            ],
            'show_roles'   => [
                'name' => 'show roles',
                'description' => 'View role details',
            ],
            'create_roles' => [
                'name' => 'create roles',
                'description' => 'Create new roles',
            ],
            'edit_roles'   => [
                'name' => 'edit roles',
                'description' => 'Edit existing roles',
            ],
            'delete_roles' => [
                'name' => 'delete roles',
                'description' => 'Delete roles',
            ],

            // Permission permissions
            'list_permissions' => [
                'name' => 'list permissions',
                'description' => 'View list of permissions',
            ],
            'edit_permissions' => [
                'name' => 'edit permissions',
                'description' => 'Edit existing permissions',
            ],
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
            'class' => \Ghazym\LaravelModuleSuite\Middleware\CheckPermission::class,
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