<?php

namespace Nghh\Lib\Wordpress;

use function Nghh\Lib\Utils\Func\logger;
use function Nghh\Lib\Wordpress\Func\notice;

class Cronjobs
{

    private $_cronjobs;

    public function __construct(array $config)
    {
        // More Infos: 
        // http://wordpress-hackers.1065353.n5.nabble.com/ALTERNATE-WP-CRON-Is-it-worth-it-tp39843p39846.html
        // define('ALTERNATE_WP_CRON', true);

        $this->_cronjobs =  $config;
    }

    public function registerHooks()
    {
        // Default WP Cron Intervals
        $wp_recurrences = ['twicedaily', 'daily', 'fortnightly', 'weekly', 'monthly'];

        // Add Custom Cron Intervals
        add_filter('cron_schedules', [$this, 'addCronIntervals']);

        // Add Action for scheduled events
        foreach ($this->_cronjobs as $recurrence => $cron) {
            add_action($cron['hook'], $cron['callback']);
        }
    }

    public function addCronIntervals($schedules)
    {
        foreach ($this->_cronjobs as $recurrence => $cron) {
            $schedules[$recurrence] = array(
                'interval' => $cron['interval'],
                'display' => $cron['display']
            );
        }

        return $schedules;
    }
    // Schedule events on activate theme/plugin
    public static function activate(array $config)
    {
        notice()->success('Theme Cron Jobs added');

        $cron_defaults = [
            'timestamp' => time(),
            'hook' => '',
            'args' => [],
        ];

        if (!$cronjobs = __config($config)) {
            logger('theme')->info('No Cronjobs found on activate theme');
            return;
        }

        foreach ($cronjobs as $recurrence => $cron) {
            $cron = wp_parse_args($cron, $cron_defaults);
            if (
                !$recurrence ||
                !$cron['hook']
            ) continue;

            if (!wp_next_scheduled($cron['hook'])) {
                wp_schedule_event(
                    $cron['timestamp'],
                    $recurrence,
                    $cron['hook'],
                    $cron['args']
                );
            }
        }
    }

    // Clear events on deactive theme/plugin
    public static function deactivate(array $config)
    {

        notice()->success('Theme Cron Jobs cleared');

        $cronjobs = $config;

        foreach ($cronjobs as $cron) {
            wp_clear_scheduled_hook($cron['hook']);
        }
    }
}
