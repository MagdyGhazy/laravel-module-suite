# Changelog

All notable changes to this project will be documented in this file.

## [1.1.0] - 2024-03-19

### Added
- Enhanced permission management system with improved methods
- New flexible role assignment system supporting multiple input types
- Optimized permission checking with eager loading
- Comprehensive permission documentation in README
- New configuration options for auth middleware

### Changed
- Improved `assignRole` method to support multiple input types (ID, name, model)
- Optimized `getPermissions` method with eager loading
- Updated README with detailed permission usage examples
- Enhanced error handling in permission methods

### Fixed
- N+1 query issue in permission checking
- Type safety improvements in role assignment

## [1.0.1] - 2024-03-18

### Added
- Initial release with basic module generation
- Role and permission system
- API response standardization
- Configuration system 