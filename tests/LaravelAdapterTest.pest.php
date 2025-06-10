<?php
/**
 * Pest tests for Laravel AssetServiceProvider adapter.
 * Ensures the provider can be instantiated, registers assets, and provides correct bindings.
 */
use Rumenx\Assets\Laravel\AssetServiceProvider;

if (!class_exists('Illuminate\\Support\\ServiceProvider')) {
    test('Laravel adapter tests are skipped because Illuminate ServiceProvider is not installed', function () {
        $this->markTestSkipped('Illuminate ServiceProvider not available.');
    });
    return;
}

describe('AssetServiceProvider', function () {
    it('can be instantiated', function () {
        $provider = new AssetServiceProvider(null);
        expect($provider)->toBeInstanceOf(AssetServiceProvider::class);
    });

    it('registers and boots without error (with mock app)', function () {
        $mockApp = new class {
            public $bound = [];
            public function bind($key, $closure) {
                $this->bound[$key] = $closure();
            }
        };
        $provider = new AssetServiceProvider($mockApp);
        $provider->register();
        $provider->boot();
        expect($mockApp->bound)->toHaveKey('assets');
        expect($mockApp->bound['assets'])->toBeInstanceOf(\Rumenx\Assets\Asset::class);
    });

    it('provides returns correct value', function () {
        $provider = new AssetServiceProvider(null);
        expect($provider->provides())->toBe(['asset']);
    });
});
