<?php

namespace Nghh\Lib\Wordpress\Dashboard;

class WPDashboard
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
