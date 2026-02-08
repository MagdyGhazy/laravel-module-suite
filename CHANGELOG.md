# Changelog

All notable changes to this project will be documented in this file.

## [2.0.0] - 2025-02-08

### Added
- Smart Media Synchronization: Introduced advanced updateMultipleMedia logic that uses keep_media_ids to intelligently sync files, preventing unnecessary deletions and re-uploads.
- Context-Aware API Resources: New intelligent Resource generation that automatically toggles between listFormat() and showFormat() based on the current controller action.
- Standardized Pagination: Integrated automatic pagination metadata detection within the ResponseTrait for all API collections.
- Multipurpose File Storage: Support for categorizing media into (images, videos, audio, documents, archives) with configurable storage disks for each type.
- Enhanced Module Scaffolding: Updated make:module command to generate cleaner Service and Resource patterns.

### Changed
- HasMedia Trait Rewrite: Complete architectural rewrite to support transaction-based operations and better data integrity.
- Response Structure: Unified the API response format to strictly follow the {success, message, data, pagination, errors} structure.
- Service Layer Pattern: Encouraged explicit media handling in services for better folder organization and readability.

### Breaking Changes
- UMedia Update Behavior: The updateMultipleMedia method now expects a "Sync" approach. Existing implementations must send keep_media_ids to retain files.
- Resource Implementation: Generated Resources now require listFormat() and showFormat() methods to function with the new dual-mode logic.
- Pagination Key: Pagination data has moved from being inside the data object to a top-level pagination key in the JSON response.

## [1.2.0] - 2024-03-19

### Added
- New Media system with polymorphic relationships
- Media model with file management capabilities
- HasMedia trait for easy media handling
- Media migration for storing file information
- File upload and management functionality
- Support for multiple file uploads
- File type validation and error handling
- Transaction support for file operations

### Changed
- Updated package description to include media management
- Improved error handling in file operations
- Enhanced type safety in media operations
- Better file cleanup on failed operations

## [1.1.0] - 2024-03-18

### Added
- Enhanced permission system with improved methods
- Better error handling in permission operations
- New documentation for permission methods
- Improved role management functionality

### Changed
- Updated README with new permission features
- Improved code organization and documentation
- Enhanced error handling in role operations

## [1.0.1] - 2024-03-17

### Added
- Initial release of the package
- Basic RBAC functionality
- Role and permission management
- Custom module building capabilities
- Basic documentation 