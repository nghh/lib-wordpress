<?php
namespace Nghh\Lib\Wordpress;

class CustomPostStatus
{   
    private $_config;

    public function __construct(array $config)
    {
        // Get Config
        $this->_config = $config;
    }

    public function registerHooks()
    {
        add_action('init', [$this, 'addCustomPostStaus']);
    }

    public function addCustomPostStaus()
    {
        foreach ($this->_config as $post_status => $args) {
            register_post_status( $post_status, $args);
        }
    }
}