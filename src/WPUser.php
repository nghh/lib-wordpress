<?php

namespace Nghh\Lib\Wordpress;

class WPUser
{

    protected $_user;

    public $username;
    public $email;
    public $firstname;
    public $lastname;
    public $displayname;
    public $ID;
    public $role;

    public $is_guest = true;
    public $is_admin = false;

    public function __construct($id)
    {


        $this->_user = ((int) $id) ? new \WP_User($id) : wp_get_current_user();

        // If User is logged in set properties
        if (!$this->exists()) return;

        $this->is_guest = false;

        $this->username = $this->_user->user_login;
        $this->email = $this->_user->user_email;
        $this->firstname = $this->_user->user_firstname;
        $this->lastname = $this->_user->user_lastname;
        $this->displayname = $this->_user->display_name;
        $this->id = $this->_user->ID;
        $this->role = $this->_user->roles;

        if (in_array('administrator', $this->role)) {
            $this->is_admin = true;
        }
    }

    public function getRole()
    {
        if (is_array($this->role) && !empty($this->role)) {
            return $this->role[0];
        }
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
        return apply_filters('ng_get_ip', $ip);
    }

    public function exists()
    {
        return $this->_user->exists();
    }

    public function get()
    {
    }
}
