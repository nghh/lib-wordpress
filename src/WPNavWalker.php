<?php

namespace Nghh\Lib\Wordpress;

/**
 * See:  https://github.com/wp-bootstrap/wp-bootstrap-navwalker
 */

class WPNavWalker extends \Walker_Nav_Menu
{

    public function init()
    {
    }

    public function registerHooks()
    {
        add_action('after_setup_theme', [$this, 'init']);
    }
}
