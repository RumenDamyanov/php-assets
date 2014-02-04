<?php namespace Roumen\Asset;

/**
 * Asset class for laravel-assets package.
 *
 * @author Roumen Damianoff <roumen@dawebs.com>
 * @version 2.3.7
 * @link http://roumen.it/projects/laravel-assets
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class Asset
{

    protected static $css = array();
    protected static $less = array();
    protected static $styles = array();
    protected static $js = array();
    protected static $scripts = array();
    protected static $domain = '/';
    protected static $prefix = '';
    protected static $hash = null;

    /**
     * Check environment
     *
     * @return void
    */
    public static function checkEnv()
    {
        $env = \App::environment();

        if (($env == 'local' || $env == 'testing') && (self::$domain === '/'))
        {
            self::$domain = '/';
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
        self::$domain = $url;
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
        self::$prefix = $prefix;
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
        if (file_exists($cachebuster)) self::$hash = json_decode(file_get_contents($cachebuster));
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
            foreach ($a as $item) self::processAdd($item, $name);
        else
            self::processAdd($a, $name);
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
        $a = (self::$hash && property_exists(self::$hash, $a)) ? $a."?".self::$hash->{$a} : $a;

        if (preg_match("/\.css/i", $a))
        {
            // css
            self::$css[] = $a;
        }

        if (preg_match("/\.less/i", $a))
        {
            // less
            self::$less[] = $a;
        }

        elseif (preg_match("/\.js/i", $a))
        {
            // js
            self::$js[$name][] = $a;
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
        $a = (self::$hash && property_exists(self::$hash, $a)) ? $a."?".self::$hash->{$a} : $a;

        if (preg_match("/\.css/i", $a))
        {
            // css
            array_unshift(self::$css, $a);
        }

        if (preg_match("/\.less/i", $a))
        {
            // less
            array_unshift(self::$less, $a);
        }

        elseif (preg_match("/\.js/i", $a))
        {
            // js
            if (!empty(self::$js[$name]))
            {
                array_unshift(self::$js[$name], $a);
            } else
                {
                    self::$js[$name][] = $a;
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
        self::$scripts[$name][] = $s;
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
        self::$styles[$s][] = $style;
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
        self::checkEnv();

        if (!empty(self::$css))
        {
            foreach(self::$css as $file)
            {
                if (preg_match('/(https?:)?\/\//i', $file))
                {
                    $url = $file;
                } else
                    {
                        $url = self::$domain . $file;
                    }
                echo self::$prefix . $url . $separator;
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
        self::checkEnv();

        if (!empty(self::$css))
        {
            foreach(self::$css as $file)
            {
                if (preg_match('/(https?:)?\/\//i', $file))
                {
                    $url = $file;
                } else
                    {
                        $url = self::$domain . $file;
                    }
                echo self::$prefix . '<link rel="stylesheet" type="text/css" href="' . $url . '" />' . "\n";
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
        self::checkEnv();

        if (!empty(self::$less))
        {
            foreach(self::$less as $file)
            {
                if (preg_match('/(https?:)?\/\//i', $file))
                {
                    $url = $file;
                } else
                    {
                        $url = self::$domain . $file;
                    }
                echo self::$prefix . $url . $separator;
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
        self::checkEnv();

        if (!empty(self::$less))
        {
            foreach(self::$less as $file)
            {
                if (preg_match('/(https?:)?\/\//i', $file))
                {
                    $url = $file;
                } else
                    {
                        $url = self::$domain . $file;
                    }
                echo self::$prefix . '<link rel="stylesheet/less" type="text/css" href="' . $url . '" />' . "\n";
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
        if (($name !== '') && (!empty(self::$styles[$name])))
        {
            $p = "\n" . self::$prefix . "<style type=\"text/css\">\n" . self::$prefix;
            foreach(self::$styles[$name] as $style)
            {
                $p .= $style . "\n" . self::$prefix;
            }
            $p .= self::$prefix . "</style>\n";
            echo $p;
        }
        else if (!empty(self::$styles))
        {
            $p = self::$prefix . "<style type=\"text/css\">\n";
            foreach(self::$styles as $style)
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
        self::checkEnv();

        if (!empty(self::$js[$name]))
        {
            foreach(self::$js[$name] as $file)
            {
                if (preg_match('/(https?:)?\/\//i', $file))
                {
                    $url = $file;
                } else
                    {
                       $url = self::$domain . $file;
                    }
                echo self::$prefix . $url . $separator;
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
        self::checkEnv();

        if ($name === false) $name = 'footer';
        if (!empty(self::$js[$name]))
        {
            foreach(self::$js[$name] as $file)
            {
                if (preg_match('/(https?:)?\/\//i', $file))
                {
                    $url = $file;
                } else
                    {
                       $url = self::$domain . $file;
                    }
                echo self::$prefix . '<script src="' . $url . '"></script>' . "\n";
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
            if (!empty(self::$scripts['ready']))
            {
                $p = self::$prefix . '<script>$(document).ready(function(){';
                foreach(self::$scripts['ready'] as $script)
                {
                    $p .= $script . "\n" . self::$prefix;
                }
                $p .= "});</script>\n";
                echo $p;
            }
        } else
            {
                if (!empty(self::$scripts[$name]))
                {
                    foreach(self::$scripts[$name] as $script)
                    {
                        echo self::$prefix . '<script>' . $script . "</script>\n";
                    }
                }
            }
    }


}
