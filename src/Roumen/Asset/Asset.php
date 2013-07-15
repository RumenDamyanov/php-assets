<?php namespace Roumen\Asset;

/**
 * Asset class for laravel4-assets package.
 *
 * @author Roumen Damianoff <roumen@dawebs.com>
 * @version 1.4
 * @link http://roumen.me/projects/laravel4-asset
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class Asset
{

    // assets arrays
    protected static $css = array();
    protected static $styles = array();
    protected static $js_header = array();
    protected static $js_footer = array();
    protected static $script_header = array();
    protected static $script_footer = array();
    protected static $script_ready = array();
    protected static $assets_domain = '/'; // e.g. http://cdn.domain.tld/


    /**
     * Check environment
     *
     * @return void
     */
    public static function checkEnv()
    {
        $env = \App::environment();

        if ($env == 'local' || $env == 'testing')
        {
            self::$assets_domain = '/';
        }
    }

    /**
     * Set domain
     *
     * @return void
     */
    public static function setDomain($url)
    {
        self::$assets_domain = $url;
    }


    /**
     * Add new asset
     *
     * @param string $a
     * @param string $position
     *
     * @return void
     */
    public static function add($a, $position = 'footer')
    {
        if (preg_match("/\.css/i", $a))
        {
            // css
            self::$css[] = $a;
        }

        elseif (preg_match("/\.js/i", $a))
        {
            // js
            if ($position == 'header')
                self::$js_header[] = $a;
            else
                self::$js_footer[] = $a;
        }
    }


    /**
     * Add new asset as first in its array
     *
     * @param string $a
     * @param string $position
     *
     * @return void
     */
    public static function addFirst($a, $position = 'footer')
    {
        if (preg_match("/\.css/i", $a))
        {
            // css
            array_unshift(self::$css, $a);
        }

        elseif (preg_match("/\.js/i", $a))
        {
            // js
            if ($position == 'header')
                array_unshift(self::$js_header, $a);
            else
                array_unshift(self::$js_footer, $a);
        }
    }


    /**
     * Add new script
     *
     * @param string $s
     * @param string $position
     *
     * @return void
     */
    public static function addScript($s, $position = 'footer')
    {
        if ($position == 'footer')
            self::$script_footer[] = $s;
        elseif ($position == 'header')
            self::$script_header[] = $s;
        else
            self::$script_ready[] = $s;
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
                        $url = self::$assets_domain . $file;
                    }
                echo '<link rel="stylesheet" type="text/css" href="' . $url . '" />' . "\n";
            }
        }
    }


    /**
     * Loads all items from $styles array
     *
     * @param string $s
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
     * Loads all items from $js_header or $js_footer arrays
     *
     * @param string $p (options: 'footer', 'header')
     */
    public static function js($p = 'footer')
    {
        self::checkEnv();

        if ($p == 'header')
        {
            if (!empty(self::$js_header))
            {
                foreach(self::$js_header as $file)
                {
                    if (stripos($file, "http://") === 0)
                    {
                        $url = $file;
                    } else
                        {
                           $url = self::$assets_domain . $file;
                        }
                    echo '<script src="' . $url . '"></script>' . "\n";
                }
            }
        } else {
            if (!empty(self::$js_footer))
            {
                foreach(self::$js_footer as $file)
                {
                    if (stripos($file, "http://") === 0)
                    {
                        $url = $file;
                    } else
                        {
                           $url = self::$assets_domain . $file;
                        }
                    echo '<script src="' . $url . '"></script>' . "\n";
                }
            }
        }
    }


    /**
     * Loads all items from $scripts_header, $scripts_footer or $scripts_ready arrays
     *
     * @param string $p (options: 'footer', 'header' or 'ready')
     */
    public static function scripts($p = 'footer')
    {
        if ($p == 'footer')
        {
            if (!empty(self::$script_footer))
            {
                foreach(self::$script_footer as $script)
                {
                    echo '<script>' . $script . "</script>\n";
                }
            }
        } elseif ($p == 'header')
        {
            if (!empty(self::$script_header))
            {
                foreach(self::$script_header as $script)
                {
                    echo '<script>' . $script . "</script>\n";
                }
            }
        } else {
            if (!empty(self::$script_ready))
            {
                $p = '<script>$(document).ready(function(){';
                foreach(self::$script_ready as $script)
                {
                    $p .= $script . "\n";
                }
                $p .= "});</script>\n";
                echo $p;
            }
        }
    }


}