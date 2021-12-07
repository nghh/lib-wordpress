<?php

namespace Nghh\Lib\Wordpress;

class Cronjobs
{
    private static $_instance;
    private $_cronjobs;

    public function __construct(array $config)
    {
        $this->_cronjobs =  $config;
    }

    public function registerHooks()
    {
        // Default WP Cron Intervals
        $wp_recurrences = ['twicedaily', 'daily', 'fortnightly', 'weekly', 'monthly'];

        // Add Cron Schedules
        add_filter('cron_schedules', [$this, 'addCronSchedules']);

        // Schedule Events on each Pageload
        add_action('wp', [$this, 'scheduleEvents']);

        // Fire each scheduled event
        foreach ($this->_cronjobs as $recurrence => $cron) {
            add_action($cron['hook'], $cron['callback']);
        }
    }

    public function addCronSchedules($schedules)
    {
        foreach ($this->_cronjobs as $recurrence => $cron) {
            $schedules[$recurrence] = array(
                'interval' => $cron['interval'],
                'display' => $cron['display']
            );
        }

        return $schedules;
    }

    public function scheduleEvents()
    {
        // No Cronjobs Found
        if (!$this->_cronjobs) {
            return;
        }

        // Cron Defaults
        $cron_defaults = [
            'timestamp' => time(),
            'hook' => '',
            'args' => [],
        ];

        // Loop each Cronjob
        foreach ($this->_cronjobs as $recurrence => $cron) {
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
        // notice()->success('Theme Cron Jobs cleared');

        $cronjobs = $config;

        foreach ($cronjobs as $cron) {
            wp_clear_scheduled_hook($cron['hook']);
        }
    }

    /**
     * Singelton Function
     *
     * @param [type] $path
     * @param array $available
     * @return void
     */
    public static function instance(): object
    {
        if (is_null(static::$_instance)) {
            return static::$_instance = new static();
        }
        return static::$_instance;
    }
}
