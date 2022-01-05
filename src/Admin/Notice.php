<?php

namespace Nghh\Lib\Wordpress\Utils\Admin;

class Notice {

    private static $_instance;

    private $_args;

    private $_notices;


    public function __construct(array $args)
    {
        $default_args = [
            'transient_name' => '_ng_admin_notices',
            'date_format' => false,
            'template' => $this->_template(),
        ];

        $this->_args = wp_parse_args($args, $default_args);

        // Get Admin Notices
        $this->_notices = (get_transient($this->_args['transient_name'])) ?: [];
    }

    public function registerHooks()
    {
        add_action('admin_notices', [$this, 'showAdminNotices']);
    }

    public function warn($msg, $dismissible = true)
    {
        $this->_addNotice($msg, 'warning', $dismissible);
    }

    public function info($msg, $dismissible = true)
    {
        $this->_addNotice($msg, 'info', $dismissible);
    }

    public function success($msg, $dismissible = true)
    {
        $this->_addNotice($msg, 'success', $dismissible);
    }

    public function error($msg, $dismissible = true)
    {
        $this->_addNotice($msg, 'error', $dismissible);
    }

    public function showAdminNotices()
    {
        // Get notices
        if (false === ($notices = get_transient($this->_args['transient_name']))) return;

        // Loop them
        foreach ($notices as $id => $notice) {
            $dismissible = ($notice['dismissible']) ? 'is-dismissible' : '';

            $message = (!is_string($notice['message'])) ? print_r($notice['message'], true) : $notice['message'];
            $date_format = ($this->_args['date_format']) ?: get_option('date_format') . ' – ' . get_option('time_format');

            printf(
                $this->_template(),
                esc_attr($notice['class']),
                esc_attr($dismissible),
                get_bloginfo('name'),
                date_i18n($date_format),
                $message
            );
        }

        // Empty Transient and 
        set_transient($this->_args['transient_name'], []);
        $this->_notices = [];
    }

    private function _addNotice($msg, $class = 'info', $dismissible = true)
    {
        $notice = array(
            'time' => current_time('mysql'),
            'class' => $class,
            'message' => $msg,
            'dismissible' => $dismissible
        );

        // Push to array
        $this->_notices[] = $notice;

        // Set Transient with notices
        set_transient($this->_args['transient_name'], $this->_notices);
    }

    private function _template()
    {
        return '
            <div class="notice notice-%1$s %2$s">
                <h3 class="notice__heading" style="margin: 1em 0 .2em 0;">%3$s</h3>
                <span class="notice__date" style="font-style: italic; color: #888; font-size: 11px;">%4$s</span>
                <p class="notice__message" style="padding: .6em 0 0; border-top: 1px solid #e8e8e8;">%5$s</p>
            </div>
        ';
    }

    public static function instance($args = [])
    {
        if (is_null(static::$_instance)) {
            static::$_instance = new static($args);
        }
        return static::$_instance;
    }
}