<?php namespace Roumen\Asset;
/**
 * Asset class for laravel4-assets package.
 *
 * @author Roumen Damianoff <roumen@dawebs.com>
 * @version 1.0.1
 * @link http://roumen.me/projects/laravel4-asset
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */

class Asset {

    public $css = array();

    public $js_header = array();
    public $js_footer = array();

    public $script_header = array();
    public $script_footer = array();
    public $script_ready = array();


/**
 * Add new asset
 *
 * @param string $a
 * @param string $position
 *
 * @return void
 */
    function add($a, $position = 'footer')
    {
        if (preg_match("/\.css/i", $a))
        {
            // css
            $this->css[] = $a;
        }

        elseif (preg_match("/\.js/i", $a))
        {
            // js
            if ($position == 'header')
                $this->js_header[] = $a;
            else
                $this->js_footer[] = $a;
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
    function add_script($s, $position = 'footer')
    {
        if ($position == 'footer')
            $this->scr_footer[] = $s;
        elseif ($position == 'header')
            $this->scr_header[] = $s;
        else
            $this->scr_ready[] = $s;
    }


/**
 * Loads all items from $css array
 */
    function css()
    {
        if (!empty($this->css))
        {
            foreach($this->css as $file)
            {
                echo '<link rel="stylesheet" type="text/css" href="' . $file . '" />' . "\n";
            }
        }
    }


/**
 * Loads all items from $js_header or $js_footer arrays
 *
 * @param string $p (options: 'footer', 'header')
 */
    function js($p = 'footer')
    {
        if ($p == 'header')
        {
            if (!empty($this->js_header))
            {
                foreach($this->js_header as $file)
                {
                    echo '<script type="text/javascript" src="' . $file . '"></script>' . "\n";
                }
            }
        } else {
            if (!empty($this->js_footer))
            {
                foreach($this->js_footer as $file)
                {
                    echo '<script type="text/javascript" src="' . $file . '"></script>' . "\n";
                }
            }
        }
    }


/**
 * Loads all items from $scripts_header, $scripts_footer or $scripts_ready arrays
 *
 * @param string $p (options: 'footer', 'header' or 'ready')
 */
    function scripts($p = 'footer')
    {
        if ($p == 'footer')
        {
            if (!empty($this->script_footer))
            {
                foreach($this->script_footer as $script)
                {
                    echo '<script type="text/javascript">' . $script . "</script>\n";
                }
            }
        } elseif ($p == 'header')
        {
            if (!empty($this->scr_header))
            {
                foreach($this->scr_header as $script)
                {
                    echo '<script type="text/javascript">' . $script . "</script>\n";
                }
            }
        } else {
            if (!empty($this->scr_ready))
            {
                $p = '<script type="text/javascript">$(document).ready(function(){';
                foreach($this->scr_ready as $script)
                {
                    $p .= $script;
                }
                $p .= "});</script>\n";
                echo $p;
            }
        }
    }

}