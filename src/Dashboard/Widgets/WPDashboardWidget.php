<?php

namespace Nghh\Lib\Wordpress\Dashboard\Widgets;

use function Nghh\Lib\Helper\func\path;

class WPDashboardWidget
{
    protected $default_args = [
        'title' => 'Dashboard Widget', 'nghh-text',
        'slug' => 'ng_dashboard_widget',
        'context'  => 'side',
        'priority' => 'normal'
    ];

    protected $styles = [];
    protected $scripts = [];

    public function registerStylesAndScripts()
    {
        add_action('admin_footer', [$this, 'addAdminStyles']);
        add_action('admin_footer', [$this, 'addAdminScripts']);
    }

    public function addAdminStyles()
    {
        echo '<style>' . "\n";
        foreach ($this->styles as $file) {
            echo '/* Start: ' . $file . '*/' . "\n";
            echo file_get_contents(__DIR__ . '/css/' . $file) . "\n";
            echo '/* End: ' . $file . '*/' . "\n\n\n";
        }
        echo '</style>';
    }
    public function addAdminScripts()
    {
        echo '<script>' . "\n";
        foreach ($this->scripts as $file) {
            echo '/* Start: ' . $file . '*/' . "\n";
            echo file_get_contents(__DIR__ . '/js/' . $file) . "\n";
            echo '/* End: ' . $file . '*/' . "\n\n\n";
        }
        echo '</script>';
    }
}
