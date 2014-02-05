<?php

use Roumen\Asset\Asset as Asset;

class AssetTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        parent::setUp();
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

    public function testRaw()
    {
        //TODO
    }

    public function testWrapped()
    {
        //TODO
    }

}