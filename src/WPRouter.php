<?php

namespace Nghh\Lib\Wordpress;

use function Nghh\Lib\Helper\func\camel_case;
use function Nghh\Lib\Utils\func\jsroutes;

class WPRouter
{
    private $namespace;
    private $controller;
    private $body_classes;

    public function __construct($namespace)
    {
        $this->namespace = $namespace;
    }

    public function registerHooks()
    {
        add_action('template_include', [$this, 'parseQuery']);
        add_filter('body_class', [$this, 'bodyClass']);
    }

    public function registerAdminHooks()
    {
        add_action('current_screen', [$this, 'setAdminController']);
    }

    public function setAdminController($current_screen)
    {
        $this->controller = [
            'namespace' => $this->namespace,
            'name' => 'gutenberg',
            'action' => $current_screen->post_type
        ];

        // Format Controller and action names
        $this->controller['name'] = ucfirst(camel_case($this->controller['name'])) . 'Controller';
        $this->controller['action'] = camel_case($this->controller['action']);

        // Set Controller and Action for Javascript Routes
        jsroutes()->setController($this->controller['name']);
        jsroutes()->setAction($this->controller['action']);
    }

    public function bodyClass($classes)
    {
        // Reset Classes
        $classes = $this->body_classes;

        return $classes;
    }

    public function parseQuery()
    {
        global $wp_query;

        // return;
        // flush_rewrite_rules(true);
        // dd(wp_get_current_user());
        // dd($original_template);
        // dd(is_sitemap());
        // dd($wp_query->query['sitemap']);
        // if (isset($wp_query->query['sitemap']) || isset($wp_query->query['sitemap-stylesheet'])) {
        //     return;
        //     exit;
        // }
        // dd(get_queried_object());


        // https://wphierarchy.com/

        // vaR_dump(get_query_var('grid'));
        // return;
        // var_dump($wp);
        // dd($wp_query->get('custom_pagename', false));


        $this->controller = [
            'namespace' => $this->namespace,
            'name' => false,
            'action' => 'index',
            'wp_query' => $wp_query,
            'queried_object' => get_queried_object(),
            'vars' => $wp_query->query,
        ];

        // Reset Body classes
        $this->body_classes = [];

        // Check if Sitemap

        // is 404
        if ($wp_query->is_404()) {
            $this->controller['name'] = 'error';
            $this->controller['action'] = 'error404';
        }
        // Custom Rewrite Rules
        elseif ($wp_query->get('custom_pagename', false)) {
            $this->controller['name'] = 'custom-page';
            $this->controller['action'] = $wp_query->get('custom_pagename');
        }
        // Frontpage
        elseif ($wp_query->is_front_page()) {
            $this->controller['name'] = 'index';
            $this->controller['action'] = ($wp_query->is_page) ? 'page' : 'posts';
        }
        // Blog (Home) Page
        elseif ($wp_query->is_home()) {
            $this->controller['name'] = 'blog';
        }

        // Singulars
        elseif ($wp_query->is_singular()) {
            $this->controller['name'] = 'singular';
            $this->controller['action'] = get_post_type();
        }

        // Custom Post Type Archive
        elseif ($wp_query->is_post_type_archive()) {
            $this->controller['name'] = 'archive';
            $this->controller['action'] = $wp_query->queried_object->name;
        }

        // Author
        elseif ($wp_query->is_author()) {
            $this->controller['name'] = 'archive';
            $this->controller['action'] = 'author';
        }

        // Archive
        elseif ($wp_query->is_archive()) {
            $this->controller['name'] = 'archive';
            $this->controller['action'] = $wp_query->queried_object->taxonomy;
        }

        // Search
        elseif ($wp_query->is_search()) {
            // var_dump('Is Search', $wp_query->is_search);
            // var_dump('Is Search', $wp_query);
            // die();
            $this->controller['name'] = 'search';
            $this->controller['action'] = 'index';
        }

        // Is Favicon 
        elseif ($wp_query->is_favicon()) {
            var_dump('Is Favicon Query', $wp_query->is_favicon);
            die('What is this for?');
        } else {
            die('Couldn\'t find a route  in ' . __METHOD__);
        }

        // Add Body Classes
        $this->body_classes[] = $this->controller['name'];
        $this->body_classes[] = $this->controller['action'];

        // Format Controller and action names
        $this->controller['name'] = ucfirst(camel_case($this->controller['name'])) . 'Controller';
        $this->controller['action'] = camel_case($this->controller['action']);

        // Set Controller and Action for Javascript Routes
        jsroutes()->setController($this->controller['name']);
        jsroutes()->setAction($this->controller['action']);

        // Apply WP Filter
        $controller = apply_filters('nghh/lib/router', $this->controller, $wp_query);

        // Call the Controller
        $this->callController($controller);

        exit;
    }

    private function callController($controller)
    {
        // Set Controller Name with Namespace and camel case the action
        $controller_name = $controller['namespace'] . $controller['name'];
        $controller_action = $controller['action'];

        // Check if Controller exists
        if (!class_exists($controller_name)) {
            throw new \ErrorException($controller_name . ' not found!');
        }

        // Construct Controller
        $controllerObject = new $controller_name(
            $controller['wp_query'],
            $controller['queried_object'],
            $controller['vars']
        );

        // Check if Method exists
        if (!is_callable(array($controllerObject, $controller_action))) {
            throw new \ErrorException('Method ' . $controller_action . ' doesn\'t exist in ' . $controller_name);
        }

        // ... call $handler with $vars
        return call_user_func_array(
            array($controllerObject, $controller_action),
            array($controller['wp_query'], $controller['queried_object'], $controller['vars'])
        );
    }
}
