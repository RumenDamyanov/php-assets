<?php

use Roumen\Asset\Asset as Asset;

class AssetTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        parent::setUp();
        Asset::$environment = 'testing';
    }

    public function testAdd()
    {
        Asset::add('style.css');
        Asset::add('style.less');
        Asset::add('script.js');
        Asset::add('script.js','foobar');

        $this->assertEquals('style.css', Asset::$css[0]);
        $this->assertEquals('style.less', Asset::$less[0]);
        $this->assertEquals('script.js', Asset::$js['footer'][0]);
        $this->assertEquals('script.js', Asset::$js['foobar'][0]);

    }

    public function testAddScript()
    {
        Asset::addScript('test');

        $this->assertEquals('test', Asset::$scripts['footer'][0]);

        Asset::addScript('test','foobar');

        $this->assertEquals('test', Asset::$scripts['foobar'][0]);
    }

    public function testAddStyle()
    {
        Asset::addStyle('test');
        $this->assertEquals('test', Asset::$styles['header'][0]);

        Asset::addStyle('test','foobar');
        $this->assertEquals('test', Asset::$styles['foobar'][0]);
    }

    public function testAddFirst()
    {
        Asset::$css = array();
        Asset::$less = array();
        Asset::$js = array();

        Asset::add('style.css');
        Asset::addFirst('styleFirst.css');

        $this->assertEquals('styleFirst.css', Asset::$css[0]);
        $this->assertEquals('style.css', Asset::$css[1]);

        Asset::add('style.less');
        Asset::addFirst('styleFirst.less');

        $this->assertEquals('styleFirst.less', Asset::$less[0]);
        $this->assertEquals('style.less', Asset::$less[1]);

        Asset::add('script.js');
        Asset::addFirst('scriptFirst.js');

        $this->assertEquals('scriptFirst.js', Asset::$js['footer'][0]);
        $this->assertEquals('script.js', Asset::$js['footer'][1]);

        Asset::add('script3.js','foobar');
        Asset::addFirst('scriptFirst.js','foobar');

        $this->assertEquals('scriptFirst.js', Asset::$js['foobar'][0]);
        $this->assertEquals('script3.js', Asset::$js['foobar'][1]);
    }

    public function testAddBefore()
    {
        Asset::$css = array();
        Asset::$less = array();
        Asset::$js = array();

        Asset::add(array('1.css','2.css','3.css'));
        Asset::addBefore('before2.css','2.css');

        $this->assertEquals('before2.css', Asset::$css[1]);
        $this->assertEquals('2.css', Asset::$css[2]);

        Asset::add(array('1.less','2.less','3.less'));
        Asset::addBefore('before2.less','2.less');

        $this->assertEquals('before2.less', Asset::$less[1]);
        $this->assertEquals('2.less', Asset::$less[2]);

        Asset::add(array('1.js','2.js','3.js'));
        Asset::addBefore('before2.js','2.js');

        $this->assertEquals('before2.js', Asset::$js['footer'][1]);
        $this->assertEquals('2.js', Asset::$js['footer'][2]);

        Asset::add(array('1.js','2.js','3.js'),'foobar');
        Asset::addBefore('before2.js','2.js', 'foobar');

        $this->assertEquals('before2.js', Asset::$js['foobar'][1]);
        $this->assertEquals('2.js', Asset::$js['foobar'][2]);
    }

    public function testAddAfter()
    {
        Asset::$css = array();
        Asset::$less = array();
        Asset::$js = array();

        Asset::add(array('1.css','2.css','3.css'));
        Asset::addAfter('after2.css','2.css');

        $this->assertEquals('after2.css', Asset::$css[2]);
        $this->assertEquals('2.css', Asset::$css[1]);

        Asset::add(array('1.less','2.less','3.less'));
        Asset::addAfter('after2.less','2.less');

        $this->assertEquals('after2.less', Asset::$less[2]);
        $this->assertEquals('2.less', Asset::$less[1]);

        Asset::add(array('1.js','2.js','3.js'));
        Asset::addAfter('after2.js','2.js');

        $this->assertEquals('after2.js', Asset::$js['footer'][2]);
        $this->assertEquals('2.js', Asset::$js['footer'][1]);

        Asset::add(array('1.js','2.js','3.js'),'foobar');
        Asset::addAfter('after2.js','2.js', 'foobar');

        $this->assertEquals('after2.js', Asset::$js['foobar'][2]);
        $this->assertEquals('2.js', Asset::$js['foobar'][1]);
    }

    public function testCssGoogleFonts()
    {
        Asset::$css = array();

        Asset::add(array('http://fonts.googleapis.com/css?family=Londrina+Outline','http://fonts.googleapis.com/css?family=Nova+Square','http://fonts.googleapis.com/css?family=Special+Elite'));
        $this->assertEquals('http://fonts.googleapis.com/css?family=Londrina+Outline', Asset::$css[0]);
        $this->assertEquals('http://fonts.googleapis.com/css?family=Nova+Square', Asset::$css[1]);
        $this->assertEquals('http://fonts.googleapis.com/css?family=Special+Elite', Asset::$css[2]);

        Asset::addFirst('http://fonts.googleapis.com/css?family=Share+Tech+Mono');
        Asset::addAfter('http://fonts.googleapis.com/css?family=Playfair+Display+SC', 'http://fonts.googleapis.com/css?family=Share+Tech+Mono');
        Asset::addBefore('http://fonts.googleapis.com/css?family=Arapey', 'http://fonts.googleapis.com/css?family=Londrina+Outline');

        $this->assertEquals('http://fonts.googleapis.com/css?family=Share+Tech+Mono', Asset::$css[0]);
        $this->assertEquals('http://fonts.googleapis.com/css?family=Playfair+Display+SC', Asset::$css[1]);
        $this->assertEquals('http://fonts.googleapis.com/css?family=Arapey', Asset::$css[2]);
        $this->assertEquals('http://fonts.googleapis.com/css?family=Londrina+Outline', Asset::$css[3]);
        $this->assertEquals('http://fonts.googleapis.com/css?family=Nova+Square', Asset::$css[4]);
        $this->assertEquals('http://fonts.googleapis.com/css?family=Special+Elite', Asset::$css[5]);

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

}