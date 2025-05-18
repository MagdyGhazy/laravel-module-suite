# Laravel Module Builder

A Laravel package for quickly building modular applications with a standardized structure.

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

Create a new module using the command:

```bash
php artisan make:module YourModuleName
```

This will generate:
- Model with migration
- API Controller
- Service class
- Form Request classes
- API routes
- Permissions
- Database seeder

## Structure

The generated module will follow this structure:

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       └── YourModule/
│   │           └── YourModuleController.php
│   ├── Services/
│   │   └── YourModule/
│   │       └── YourModuleService.php
│   └── Requests/
│       └── YourModule/
│           ├── StoreYourModuleRequest.php
│           └── UpdateYourModuleRequest.php
├── Models/
│   └── YourModule.php
└── database/
    ├── migrations/
    │   └── xxxx_xx_xx_create_your_modules_table.php
    └── seeders/
        └── YourModuleSeeder.php
```

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email magdyghazy04@gmail.com instead of using the issue tracker.

## Credits

- [Magdy Ghazy](https://github.com/ghazym)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.