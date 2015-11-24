<?php

use Roumen\Asset\Asset as Asset;

class AssetTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        parent::setUp();
        Asset::$environment = 'testing';
        Asset::$cacheEnabled = false;
    }

    public function testAdd()
    {
        Asset::add('style.css');
        Asset::add('style.css');
        Asset::add('style.less');
        Asset::add('script.js');
        Asset::add('script.js', 'foobar');

        Asset::add('scriptWithParams.js', ['name'=>'footer2', 'type'=>'text/jsx', 'async' => 'true', 'defer'=>'true']);

        $this->assertEquals('style.css', Asset::$css['style.css']);
        $this->assertEquals('style.less', Asset::$less['style.less']);
        $this->assertEquals('script.js', Asset::$js['footer']['script.js']);
        $this->assertEquals('script.js', Asset::$js['foobar']['script.js']);

        $this->assertEquals('scriptWithParams.js', Asset::$js['footer2']['scriptWithParams.js']);
    }

    public function testAddWildcard()
    {
        /* local assets */

        Asset::add('tests/files/cdn/test/test-*.min.js','foobar');
        Asset::add('tests/files/cdn/*/test.min.js','foobar');

        $this->assertEquals('tests/files/cdn/test/test-3.3.3.min.js', Asset::$js['foobar']['tests/files/cdn/test/test-3.3.3.min.js']);
        $this->assertEquals('tests/files/cdn/3.3.3/test.min.js', Asset::$js['foobar']['tests/files/cdn/3.3.3/test.min.js']);

        // NOTE: KEEP TESTS BELLOW COMMENTED OUT! Versions change often and they will fail.

        /* cdn.roumen.it */

        //sset::add('https://cdn.roumen.it/repo/jquery/jquery-*.min.js','foobar');
        //Asset::add('https://cdn.roumen.it/repo/jquery-ui/*/jquery-ui.min.js','foobar');
        //Asset::add('https://cdn.roumen.it/repo/bootstrap/*/css/bootstrap.min.css');
        //Asset::add('https://cdn.roumen.it/repo/bootstrap/*/js/bootstrap.min.js','foobar');
        //Asset::add('https://cdn.roumen.it/repo/ckeditor/*/full/ckeditor.js','foobar');
        //Asset::add('https://cdn.roumen.it/repo/respond.js/*/respond.min.js','foobar');
        //Asset::add('https://cdn.roumen.it/repo/html5shiv/*/html5shiv.js','foobar');

        //$this->assertEquals('https://cdn.roumen.it/repo/jquery/jquery-2.1.4.min.js', Asset::$js['foobar']['https://cdn.roumen.it/repo/jquery/jquery-2.1.4.min.js']);
        //$this->assertEquals('https://cdn.roumen.it/repo/jquery-ui/1.11.4/jquery-ui.min.js', Asset::$js['foobar']['https://cdn.roumen.it/repo/jquery-ui/1.11.4/jquery-ui.min.js']);
        //$this->assertEquals('https://cdn.roumen.it/repo/bootstrap/3.3.1/css/bootstrap.min.css', Asset::$css['https://cdn.roumen.it/repo/bootstrap/3.3.1/css/bootstrap.min.css']);
        //$this->assertEquals('https://cdn.roumen.it/repo/bootstrap/3.3.1/js/bootstrap.min.js', Asset::$js['foobar']['https://cdn.roumen.it/repo/bootstrap/3.3.1/js/bootstrap.min.js']);
        //$this->assertEquals('https://cdn.roumen.it/repo/ckeditor/4.4.6/full/ckeditor.js', Asset::$js['foobar']['https://cdn.roumen.it/repo/ckeditor/4.4.6/full/ckeditor.js']);
        //$this->assertEquals('https://cdn.roumen.it/repo/respond.js/1.4.2/respond.min.js', Asset::$js['foobar']['https://cdn.roumen.it/repo/respond.js/1.4.2/respond.min.js']);
        //$this->assertEquals('https://cdn.roumen.it/repo/html5shiv/3.7.0/html5shiv.js', Asset::$js['foobar']['https://cdn.roumen.it/repo/html5shiv/3.7.0/html5shiv.js']);

        /* code.jquery.com */

        //Asset::add('https://code.jquery.com/jquery-*.min.js','foobar');
        //Asset::add('https://code.jquery.com/ui/*/jquery-ui.min.js','foobar');
        //Asset::add('https://code.jquery.com/mobile/*/jquery.mobile-1.4.5.min.js','foobar');
        //Asset::add('https://code.jquery.com/color/jquery.color-*.min.js','foobar');

        //$this->assertEquals('https://code.jquery.com/jquery-2.1.4.min.js', Asset::$js['foobar']['https://code.jquery.com/jquery-2.1.4.min.js']);
        //$this->assertEquals('https://code.jquery.com/ui/1.11.4/jquery-ui.min.js', Asset::$js['foobar']['https://code.jquery.com/ui/1.11.4/jquery-ui.min.js']);
        //$this->assertEquals('https://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js', Asset::$js['foobar']['https://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js']);
        //$this->assertEquals('https://code.jquery.com/color/jquery.color-2.1.2.min.js', Asset::$js['foobar']['https://code.jquery.com/color/jquery.color-2.1.2.min.js']);
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
        Asset::addStyle('test2');
        $this->assertEquals('test2', Asset::$styles['header'][1]);

        Asset::addStyle('test123','foobar');
        $this->assertEquals('test123', Asset::$styles['foobar'][0]);
    }

    public function testAddFirst()
    {
        Asset::$css = [];
        Asset::$less = [];
        Asset::$js = [];

        Asset::add('style.css');
        Asset::addFirst('styleFirst.css');

        // get keys as numbers
        $keys = array_keys(Asset::$css);

        $this->assertEquals('styleFirst.css', Asset::$css[$keys[0]]);
        $this->assertEquals('style.css', Asset::$css[$keys[1]]);

        Asset::add('style.less');
        Asset::addFirst('styleFirst.less');

        $keys = array_keys(Asset::$less);

        $this->assertEquals('styleFirst.less', Asset::$less[$keys[0]]);
        $this->assertEquals('style.less', Asset::$less[$keys[1]]);

        Asset::add('script.js');
        Asset::addFirst('scriptFirst.js');

        $keys = array_keys(Asset::$js['footer']);

        $this->assertEquals('scriptFirst.js', Asset::$js['footer'][$keys[0]]);
        $this->assertEquals('script.js', Asset::$js['footer'][$keys[1]]);

        Asset::add('script3.js','foobar');
        Asset::addFirst('scriptFirst.js','foobar');
        Asset::addFirst('scriptFirst2.js',['name'=>'foobar','type'=>'text/jsx','async'=>'true','defer'=>'false']);

        $keys = array_keys(Asset::$js['foobar']);

        $this->assertEquals('scriptFirst2.js', Asset::$js['foobar'][$keys[0]]);
        $this->assertEquals('scriptFirst.js', Asset::$js['foobar'][$keys[1]]);
        $this->assertEquals('script3.js', Asset::$js['foobar'][$keys[2]]);
        $this->assertEquals(['name'=>'foobar','type'=>'text/jsx','async'=>'true','defer'=>'false'], Asset::$jsParams['foobar']['scriptFirst2.js']);

    }


    public function testAddBefore()
    {
        Asset::$css = [];
        Asset::$less = [];
        Asset::$js = [];

        Asset::add(['1.css','2.css','3.css']);
        Asset::addBefore('before2.css','2.css');

        $keys = array_keys(Asset::$css);

        $this->assertEquals('before2.css', Asset::$css[$keys[1]]);
        $this->assertEquals('2.css', Asset::$css[$keys[2]]);


        Asset::add(['1.less','2.less','3.less']);
        Asset::addBefore('before2.less','2.less');

        $keys = array_keys(Asset::$less);

        $this->assertEquals('before2.less', Asset::$less[$keys[1]]);
        $this->assertEquals('2.less', Asset::$less[$keys[2]]);

        Asset::add(['1.js','2.js','3.js']);
        Asset::addBefore('before2.js','2.js');

        $keys = array_keys(Asset::$js['footer']);

        $this->assertEquals('before2.js', Asset::$js['footer'][$keys[1]]);
        $this->assertEquals('2.js', Asset::$js['footer'][$keys[2]]);

        Asset::add(['1.js','2.js','3.js'],'foobar');
        Asset::addBefore('before2.js','2.js', 'foobar');
        Asset::addBefore('before3.js','3.js',['name'=>'foobar','type'=>'text/jsx','async'=>'true','defer'=>'false']);

        $keys = array_keys(Asset::$js['foobar']);

        $this->assertEquals('before2.js', Asset::$js['foobar'][$keys[1]]);
        $this->assertEquals('2.js', Asset::$js['foobar'][$keys[2]]);
        $this->assertEquals('before3.js', Asset::$js['foobar'][$keys[3]]);
        $this->assertEquals(['name'=>'foobar','type'=>'text/jsx','async'=>'true','defer'=>'false'], Asset::$jsParams['foobar']['before3.js']);
    }

    public function testAddAfter()
    {
        Asset::$css = [];
        Asset::$less = [];
        Asset::$js = [];

        Asset::add(['1.css','2.css','3.css']);
        Asset::addAfter('after2.css','2.css');

        $keys = array_keys(Asset::$css);

        $this->assertEquals('after2.css', Asset::$css[$keys[2]]);
        $this->assertEquals('2.css', Asset::$css[$keys[1]]);

        Asset::add(['1.less','2.less','3.less']);
        Asset::addAfter('after2.less','2.less');

        $keys = array_keys(Asset::$less);

        $this->assertEquals('after2.less', Asset::$less[$keys[2]]);
        $this->assertEquals('2.less', Asset::$less[$keys[1]]);

        Asset::add(['1.js','2.js','3.js']);
        Asset::addAfter('after2.js','2.js');

        $keys = array_keys(Asset::$js['footer']);

        $this->assertEquals('after2.js', Asset::$js['footer'][$keys[2]]);
        $this->assertEquals('2.js', Asset::$js['footer'][$keys[1]]);

        Asset::add(['1.js','2.js','3.js'],'foobar');
        Asset::addAfter('after2.js','2.js', 'foobar');
        Asset::addAfter('after1.js','1.js',['name'=>'foobar','type'=>'text/jsx','async'=>'true','defer'=>'false']);

        $keys = array_keys(Asset::$js['foobar']);

        $this->assertEquals('after2.js', Asset::$js['foobar'][$keys[3]]);
        $this->assertEquals('2.js', Asset::$js['foobar'][$keys[2]]);
        $this->assertEquals('after1.js', Asset::$js['foobar'][$keys[1]]);

        $this->assertEquals(['name'=>'foobar','type'=>'text/jsx','async'=>'true','defer'=>'false'], Asset::$jsParams['foobar']['after1.js']);
    }

    public function testCssGoogleFonts()
    {
        Asset::$css = [];

        Asset::add([
                    'http://fonts.googleapis.com/css?family=Londrina+Outline',
                    'http://fonts.googleapis.com/css?family=Nova+Square',
                    'http://fonts.googleapis.com/css?family=Special+Elite'
                    ]);

        $keys = array_keys(Asset::$css);

        $this->assertEquals('http://fonts.googleapis.com/css?family=Londrina+Outline', Asset::$css[$keys[0]]);
        $this->assertEquals('http://fonts.googleapis.com/css?family=Nova+Square', Asset::$css[$keys[1]]);
        $this->assertEquals('http://fonts.googleapis.com/css?family=Special+Elite', Asset::$css[$keys[2]]);

        Asset::addFirst('http://fonts.googleapis.com/css?family=Share+Tech+Mono');
        Asset::addAfter('http://fonts.googleapis.com/css?family=Playfair+Display+SC', 'http://fonts.googleapis.com/css?family=Share+Tech+Mono');
        Asset::addBefore('http://fonts.googleapis.com/css?family=Arapey', 'http://fonts.googleapis.com/css?family=Londrina+Outline');

        $keys = array_keys(Asset::$css);

        $this->assertEquals('http://fonts.googleapis.com/css?family=Share+Tech+Mono', Asset::$css[$keys[0]]);
        $this->assertEquals('http://fonts.googleapis.com/css?family=Playfair+Display+SC', Asset::$css[$keys[1]]);
        $this->assertEquals('http://fonts.googleapis.com/css?family=Arapey', Asset::$css[$keys[2]]);
        $this->assertEquals('http://fonts.googleapis.com/css?family=Londrina+Outline', Asset::$css[$keys[3]]);
        $this->assertEquals('http://fonts.googleapis.com/css?family=Nova+Square', Asset::$css[$keys[4]]);
        $this->assertEquals('http://fonts.googleapis.com/css?family=Special+Elite', Asset::$css[$keys[5]]);

    }

    public function testCssRaw()
    {
        Asset::$css = [];

        Asset::add(['1.css','2.css','3.css']);

        $this->expectOutputString('/1.css,/2.css,/3.css,', Asset::cssRaw(','));
    }

    public function testCssWrapped()
    {
        Asset::$css = [];
        Asset::add(['1.css','http://foo.dev/2.css'], 'header');

        $expected = '<link rel="stylesheet" type="text/css" href="/1.css" />'."\n".'<link rel="stylesheet" type="text/css" href="http://foo.dev/2.css" />'."\n";

        $this->expectOutputString($expected, Asset::css('header'));
    }

    public function testLessRaw()
    {
        Asset::$less = [];

        Asset::add(['1.less','2.less','3.less']);
        $this->expectOutputString('/1.less,/2.less,/3.less,', Asset::lessRaw(','));
    }

    public function testLessWrapped()
    {
        Asset::$less = [];
        Asset::add(['1.less','http://foo.dev/2.less'], 'header');

        $expected = '<link rel="stylesheet/less" type="text/css" href="/1.less" />'."\n".'<link rel="stylesheet/less" type="text/css" href="http://foo.dev/2.less" />'."\n";

        $this->expectOutputString($expected, Asset::less('header'));
    }

    public function testJsRaw()
    {
        Asset::$js = [];

        Asset::add(['1.js','2.js','3.js']);
        $this->expectOutputString('/1.js,/2.js,/3.js,', Asset::jsRaw(','));
    }

    public function testJsWrapped()
    {
        Asset::$js = [];
        Asset::add(['1.js','http://foo.dev/2.js'], 'footer');
        Asset::add('scriptWithParams.js',['name'=>'footer', 'type'=>'text/jsx', 'async' => 'true', 'defer'=>'true']);


        $expected  = '<script src="/1.js"></script>'."\n".'<script src="http://foo.dev/2.js"></script>'."\n";
        $expected .= '<script src="/scriptWithParams.js" type="text/jsx" defer="true" async="true"></script>'."\n";

        $this->expectOutputString($expected, Asset::js('footer'));
    }

    public function testStyles()
    {
        Asset::$styles = [];
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

    public function testSecure()
    {
        Asset::$secure = true;

        $this->assertEquals(true, Asset::$secure);
    }

    public function testCachebusterFile()
    {
        Asset::$js = [];
        Asset::$css = [];
        Asset::$hash = [];

        Asset::setCachebuster('tests/files/cache.json');

        $this->assertEquals(Asset::$hash, [
                                            '1.js' => '27f771f4d8aeea4878c2b5ac39a2031f',
                                            '3.js' => '82f0e3247f8516bd91abcdbed83c71c0',
                                            '2.css' => '42b98f2980dc1366cf1d2677d4891eda'
                                          ]
        );

        Asset::add(['1.js','2.js','3.js']);
        Asset::add(['1.css','2.css','3.css']);

        $this->expectOutputString('/1.js?27f771f4d8aeea4878c2b5ac39a2031f,/2.js,/3.js?82f0e3247f8516bd91abcdbed83c71c0,/1.css,/2.css?42b98f2980dc1366cf1d2677d4891eda,/3.css,', Asset::jsRaw(','), Asset::cssRaw(','));
    }

    public function testCachebusterFunction()
    {
        Asset::$js = [];
        Asset::$css = [];

        function _hash($name)
        {
            if($name == '1.js') {
                return '';
            }
            if($name == '2.css') {
                return null;
            }
            return substr($name, 0, 1);
        }

        Asset::setCacheBusterGeneratorFunction('_hash');

        Asset::add(['1.js','2.js','3.js']);
        Asset::add(['1.css','2.css','3.css']);

        $this->expectOutputString('/1.js,/2.js?2,/3.js?3,/1.css?1,/2.css,/3.css?3,', Asset::jsRaw(','), Asset::cssRaw(','));
    }

}
