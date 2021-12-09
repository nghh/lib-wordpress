<?php
namespace Nghh\Lib\Wordpress\func;

function dotreader($string, $array, $env = false)
{
    if (!$string || !$array) return null;

    $parts = explode('.', $string);

    foreach ($parts as $part) {
        if ($env && isset($array[$env][$part])) {
            $array = $array[$env][$part];
        } elseif (isset($array[$part])) {
            $array = $array[$part];
        } else {
            return null;
        }
    }

    return $array;
}

function camelcase($str, array $noStrip = [])
{
    // non-alpha and non-numeric characters become spaces
    $str = preg_replace('/[^a-z0-9' . implode("", $noStrip) . ']+/i', ' ', $str);
    $str = trim($str);
    $str = str_replace(['-', '_', '–'], " ", $str);
    // uppercase the first character of each word
    $str = ucwords($str);
    $str = str_replace(" ", "", $str);
    $str = lcfirst($str);

    return $str;
}


function mix(string $key, $path = 'assets', $manifest_filename = 'mix-manifest.json')
{
    static $manifest_file;

    if (!$manifest_file) {
        // File
        $file = path($path, $manifest_filename);

        // Check if exists
        if (!file_exists($file)) {
            return null;
        }
        // Read Manifest
        $manifest_file = json_decode(file_get_contents($file), true);
    }

    if (isset($manifest_file[$key])) {
        $key =  $manifest_file[$key];
    }

    return $path . $key;
}


function path(string $path = '', string $file = null)
{
    // Check if Root Path is defined
    if (!defined('NG_ROOT_PATH')) {
        define('NG_ROOT_PATH', '');
    }

    // Construct Absolute Path
    if ($path) {
        $path = str_replace('.', DIRECTORY_SEPARATOR, $path);
        $path = NG_ROOT_PATH . DIRECTORY_SEPARATOR . $path;
    } else {
        $path = NG_ROOT_PATH;
    }

    // Add File
    if ($file) {
        $path = $path . DIRECTORY_SEPARATOR . $file;
    }

    return $path;
}