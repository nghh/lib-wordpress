<?php

/**
 * Media Class
 * 
 * Class to handle Wordpress Media
 * 
 * @author   Jan Reiland <moin@nordgestalten.com>
 * @version  0.1
 */

namespace Nghh\Lib\Wordpress;

use function Nghh\Lib\Helper\func\dot_reader;

class Media
{
    protected $_post;
    protected $_meta;

    public function __construct(\WP_Post $post)
    {
        $this->_post = $post;
    }

    public function get(string $key)
    {
        return dot_reader($key, $this->_meta);
    }

    public function set(string $key, $value = '')
    {
        if ($value) {
            $this->_meta[$key] = $value;
        }
    }
}
