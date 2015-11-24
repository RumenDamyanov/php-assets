# [laravel-assets](http://roumen.it/projects/laravel-assets) package

[![Latest Stable Version](https://poser.pugx.org/roumen/asset/version.png)](https://packagist.org/packages/roumen/asset) [![Total Downloads](https://poser.pugx.org/roumen/asset/d/total.png)](https://packagist.org/packages/roumen/asset) [![Build Status](https://travis-ci.org/RoumenDamianoff/laravel-assets.png?branch=master)](https://travis-ci.org/RoumenDamianoff/laravel-assets) [![License](https://poser.pugx.org/roumen/asset/license.png)](https://packagist.org/packages/roumen/asset)

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

## Examples

[Example usage and layout structure](https://github.com/RoumenDamianoff/laravel-assets/wiki)
