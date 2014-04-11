<?php namespace Roumen\Asset;

/**
 * Asset class for laravel-assets package.
 *
 * @author Roumen Damianoff <roumen@dawebs.com>
 * @version 2.3.12
 * @link http://roumen.it/projects/laravel-assets
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class Asset
{

    public static $css = array();
    public static $less = array();
    public static $styles = array();
    public static $js = array();
    public static $scripts = array();
    public static $domain = '/';
    public static $prefix = '';
    public static $hash = null;
    public static $environment = null;
    protected static $cacheBusterGeneratorFunction = null;

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
        if (static::$environment == 'local' && static::$domain != '/')
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
        static::$domain = $url;
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
        if (file_exists($cachebuster)) static::$hash = json_decode(file_get_contents($cachebuster));
    }

    /**
     * Set cache buster filename
     *
     * @param Closure $fn
     *
     * Closure must accepts ONE argument {String}
     * and return a {String}
     *
     * @return void
    */
    private static function setCacheBusterGeneratorFunction(\Closure $fn)
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
        if(!static::$cacheBusterGeneratorFunction instanceof \Closure){
            return (static::$hash && property_exists(static::$hash, $a)) ? static::$hash->{$a} : $a;
        }else{
            return static::$cacheBusterGeneratorFunction($a);
        }
    }

    /**
     * Add new asset
     *
     * @param string $a
     * @param string $name
     *
     * @return void
    */
    public static function add($a, $name = 'footer')
    {
        if (is_array($a))
            foreach ($a as $item) static::processAdd($item, $name);
        else
            static::processAdd($a, $name);
    }

    /**
     * Process add method
     *
     * @param string $a
     * @param string $name
     *
     * @return void
    */
    protected static function processAdd($a, $name)
    {
        $a = static::generateCacheBusterFilename($a);

        if (preg_match("/(\.css|\/css\?)/i", $a))
        {
            // css
            static::$css[] = $a;
        }

        if (preg_match("/\.less/i", $a))
        {
            // less
            static::$less[] = $a;
        }

        elseif (preg_match("/\.js/i", $a))
        {
            // js
            static::$js[$name][] = $a;
        }
    }

    /**
     * Add new asset as first in its array
     *
     * @param string $a
     * @param string $name
     *
     * @return void
    */
    public static function addFirst($a, $name = 'footer')
    {
        $a = static::generateCacheBusterFilename($a);

        if (preg_match("/(\.css|\/css\?)/i", $a))
        {
            // css
            array_unshift(static::$css, $a);
        }

        if (preg_match("/\.less/i", $a))
        {
            // less
            array_unshift(static::$less, $a);
        }

        elseif (preg_match("/\.js/i", $a))
        {
            // js
            if (!empty(static::$js[$name]))
            {
                array_unshift(static::$js[$name], $a);
            } else
                {
                    static::$js[$name][] = $a;
                }
        }
    }

    /**
     * Add new asset before another asset in its array
     *
     * @param string $a
     * @param string $b
     * @param string $name
     *
     * @return void
    */
    public static function addBefore($a, $b, $name = 'footer')
    {
        $a = static::generateCacheBusterFilename($a);

        if (preg_match("/(\.css|\/css\?)/i", $a))
        {
            // css
            $bpos = array_search($b, static::$css);

            if ($bpos === 0)
            {
                static::addFirst($a, $name);
            } elseif ($bpos >= 1)
                {
                    $barr = array_slice(static::$css, $bpos);
                    $aarr = array_slice(static::$css, 0, $bpos);
                    array_push($aarr, $a);
                    static::$css = array_merge($aarr, $barr);
                } else
                    {
                        static::$css[] = $a;
                    }
        }

        if (preg_match("/\.less/i", $a))
        {
            // less
            $bpos = array_search($b, static::$less);

            if ($bpos === 0)
            {
                static::addFirst($a, $name);
            } elseif ($bpos >= 1)
                {
                    $barr = array_slice(static::$less, $bpos);
                    $aarr = array_slice(static::$less, 0, $bpos);
                    array_push($aarr, $a);
                    static::$less = array_merge($aarr, $barr);
                } else
                    {
                        static::$less[] = $a;
                    }
        }

        elseif (preg_match("/\.js/i", $a))
        {
            // js
            if (!empty(static::$js[$name]))
            {
                $bpos = array_search($b, static::$js[$name]);

                if ($bpos === 0)
                {
                    static::addFirst($a, $name);
                } elseif ($bpos >= 1)
                    {
                        $barr = array_slice(static::$js[$name], $bpos);
                        $aarr = array_slice(static::$js[$name], 0, $bpos);
                        array_push($aarr, $a);
                        static::$js[$name] = array_merge($aarr, $barr);
                    } else
                        {
                            static::$js[$name][] = $a;
                        }
            }
        }
    }

    /**
     * Add new asset after another asset in its array
     *
     * @param string $a
     * @param string $b
     * @param string $name
     *
     * @return void
    */
    public static function addAfter($a, $b, $name = 'footer')
    {
        $a = static::generateCacheBusterFilename($a);

        if (preg_match("/(\.css|\/css\?)/i", $a))
        {
            // css
            $bpos = array_search($b, static::$css);

            if ($bpos === 0 || $bpos > 0)
            {
                $barr = array_slice(static::$css, $bpos+1);
                $aarr = array_slice(static::$css, 0, $bpos+1);
                array_push($aarr, $a);
                static::$css = array_merge($aarr, $barr);
            } else
                {
                    static::$css[] = $a;
                }
        }

        if (preg_match("/\.less/i", $a))
        {
            // less
            $bpos = array_search($b, static::$less);

            if ($bpos === 0 || $bpos > 0)
            {
                    $barr = array_slice(static::$less, $bpos+1);
                    $aarr = array_slice(static::$less, 0, $bpos+1);
                    array_push($aarr, $a);
                    static::$less = array_merge($aarr, $barr);
                } else
                    {
                        static::$less[] = $a;
                    }
        }

        elseif (preg_match("/\.js/i", $a))
        {
            // js
            if (!empty(static::$js[$name]))
            {
                $bpos = array_search($b, static::$js[$name]);

                if ($bpos === 0 || $bpos > 0)
                {
                    $barr = array_slice(static::$js[$name], $bpos+1);
                    $aarr = array_slice(static::$js[$name], 0, $bpos+1);
                    array_push($aarr, $a);
                    static::$js[$name] = array_merge($aarr, $barr);
                } else
                    {
                        static::$js[$name][] = $a;
                    }
            }
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
                if (preg_match('/(https?:)?\/\//i', $file))
                {
                    $url = $file;
                } else
                    {
                        $url = static::$domain . $file;
                    }
                echo static::$prefix . $url . $separator;
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
                if (preg_match('/(https?:)?\/\//i', $file))
                {
                    $url = $file;
                } else
                    {
                        $url = static::$domain . $file;
                    }
                echo static::$prefix . '<link rel="stylesheet" type="text/css" href="' . $url . '" />' . "\n";
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
                if (preg_match('/(https?:)?\/\//i', $file))
                {
                    $url = $file;
                } else
                    {
                        $url = static::$domain . $file;
                    }
                echo static::$prefix . $url . $separator;
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
                if (preg_match('/(https?:)?\/\//i', $file))
                {
                    $url = $file;
                } else
                    {
                        $url = static::$domain . $file;
                    }
                echo static::$prefix . '<link rel="stylesheet/less" type="text/css" href="' . $url . '" />' . "\n";
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
            $p = "\n" . static::$prefix . "<style type=\"text/css\">\n" . static::$prefix;
            foreach(static::$styles[$name] as $style)
            {
                $p .= $style . "\n" . static::$prefix;
            }
            $p .= static::$prefix . "</style>\n";
            echo $p;
        }
        else if (!empty(static::$styles))
        {
            $p = static::$prefix . "<style type=\"text/css\">\n";
            foreach(static::$styles as $style)
            {
                $p .= $style . "\n";
            }
            $p .= "</style>\n";
            echo $p;
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
                if (preg_match('/(https?:)?\/\//i', $file))
                {
                    $url = $file;
                } else
                    {
                       $url = static::$domain . $file;
                    }
                echo static::$prefix . $url . $separator;
            }
        }

    }

    /**
     * Loads items from $js array
     *
     * @param string $name
     * @param boolean $tags
     * @param string $join
     *
     * @return void
    */
    public static function js($name = 'footer')
    {
        static::checkEnv();

        if ($name === false) $name = 'footer';
        if (!empty(static::$js[$name]))
        {
            foreach(static::$js[$name] as $file)
            {
                if (preg_match('/(https?:)?\/\//i', $file))
                {
                    $url = $file;
                } else
                    {
                       $url = static::$domain . $file;
                    }
                echo static::$prefix . '<script src="' . $url . '"></script>' . "\n";
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
                $p = static::$prefix . '<script>$(document).ready(function(){';
                foreach(static::$scripts['ready'] as $script)
                {
                    $p .= $script . "\n" . static::$prefix;
                }
                $p .= "});</script>\n";
                echo $p;
            }
        } else
            {
                if (!empty(static::$scripts[$name]))
                {
                    foreach(static::$scripts[$name] as $script)
                    {
                        echo static::$prefix . '<script>' . $script . "</script>\n";
                    }
                }
            }
    }


}
