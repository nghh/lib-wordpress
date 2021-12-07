<?php

namespace Nghh\Lib\Wordpress\Save;

use function Nghh\Lib\Helper\func\camel_case;

class SavePost
{

    private $_namespace;

    public function __construct(string $namespace)
    {
        $this->_namespace = $namespace;
    }

    public function registerHooks()
    {
        add_action('save_post', [$this, 'prepare'], 10, 3);
    }

    public function prepare($post_id, $post, $update)
    {
        // Exit on create product
        if ($post->post_status == 'auto-draft') return;

        // Return if this is just a revision
        if (wp_is_post_revision($post_id)) return;

        // If doing trash, exit
        if ('trash' === $post->post_status) return;

        // If doing Autosave, exit
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

        // We are good to go

        // unhook this function so it doesn't loop infinitely
        // This happens when you use a function that will call the save_post hook like: wp_update_post()
        remove_action('save_post', [$this, 'prepare']);

        // Init Class if exist
        $class_name = ucfirst(camel_case($post->post_type));
        $class = $this->_namespace . "Save{$class_name}";

        if (class_exists($class)) {
            new $class($post_id, $post, $update);
        }

        // re-hook this function
        add_action('save_post', array($this, 'prepare'), 3, 10);
    }
}
