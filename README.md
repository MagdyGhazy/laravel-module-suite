# Laravel Module Builder

A Laravel package for building custom modules with built-in role and permission management.

## Prerequisites

Before using this package, ensure you have:

1. Laravel 9.x, 10.x, 11.x, or 12.x installed
2. API routes configured in your Laravel application
   - For Laravel 11+, run `php artisan install:api` if you haven't already
   - For earlier versions, ensure `routes/api.php` exists and is properly configured
3. Sanctum authentication set up (for API authentication)
4. Spatie Permission package installed (for role and permission management)

## Installation

You can install the package via composer:

```bash
composer require ghazym/module-builder
```

After installing the package, publish the configuration file:

```bash
php artisan vendor:publish --provider="Ghazym\ModuleBuilder\ModuleBuilderServiceProvider"
```

## Features

### Module Generation

Generate complete modules with a single command:

```bash
php artisan make:module ModuleName
```

This will create:
- Model with migration
- Service class
- Controller
- Form Requests (Store/Update)
- Seeder
- Routes with permissions
- API endpoints

### Role and Permission Management

The package includes a complete role and permission system:

1. **Roles**: Manage user roles with different permission sets
2. **Permissions**: Granular control over user actions
3. **Middleware**: Built-in permission checking middleware

#### Package Routes

The package automatically registers the following routes:

```php
// Role Management Routes
Route::group(['prefix' => 'role', 'middleware' => 'auth:sanctum'], function () {
    Route::get('/', 'index')->middleware('permission:list roles');
    Route::get('/{id}', 'show')->middleware('permission:show roles');
    Route::post('/', 'store')->middleware('permission:create roles');
    Route::put('/{id}', 'update')->middleware('permission:edit roles');
    Route::delete('/{id}', 'destroy')->middleware('permission:delete roles');
    Route::get('all/permissions', 'allPermissions')->middleware('permission:list permissions');
    Route::put('permission/{id}', 'updatePermission')->middleware('permission:edit permissions');
});
```

These routes are automatically loaded when the package is installed. You can customize the middleware and prefix in the configuration file.

#### Permission Methods

The package provides several methods for managing permissions:

```php
// Assign a role to a user (multiple ways)
$user->assignRole(1);                    // Using role ID
$user->assignRole('admin');              // Using role name
$user->assignRole($roleModel);           // Using role model instance

// Get all permissions for a user
$permissions = $user->getPermissions();  // Returns array of permission names

// Check permissions
$user->hasPermission('edit user');       // Check single permission
$user->hasAnyPermission(['create user', 'edit user']);  // Check if user has any of these permissions
$user->hasAllPermissions(['create user', 'edit user']); // Check if user has all of these permissions
```

### Configuration

The package is highly configurable through the `config/module-builder.php` file:

```php
return [
    // Auth middleware configuration
    'auth' => [
        'middleware' => 'auth:sanctum', // Customize authentication middleware
    ],

    // Role and permission settings
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

    // Middleware configuration
    'middleware' => [
        'permission' => [
            'name' => 'permission',
            'class' => \Ghazym\ModuleBuilder\Middleware\CheckPermission::class,
        ],
    ],

    // Response format configuration
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
```

### Usage

1. **Add HasPermissions Trait to User Model**:
```php
use Ghazym\ModuleBuilder\Traits\HasPermissions;

class User extends Authenticatable
{
    use HasPermissions;
    // ...
}
```

2. **Use Permission Middleware in Routes**:
```php
Route::middleware('permission:list users')->group(function () {
    Route::get('/users', [UserController::class, 'index']);
});
```


### Generated Module Structure

When you run `make:module`, it creates:

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       └── ModuleName/
│   │           └── ModuleNameController.php
│   ├── Requests/
│   │   └── ModuleName/
│   │       ├── StoreModuleNameRequest.php
│   │       └── UpdateModuleNameRequest.php
│   ├── Services/
│   │   └── ModuleName/
│   │       └── ModuleNameService.php
│   └── Traits/
│       ├── RepositoryTrait.php
│       └── ResponseTrait.php
├── Models/
│   └── ModuleName.php
└── database/
    ├── migrations/
    │   └── xxxx_xx_xx_create_module_names_table.php
    └── seeders/
        └── ModuleNameSeeder.php
```

### API Response Format

All API responses follow a consistent format:

```json
{
    "success": true,
    "message": "Operation completed successfully",
    "data": {
        // Response data
    },
    "errors": null
}
```

## Requirements

- PHP >= 8.0
- Laravel >= 9.0

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.