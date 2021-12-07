<?php

namespace Nghh\Lib\Wordpress;

class Maintenance
{
  // Pages to excluded from Maintenance Status
  private $_exculde_pages = array(
    'wp-login.php',
    'wp-register.php',
    'wp-cron.php'
  );

  private $_active;

  public function __construct($active)
  {
    $this->_active = $active;
  }

  public function check()
  {
    if (
      is_user_logged_in() ||
      is_admin() ||
      $this->is_valid_page() ||
      defined('WP_CLI')
    ) return;

    $this->redirect();
  }

  private function redirect()
  {
    header('HTTP/1.0 503 Service Unavailable');
    echo __view('pages.singular.maintenance');
    exit();
  }

  private function is_valid_page()
  {
    return in_array(
      $GLOBALS['pagenow'],
      $this->_exculde_pages
    );
  }

  public function registerHooks()
  {
    if (!$this->_active) return;

    add_action('init', array($this, 'check'), 99);
  }
}
