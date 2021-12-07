<?php

/**
 * Wordpress Date & Time Helper
 */

namespace Nghh\Lib\Wordpress\func;

function date($date = null, $format = null)
{
    // Cache get_option DB query
    static $_format;
    // Get the default date format from db
    $date_format = (!$_format) ? $_format = get_option('date_format') : $_format;
    // Check if we have a custom format
    $date_format = (!$format) ? $date_format : $format;
    // If we have a custom time
    if ($date) {
        if ($unix = strtotime($date)) {
            return date_i18n($date_format, $unix);
        }
        return date_i18n($date_format);
    } else {
        return current_time($date_format);
    }
}

function time($time = null, $format = null)
{
    // Cache get_option DB query
    static $_format;
    // Get the default time format from db
    $time_format = (!$_format) ? $_format = get_option('time_format') : $_format;
    // Check if we have a custom format
    $time_format = (!$format) ? $time_format : $format;
    // If we have a custom time
    if ($time) {
        if ($unix = strtotime($time)) {
            return date_i18n($time_format, $unix);
        }
        return date_i18n($time_format);
    } else {
        return current_time($time_format);
    }
}

function datetime($datetime = null, $format = null, $delimiter = ' ')
{
    return date($datetime, $format) . $delimiter . time($datetime, $format);
}
