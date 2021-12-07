<?php

namespace Nghh\Lib\Wordpress;

use function Nghh\Lib\Utils\func\logger;

class WPAjax
{

    protected function prepare($check_nonce = true)
    {
        header("Content-Encoding: None", true); // Disable Output Compression
        header('Content-Type: text/octet-stream; charset=utf-8');
        header('Cache-Control: no-cache');
        header("X-Accel-Buffering: no");

        ob_implicit_flush(true);
        set_time_limit(300);

        if (ob_get_level() == 0) ob_start();

        // Check Security Nonce
        if ($check_nonce && !check_ajax_referer('ng-ajax-nonce', 'nonce', false)) {

            logger('theme')->error('Nonce Check failed!');

            $response = [
                'error'     => true,
                'message'   => 'Nonce Check failed!',
            ];

            $this->send($response);

            wp_die();
        }
    }

    protected function send($args, $die = false)
    {
        $default_args = [
            'error'     => false,
            'message'   => '',
            'data'      => null,
            'progress'  => null
        ];

        if (is_string($args)) {
            $data = wp_parse_args(['message' => $args], $default_args);
        } else {
            $data = wp_parse_args($args, $default_args);
        }

        echo json_encode($data, JSON_HEX_APOS | JSON_HEX_QUOT);
        ob_flush();
        flush();

        if ($die) {
            wp_die();
        }
    }

    protected function error($message = '', $die = false)
    {
        $data = [
            'error'     => true,
            'message'   => $message
        ];



        echo json_encode($data, JSON_HEX_APOS | JSON_HEX_QUOT);
        ob_flush();
        flush();

        if ($die) {
            wp_die();
        }
    }
}
