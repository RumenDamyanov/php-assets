<?php

use Roumen\Asset\Asset as Asset;

class AssetTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        parent::setUp();
        Asset::$environment = 'local';
    }

    public function testAdd()
    {
        Asset::add('style.css');
        Asset::add('style.less');
        Asset::add('script.js');
        Asset::add('script1.js','footer');
        Asset::add('script2.js','header');
        Asset::add('script3.js','foobar');

        $this->assertEquals('style.css', Asset::$css[0]);
        $this->assertEquals('style.less', Asset::$less[0]);
        $this->assertEquals('script.js', Asset::$js['footer'][0]);
        $this->assertEquals('script1.js', Asset::$js['footer'][1]);
        $this->assertEquals('script2.js', Asset::$js['header'][0]);
        $this->assertEquals('script3.js', Asset::$js['foobar'][0]);

        Asset::addFirst('styleFirst.css');
        $this->assertEquals('styleFirst.css', Asset::$css[0]);
        $this->assertEquals('style.css', Asset::$css[1]);

        Asset::addFirst('styleFirst.less');
        $this->assertEquals('styleFirst.less', Asset::$less[0]);
        $this->assertEquals('style.less', Asset::$less[1]);

        Asset::addFirst('scriptFirst.js');
        $this->assertEquals('scriptFirst.js', Asset::$js['footer'][0]);
        $this->assertEquals('script.js', Asset::$js['footer'][1]);

        Asset::addFirst('scriptFirst.js','header');
        $this->assertEquals('scriptFirst.js', Asset::$js['header'][0]);
        $this->assertEquals('script2.js', Asset::$js['header'][1]);

        Asset::addFirst('scriptFirst.js','foobar');
        $this->assertEquals('scriptFirst.js', Asset::$js['foobar'][0]);
        $this->assertEquals('script3.js', Asset::$js['foobar'][1]);

        Asset::addScript('test');
        $this->assertEquals('test', Asset::$scripts['footer'][0]);

        Asset::addScript('test2','header');
        $this->assertEquals('test2', Asset::$scripts['header'][0]);

        Asset::addStyle('test');
        $this->assertEquals('test', Asset::$styles['header'][0]);

        Asset::addStyle('test2','foobar');
        $this->assertEquals('test2', Asset::$styles['foobar'][0]);
    }

    public function testCssRaw()
    {
        Asset::$css = array();

        Asset::add(array('1.css','2.css','3.css'));
        $this->expectOutputString('/1.css,/2.css,/3.css,', Asset::cssRaw(','));
    }

    public function testCssWrapped()
    {
        Asset::$css = array();
        Asset::add(array('1.css','http://foo.dev/2.css'), 'header');

        $expected = '<link rel="stylesheet" type="text/css" href="/1.css" />'."\n".'<link rel="stylesheet" type="text/css" href="http://foo.dev/2.css" />'."\n";

        $this->expectOutputString($expected, Asset::css('header'));
    }

    public function testLessRaw()
    {
        Asset::$less = array();

        Asset::add(array('1.less','2.less','3.less'));
        $this->expectOutputString('/1.less,/2.less,/3.less,', Asset::lessRaw(','));
    }

    public function testLessWrapped()
    {
        Asset::$less = array();
        Asset::add(array('1.less','http://foo.dev/2.less'), 'header');

        $expected = '<link rel="stylesheet/less" type="text/css" href="/1.less" />'."\n".'<link rel="stylesheet/less" type="text/css" href="http://foo.dev/2.less" />'."\n";

        $this->expectOutputString($expected, Asset::less('header'));
    }

    public function testJsRaw()
    {
        Asset::$js = array();

        Asset::add(array('1.js','2.js','3.js'));
        $this->expectOutputString('/1.js,/2.js,/3.js,', Asset::jsRaw(','));
    }

    public function testJsWrapped()
    {
        Asset::$js = array();
        Asset::add(array('1.js','http://foo.dev/2.js'), 'footer');

        $expected = '<script src="/1.js"></script>'."\n".'<script src="http://foo.dev/2.js"></script>'."\n";

        $this->expectOutputString($expected, Asset::js('footer'));
    }

    public function testStyles()
    {
        Asset::$styles = array();
        $s = 'h1 {font:26px;}';
        Asset::addStyle($s);

        $expected = "\n" . '<style type="text/css">' ."\n" . $s ."\n". '</style>' . "\n";

        $this->expectOutputString($expected, Asset::styles());
    }

    public function testDomain()
    {
        Asset::setDomain('http://cdn.domain.tld/');

        $this->assertEquals('http://cdn.domain.tld/', Asset::$domain);
    }

    public function testCheckEnv()
    {
        Asset::setDomain('http://cdn.domain.tld/');
        Asset::$environment = 'online';
        Asset::checkEnv();

        $this->assertEquals('http://cdn.domain.tld/', Asset::$domain);

        Asset::$environment = 'local';
        Asset::checkEnv();

        $this->assertEquals('/', Asset::$domain);
    }

    public function testHash()
    {
        //TODO
    }

}