Laravel Module Suite (V2.0.0)
A powerful, all-in-one Laravel package for building modular applications. It handles RBAC, Smart Media Management, and Standardized API Responses with ease.

Prerequisites
Laravel: 10.x, 11.x, or 12.x

PHP: >= 8.1

Laravel API: (For Laravel 11+) php artisan install:api

Features
 Module Generation: Create a full module (Controller, Service, Resource, Request, Migration, Seeder) in one command.

 Polymorphic RBAC: Flexible roles and permissions that can be attached to any model.

 Smart Media Sync: Advanced media handling with "Keep-IDs" logic to sync files between Frontend and Backend.

 Context-Aware Resources: Intelligent API Resources that automatically switch between list and show formats.

 Auto-Pagination: Standardized API responses that automatically include pagination metadata.

Installation
Bash
composer require ghazym/laravel-module-suite
Setup
Publish configuration and migrations:

Bash
php artisan vendor:publish --provider="Ghazym\LaravelModuleSuite\LaravelModuleSuiteServiceProvider"
Run migrations:

Bash
php artisan migrate
Seed default permissions and roles:

Bash
php artisan db:seed --class=PermissionSeeder
php artisan db:seed --class=RoleSeeder
Module Generation
Generate a full-featured module with one command:

Bash
php artisan make:module House
This command generates:

HouseController: Pre-configured with API Resource logic.

HouseService: Separated business logic with Media handling.

HouseResource: Smart resource for index and show routes.

StoreHouseRequest & UpdateHouseRequest.

Migration & Seeder.

Media Management
The package includes a sophisticated media system using the HasMedia trait.

Smart Syncing (Update)
In version 2.0, updating multiple images is easier. The frontend sends the new files and an array of existing IDs to keep (keep_media_ids).

PHP
// In your Service
public function update(array $request, int $id)
{
    $house = House::find($id);

    // Sync Gallery: Keep IDs [1, 5], delete others, and add new files
    if (isset($request['house_images']) || isset($request['keep_house_images'])) {
        $house->updateMultipleMedia(
            $request['house_images'] ?? [], 
            'house_images', 
            'houses/gallery', 
            $request['keep_house_images'] ?? []
        );
    }
}
Basic Operations
PHP
// Add single file
$model->addMedia($file, 'cover', 'houses/covers');

// Get media
$model->getMedia('gallery');      // Get collection
$model->getFirstMedia('cover');   // Get first item
API Resources & Responses
All generated modules use a standardized response format.

Smart Toggle (List vs Show)
The Resource class automatically detects the controller method:

List (index): Returns minimal data for performance.

Show (detail): Returns full data with relationships.

Pagination Support
When returning a collection, pagination is handled automatically:

JSON
{
    "success": true,
    "message": "Success",
    "data": [...],
    "pagination": {
        "total": 100,
        "per_page": 15,
        "current_page": 1,
        "last_page": 7
    }
}
RBAC (Roles & Permissions)
Add the HasPermissions trait to any model:

PHP
use Ghazym\LaravelModuleSuite\Traits\HasPermissions;

class User extends Model {
    use HasPermissions;
}
Usage
PHP
$user->assignRole('admin');
$user->hasPermission('create houses'); // true/false
$user->syncRoles([1, 2]); // Sync by IDs
Directory Structure
Generated modules follow a clean architecture:

Plaintext
app/
├── Http/
│   ├── Controllers/Api/House/HouseController.php
│   ├── Requests/House/StoreHouseRequest.php
│   └── Resources/House/HouseResource.php
├── Services/House/HouseService.php
└── Models/House.php
License
This package is open-sourced software licensed under the MIT license.
