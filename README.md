# rumenx/php-assets

**This package is abandoned.**

The GitHub repository is archived and read-only. Packagist marks [`rumenx/php-assets`](https://packagist.org/packages/rumenx/php-assets) as abandoned. Composer still installs the last release and prints a warning. There will be no further PHP, security, or feature updates.

Abandoned on 28 October 2026.

## What it was

`rumenx/php-assets` collects CSS, LESS, and JavaScript URLs during a request and prints `<link>` and `<script>` tags. The list lives in public static properties on `Rumenx\Assets\Asset`. Cache busting is a query string. LESS is emitted as `<link rel="stylesheet/less">`, which depends on less.js in the browser. Inline "ready" scripts can be wrapped in `$(document).ready(...)`.

That is the asset-manager style from around 2013–2015. A `v2.5.5` tag in this history is from December 2015. The 2025 release kept the same static collector and dropped the old Laravel and Symfony adapters.

## Why it stops here

Rendering a page no longer needs a PHP class to decide how stylesheets and scripts are built.

- Files are bundled, minified, and content-hashed by a build tool. The hash is in the filename, so a query-string bump is unnecessary.
- LESS in the browser, and a jQuery ready wrapper, are not how pages are written now.
- A static list on a class is process-wide. On a long-running worker such as Laravel Octane or Swoole, one request's assets leak into the next.
- The interesting remaining case is a classic multi-page PHP app with a few known files and no build step. That is a couple of tags in the layout, or `filemtime()` on the query string. It does not need this package.

Nothing else on Packagist depends on `rumenx/php-assets`.

## What to use instead

**Laravel.** Use [Vite](https://laravel.com/docs/vite). Register entry files in `vite.config.js` and render them with `@vite`. Laravel's Vite integration writes the hashed URLs. Do not collect those URLs again in PHP.

**Symfony.** Use [AssetMapper](https://symfony.com/doc/current/frontend/asset_mapper.html) when the app should not require Node. AssetMapper serves import maps and versioned assets. Use [Webpack Encore](https://symfony.com/doc/current/frontend.html) or Vite when the app needs a bundler. Symfony's asset component already versions URLs.

**Any app with a build.** Use Vite or esbuild. Point the HTML at the built files. The content hash in the filename is the cache buster. A CDN is a copy of that `build/` directory, not a domain prefix applied in PHP.

**No build step.** Put the tags in the layout and use the file's modification time as the query string:

```php
$css = '/css/app.css';
$js = '/js/app.js';
$cssVersion = filemtime(__DIR__ . '/public' . $css);
$jsVersion = filemtime(__DIR__ . '/public' . $js);
```

```html
<link rel="stylesheet" href="/css/app.css?v=<?= $cssVersion ?>">
<script src="/js/app.js?v=<?= $jsVersion ?>" defer></script>
```

In Laravel that is `filemtime(public_path('css/app.css'))` inside a Blade layout. In Twig, pass the version from the controller or call `filemtime` through a small extension. Escape any URL that comes from user input before it is placed in `href` or `src`.

**WordPress.** Use `wp_enqueue_style()` and `wp_enqueue_script()`.

## If you already depend on it

The last release keeps working. Pin it so Composer will not surprise you, then remove it when you next touch the layout:

```bash
composer require rumenx/php-assets:^1.0
```

After this notice, switch that layout to one of the approaches above and delete the dependency. Issues and pull requests are closed because the repository is archived.

## License

Released under the [MIT License](LICENSE.md).
