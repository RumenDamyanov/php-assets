# laravel-assets package

A simple assets manager for Laravel 5.

## Notes

Latest supported version for Laravel 4 is 2.4.* (e.g v2.4.3)

Branch dev-master is for development and is unstable

## Installation

Run the following command and provide the latest stable version (e.g v2.5.4) :

```bash
composer require roumen/asset
```

or add the following to your `composer.json` file :

```json
"roumen/asset": "2.5.*"
```

Then register this service provider with Laravel :

```php
'Roumen\Asset\AssetServiceProvider',
```

and add class alias for easy usage
```php
'Asset' => 'Roumen\Asset\Asset',
```

Don't forget to use ``composer update`` and ``composer dump-autoload`` when is needed!
