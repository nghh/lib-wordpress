<?php

namespace Nghh\Theme\Utils;

use function Nghh\Lib\Helper\func\camel_case;
use function Nghh\Lib\Utils\func\logger;
use function Nghh\Theme\func\view;

class Router
{

    private $_namesapce;
    private $_env;
    private $_bodyClasses = [];

    public function __construct(string $namespace, $env = 'production')
    {
        $this->_namesapce = $namespace;
        $this->_env = $env;
    }

    public function registerHooks()
    {
        add_filter('template_include', [$this, 'parseQuery'], 99);
        add_filter('body_class', [$this, 'bodyClass']);
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
            $controller['name'] = 'home';
            $controller['action'] = 'static';
        }
        // Blog Home
        elseif ($wp_query->is_home()) {
            $controller['name'] = 'home';
            $controller['action'] = 'blog';
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

        $this->_callController($controller);
        exit;
    }

    public function bodyClass($classes)
    {
        // Reset Classes
        $classes = $this->_bodyClasses;

        return $classes;
    }

    private function _callController($controller)
    {
        $this->_bodyClasses[] = $controller['name'];
        $this->_bodyClasses[] = $controller['action'];

        $controllerName = $this->_namesapce . '\Controllers\\' . ucfirst(camel_case($controller['name'])) . 'Controller';
        $controllerAction = camel_case($controller['action']);
        $controllerError = [
            'error' => false,
            'message' => ''
        ];
        // Check if Controller exists
        if (!class_exists($controllerName)) {
            if ($this->_env !== 'production') {
                throw new \ErrorException('Controller ' . $controllerName . ' not found!');
            } else {
                logger('error')->error('Controller ' . $controllerName . ' not found!');

                global $wp_query;
                $wp_query->set_404();
                status_header(404);
                echo view('errors.404');
                exit;
            }
        }

        // Init Controller
        $controllerObject = new $controllerName();

        // Check if Action exists
        if (!is_callable(array($controllerObject, $controllerAction))) {
            if ($this->_env !== 'production') {
                throw new \ErrorException('Action ' . $controllerAction . ' doesn\'t exist in ' . $controllerName);
            } else {
                logger('error')->error('Action ' . $controllerAction . ' doesn\'t exist in ' . $controllerName);

                global $wp_query;
                $wp_query->set_404();
                status_header(404);
                echo view('errors.404');
                exit;
            }
        }


        // ... call $handler with $vars
        return call_user_func_array(
            [$controllerObject, $controllerAction],
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
