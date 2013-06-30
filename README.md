# [laravel4-asset](http://roumen.me/projects/laravel4-asset)

[![Latest Stable Version](https://poser.pugx.org/roumen/asset/version.png)](https://packagist.org/packages/roumen/asset) [![Total Downloads](https://poser.pugx.org/roumen/asset/d/total.png)](https://packagist.org/packages/roumen/asset)

A simple asset manager for Laravel 4.


## Installation

Add the following to your `composer.json` file :

```json
"roumen/asset": "dev-master"
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

## Example

```php
// adds css asset
Asset::add('css/default.css');

// adds js asset
Asset::add('js/home.js');

// adds js asset to the 'footer' array
Asset::add('js/some.js', 'footer');

// adds script to 'ready' array (scripts are loaded in $(document).ready() function)
$script1 = '$("#hello").html("Hello World!")';
Asset::add_script($script1, 'ready');

// loads css assets (place this in your master layout before close head tag)
Asset::css();

// loads js assets for your header and footer
Asset::js();
Asset::js('header');

// loads all scripts for your header, footer or for your $(document).ready() function
Asset::scripts('header');
Asset::scripts('footer');
Asset::scripts('ready');

// in case that you need to load asset as first element in its own array
Asset::addFirst('js/toBeLoadedFirst.js');
```