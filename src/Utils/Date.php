<?php

namespace Nghh\Lib\Wordpress\Utils;

class Date
{
    private $_datetime_object;
    private $_time_format;
    private $_date_format;
    private $_timezone;
    private $_gmt_offset;
    private $_datetime_delimiter = ' - ';

    /**
     * Public available Properties
     */
    public $date;
    public $time;
    public $datetime;
    public $timestamp;
    public $mysql;

    public function __construct($date = null)
    {
        $this->_time_format = get_option('time_format');
        $this->_date_format = get_option('date_format');
        $this->_timezone    = wp_timezone();
        $this->_gmt_offset  = get_option('gmt_offset');


        // Create DateTime Object
        if($this->_isTimestamp($date)) {
            $this->_datetime_object = date_create("@$date",$this->_timezone);
        } elseif (is_string($date)) {
            $this->_datetime_object = date_create($date, $this->_timezone);
        } elseif ($date instanceof \WP_post) {
            $this->_datetime_object = date_create($date->post_date, $this->_timezone);
        } else {
            $this->_datetime_object = date_create('now', $this->_timezone);
        }

        // Set Public Properties
        $this->_setPublicProperties();

        return $this;
    }

    public function format(string $format)
    {

        return wp_date($format, $this->_datetime_object->getTimestamp(), $this->_timezone);
    }

    public function dateFormat(string $format)
    {
        $this->date = wp_date($format, $this->_datetime_object->getTimestamp());

        return $this;
    }

    public function timeFormat(string $format)
    {
        $this->time = wp_date($format, $this->_datetime_object->getTimestamp());

        return $this;
    }

    public function delimiter(string $delimiter)
    {
        $this->datetime = $this->date . $delimiter . $this->time;

        return $this;
    }

    private function _isTimestamp(string $timestamp)
    {
        return (is_numeric($timestamp) && (int)$timestamp == $timestamp);
    }

    private function _setPublicProperties()
    {
        $this->date        = wp_date($this->_date_format, $this->_datetime_object->getTimestamp());
        $this->time        = wp_date($this->_time_format, $this->_datetime_object->getTimestamp());
        $this->mysql       = wp_date('Y-m-d H:i:s', $this->_datetime_object->getTimestamp());
        $this->timestamp   = $this->_datetime_object->getTimestamp();
        $this->datetime    = $this->date . $this->_datetime_delimiter . $this->time;
    }
}
