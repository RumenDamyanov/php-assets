# rumenx/php-assets

[![CI](https://github.com/RumenDamyanov/php-assets/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/RumenDamyanov/php-assets/actions/workflows/ci.yml)
[![PHPStan](https://github.com/RumenDamyanov/php-assets/actions/workflows/phpstan.yml/badge.svg?branch=master)](https://github.com/RumenDamyanov/php-assets/actions/workflows/phpstan.yml)
[![codecov](https://codecov.io/gh/RumenDamyanov/php-assets/branch/master/graph/badge.svg)](https://codecov.io/gh/RumenDamyanov/php-assets)

Framework-agnostic PHP package to manage frontend assets in the backend. Works with plain PHP, Laravel, and Symfony with no special configuration required.


## Features

- Add, order, and output CSS, LESS, and JS assets from PHP
- Cache busting (file or function based)
- Environment and domain support
- Works directly with Laravel, Symfony, and any PHP framework
- No special configuration or adapters required
- 100% test coverage, static analysis, and CI

---

## Installation

```bash
composer require rumenx/php-assets
```

---

## Usage Examples

### Plain PHP

```php
use Rumenx\Assets\Asset;

// Add assets
Asset::add('style.css');
Asset::add('theme.less');
Asset::add('app.js');
Asset::add(['extra.js', 'extra2.js'], 'footer');

// Add inline style or script
Asset::addStyle('body { background: #fafafa; }');
Asset::addScript('console.log("Hello!");');

// Output in your template
Asset::css();      // <link rel="stylesheet" ...>
Asset::less();     // <link rel="stylesheet/less" ...>
Asset::js();       // <script src=...></script>
Asset::styles();   // <style>...</style>
Asset::scripts();  // <script>...</script>

// Use cachebuster (file-based)
Asset::setCachebuster(__DIR__.'/cache.json');

// Use cachebuster (function-based)
Asset::setCacheBusterGeneratorFunction(function($file) {
    return md5($file);
});

// Custom domain or prefix
Asset::setDomain('https://cdn.example.com/');
Asset::setPrefix('X-');
```

---

## Design Philosophy

This package follows a **simple, framework-agnostic approach** by design. Unlike some asset management packages that require service providers, adapters, or complex integrations, php-assets works out-of-the-box with any PHP framework or plain PHP project.

**Why no special adapters or service providers?**

- **Simplicity**: Just use `Asset::add()` - no magic, no hidden complexity
- **Universal compatibility**: Works with Laravel, Symfony, CodeIgniter, or any PHP framework
- **Easy debugging**: No framework-specific layers to troubleshoot
- **Minimal maintenance**: No need to maintain separate adapters for different frameworks
- **Standard PHP**: Uses only basic PHP features (static methods, arrays, string manipulation)

This approach makes the package more reliable, easier to understand, and ensures it will continue working across different framework versions without requiring updates.

---

### Laravel Integration

```php
use Rumenx\Assets\Asset;

// In your controller or anywhere in your Laravel app
Asset::add('main.css');
Asset::add('main.js');

// In your Blade template
{!! Asset::css() !!}
{!! Asset::js() !!}
```

---

### Symfony Integration

```php
use Rumenx\Assets\Asset;

// In your controller
Asset::add('main.css');
Asset::add('main.js');

// In a Twig template
{{ asset_css()|raw }}
{{ asset_js()|raw }}
```

Or create a simple Twig extension:

```php
// src/Twig/AssetExtension.php
use Rumenx\Assets\Asset;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AssetExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('asset_css', fn() => Asset::css()),
            new TwigFunction('asset_js', fn() => Asset::js()),
        ];
    }
}
```

---

## Advanced Usage

- **Add assets to specific locations:**
  - `Asset::add('file.js', 'header');` // Add JS to header
  - `Asset::addFirst('file.js');` // Add as first asset
  - `Asset::addBefore('new.js', 'old.js');` // Insert before another
  - `Asset::addAfter('new.js', 'old.js');` // Insert after another
- **Environment detection:**
  - `Asset::$envResolver = fn() => app()->environment();`
- **Custom URL generator:**
  - `Asset::$urlGenerator = fn($file, $secure) => asset($file, $secure);`

---

## Testing

```bash
composer test
```

## Static Analysis

```bash
composer analyze
```

## Development & Testing

### Running Tests

```bash
composer test
```

### Running Static Analysis

```bash
composer analyze
```

### CI/CD

- GitHub Actions for tests, static analysis, and Codecov coverage reporting.

## Contributing

We welcome contributions! Please see our [Contributing Guidelines](CONTRIBUTING.md) for details on how to get started.

## Security

If you discover a security vulnerability, please review our [Security Policy](SECURITY.md) for information on how to report it responsibly.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for a detailed history of changes to this project.

## Funding

If you find this project useful, consider [supporting its development](FUNDING.yml).

## License

This project is licensed under the [MIT License](LICENSE.md).
