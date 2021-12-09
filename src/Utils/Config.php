<?php

namespace Nghh\Lib\Wordpress\Utils;

use function Nghh\Lib\Wordpress\func\dotreader;

class Config
{

    private static $_instance;

    private $_env;

    private $_data;

    /**
     * Returns config file
     *
     * @param string $config
     * @return string|array|null
     */
    public function get(string $config)
    {
        return dotreader($config, $this->_data, $this->_env);
    }

    /**
     * Undocumented function
     *
     * @param [type] $pathToConfigFiles
     * @param boolean $env
     * @return void
     */
    public function init($pathToConfigFiles, $env = false)
    {
        // Set Environment
        $this->_env = $env;

        // Scan Config Dir
        $this->_scanConfigDir($pathToConfigFiles);

        return $this;
    }

    private function _scanConfigDir($pathToConfigFiles)
    {
        $dir  = new \RecursiveDirectoryIterator($pathToConfigFiles, \RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new \RecursiveIteratorIterator($dir, \RecursiveIteratorIterator::SELF_FIRST);

        foreach ($files as $file) {

            if ($file->isFile()) {
                $path = str_replace($pathToConfigFiles, '', $file->getPathname());
                $path = array_filter(explode(DIRECTORY_SEPARATOR, $path));
                if (count($path) > 1) {
                    $filename = array_pop($path);
                } else {
                    $path = [];
                }

                $this->_addToData($file, $path);
            }
        }
    }

    private function _addToData($file, array $path)
    {
        $pathinfo = pathinfo($file->getPathname());
        $filedata = include($file->getPathname());

        if (empty($path)) {
            $this->_data[$pathinfo['filename']] = $filedata;
            return;
        }

        $data = &$this->_data;
        foreach ($path as $dir) {
            $data = &$data[$dir];
        }

        $data[$pathinfo['filename']] = $filedata;
    }

    /**
     * Magic method clone is empty to 
     * prevent duplication of connection
     *
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * Singelton Function
     *
     * @param [type] $path
     * @return object
     */
    public static function instance()
    {
        if (is_null(static::$_instance)) {
            static::$_instance = new static();
        }
        return static::$_instance;
    }
}
