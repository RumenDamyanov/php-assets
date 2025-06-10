<?php
/**
 * Pest tests for Symfony AssetBundle adapter.
 * Ensures the bundle can be instantiated and integrates with Symfony's Bundle system.
 */
use Rumenx\Assets\Symfony\AssetBundle;

if (!class_exists('Symfony\\Component\\HttpKernel\\Bundle\\Bundle')) {
    test('Symfony adapter tests are skipped because Symfony Bundle is not installed', function () {
        $this->markTestSkipped('Symfony Bundle not available.');
    });
    return;
}

test('Symfony AssetBundle can be instantiated', function () {
    $bundle = new AssetBundle();
    expect($bundle)->toBeInstanceOf(AssetBundle::class);
});

test('Symfony AssetBundle extends Bundle', function () {
    $bundle = new AssetBundle();
    expect(get_parent_class($bundle))->toBe('Symfony\\Component\\HttpKernel\\Bundle\\Bundle');
});

test('Symfony AssetBundle has a name', function () {
    $bundle = new AssetBundle();
    expect(method_exists($bundle, 'getName'))->toBeTrue();
    expect($bundle->getName())->toBe('AssetBundle');
})->skip(!method_exists(AssetBundle::class, 'getName'));
