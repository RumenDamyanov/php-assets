<?php
declare(strict_types=1);
// Framework-agnostic Asset manager core
namespace Rumenx\Assets;

/**
 * Class Asset
 * Framework-agnostic core for managing frontend assets (CSS, LESS, JS) in PHP.
 *
 * Features:
 * - Add, order, and output CSS, LESS, and JS assets
 * - Cache busting (file or function based)
 * - Environment and domain support
 * - Adapter hooks for Laravel/Symfony
 *
 * Usage:
 *   Asset::add('style.css');
 *   Asset::add('script.js');
 *   echo Asset::css();
 *   echo Asset::js();
 */
final class Asset
{
    // Asset type constants
    const ADD_TO_NONE   = 0; // Not added
    const ADD_TO_CSS    = 1; // CSS file
    const ADD_TO_LESS   = 2; // LESS file
    const ADD_TO_JS     = 3; // JS file

    // Unknown extension handling
    const ON_UNKNOWN_EXTENSION_NONE  = 0;
    const ON_UNKNOWN_EXTENSION_CSS   = 1;
    const ON_UNKNOWN_EXTENSION_LESS  = 2;
    const ON_UNKNOWN_EXTENSION_JS    = 3;

    /**
     * Map for unknown extension handling
     * @var array<int, int>
     */
    private static $ON_UNKNOWN_EXTENSION_TO_ADD_TO = [
        self::ON_UNKNOWN_EXTENSION_NONE => self::ADD_TO_CSS,
        self::ON_UNKNOWN_EXTENSION_LESS => self::ADD_TO_LESS,
        self::ON_UNKNOWN_EXTENSION_JS   => self::ADD_TO_JS
    ];

    /** @var array<string, string> CSS assets */
    public static array $css = [];
    /** @var array<string, string> LESS assets */
    public static array $less = [];
    /** @var array<string, string[]> Inline styles by section */
    public static array $styles = [];
    /** @var array<string, string[]> JS assets by section */
    public static array $js = [];
    /** @var array<string, array<string, array<string, bool|int|string|null>>> JS params by section and asset */
    public static array $jsParams = [];
    /** @var array<string, string[]> Inline scripts by section */
    public static array $scripts = [];
    /** @var string Domain for asset URLs */
    public static $domain = '/';
    /** @var string Prefix for output */
    public static $prefix = '';
    /** @var array<string, string> Cachebuster hashes */
    public static $hash = [];
    /** @var string|null Current environment */
    public static $environment = null;
    /** @var bool Use HTTPS URLs */
    public static $secure = false;
    /** @var bool Enable cache for wildcard versioning */
    public static $cacheEnabled = true;
    /** @var int Cache duration in minutes */
    public static $cacheDuration = 360; // 6 hours
    /** @var string Cache key prefix */
    public static $cacheKey = 'php-assets';
    /** @var callable|null Custom cachebuster function */
    protected static $cacheBusterGeneratorFunction = null;
    /** @var bool Use jQuery shorthand for ready scripts */
    private static $useShortHandReady = false;

    // Adapter hooks (set by framework integration)
    /** @var callable|null Environment resolver */
    public static $envResolver = null; // fn(): string
    /** @var callable|null URL generator */
    public static $urlGenerator = null; // fn(string $file, bool $secure): string
    /** @var object|null Cache object with has/get/put */
    public static $cache = null;

    /**
     * Detect and set the current environment.
     * If an envResolver is set, use it. Otherwise, default to 'production'.
     * If environment is 'local', force domain to '/'.
     */
    public static function checkEnv(): void
    {
        if (static::$environment === null) {
            if (is_callable(static::$envResolver)) {
                $env = call_user_func(static::$envResolver);
                static::$environment = is_string($env) ? $env : 'production';
            } else {
                static::$environment = 'production';
            }
        }
        if (static::$environment === 'local' && static::$domain !== '/') {
            static::$domain = '/';
        }
    }

    /**
     * Set the domain for asset URLs.
     */
    public static function setDomain(string $url): void
    {
        static::$domain = $url;
    }

    /**
     * Set the prefix for asset output.
     */
    public static function setPrefix(string $prefix): void
    {
        static::$prefix = $prefix;
    }

    /**
     * Load cachebuster hashes from a JSON file.
     * If the file is invalid, fallback to an empty array.
     */
    public static function setCachebuster(string $cachebuster): void
    {
        if (file_exists($cachebuster)) {
            $json = static::getFileContents($cachebuster);
            if (!is_string($json)) {
                static::$hash = [];
                return;
            }
            $hash = json_decode($json, true);
            if (is_array($hash)) {
                $filtered = [];
                foreach ($hash as $k => $v) {
                    if (is_string($k) && is_string($v)) {
                        $filtered[$k] = $v;
                    }
                }
                static::$hash = $filtered;
            } else {
                static::$hash = [];
            }
        }
    }

    /**
     * Set a custom cachebuster generator function.
     */
    public static function setCacheBusterGeneratorFunction(?callable $fn): void
    {
        static::$cacheBusterGeneratorFunction = $fn;
    }

    /**
     * Generate a cachebuster string for a file.
     * @param string $a
     * @return string
     */
    private static function generateCacheBusterFilename($a)
    {
        $hash = '';
        if (!is_callable(static::$cacheBusterGeneratorFunction)) {
            // Remove always-true is_array() check
            if (array_key_exists($a, static::$hash)) {
                $hash .= static::$hash[$a];
            }
        } else {
            $hash = call_user_func(static::$cacheBusterGeneratorFunction, $a);
        }
        if (is_string($hash) && $hash !== '') {
            $a .= '?' . $hash;
        }
        return $a;
    }

    /**
     * Use jQuery shorthand for ready scripts.
     */
    public static function setUseShortHandReady(bool $useShortHandReady): void
    {
        static::$useShortHandReady = $useShortHandReady;
    }

    /**
     * Internal: Add a single CSS or LESS asset.
     * @param string $a
     * @param string $section
     */
    protected static function processAddCssOrLess(string $a, string $section, int $addTo): void
    {
        if ($addTo === self::ADD_TO_CSS) {
            static::$css[$a] = $a;
        } elseif ($addTo === self::ADD_TO_LESS) {
            static::$less[$a] = $a;
        }
    }

    /**
     * Remove a JS asset from all sections and clean up any empty sections, except the target section.
     * @param string $a
     * @param string $targetSection
     */
    private static function removeJsAssetFromAllSections(string $a, string $targetSection): void
    {
        foreach (array_keys(static::$js) as $section) {
            if (isset(static::$js[$section][$a])) {
                unset(static::$js[$section][$a]);
            }
            // Only unset if section is empty and not the target section
            if ($section !== $targetSection && empty(static::$js[$section])) {
                unset(static::$js[$section]);
            }
        }
    }

    /**
     * Internal: Add a single JS asset.
     * @param string $a
     * @param array<string, bool|int|string|null> $params
     */
    protected static function processAddJs(string $a, array $params): void
    {
        $name = isset($params['name']) ? (string)$params['name'] : 'footer';
        static::removeJsAssetFromAllSections((string)$a, $name);
        if ($a !== '') {
            if (!isset(static::$js[$name])) {
                static::$js[$name] = [];
            }
            static::$js[$name][(string)$a] = $a;
            if (!isset(static::$jsParams[$name])) static::$jsParams[$name] = [];
            static::$jsParams[$name][(string)$a] = static::$jsParams[$name][(string)$a] ?? [];
            foreach ($params as $k => $v) {
                if ($k === 'name') continue;
                static::$jsParams[$name][(string)$a][$k] = $v;
            }
        }
        // Remove the section if it is empty after the add
        if (isset(static::$js[$name]) && empty(static::$js[$name])) {
            unset(static::$js[$name]);
        }
    }

    /**
     * Add one or more assets.
     * @param string|array<int, string> $a
     * @param string|array<string, bool|int|string|null> $params
     * @param int $onUnknownExtension
     */
    public static function add(string|array $a, string|array $params = 'footer', int $onUnknownExtension = self::ON_UNKNOWN_EXTENSION_NONE): void
    {
        if (is_array($a)) {
            foreach ($a as $item) {
                // Remove always-true is_string() check
                $addTo = static::getAddTo($item, $onUnknownExtension);
                if ($addTo === self::ADD_TO_JS) {
                    static::processAddJs($item, is_array($params) ? $params : []);
                } elseif ($addTo === self::ADD_TO_CSS || $addTo === self::ADD_TO_LESS) {
                    static::processAddCssOrLess($item, is_string($params) ? $params : 'footer', $addTo);
                }
            }
        } else {
            $addTo = static::getAddTo($a, $onUnknownExtension);
            if ($addTo === self::ADD_TO_JS) {
                static::processAddJs($a, is_array($params) ? $params : []);
            } elseif ($addTo === self::ADD_TO_CSS || $addTo === self::ADD_TO_LESS) {
                static::processAddCssOrLess($a, is_string($params) ? $params : 'footer', $addTo);
            }
        }
    }

    /**
     * Determine which asset type to add to based on extension.
     * @param string $a
     * @param int $onUnknownExtension
     * @return int
     */
    private static function getAddTo($a, int $onUnknownExtension = self::ON_UNKNOWN_EXTENSION_NONE)
    {
        if (preg_match("/(\.css|\/css\?)/i", $a)) {
            return self::ADD_TO_CSS;
        }
        if (preg_match("/\.less/i", $a)) {
            return self::ADD_TO_LESS;
        }
        if (preg_match("/\.js|\/js/i", $a)) {
            return self::ADD_TO_JS;
        }
        // Use the default if not explicitly passed
        if ($onUnknownExtension === self::ON_UNKNOWN_EXTENSION_NONE) {
            $onUnknownExtension = static::$onUnknownExtensionDefault;
        }
        if (
            $onUnknownExtension !== self::ON_UNKNOWN_EXTENSION_NONE &&
            isset(static::$ON_UNKNOWN_EXTENSION_TO_ADD_TO[$onUnknownExtension])
        ) {
            return static::$ON_UNKNOWN_EXTENSION_TO_ADD_TO[$onUnknownExtension];
        }
        return self::ADD_TO_NONE;
    }

    /**
     * Remove an empty JS section if it exists.
     * @param string $name
     */
    private static function cleanupEmptyJsSection(string $name): void
    {
        if (isset(static::$js[$name]) && empty(static::$js[$name])) {
            unset(static::$js[$name]);
        }
    }

    /**
     * Internal: Add a JS asset before another.
     * @param string $a
     * @param string $b
     * @param array<string, bool|int|string|null> $params
     */
    protected static function addBeforeJs(string $a, string $b, array $params): void
    {
        if ($a === '') {
            return;
        }
        $name = isset($params['name']) ? (string)$params['name'] : 'footer';
        static::removeJsAssetFromAllSections((string)$a, $name);
        if (!isset(static::$js[$name])) {
            static::$js[$name] = [];
        }
        $new = [];
        $inserted = false;
        foreach (static::$js[$name] as $key => $val) {
            $key = (string)$key;
            if (!$inserted && $key === (string)$b) {
                $new[(string)$a] = $a;
                $inserted = true;
            }
            $new[$key] = $val;
        }
        if (!$inserted) {
            $new[(string)$a] = $a;
        }
        // Ensure all keys are strings
        static::$js[$name] = [];
        foreach ($new as $k => $v) {
            static::$js[$name][(string)$k] = $v;
        }
        // Always set params for $a if provided
        if (!isset(static::$jsParams[$name])) static::$jsParams[$name] = [];
        static::$jsParams[$name][(string)$a] = static::$jsParams[$name][(string)$a] ?? [];
        foreach ($params as $k => $v) {
            if ($k === 'name') continue;
            static::$jsParams[$name][(string)$a][$k] = $v;
        }
        static::cleanupEmptyJsSection($name);
    }

    /**
     * Internal: Add a CSS or LESS asset before another.
     * @param string $a
     * @param string $b
     * @param string $section
     * @param int $addTo
     */
    protected static function addBeforeCssOrLess(string $a, string $b, string $section, int $addTo): void
    {
        if ($addTo === self::ADD_TO_CSS) {
            $array = &static::$css;
        } elseif ($addTo === self::ADD_TO_LESS) {
            $array = &static::$less;
        } else {
            return;
        }
        $new = [];
        $inserted = false;
        foreach ($array as $key => $val) {
            if (!$inserted && $key === $b) {
                $new[$a] = $a;
                $inserted = true;
            }
            $new[$key] = $val;
        }
        if (!$inserted) {
            $new[$a] = $a;
        }
        $array = $new;
    }

    /**
     * Internal: Add a single asset to the correct collection.
     * For JS assets, $params must be array<string, bool|int|string|null>.
     * For CSS/LESS, $params is a string (section name).
     * @param string $a
     * @param string|array<string, bool|int|string|null> $params
     * @param int $onUnknownExtension
     */
    protected static function processAdd(string $a, string|array $params, int $onUnknownExtension = self::ON_UNKNOWN_EXTENSION_NONE): void
    {
        $addTo = static::getAddTo($a, $onUnknownExtension);
        if ($addTo === self::ADD_TO_JS) {
            static::processAddJs($a, is_array($params) ? $params : []);
        } elseif ($addTo === self::ADD_TO_CSS || $addTo === self::ADD_TO_LESS) {
            static::processAddCssOrLess($a, is_string($params) ? $params : 'footer', $addTo);
        }
    }

    /**
     * Add an asset before an existing asset.
     * For JS assets, $params must be array<string, bool|int|string|null>.
     * For CSS/LESS, $params is a string (section name).
     * @param string $a
     * @param string $b
     * @param string|array<string, bool|int|string|null> $params
     * @param int $onUnknownExtension
     */
    public static function addBefore(string $a, string $b, string|array $params = 'footer', int $onUnknownExtension = self::ON_UNKNOWN_EXTENSION_NONE): void
    {
        static::checkVersion($a);
        $addTo = static::getAddTo($a, $onUnknownExtension);
        if ($addTo === self::ADD_TO_JS) {
            static::addBeforeJs($a, $b, is_array($params) ? $params : []);
        } else {
            static::addBeforeCssOrLess($a, $b, is_string($params) ? $params : 'footer', $addTo);
        }
    }

    /**
     * Add an asset after an existing asset.
     * For JS assets, $params must be array<string, bool|int|string|null>.
     * For CSS/LESS, $params is a string (section name).
     * @param string $a
     * @param string $b
     * @param string|array<string, bool|int|string|null> $params
     * @param int $onUnknownExtension
     */
    public static function addAfter(string $a, string $b, string|array $params = 'footer', int $onUnknownExtension = self::ON_UNKNOWN_EXTENSION_NONE): void
    {
        static::checkVersion($a);
        $addTo = static::getAddTo($a, $onUnknownExtension);
        if ($addTo === self::ADD_TO_JS) {
            static::addAfterJs($a, $b, is_array($params) ? $params : []);
        } else {
            static::addAfterCssOrLess($a, $b, is_string($params) ? $params : 'footer', $addTo);
        }
    }

    /**
     * Internal: Add a JS asset after another.
     * @param string $a
     * @param string $b
     * @param array<string, bool|int|string|null> $params
     */
    protected static function addAfterJs(string $a, string $b, array $params): void
    {
        if ($a === '') {
            return;
        }
        $name = isset($params['name']) ? (string)$params['name'] : 'footer';
        static::removeJsAssetFromAllSections((string)$a, $name);
        if (!isset(static::$js[$name])) {
            static::$js[$name] = [];
        }
        $keys = array_keys(static::$js[$name]);
        $bpos = array_search((string)$b, $keys, true);
        if ($bpos !== false) {
            $aarr = array_slice(static::$js[$name], 0, $bpos+1, true);
            $barr = array_slice(static::$js[$name], $bpos+1, null, true);
            $aarr[(string)$a] = $a;
            // Ensure all keys are strings
            $merged = [];
            foreach ($aarr as $k => $v) $merged[(string)$k] = $v;
            foreach ($barr as $k => $v) $merged[(string)$k] = $v;
            static::$js[$name] = $merged;
        } else {
            static::$js[$name][(string)$a] = $a;
        }
        // Always set params for $a if provided
        if (!isset(static::$jsParams[$name])) static::$jsParams[$name] = [];
        static::$jsParams[$name][(string)$a] = static::$jsParams[$name][(string)$a] ?? [];
        foreach ($params as $k => $v) {
            if ($k === 'name') continue;
            static::$jsParams[$name][(string)$a][$k] = $v;
        }
        static::cleanupEmptyJsSection($name);
    }

    /**
     * Internal: Add a CSS or LESS asset after another.
     * @param string $a
     * @param string $b
     * @param string $section
     * @param int $addTo
     */
    protected static function addAfterCssOrLess(string $a, string $b, string $section, int $addTo): void
    {
        if ($addTo === self::ADD_TO_CSS) {
            $array = &static::$css;
        } elseif ($addTo === self::ADD_TO_LESS) {
            $array = &static::$less;
        } else {
            return;
        }
        $keys = array_keys($array);
        $bpos = array_search($b, $keys, true);
        if ($bpos !== false) {
            $aarr = array_slice($array, 0, $bpos+1, true);
            $barr = array_slice($array, $bpos+1, null, true);
            $aarr[$a] = $a;
            $array = $aarr + $barr;
        } else {
            $array[$a] = $a;
        }
    }

    /**
     * Add a script to be output.
     * @param string $s Script content
     * @param string $name Section name
     */
    public static function addScript(string $s, string $name = 'footer'): void
    {
        static::$scripts[$name][] = $s;
    }

    /**
     * Add a style to be output.
     * @param string $style Style content
     * @param string $s Section name
     */
    public static function addStyle(string $style, string $s = 'header'): void
    {
        static::$styles[$s][] = $style;
    }

    /**
     * Generate the URL for an asset file.
     * @param string $file
     * @return string
     */
    protected static function url(string $file): string
    {
        if (preg_match('/(https?:)?\/\//i', $file)) {
            return $file;
        }
        $file = static::generateCacheBusterFilename($file);
        if (is_callable(static::$urlGenerator)) {
            $url = call_user_func(static::$urlGenerator, $file, static::$secure);
            return is_string($url) ? $url : '';
        }
        return rtrim(static::$domain, '/') . '/' . ltrim($file, '/');
    }

    /**
     * Output CSS links as <link> tags.
     */
    public static function css(): void
    {
        static::checkEnv();
        if (!empty(static::$css)) {
            foreach(static::$css as $file) {
                echo static::$prefix, '<link rel="stylesheet" type="text/css" href="', static::url($file), '">\n';
            }
        }
    }

    /**
     * Output CSS links.
     * @param string $separator Separator string
     */
    public static function cssRaw(string $separator = ""): void
    {
        static::checkEnv();
        if (!empty(static::$css)) {
            foreach(static::$css as $file) {
                echo static::$prefix, static::url($file), $separator;
            }
        }
    }

    /**
     * Output LESS links.
     * @param string $separator Separator string
     */
    public static function lessRaw(string $separator = ""): void
    {
        static::checkEnv();
        if (!empty(static::$less)) {
            foreach(static::$less as $file) {
                echo static::$prefix, static::url($file), $separator;
            }
        }
    }

    /**
     * Output LESS links as <link> tags.
     */
    public static function less(): void
    {
        static::checkEnv();
        if (!empty(static::$less)) {
            foreach(static::$less as $file) {
                echo static::$prefix, '<link rel="stylesheet/less" type="text/css" href="', static::url($file), '">\n';
            }
        }
    }

    /**
     * Output inline styles as <style> tags.
     * @param string $name Section name
     */
    public static function styles(string $name = 'header'): void
    {
        if (($name !== '') && (!empty(static::$styles[$name]))) {
            echo "\n", static::$prefix, "<style type=\"text/css\">\n", static::$prefix;
            foreach(static::$styles[$name] as $style) {
                if (is_string($style)) {
                    echo "$style\n", static::$prefix;
                }
            }
            echo static::$prefix, "</style>\n";
        } elseif (!empty(static::$styles)) {
            echo static::$prefix, "<style type=\"text/css\">\n";
            foreach(static::$styles as $section) {
                foreach ($section as $style) {
                    if (is_string($style) && $style !== '') {
                        echo static::$prefix, '<style>', $style, '</style>\n';
                    }
                }
            }
            echo "</style>\n";
        }
    }

    /**
     * Output JS scripts.
     * @param string $separator Separator string
     * @param string $name Section name
     */
    public static function jsRaw(string $separator = "", string $name = 'footer'): void
    {
        static::checkEnv();
        if (!empty(static::$js[$name])) {
            foreach(static::$js[$name] as $file) {
                echo static::$prefix, static::url($file), $separator;
            }
        }
    }

    /**
     * Output JS scripts as <script> tags.
     * @param string $name Section name
     */
    public static function js(string $name = 'footer'): void
    {
        static::checkEnv();
        if (!empty(static::$js[$name])) {
            foreach(static::$js[$name] as $file) {
                $params = static::$jsParams[$name][$file] ?? [];
                $type = $params['type'] ?? '';
                $defer = $params['defer'] ?? '';
                $async = $params['async'] ?? '';
                $e  = static::$prefix;
                $e .= '<script src="'.static::url($file).'"';
                if ($type !== '') $e .= ' type="'.$type.'"';
                if ($defer !== '') $e .= ' defer="'.$defer.'"';
                if ($async !== '') $e .= ' async="'.$async.'"';
                $e .= '></script>'."\n";
                echo $e;
            }
        }
    }

    /**
     * Output inline scripts as <script> tags.
     * @param string $name Section name
     */
    public static function scripts(string $name = 'footer'): void
    {
        if ($name == 'ready') {
            if (!empty(static::$scripts['ready'])) {
                echo static::$prefix, '<script>', (static::$useShortHandReady ? '$(' : '$(document).ready('), "function(){\n";
                foreach(static::$scripts['ready'] as $script) {
                    echo "$script\n", static::$prefix;
                }
                echo "});</script>\n";
            }
        } else {
            if (!empty(static::$scripts[$name])) {
                foreach(static::$scripts[$name] as $script) {
                    echo static::$prefix, "<script>\n$script\n</script>\n";
                }
            }
        }
    }

    /**
     * Check and resolve version placeholders in asset paths.
     * Replaces '*' with the latest version found in the directory.
     * @param string &$a Asset path (passed by reference)
     */
    public static function checkVersion(string &$a): void
    {
        // check for '*' character
        if (preg_match("/\*/i", $a)) {
            $a_org = $a;
            $cache = static::$cache;
            if (static::$cacheEnabled && $cache && method_exists($cache, 'has') && $cache->has(static::$cacheKey.$a)) {
                if (method_exists($cache, 'get')) {
                    $cached = $cache->get(static::$cacheKey.$a);
                    if (is_string($cached)) {
                        $a = $cached;
                        return;
                    }
                }
            } else {
                // Find the directory and pattern
                $dir = dirname($a);
                $pattern = basename($a);
                $files = @scandir($dir);
                if (is_array($files)) {
                    $matches = array_filter($files, function ($file) use ($pattern) {
                        return fnmatch($pattern, $file);
                    });
                    if (!empty($matches)) {
                        // Use the highest version (last in sorted order)
                        natsort($matches);
                        $last = end($matches);
                        $a = $dir . '/' . $last;
                    }
                }
            }
            if (static::$cacheEnabled && $cache && method_exists($cache, 'put')) {
                $cache->put(static::$cacheKey.$a_org, $a, static::$cacheDuration);
            }
        }
    }

    /**
     * Set the default behavior for unknown asset extensions.
     * @param int $onUnknownExtension
     */
    public static function setOnUnknownExtensionDefault(int $onUnknownExtension): void
    {
        // Only allow valid constants; fallback to NONE if invalid
        if (!in_array($onUnknownExtension, [
            self::ON_UNKNOWN_EXTENSION_NONE,
            self::ON_UNKNOWN_EXTENSION_LESS,
            self::ON_UNKNOWN_EXTENSION_JS
        ], true)) {
            $onUnknownExtension = self::ON_UNKNOWN_EXTENSION_NONE;
        }
        static::$onUnknownExtensionDefault = $onUnknownExtension;
    }

    /**
     * Get the current default for unknown asset extensions.
     * @return int
     */
    public static function getOnUnknownExtensionDefault(): int
    {
        return static::$onUnknownExtensionDefault;
    }

    /**
     * @var int Default behavior for unknown asset extensions
     */
    private static $onUnknownExtensionDefault = self::ON_UNKNOWN_EXTENSION_NONE;

    /**
     * Internal: file_get_contents wrapper for testability.
     * @internal
     */
    private static function getFileContents(string $file): string|false
    {
        if (isset(static::$fileContentsCallback) && is_callable(static::$fileContentsCallback)) {
            return call_user_func(static::$fileContentsCallback, $file);
        }
        return file_get_contents($file);
    }

    /**
     * @internal For testing: override file_get_contents
     * @var null|callable(string):string|false
     */
    public static $fileContentsCallback = null;
}
