<?php

namespace Nghh\Lib\Wordpress\Utils;

use function Nghh\Lib\Wordpress\func\camelcase;

class WP_Router
{

    private $_namesapce;
    private $_env;

    public function __construct($args = [])
    {
        $default_args = [
            'namespace' => '', 
            'env' => 'production'
        ];

        $args = wp_parse_args($args, $default_args);

        $this->_namesapce = $args['namespace'];
        $this->_env = $args['env'];
    }

    public function registerHooks()
    {
        add_filter('template_include', [$this, 'parseQuery'], 99);
    }

    /**
     * Parse Query based on WP Template Hierachy
     *
     * Prepare and call Controller
     * 
     * @see https://wphierarchy.com
     * @param [type] $template
     * @return void
     */
    public function parseQuery($template)
    {
        global $wp_query;

        $controller = [
            'name' => '',
            'action' => ''
        ];

        // Archive
        if ($wp_query->is_archive()) {
            $controller = $this->_getArchiveController($wp_query);
        }
        // Singular
        elseif ($wp_query->is_singular()) {
            $controller = $this->_getSingularController($wp_query);
        }
        // Frontpage
        elseif ($wp_query->is_front_page()) {
            $controller['name'] = 'index';
            $controller['action'] = 'frontPage';
        }
        // Blog Home
        elseif ($wp_query->is_home()) {
            $controller['name'] = 'index';
            $controller['action'] = 'home';
        }
        // Error
        elseif ($wp_query->is_404()) {
            $controller['name'] = 'error';
            $controller['action'] = 'error404';
        }
        // Search
        elseif ($wp_query->is_search()) {
            $controller['name'] = 'search';
            $controller['action'] = 'index';
        }

        // apply WP filter
        $controller = apply_filters('nghh/lib/router', $controller, $wp_query);

        // namespaced controller 
        $controller['name']  = $this->_namesapce . ucfirst(camelcase($controller['name'])) . 'Controller';
        // controller metthod
        $controller['action'] = camelcase($controller['action']);
        
        // try to call controller
        $this->_callController($controller);

        exit;
    }

    private function _callController($controller)
    {
        /**
         * TODO: If in production mode call 404 error rather new exception
         */

        // Check if Controller exists
        if (!class_exists($controller['name'])) {
            throw new \ErrorException('Controller ' . $controller['name'] . ' not found!');
        }

        // Init Controller
        $controllerObject = new $controller['name']();

        // Check if Action exists
        if (!is_callable(array($controllerObject, $controller['action']))) {
            throw new \ErrorException('Action ' . $controller['action'] . ' doesn\'t exist in ' . $controller['name']);
        }

        // ... call $handler with $vars
        return call_user_func_array(
            [$controllerObject, $controller['action']],
            []
        );
    }

    private function _getArchiveController($wp_query)
    {
        $controllerName = 'archive';
        $controllerAction = 'index';

        // Author
        if ($wp_query->is_author()) {
            $controllerAction = 'author';
        }
        // Custom Post Type Archive
        elseif ($wp_query->is_post_type_archive()) {
            $controllerAction = $wp_query->queried_object->name;
        }
        // Taxonomy
        else {
            $controllerAction = $wp_query->queried_object->taxonomy;
        }

        return [
            'name' => $controllerName,
            'action' => $controllerAction
        ];
    }

    private function _getSingularController($wp_query)
    {
        return [
            'name' => 'singular',
            'action' => $wp_query->queried_object->post_type
        ];
    }
}
