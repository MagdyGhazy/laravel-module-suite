<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Module Builder Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can configure the default settings for the module builder package.
    |
    */

    // Default namespace for generated modules
    'namespace' => 'App',

    // Default paths for generated files
    'paths' => [
        'models' => 'app/Models',
        'controllers' => 'app/Http/Controllers/Api',
        'services' => 'app/Http/Services',
        'requests' => 'app/Http/Requests',
        'seeders' => 'database/seeders',
        'migrations' => 'database/migrations',
    ],

    // Default middleware for API routes
    'middleware' => [
        'api' => ['auth:sanctum'],
    ],

    // Default pagination settings
    'pagination' => [
        'per_page' => 10,
        'page_name' => 'page',
    ],

    // Default search settings
    'search' => [
        'enabled' => true,
        'fields' => ['id', 'name'],
    ],

    // Default response messages
    'messages' => [
        'created' => ':name created successfully',
        'updated' => ':name updated successfully',
        'deleted' => ':name deleted successfully',
        'not_found' => ':name not found',
        'error' => 'An error occurred while processing your request',
    ],

    // Stub settings
    'stubs' => [
        'path' => __DIR__ . '/../stubs',
        'files' => [
            'service' => 'service.stub',
            'controller' => 'controller.module.stub',
        ],
    ],
];