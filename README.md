# Laravel Module Suite

A comprehensive Laravel package for building modular applications with RBAC, media management, and API endpoints.

## Prerequisites

Before using this package, ensure you have:

1. Laravel 9.x, 10.x, 11.x, or 12.x installed
2. For Laravel 11.x or higher, you need to install the API package:

```bash
php artisan install:api
```

## Features

- Role-Based Access Control (RBAC) with polymorphic relationships
- Media management with file uploads and storage
- Custom module generation
- Permission management
- API endpoints for roles and permissions
- Repository pattern implementation
- Standardized API responses

## Requirements

- PHP >= 8.1
- Laravel >= 10.0
- Composer

## Installation

```bash
composer require ghazym/laravel-module-suite
```

Publish the configuration and migrations:

```bash
php artisan vendor:publish --provider="Ghazym\LaravelModuleSuite\LaravelModuleSuiteServiceProvider"
```

Run the migrations:

```bash
php artisan migrate
```

Seed the default permissions and roles:

```bash
php artisan db:seed --class=PermissionSeeder
php artisan db:seed --class=RoleSeeder
```

## Module Generation

Generate a new module with all necessary files:

```bash
php artisan make:module ModuleName
```

This command will create:
- Controller with API endpoints
- Model with relationships
- Service class for business logic
- Form Request classes for validation
- Migration file
- Seeder file
- Repository trait for data access
- Response trait for standardized API responses

## Usage

### Roles and Permissions

The package provides a polymorphic role system that can be used with any model:

```php
use Ghazym\LaravelModuleSuite\Traits\HasPermissions;

class User extends Model
{
    use HasPermissions;
}

class Team extends Model
{
    use HasPermissions;
}
```

#### Assigning Roles

```php
// Assign a role to a model
$user->assignRole(1);                    // Using role ID
$user->assignRole('admin');              // Using role name
$user->assignRole($roleModel);           // Using role model instance

// Assign multiple roles
$user->syncRoles([1, 2, 3]);            // Replace all roles

// Remove a role to a model
$user->removeRole(1);                    // Using role ID
$user->removeRole('admin');              // Using role name
$user->removeRole($roleModel);           // Using role model instance

```

#### Checking Permissions

```php
// Get all permissions for a model
$permissions = $user->getPermissions();  // Returns array of permission names

// Check permissions
$user->hasPermission('edit user');       // Check single permission
$user->hasAnyPermission(['create user', 'edit user']);  // Check if model has any of these permissions
$user->hasAllPermissions(['create user', 'edit user']); // Check if model has all of these permissions
```

### Media Management

The package includes a media management system with automatic file cleanup:

```php
use Ghazym\LaravelModuleSuite\Traits\HasMedia;

class Post extends Model
{
    use HasMedia;
}
```

#### Managing Media

```php
// Add media
$model->addMedia($file, $mediaName, $folderName);

// Add multiple files
$model->addMultipleMedia($files, $mediaName, $folderName);

// Get media
$model->getMedia('profile_image');           // Get all media with name
$model->getFirstMedia('id_images');          // Get first media
$model->getLastMedia('licence_images');      // Get last media

// Update media
$model->updateMedia($media, $newFile, $name, $folder);
$model->updateMultipleMedia($files, $name, $folder);

// Remove media
$model->removeMedia($media);                 // Remove single media
$model->removeMultipleMedia($name);          // Remove all media with name
$model->removeAllMedia();                    // Remove all media (automatic on model delete)
```

### Configuration

The package is highly configurable through the `config/laravel-module-suite.php` file:

```php
// Example configuration
return [
    'media' => [
        'max_size' => 10 * 1024 * 1024, // 10MB
        'allowed_mimes' => ['image/jpeg', 'image/png', 'image/gif'],
        'disk' => [
            'default' => 'public',
            'types' => [
                'image' => 'public',
                'document' => 'local',
            ],
        ],
    ],
    // ... other configurations
];
```

## API Routes

The package provides the following API endpoints:

```php
// Role routes
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

## Security

- All file uploads are validated for size and type
- Files are stored in appropriate disks based on type
- Automatic cleanup of files when models are deleted
- Role and permission checks are enforced through middleware

When you run `make:module`, it creates:

```

## Structure

When you run `make:module`, it creates the following directory structure:
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
{
    "success": false,
    "message": "Operation Operation failed",
    "data": null,
    "errors": {
        // errors
    }
}


```

## License

This package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).