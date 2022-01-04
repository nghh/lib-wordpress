<?php

namespace Nghh\Lib\Wordpress\Utils;


class Logger {

    private static $_instance;

    private function __construct()
    {}

    public function info($msg)
    {}

    public function warn($msg)
    {}

    public function error($msg)
    {}

    public function success($msg)
    {}

    /**
     * Magic method clone is empty to 
     * prevent duplication of connection
     *
     * @return void
     */
    private function __clone() { }

    /**
     * Singelton Function
     *
     * @param [type] path
     * @param array available
     * @return void
     */
    public static function instance()
    {
        if (is_null(static::$_instance)) {
            static::$_instance = new static();
        }
        return static::$_instance;
    }
}