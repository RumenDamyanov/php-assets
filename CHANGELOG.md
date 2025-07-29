# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Changed

- Simplified package architecture by removing Laravel ServiceProvider and Symfony Bundle
- Updated documentation to emphasize framework-agnostic approach
- Removed unnecessary framework dependencies from composer.json
- Improved test coverage back to 100% with additional edge case tests

### Removed

- Laravel ServiceProvider (AssetServiceProvider) - not needed for static class usage
- Symfony Bundle (AssetBundle) - not needed for static class usage
- Framework-specific adapter tests

## [1.0.0] - 2025-01-10

### Added

- Initial release of rumenx/php-assets
- Framework-agnostic asset management for CSS, LESS, and JS files
- Cache busting support (file-based and function-based)
- Environment and domain support
- Works directly with Laravel, Symfony, and any PHP framework
- Comprehensive test suite with Pest
- Static analysis with PHPStan
- CI/CD pipeline with GitHub Actions
- Code coverage reporting with Codecov

### Features

- Add, order, and output frontend assets from PHP
- Support for inline styles and scripts
- Asset ordering with `addBefore`, `addAfter`, and `addFirst` methods
- Multiple section support for organizing assets
- External URL detection and handling
- Version placeholder support with wildcard matching
- Configurable asset URL generation
- Environment-aware asset management

### Technical

- PHP 8.3+ requirement
- PSR-4 autoloading
- Strict typing throughout
- Comprehensive documentation
- 98%+ test coverage
- PHPStan level 6 compliance

[Unreleased]: https://github.com/RumenDamyanov/php-assets/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/RumenDamyanov/php-assets/releases/tag/v1.0.0
