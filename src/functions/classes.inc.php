<?php

namespace Nghh\Lib\Wordpress\func;

/**
 * Helper function for Config Class
 *
 * @param string $config
 * @return string|array|null
 */
function config(string $config)
{
    return \Nghh\Lib\Wordpress\Utils\Config::instance()->get($config);
}

// Notice helper
function notice($msg = false)
{
    if ($msg) {
        return \Nghh\Lib\Wordpress\Utils\Admin\Notice::instance()->info($msg);
    }

    return \Nghh\Lib\Wordpress\Utils\Admin\Notice::instance();
}