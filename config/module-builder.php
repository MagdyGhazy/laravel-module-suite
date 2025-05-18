<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Module Builder Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can specify default settings for your module generation
    |
    */

    // Default namespace for generated files
    'namespace' => 'App',

    // Default paths
    'paths' => [
        'models' => 'app/Models',
        'controllers' => 'app/Http/Controllers/Api',
        'services' => 'app/Http/Services',
        'requests' => 'app/Http/Requests',
    ],

    // Stub settings
    'stubs' => [
        'path' => base_path('stubs'),
        'files' => [
            'service' => 'service.stub',
            'controller' => 'controller.module.stub',
        ],
    ],
];