<?php

namespace Nghh\Lib\Wordpress;

class UserRegistration
{
    private $_args;

    public function __construct($args = [])
    {
        $default_args = [
            'allow_reset_password' => '__return_false'
        ];

        $this->_args = wp_parse_args($args, $default_args);
    }


    public function registerHooks()
    {
    }
}
