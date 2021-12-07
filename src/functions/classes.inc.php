<?php

namespace Nghh\Lib\Wordpress\func;

// Notice helper
if (!function_exists('notice')) {
    function notice($msg = false)
    {
        if ($msg) {
            return \Nghh\Lib\Wordpress\Notice::instance()->info($msg);
        }

        return \Nghh\Lib\Wordpress\Notice::instance();
    }
}
