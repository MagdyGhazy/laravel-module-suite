# Laravel Module Builder

A Laravel package for custom module building with repository pattern and standardized API responses.

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

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --provider="Ghazym\ModuleBuilder\ModuleBuilderServiceProvider"
```

## Usage

To create a new module, use the following command:

```bash
php artisan make:module ModuleName
```

This will generate:
- Model with migration
- Service class with repository pattern
- API Controller with standardized responses
- Form Request classes for validation
- Seeder
- API Routes with permissions
- Required traits (RepositoryTrait and ResponseTrait)

## Generated Structure

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

## Features

- Repository Pattern implementation
- Standardized API responses
- Automatic route generation with permissions
- Form request validation
- Database seeding support
- Type-safe code generation
- Pagination support
- Search functionality
- Proper error handling

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email magdyghazy04@gmail.com instead of using the issue tracker.

## Credits

- [Magdy Ghazy](https://github.com/ghazym)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.