<?php

namespace NGHH\PHPLibrary\Wordpress;

/**
 * Class to display admin notices
 * 
 * @author   Jan Reiland <moin@nordgestalten.com>
 * @version  0.2
 */
class Notice
{

    private static $_instance;

    /**
     * Holds the messages
     *
     * @var array
     */
    private $_messages = array();

    /**
     * Admin Notices Transient Time
     *
     * @var string
     */
    private $_notices_transient_name;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Set Properties
        $this->_notices_transient_name = '_ng_admin_notices';
    }

    public function registerHooks() {
        add_action('admin_notices', array($this, 'show'));
    }

    public function warn($msg, $dismissible = true)
    {
        static::add($msg, 'warning', $dismissible);
    }

    public function info($msg, $dismissible = true)
    {
        static::add($msg, 'info', $dismissible);
    }

    public function success($msg, $dismissible = true)
    {
        static::add($msg, 'success', $dismissible);
    }

    public function error($msg, $dismissible = true)
    {
        static::add($msg, 'error', $dismissible);
    }

    /**
     * Static function to add messages
     * to static property $messages
     *
     * @param string  $msg            The Message to show
     * @param string  $class          Classname e.g. info, wraning, error, success
     * @param boolean $dismissible    If the user can click on the close icon
     * @return void
     */
    public static function add($msg, $class = 'info', $dismissible = true)
    {
        $message = array(
            'time' => current_time('mysql'),
            'class' => $class,
            'message' => $msg,
            'dismissible' => $dismissible
        );

        // Push to Messages
        static::instance()->push($message);
    }

    private function push($message)
    { 
        // Get Stored Messages
        if (false !== ($admin_notices = get_transient($this->_notices_transient_name))) {
            $this->messages = $admin_notices;
        }

        // Push to array
        $this->messages[] = $message;

        // Set Transient
        set_transient($this->_notices_transient_name, $this->messages);

    }
    /**
     * admin_notices callback function 
     * outputs messages array
     *
     * @return void
     */
    public function show()
    {
        // Only show if in admin
        if (!is_admin()) return;
        // If notices
        if (false === ($admin_notices = get_transient($this->_notices_transient_name))) return;

        // Loop them
        foreach ($admin_notices as $id => $msg) {
            $dismissible = ($msg['dismissible']) ? 'is-dismissible' : '';

            $message = (!is_string($msg['message'])) ? print_r($msg['message'], true) : $msg['message'];

            printf(
                '<div class="ng-notice notice notice-%2$s %3$s">
                    <div>
                        <h3 class="notice-heading" style="margin: 1em 0 .2em 0;">%1$s</h3>
                        <span class="notice-date" style="font-style: italic; color: #888; font-size: 11px;">%4$s</span>
                        <p class="notice-message" style="padding: .6em 0 0; border-top: 1px solid #E8E8E8;">%5$s</p>
                    </div>
                </div>',
                get_bloginfo('name'),
                esc_attr($msg['class']),
                esc_attr($dismissible),
                __datetime(null, null, ' @ '),
                $message
            );
        }

        // Empty Transient
        set_transient($this->_notices_transient_name, array());
        $this->messages = array();

    }


    public static function instance()
    {
        if (is_null(static::$_instance)) {
            static::$_instance = new static();
        }
        return static::$_instance;
    }

}
