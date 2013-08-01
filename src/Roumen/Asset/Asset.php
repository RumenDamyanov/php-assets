<?php namespace Roumen\Asset;

/**
 * Asset class for laravel4-assets package.
 *
 * @author Roumen Damianoff <roumen@dawebs.com>
 * @version 1.7
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
    public static function addStyle($s)
    {
        self::$style[] = $s;
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
                if (stripos($file, "http://") === 0)
                {
                    $url = $file;
                } else
                    {
                        $url = self::$domain . $file;
                    }
                echo '<link rel="stylesheet" type="text/css" href="' . $url . '" />' . "\n";
            }
        }
    }


    /**
     * Loads all items from $styles array
     *
     * @param string $s
     *
     * @return void
    */
    public static function styles($s)
    {
        if (!empty(self::$styles))
        {
            $p = '<style type="text/css">';
            foreach(self::$styles as $style)
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
                if (stripos($file, "http://") === 0)
                {
                    $url = $file;
                } else
                    {
                       $url = self::$domain . $file;
                    }
                echo '<script src="' . $url . '"></script>' . "\n";
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
                $p = '<script>$(document).ready(function(){';
                foreach(self::$scripts['ready'] as $script)
                {
                    $p .= $script . "\n";
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
                        echo '<script>' . $script . "</script>\n";
                    }
                }
            }
    }


}
