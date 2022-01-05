<?php

namespace Nghh\Lib\Wordpress\Utils\Admin;

use Nghh\Lib\Wordpress\Utils\Admin\Dashboard\Widgets\GithubIssues;
use Nghh\Lib\Wordpress\Utils\Admin\Dashboard\WP_Dashboard;

use function Nghh\Lib\Wordpress\func\path;

class Dashboard extends WP_Dashboard
{
    protected $widgets;

    public function __construct()
    {
        // $context: 'advanced', 'normal', 'side', 'column3', 'column4'
        // $priority: 'high', 'core', 'default', 'low'

        $this->widgets = [
            [
                'class' => 'GithubChangelog',
                'title' => __('Theme Changelog', 'nghh-text'),
                'slug' => 'ng_github_changelog',
                'context'  => 'side',
                'priority' => 'high',
                'file' => path('storage.logs', 'git.log')
            ],
            [
                'class' => 'GithubIssues',
                'title' => __('Theme Tickets', 'nghh-text'),
                'slug' => 'ng_github_issues',
                'context'  => 'side',
                'priority' => 'high',
                'config' => [
                    'github_token' => 'eea2bd9d703b7c5eafec17ac5616883535385e9b',
                    'github_owner' => 'nghh',
                    'github_repo' => 'geheimtipp',
                ]
            ]
        ];
    }

    private function _removeDashboardWidgets()
    {
        remove_action('welcome_panel', 'wp_welcome_panel');
        remove_meta_box('dashboard_incoming_links', 'dashboard', 'normal');
        remove_meta_box('dashboard_plugins', 'dashboard', 'normal');
        remove_meta_box('dashboard_primary', 'dashboard', 'side');
        remove_meta_box('dashboard_secondary', 'dashboard', 'normal');
        remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
        remove_meta_box('dashboard_recent_drafts', 'dashboard', 'side');
        remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
        remove_meta_box('dashboard_right_now', 'dashboard', 'normal');
        remove_meta_box('dashboard_activity', 'dashboard', 'normal'); //since 3.8
    }

    public function dashboardSetup()
    {
        $this->_removeDashboardWidgets();
    }

    public function registerHooks()
    {
        // Hook into the Dashboard
        add_action('wp_dashboard_setup', [$this, 'dashboardSetup']);

        // Register each Widget
        $this->registerDashboardWidgets();
    }
}
