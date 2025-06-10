# rumenx/php-assets

[![CI](https://github.com/RumenDamyanov/php-assets/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/RumenDamyanov/php-assets/actions/workflows/ci.yml)
[![PHPStan](https://github.com/RumenDamyanov/php-assets/actions/workflows/phpstan.yml/badge.svg?branch=master)](https://github.com/RumenDamyanov/php-assets/actions/workflows/phpstan.yml)
[![codecov](https://codecov.io/gh/RumenDamyanov/php-assets/branch/master/graph/badge.svg)](https://codecov.io/gh/RumenDamyanov/php-assets)

Framework-agnostic PHP package to manage frontend assets in the backend. Works with plain PHP, Laravel, and Symfony (via adapters).


## Features

- Add, order, and output CSS, LESS, and JS assets from PHP
- Cache busting (file or function based)
- Environment and domain support
- Laravel and Symfony integration via adapters
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

### Laravel Integration

1. Register the service provider in `config/app.php`:

    ```php
    Rumenx\Assets\Laravel\AssetServiceProvider::class,
    ```

2. Use the Asset class anywhere in your app:

    ```php
    use Rumenx\Assets\Asset;

    Asset::add('main.css');
    Asset::add('main.js');

    // In your Blade template
    {!! Asset::css() !!}
    {!! Asset::js() !!}
    ```

3. (Optional) Bindings are available via the Laravel container:

    ```php
    $assets = app('assets');
    $assets::add('custom.js');
    ```

---

### Symfony Integration

1. Register the bundle in your Symfony app:

    ```php
    // config/bundles.php
    return [
        Rumenx\Assets\Symfony\AssetBundle::class => ['all' => true],
    ];
    ```

2. Use the Asset class in your controllers or templates:

    ```php
    use Rumenx\Assets\Asset;

    Asset::add('main.css');
    Asset::add('main.js');

    // In a Twig template
    dump(Asset::css());
    dump(Asset::js());
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

## CI/CD

- GitHub Actions for tests, static analysis, and Codecov coverage reporting.

## License

This project is licensed under the [MIT License](LICENSE.md).
