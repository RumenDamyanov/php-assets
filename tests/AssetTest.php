<?php

use PHPUnit\Framework\TestCase;
use Rumenx\Assets\Asset;

class AssetTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Asset::$css = [];
        Asset::$less = [];
        Asset::$js = [];
        Asset::$jsParams = [];
        Asset::$styles = [];
        Asset::$scripts = [];
        Asset::$domain = '/';
        Asset::$prefix = '';
        Asset::$hash = [];
        Asset::$environment = null;
        Asset::$secure = false;
        Asset::$cacheEnabled = true;
        Asset::$cacheDuration = 360;
        Asset::$cacheKey = 'php-assets';
        Asset::setCacheBusterGeneratorFunction(null);
        Asset::setUseShortHandReady(false);
        Asset::$envResolver = null;
        Asset::$urlGenerator = null;
        Asset::$cache = null;
        Asset::setOnUnknownExtensionDefault(Asset::ON_UNKNOWN_EXTENSION_NONE);
    }

    public function testAddAndOutputCssAssets(): void
    {
        Asset::add('style.css');
        ob_start();
        Asset::css();
        $out = ob_get_clean();
        $this->assertStringContainsString('<link rel="stylesheet" type="text/css" href="/style.css">', $out);
    }

    public function testAddAndOutputJsAssetsWithParams(): void
    {
        Asset::add('script.js', ['name' => 'footer', 'type' => 'module', 'defer' => true]);
        ob_start();
        Asset::js('footer');
        $out = ob_get_clean();
        $this->assertStringContainsString('<script src="/script.js" type="module" defer="1"', $out);
    }

    public function testAddAndOutputLessAssets(): void
    {
        Asset::add('theme.less');
        ob_start();
        Asset::less();
        $out = ob_get_clean();
        $this->assertStringContainsString('<link rel="stylesheet/less" type="text/css" href="/theme.less">', $out);
    }

    public function testOutputInlineStylesAndScripts(): void
    {
        Asset::addStyle('body { color: red; }', 'header');
        Asset::addScript('console.log("hi");', 'footer');
        ob_start();
        Asset::styles('header');
        $styles = ob_get_clean();
        ob_start();
        Asset::scripts('footer');
        $scripts = ob_get_clean();
        $this->assertStringContainsString('<style type="text/css">', $styles);
        $this->assertStringContainsString('body { color: red; }', $styles);
        $this->assertStringContainsString('<script>', $scripts);
        $this->assertStringContainsString('console.log("hi");', $scripts);
    }

    public function testAddBeforeAndAddAfterWorkForCssJsLess(): void
    {
        Asset::add('a.css');
        Asset::add('b.css');
        Asset::addBefore('c.css', 'b.css');
        Asset::addAfter('d.css', 'a.css');
        $this->assertSame(['a.css', 'd.css', 'c.css', 'b.css'], array_keys(Asset::$css));

        Asset::add('a.js');
        Asset::add('b.js');
        Asset::addBefore('c.js', 'b.js', 'footer');
        Asset::addAfter('d.js', 'a.js', 'footer');
        $jsKeys = array_keys(Asset::$js['footer']);
        $this->assertSame(['a.js', 'd.js', 'c.js', 'b.js'], $jsKeys, 'JS asset order in footer section');
        $this->assertArrayHasKey('footer', Asset::$js, 'Footer section should exist for JS');
        $this->assertNotEmpty(Asset::$js['footer'], 'Footer section should not be empty after adding assets');

        Asset::add('a.less');
        Asset::add('b.less');
        Asset::addBefore('c.less', 'b.less');
        Asset::addAfter('d.less', 'a.less');
        $this->assertSame(['a.less', 'd.less', 'c.less', 'b.less'], array_keys(Asset::$less));
    }

    public function testSetDomainSetPrefixSetCachebusterSetCacheBusterGeneratorFunction(): void
    {
        Asset::setDomain('https://cdn.example.com');
        Asset::setPrefix('<!--ASSET-->');
        Asset::$hash = ['foo.js' => 'v123'];
        ob_start();
        Asset::add('foo.js');
        Asset::js();
        $out = ob_get_clean();
        $this->assertStringContainsString('https://cdn.example.com/foo.js?v123', $out);
        $this->assertStringContainsString('<!--ASSET-->', $out);

        Asset::setCacheBusterGeneratorFunction(fn($file) => 'cb');
        ob_start();
        Asset::add('bar.js');
        Asset::js();
        $out = ob_get_clean();
        $this->assertStringContainsString('bar.js?cb', $out);
    }

    public function testSetUseShortHandReadyOutputsJqueryShorthand(): void
    {
        Asset::setUseShortHandReady(true);
        Asset::addScript('console.log("ready");', 'ready');
        ob_start();
        Asset::scripts('ready');
        $out = ob_get_clean();
        $this->assertStringContainsString('<script>$(', $out);
    }

    public function testSetOnUnknownExtensionDefaultAndUnknownExtensionHandling(): void
    {
        Asset::setOnUnknownExtensionDefault(Asset::ON_UNKNOWN_EXTENSION_JS);
        Asset::add('foo.unknown');
        $this->assertArrayHasKey('foo.unknown', Asset::$js['footer']);
    }
}
