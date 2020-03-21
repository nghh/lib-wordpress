<?php

namespace Nghh\Lib\Wordpress;

use function Nghh\Lib\Helper\func\camel_case;
use function Nghh\Lib\Utils\func\jsroutes;

class Router
{

    protected $_controller;
    protected $_controller_ns;
    protected $_action;
    protected $_object;
    protected $_vars;

    public function __construct(string $namespace)
    {
        $this->_controller_ns = $namespace;
    }

    public function registerHooks()
    {
        add_action('template_include', [$this, 'fetch']);

        // AMP
        add_action('init', [$this, 'addAMPEndpoint']);
        add_filter('request', [$this, 'filterRequest']);
    }

    public function filterRequest($vars)
    {
        if (isset($vars['amp'])) $vars['amp'] = true;

        return $vars;
    }

    public function addAMPEndpoint()
    {
        // https://core.trac.wordpress.org/browser/trunk/src/wp-includes/rewrite.php#L112
        add_rewrite_endpoint('amp', EP_ALL);
    }

    public function fetch()
    {
        $page = $this->getPage();

        $controller_name = $page['controller'];
        $this->_action = $page['action'];
        $this->_controller = $this->_controller_ns . $controller_name . 'Controller';
        $this->_vars = $page['vars'];

        // Check if Controller exists
        if (!class_exists($this->_controller)) {
            die($this->_controller . ' doesn\'t exist');
        }

        // Construct Controller
        $controllerObject = new $this->_controller($this->_object);

        // Check if Method exists
        if (!is_callable(array($controllerObject, $this->_action))) {
            die('Method ' . $this->_action . ' doesn\'t exist in ' . $this->_controller);
        }

        // ... call $handler with $vars
        call_user_func_array(
            array($controllerObject, $this->_action),
            array($this->_vars)
        );
    }

    private function getPage()
    {
        global $wp_query;

        $action = 'notfound';
        $controller = 'Archive';
        $vars = [];

        if ($wp_query->is_page) {
            $controller = 'Single';
            $action = is_front_page() ? 'index' : 'page';
            $this->_object = $wp_query->queried_object;
        } elseif ($wp_query->is_home) {
            $this->_object = $wp_query->queried_object;
            /**
             * $wp_query->queried_object === null
             * means that we don't have a static frontpage
             * so our controller is an Archive Frontpage
             */
            $controller = (is_null($this->_object)) ? 'Archive' : 'Single';
            $action = 'blog';
        } elseif ($wp_query->is_single) {
            $controller = 'Single';
            $action = $wp_query->queried_object->post_type;
            $this->_object = $wp_query->queried_object;
        } elseif ($wp_query->is_category) {
            $action = 'category';
        } elseif ($wp_query->is_tag) {
            $action = 'tag';
        } elseif ($wp_query->is_tax) {
            $action = $wp_query->queried_object->taxonomy;
        } elseif ($wp_query->is_archive) {
            if ($wp_query->is_day) {
                $action = 'day';
            } elseif ($wp_query->is_month) {
                $action = 'month';
            } elseif ($wp_query->is_year) {
                $action = 'year';
            } elseif ($wp_query->is_author) {
                $action = 'author';
            } elseif ($wp_query->is_post_type_archive) {
                $controller = 'PostType';
                $action = $wp_query->query['post_type'];
            } else {
                $action = 'archive';
            }
        } elseif ($wp_query->is_search) {
            $action = 'search';
            $controller = 'Search';
        } elseif ($wp_query->is_404) {
            $action = 'error404';
            $controller = 'Single';
        }

        // Check if AMP Page
        // $vars['amp'] = false !== get_query_var('amp', false);

        // Format action to CamelCase
        $action = camel_case($action);

        // Set Controller and Action for Javascript Routes
        jsroutes()->setController($controller . 'Controller');
        jsroutes()->setAction($action);

        // Return 
        return [
            'controller' => $controller,
            'action'     => $action,
            'vars'       => $vars
        ];
    }
}
