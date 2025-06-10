<?php
namespace Rumenx\Assets\Laravel;

use Illuminate\Support\ServiceProvider;
use Rumenx\Assets\Asset;

class AssetServiceProvider extends ServiceProvider {
    protected $defer = false;

    public function boot() {}

    public function register()
    {
        $this->app->bind('assets', function() {
            return new Asset();
        });
    }

    public function provides()
    {
        return ['asset'];
    }
}
