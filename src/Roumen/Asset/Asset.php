<?php namespace Roumen\Asset;

/**
 * Asset class for laravel-assets package.
 *
 * @author Roumen Damianoff <roumen@dawebs.com>
 * @version 2.5.4
 * @link http://roumen.it/projects/laravel-assets
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */

use Illuminate\Support\Facades\Cache;

class Asset
{
    // This constant indicates where to add a asset;
    const ADD_TO_NONE    = 0;
    const ADD_TO_CSS     = 1;
    const ADD_TO_LESS    = 2;
    const ADD_TO_JS      = 3;

    /*
     * If the assert To add do not have extension or the extension
     * is unknown this constant indicate what to do:
     */
    const ON_UNKNOWN_EXTENSION_NONE                  = 0;
    const ON_UNKNOWN_EXTENSION_CSS                   = 1;
    const ON_UNKNOWN_EXTENSION_LESS                  = 2;
    const ON_UNKNOWN_EXTENSION_JS                    = 3;
    private static $ON_UNKNOWN_EXTENSION_TO_ADD_TO   = [
                                                            Asset::ON_UNKNOWN_EXTENSION_NONE    => Asset::ADD_TO_CSS,
                                                            Asset::ON_UNKNOWN_EXTENSION_LESS    => Asset::ADD_TO_LESS,
                                                            Asset::ON_UNKNOWN_EXTENSION_JS      => Asset::ADD_TO_JS
                                                        ];

    public static $css = [];
    public static $less = [];
    public static $styles = [];
    public static $js = [];
    public static $jsParams = [];
    public static $scripts = [];
    public static $domain = '/';
    public static $prefix = '';
    public static $hash = [];
    public static $environment = null;
    public static $secure = false;
    public static $cacheEnabled = true;
    public static $cacheDuration = 360; // 6 hours
    public static $cacheKey = 'laravel-assets';
    protected static $cacheBusterGeneratorFunction = null;
    private static $useShortHandReady = false;
    private static $onUnknownExtensionDefault = Asset::ON_UNKNOWN_EXTENSION_NONE;


    /**
     * Check environment
     *
     * @return void
    */
    public static function checkEnv()
    {
        if (static::$environment == null)
        {
            static::$environment = \App::environment();
        }

        // use only local files in local environment
        if (static::$environment == 'local' && (static::$domain != '/'))
        {
            static::$domain = '/';
        }
    }


    /**
     * Set domain name
     *
     * @param string $url
     *
     * @return void
    */
    public static function setDomain($url)
    {
        if (is_string($url))
        {
            static::$domain = $url;
        }
    }


    /**
     * Set prefix
     *
     * @param string $prefix
     *
     * @return void
    */
    public static function setPrefix($prefix)
    {
        static::$prefix = $prefix;
    }


    /**
     * Set cache buster JSON file
     *
     * @param string $cachebuster
     *
     * @return void
    */
    public static function setCachebuster($cachebuster)
    {
        if (file_exists($cachebuster))
        {
            static::$hash = json_decode(file_get_contents($cachebuster), true);
        }
    }


    /**
     * Set cache buster function
     *
     * @param Callable $fn
     *
     * Callable must accepts ONE argument {String} (filename)
     * and return a {String} (hash without filename and "?")
     *
     * @return void
    */
    public static function setCacheBusterGeneratorFunction($fn)
    {
        static::$cacheBusterGeneratorFunction = $fn;
    }


    /**
     * Generate cache buster filename
     *
     * @param string $a
     * @param string $name
     *
     * @return void
    */
    private static function generateCacheBusterFilename($a)
    {
        $hash = '';
        if(!is_callable(static::$cacheBusterGeneratorFunction))
        {
            if(is_array(static::$hash) && array_key_exists($a, static::$hash)) {
                $hash .= static::$hash[$a];
            }
        } else
            {
                $hash = call_user_func_array(static::$cacheBusterGeneratorFunction, array($a));
            }

        if(is_string($hash) && $hash !== '') {
            $a .= '?' . $hash;
        }
        return $a;
    }


    /**
     * Set indicator if use Short Hand [$()] or Normal $( document ).ready()
     *
     * @param boolean $useShortHandReady
     *
     * @return void
    */
    public static function setUseShortHandReady($useShortHandReady)
    {
        static::$useShortHandReady = $useShortHandReady;
    }


    /**
     * Indicate what to do by default if an unknown extension is found.
     *
     * @param int (static::ON_UNKNOWN_EXTENSION_NONE,
     *             static::ON_UNKNOWN_EXTENSION_JS) $onUnknownExtensionDefault
     *
     * @return void
    */
    public static function setOnUnknownExtensionDefault($onUnknownExtensionDefault)
    {
        if ((!is_int($onUnknownExtensionDefault))
            || ($onUnknownExtensionDefault < static::ON_UNKNOWN_EXTENSION_NONE)
            || ($onUnknownExtensionDefault > static::ON_UNKNOWN_EXTENSION_JS))
        {
            $onUnknownExtensionDefault = static::ON_UNKNOWN_EXTENSION_NONE;
        }

        static::$onUnknownExtensionDefault = $onUnknownExtensionDefault;
    }


    /**
     * Add new asset
     *
     * @param string $a
     * @param string/array $params
     * @param int (static::ON_UNKNOWN_EXTENSION_NONE,
     *             static::ON_UNKNOWN_EXTENSION_JS) $onUnknownExtension
     *
     * @return void
    */
    public static function add($a, $params = 'footer', $onUnknownExtension = false)
    {
        if (is_array($a))
        {
            foreach ($a as $item)
            {
                static::processAdd($item, $params, $onUnknownExtension);
            }
        } else
            {
                static::processAdd($a, $params, $onUnknownExtension);
            }
    }


    /**
     * Identify where to add an asset:
     *
     * @param string $a
     * @param int (static::ON_UNKNOWN_EXTENSION_NONE,
     *             static::ON_UNKNOWN_EXTENSION_JS)/boolean $onUnknownExtension
     *
     * @return int (static::ADD_TO_NONE, static::ADD_TO_JS)
    */
    private static function getAddTo($a, $onUnknownExtension = false)
    {
        if (false === $onUnknownExtension)
        {
        	$onUnknownExtension = static::$onUnknownExtensionDefault;
        }

        if (preg_match("/(\.css|\/css\?)/i", $a))
        {
        	// css
        	return static::ADD_TO_CSS;

        } elseif (preg_match("/\.less/i", $a))
            {
            	// less
            	return static::ADD_TO_LESS;

            } elseif (preg_match("/\.js|\/js/i", $a))
                {
                	// js
                	return static::ADD_TO_JS;

                } elseif ( (static::ON_UNKNOWN_EXTENSION_NONE != $onUnknownExtension) && isset(static::$ON_UNKNOWN_EXTENSION_TO_ADD_TO[$onUnknownExtension]) )
                    {
                    	return static::$ON_UNKNOWN_EXTENSION_TO_ADD_TO[$onUnknownExtension];
                    }

        return static::ADD_TO_NONE;
    }


    /**
     * Process add method
     *
     * @param string $a
     * @param string/array $params
     *
     * @return void
    */
    protected static function processAdd($a, $params, $onUnknownExtension = false)
    {
        // check for '*' character
        static::checkVersion($a);

        switch (static::getAddTo($a, $onUnknownExtension))
        {
        	case static::ADD_TO_CSS:

        		static::$css[$a] = $a;
        		break;

        	case static::ADD_TO_LESS:

        		static::$less[$a] = $a;
        		break;

        	case static::ADD_TO_JS:

                if (is_array($params) && !empty($params['name']))
                {
                    $name = $params['name'];
                    static::$jsParams[$name][$a] = $params;
                }
                else
                {
                    $name = $params;
                }

        		static::$js[$name][$a] = $a;
        		break;
        }
    }


    /**
     * Add new asset as first in its array
     *
     * @param string $a
     * @param string/array $params
     *
     * @return void
    */
    public static function addFirst($a, $params = 'footer', $onUnknownExtension = false)
    {
        // check for '*' character
        static::checkVersion($a);

        switch (static::getAddTo($a, $onUnknownExtension))
        {
        	case static::ADD_TO_CSS:

                static::$css = [$a => $a] + static::$css;
        		break;

        	case static::ADD_TO_LESS:

                static::$less = [$a => $a] + static::$less;
        		break;

        	case static::ADD_TO_JS:

                if (is_array($params) && !empty($params['name']))
                {
                    $name = $params['name'];
                    static::$jsParams[$name][$a] = $params;
                }
                else
                {
                    $name = $params;
                }

                if (!empty(static::$js[$name]))
                {
                    static::$js[$name] = [$a => $a] + static::$js[$name];
                }
                else
                {
                    static::$js[$name][$a] = $a;
                }
        		break;
        }
    }


    /**
     * Add new asset before another asset in its array
     *
     * @param string $a
     * @param string $b
     * @param string/array $params
     *
     * @return void
    */
    public static function addBefore($a, $b, $params = 'footer', $onUnknownExtension = false)
    {
        // check for '*' character
        static::checkVersion($a);

        switch (static::getAddTo($a, $onUnknownExtension))
        {
        	case static::ADD_TO_CSS:

                $bpos = array_search($b, array_keys(static::$css));

                if ($bpos === 0)
                {
                    static::addFirst($a, $name);
                }
                elseif ($bpos >= 1)
                {
                    $barr = array_slice(static::$css, $bpos);
                    $aarr = array_slice(static::$css, 0, $bpos);
                    $aarr[$a] = $a;
                    static::$css = array_merge($aarr, $barr);
                }
                else
                {
                    static::$css[$a] = $a;
                }

        		break;

        	case static::ADD_TO_LESS:

                $bpos = array_search($b, array_keys(static::$less));

                if ($bpos === 0)
                {
                    static::addFirst($a, $name);
                }
                elseif ($bpos >= 1)
                {
                    $barr = array_slice(static::$less, $bpos);
                    $aarr = array_slice(static::$less, 0, $bpos);
                    $aarr[$a] = $a;
                    static::$less = array_merge($aarr, $barr);
                }
                else
                {
                    static::$less[$a] = $a;
                }

        		break;

        	case static::ADD_TO_JS:

                if (is_array($params) && !empty($params['name']))
                {
                    $name = $params['name'];
                    static::$jsParams[$name][$a] = $params;
                }
                else
                {
                    $name = $params;
                }

                if (!empty(static::$js[$name]))
                {
                    $bpos = array_search($b, array_keys(static::$js[$name]));

                    if ($bpos === 0)
                    {
                        static::addFirst($a, $name);
                    }
                    elseif ($bpos >= 1)
                    {
                        $barr = array_slice(static::$js[$name], $bpos);
                        $aarr = array_slice(static::$js[$name], 0, $bpos);
                        $aarr[$a] = $a;
                        static::$js[$name] = array_merge($aarr, $barr);
                    }
                    else
                    {
                        static::$js[$name][$a] = $a;
                    }
                }

                break;
        }
    }


    /**
     * Add new asset after another asset in its array
     *
     * @param string $a
     * @param string $b
     * @param string/array $params
     *
     * @return void
    */
    public static function addAfter($a, $b, $params = 'footer', $onUnknownExtension = false)
    {
            // check for '*' character
            static::checkVersion($a);

            switch (static::getAddTo($a, $onUnknownExtension))
            {
            	case static::ADD_TO_CSS:

                    $bpos = array_search($b, array_keys(static::$css));

                    if ($bpos === 0 || $bpos > 0)
                    {
                        $barr = array_slice(static::$css, $bpos+1);
                        $aarr = array_slice(static::$css, 0, $bpos+1);
                        $aarr[$a] = $a;
                        static::$css = array_merge($aarr, $barr);
                    }
                    else
                    {
                        static::$css[$a] = $a;
                    }

            		break;

            	case static::ADD_TO_LESS:

                    $bpos = array_search($b, array_keys(static::$less));

                    if ($bpos === 0 || $bpos > 0)
                    {
                        $barr = array_slice(static::$less, $bpos+1);
                        $aarr = array_slice(static::$less, 0, $bpos+1);
                        $aarr[$a] = $a;
                        static::$less = array_merge($aarr, $barr);
                    }
                    else
                    {
                        static::$less[$a] = $a;
                    }

            		break;

            	case static::ADD_TO_JS:

                    if (is_array($params) && !empty($params['name']))
                    {
                        $name = $params['name'];
                        static::$jsParams[$name][$a] = $params;
                    }
                    else
                    {
                        $name = $params;
                    }

                    if (!empty(static::$js[$name]))
                    {
                        $bpos = array_search($b, array_keys(static::$js[$name]));

                        if ($bpos === 0 || $bpos > 0)
                        {
                            $barr = array_slice(static::$js[$name], $bpos+1);
                            $aarr = array_slice(static::$js[$name], 0, $bpos+1);
                            $aarr[$a] = $a;
                            static::$js[$name] = array_merge($aarr, $barr);
                        }
                        else
                        {
                            static::$js[$name][$a] = $a;
                        }
                    }

                    break;
        }
    }


    /**
     * Add new script
     *
     * @param string $s
     * @param string $name
     *
     * @return void
    */
    public static function addScript($s, $name = 'footer')
    {
        static::$scripts[$name][] = $s;
    }


    /**
     * Add new style
     *
     * @param string $style
     * @param string $s
     *
     * @return void
    */
    public static function addStyle($style, $s = 'header')
    {
        static::$styles[$s][] = $style;
    }


	/**
	 * Returns the full-path for an asset.
	 *
	 * @param  string  $source
	 * @return string
	 */
	protected static function url($file)
	{
        if (preg_match('/(https?:)?\/\//i', $file))
        {
            return $file;
        }

        $file = static::generateCacheBusterFilename($file);

        if (static::$domain == '/' && static::$environment != 'testing')
        {
            return asset($file, static::$secure);
        }

        return rtrim(static::$domain, '/') .'/' . ltrim($file, '/');
	}


	/**
     * Loads all items from $css array not wrapped in <link> tags
     *
     * @param string $separator
     *
     * @return void
    */
    public static function cssRaw($separator = "")
    {
        static::checkEnv();

        if (!empty(static::$css))
        {
            foreach(static::$css as $file)
            {
                echo static::$prefix, static::url($file), $separator;
            }
        }
    }


    /**
     * Loads all items from $css array
     *
     * @return void
    */
    public static function css()
    {
        static::checkEnv();

        if (!empty(static::$css))
        {
            foreach(static::$css as $file)
            {
                echo static::$prefix, '<link rel="stylesheet" type="text/css" href="', static::url($file), "\" />\n";
            }
        }
    }


    /**
     * Loads all items from $less array not wrapped in <link> tags
     *
     * @param string $separator
     *
     * @return void
    */
    public static function lessRaw($separator = "")
    {
        static::checkEnv();

        if (!empty(static::$less))
        {
            foreach(static::$less as $file)
            {
                echo static::$prefix, static::url($file), $separator;
            }
        }
    }


    /**
     * Loads all items from $less array
     *
     * @return void
    */
    public static function less()
    {
        static::checkEnv();

        if (!empty(static::$less))
        {
            foreach(static::$less as $file)
            {
                echo static::$prefix, '<link rel="stylesheet/less" type="text/css" href="', static::url($file), "\" />\n";
            }
        }
    }


    /**
     * Loads all items from $styles array
     *
     * @param string $name
     *
     * @return void
    */
    public static function styles($name = 'header')
    {
        if (($name !== '') && (!empty(static::$styles[$name])))
        {
            echo "\n", static::$prefix, "<style type=\"text/css\">\n", static::$prefix;

            foreach(static::$styles[$name] as $style)
            {
                echo "$style\n", static::$prefix;
            }

            echo static::$prefix, "</style>\n";

        } elseif (!empty(static::$styles))
            {
                echo static::$prefix, "<style type=\"text/css\">\n";

                foreach(static::$styles as $style)
                {
                    echo "$style\n";
                }

                echo "</style>\n";
            }
    }


    /**
     * Loads items from $js array not wrapped in <script> tags
     *
     * @param string $separator
     * @param string $name
     *
     * @return void
    */
    public static function jsRaw($separator = "", $name = 'footer')
    {
        static::checkEnv();

        if (!empty(static::$js[$name]))
        {
            foreach(static::$js[$name] as $file)
            {
                echo static::$prefix, static::url($file), $separator;
            }
        }
    }


    /**
     * Loads items from $js array
     *
     * @param string $name
     *
     * @return void
    */
    public static function js($name = 'footer')
    {
        static::checkEnv();

        $type = '';
        $defer = '';
        $async = '';

        if (!empty(static::$js[$name]))
        {
            foreach(static::$js[$name] as $file)
            {
                if (!empty(static::$jsParams[$name][$file]))
                {
                    if (!empty(static::$jsParams[$name][$file]['type'])) $type = static::$jsParams[$name][$file]['type'];
                    if (!empty(static::$jsParams[$name][$file]['defer'])) $defer = static::$jsParams[$name][$file]['defer'];
                    if (!empty(static::$jsParams[$name][$file]['async'])) $async = static::$jsParams[$name][$file]['async'];
                }

                $e  = static::$prefix;
                $e .= '<script src="'.static::url($file).'"';
                if ($type != '') $e .= ' type="'.$type.'"';
                if ($defer != '') $e .= ' defer="'.$defer.'"';
                if ($async != '') $e .= ' async="'.$async.'"';
                $e .= '></script>'."\n";

                echo $e;
            }
        }
    }


    /**
     * Loads items from $scripts array
     *
     * @param string $name
     *
     * @return void
    */
    public static function scripts($name = 'footer')
    {
        if ($name == 'ready')
        {
            if (!empty(static::$scripts['ready']))
            {
                echo static::$prefix, '<script>', (static::$useShortHandReady ? '$(' : '$(document).ready('), "function(){\n";

                foreach(static::$scripts['ready'] as $script)
                {
                    echo "$script\n", static::$prefix;
                }

                echo "});</script>\n";
            }
        } else
            {
                if (!empty(static::$scripts[$name]))
                {
                    foreach(static::$scripts[$name] as $script)
                    {
                        echo static::$prefix, "<script>\n$script\n</script>\n";
                    }
                }
            }
    }


    /**
     * Checks if the asset has wildcard '*' character
     *
     * @param string &$a
     *
     * @return void
    */
    public static function checkVersion(&$a)
    {
        // check for '*' character
        if (preg_match("/\*/i", $a))
        {
            $a_org = $a;

            // check for cached version
            if (static::$cacheEnabled && Cache::has(static::$cacheKey.$a))
            {
                // use cached version
                $a = Cache::get(static::$cacheKey.$a);
            }
            else
            {
                // get latest version
                preg_match("/(.*?)(\*)(.*)/", $a, $m1);
                preg_match("/(.*)\/(.*)/", $m1[1], $m2);

                // check if is url or path
                (preg_match('/(https?:)?\/\//i', $a)) ? $f = file_get_contents($m2[1]) : $f = json_encode(scandir($m2[1], 1));

                if ($m2[2] != '')
                {
                    preg_match_all("/(".str_replace('/', '\/', $m2[2]).")(\d+(?:\.\d+){1,9})/i", $f, $m3, PREG_PATTERN_ORDER);
                    usort($m3[2],'version_compare');
                    $a = $m2[0].end($m3[2]).$m1[3];
                }
                else
                {
                    preg_match_all("/(\d+(?:\.\d+){1,9})(".str_replace('/', '\/', $m1[3]).")/i", $f, $m3, PREG_PATTERN_ORDER);

                    if (!empty($m3[1]))
                    {
                        usort($m3[1],'version_compare');
                        $a = $m2[0].end($m3[1]).$m1[3];
                    }
                    else
                    {
                        preg_match_all("/(\d+(?:\.\d+){1,9})/i", $f, $m3, PREG_PATTERN_ORDER);
                        usort($m3[1],'version_compare');
                        $a = $m2[0].end($m3[1]).$m1[3];
                    }
                }
            }

            // cache latest version
            if (static::$cacheEnabled) Cache::put(static::$cacheKey.$a_org, $a, static::$cacheDuration);
        }
    }

}
