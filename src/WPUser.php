<?php

namespace Nghh\Lib\Wordpress;

class WPUser
{

    protected $_user;

    public $username = '';
    public $email = '';
    public $firstname = '';
    public $lastname = '';
    public $displayname = '';
    public $id = '';
    public $roles = [];

    public $is_guest = true;
    public $is_admin = false;

    public function __construct($author_id = false)
    {
        if ($author_id) {
            $this->get($author_id);
        }
    }

    public function permalink()
    {
        return get_author_posts_url($this->id);
    }
    /**
     * Checks wether a user has a role
    
     * Example usage:
     *
     * user()->hasRole( 'subscriber' );
     * user()->hasRole( ['subscriber', 'author] );
     *
     * @param [mixed] $role string/array
     * @return boolean
     */
    public function hasRole($role)
    {
        // If single role passed
        if (!is_array($role)) {
            return in_array($role, $this->roles);
        }

        // If array of roles passed
        foreach ($role as $r) {
            if (in_array($r, $this->roles)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get User
     * by ID 
     * otherwise get current WP User
     *
     * @param integer $user_id
     * @return void
     */
    public function get(int $user_id = null)
    {
        // Get User
        $this->_user = ($user_id) ? new \WP_User($user_id) : wp_get_current_user();

        // If User is not logged in
        if (!$this->exists()) return $this;

        // Otherwise set Properties
        $this->is_guest = false;

        $this->username     = $this->_user->user_login;
        $this->email        = $this->_user->user_email;
        $this->firstname    = $this->_user->user_firstname;
        $this->fullname     = $this->_user->user_firstname . ' ' . $this->_user->user_lastname;
        $this->lastname     = $this->_user->user_lastname;
        $this->displayname  = $this->_user->display_name;
        $this->id           = $this->_user->ID;
        $this->roles        = $this->_user->roles;

        $this->is_admin     = $this->hasRole('administrator');

        return $this;
    }

    /**
     * Returns whether the current user has the specified capability.
     *
     * Example usage:
     *
     * user()->can( 'edit_posts' );
     * user()->can( 'edit_post', $post->ID );
     * user()->can( 'edit_post_meta', $post->ID, $meta_key );

     * @param string $capability
     * @param mixed $args
     * @return boolean
     */
    public function can(string $capability, $args = null)
    {
        return $this->_user->has_cap($capability, ...$args);
    }

    public function ip()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            //check ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            //to check ip is pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return apply_filters('nghh/user/ip', $ip);
    }

    public function exists()
    {
        return $this->_user->exists();
    }

    public function bio()
    {
        return get_the_author_meta('description', $this->id);
    }

    public function validateRegistration(array $data)
    {
        $reg_errors = new \WP_Error;

        $data_defaults = array(
            'user_login'    =>   '',
            'user_email'    =>   '',
            'user_pass'     =>   '',
            'user_url'      =>   '',
            'first_name'    =>   '',
            'last_name'     =>   '',
            'nickname'      =>   '',
            'description'   =>   '',
        );

        $data = wp_parse_args($data, $data_defaults);

        // Sanitize Default Data
        $data['user_login']     = sanitize_user($data['user_login']);
        $data['user_email']     = sanitize_email($data['user_email']);
        $data['user_pass']      = esc_attr($data['user_pass']);
        $data['user_url']       = esc_url($data['user_url']);
        $data['first_name']     = sanitize_text_field($data['first_name']);
        $data['last_name']      = sanitize_text_field($data['last_name']);
        $data['nickname']       = sanitize_text_field($data['nickname']);
        $data['description']    = esc_textarea($data['description']);


        if (empty($data['user_login']) || empty($data['user_pass']) || empty($data['user_email'])) {
            $reg_errors->add('field', 'Required form field is missing');
        }

        if (4 > strlen($data['user_login'])) {
            $reg_errors->add('username_length', 'Username too short. At least 4 characters is required');
        }

        if (username_exists($data['user_login'])) {
            $reg_errors->add('user_name', 'Sorry, that username already exists!');
        }

        if (!validate_username($data['user_login'])) {
            $reg_errors->add('username_invalid', 'Sorry, the username you entered is not valid');
        }

        if (5 > strlen($data['user_pass'])) {
            $reg_errors->add('password', 'Password length must be greater than 5');
        }

        if (!is_email($data['user_email'])) {
            $reg_errors->add('email_invalid', 'Email is not valid');
        }

        if (email_exists($data['user_email'])) {
            $reg_errors->add('email', 'Email Already in use');
        }

        if (!empty($data['user_url'])) {
            if (!filter_var($data['user_url'], FILTER_VALIDATE_URL)) {
                $reg_errors->add('website', 'Website is not a valid URL');
            }
        }

        // Returns either $userdata or WP_Error object
        return (count($reg_errors->get_error_messages()) > 0) ? $reg_errors : $data;
    }

    public function completeRegistration(array $data)
    {
        return wp_insert_user($data);
    }

    private function _setProperties()
    {
    }
}
