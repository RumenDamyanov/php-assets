<?php namespace Roumen\Asset;

/**
 * Asset class for laravel4-assets package.
 *
 * @author Roumen Damianoff <roumen@dawebs.com>
 * @version 2.0
 * @link http://roumen.me/projects/laravel4-assets
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class Asset
{

    protected static $css = array();
    protected static $styles = array();
    protected static $js = array();
    protected static $scripts = array();
    protected static $domain = '/';
	protected static $prefix = '';

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
     * @return void
    */
    public static function setDomain($url)
    {
        self::$domain = $url;
    }

    /**
     * Set prefix
     *
     * @return void
    */
    public static function setPrefix($prefix)
    {
        self::$prefix = $prefix;
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
        if (preg_match("/\.css/i", $a))
        {
            // css
            self::$css[] = $a;
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
        if (preg_match("/\.css/i", $a))
        {
            // css
            array_unshift(self::$css, $a);
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
     * @param string $s
     *
     * @return void
    */
    public static function addStyle($style, $s = 'header')
    {
        self::$styles[$s] = $style;
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
     * Loads all items from $styles array
     *
     * @param string $name
     *
     * @return void
    */
    public static function styles($name = 'header')
    {
        if (($name !== '') && (!empty(self::$styles)))
        {
            $p = "\n" . self::$prefix . "<style type=\"text/css\">\n" . self::$prefix;
            foreach(self::$styles as $style)
            {
                $p .= $style . "\n" . self::$prefix;
            }
            $p .= self::$prefix . "</style>\n";
            echo $p;            
        }
        else if (!empty(self::$styles[$name]))
        {
            $p = self::$prefix . "<style type=\"text/css\">\n";
            foreach(self::$styles[$name] as $style)
            {
                $p .= $style . "\n";
            }
            $p .= "</style>\n";
            echo $p;
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
