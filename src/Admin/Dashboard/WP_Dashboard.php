<?php

namespace Nghh\Lib\Wordpress\Utils\Admin\Dashboard;

class WP_Dashboard
{
  private $_availableDashboardWidgets = [];

  public function __construct()
  {
  }

  protected function registerDashboardWidgets()
  {
    foreach ($this->widgets as $widget) {
      $class = __NAMESPACE__ . "\\Widgets\\{$widget['class']}";
      (new $class($widget))->registerHooks();
    }
  }
}
