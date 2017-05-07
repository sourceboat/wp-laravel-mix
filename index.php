<?php
/**
 * Plugin Name: WP Laravel Mix
 * Plugin URI: https://github.com/sourceboat/wp-laravel-mix/
 * Description: Get versioned Laravel Mix file paths in WordPress.
 * Version: 1.0.0
 * Author: Sourceboat
 * Author URI: https://sourceboat.com/
 * License: MIT License
 */

if (! function_exists('starts_with')) {
    /**
     * Determine if a given string starts with a given substring.
     *
     * @param  string  $haystack
     * @param  string|array  $needles
     * @return bool
     */
    function starts_with($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if ($needle != '' && substr($haystack, 0, strlen($needle)) === (string) $needle) {
                return true;
            }
        }
        return false;
    }
}

if (! function_exists('mix')) {
    /**
     * Get the path to a versioned Mix file.
     *
     * @param  string  $path
     * @param  string  $manifestDirectory
     * @return \Illuminate\Support\HtmlString
     *
     * @throws \Exception
     */
    function mix($path, $manifestDirectory = '')
    {
        static $manifest;

        if (! starts_with($path, '/')) {
            $path = "/{$path}";
        }

        if ($manifestDirectory && ! starts_with($manifestDirectory, '/')) {
            $manifestDirectory = "/{$manifestDirectory}";
        }

        $rootDir = dirname(dirname(ABSPATH));

        if (file_exists($rootDir . '/' . $manifestDirectory.'/hot')) {
            return "http://localhost:8080" . $path;
        }

        if (! $manifest) {
            $manifestPath =  $rootDir . $manifestDirectory . 'mix-manifest.json';

            if (! file_exists($manifestPath)) {
                throw new Exception('The Mix manifest does not exist.');
            }

            $manifest = json_decode(file_get_contents($manifestPath), true);
        }

        if (! array_key_exists($path, $manifest)) {
            throw new Exception(
                "Unable to locate Mix file: {$path}. Please check your ".
                'webpack.mix.js output paths and try again.'
            );
        }

        $path = $manifestDirectory . $manifest[$path];
        $path = str_replace('/web', '', $path);
        $path = str_replace('//', '/', $path);
        return WP_HOME . $path;
    }
}
