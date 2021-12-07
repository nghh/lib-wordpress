<?php

namespace Nghh\Lib\Wordpress\PostTypes;

class PostTypes extends Taxonomies
{

    protected $postType;
    protected $postType_defaults = [
        'id' => '',
        'singular' => '',
        'plural' => '',
        'description' => '',
        'rewrite' => '',
        'post_type' => '',
        'icon' => 'dashicons-sticky',
        'labels' => [],
        'arguments' => [],
        'taxonomies' => [],
        'supports' => [],
        'messages' => [],
        'bulk_messages' => [],
        'help_tab' => [],
    ];

    public function __construct(array $config)
    {
        // Get Custom Post Type Config File
        $this->postType = $config;
    }

    public function registerHooks()
    {
        add_action('init', [$this, 'init']);

        // if (is_admin()) {
        //     add_action('init', array($this, 'registerMetaboxes')); // Register Metaboxes
        //     add_filter('bulk_post_updated_messages', array($this, 'bulkPostUpdatedMessages'), 10, 2); // Bulk Post Update Messages
        //     add_filter('post_updated_messages', array($this, 'postUpdatedMessages')); // Post Update Messages
        //     add_filter('enter_title_here', array($this, 'enterTitle')); // Change enter title
        //     add_filter('admin_head', array($this, 'helpTab')); // Add a Post Type Help Tab
        // }
    }

    public function init()
    {
        /**
         * We need an array of post types like:
         * [
         *  0 => post_type_array(…)
         *  1 => post_type_array(…)
         * ]
         */
        // 
        $postTypes = isset($this->postType['id']) ? [$this->postType]  : $this->postType;

        // Loop Post Types
        foreach ($postTypes as $postType) {

            // At least we need singular, plural and a unique post type id
            if (!$postType['singular'] || !$postType['plural'] || !$postType['id']) continue;
            // Merge Post Type Defaults
            $postType = wp_parse_args($postType, $this->postType_defaults);
            // Register Post Type
            $this->registerPostType($postType);
            // Register Taxonomies
            $this->registerTaxonomies($postType['id'], $postType['taxonomies']);
        }
    }

    private function registerTaxonomies(string $postType, array $taxonomies)
    {
        foreach ($taxonomies as $taxonomy) {
            new Taxonomies($postType, $taxonomy);
        }
    }
    private function registerPostType($postType)
    {
        register_post_type($postType['id'], $this->arguments($postType));
    }

    private function arguments($postType)
    {
        $default_arguments = array(
            'public'            => true,
            'show_in_nav_menus' => true,
            'show_in_admin_bar' => true,
            'show_in_menu'      => true,
            'show_ui'           => true,
            'hierarchical'      => false,
            'rewrite'           => array(),
            'has_archive'       => true,
            'show_in_rest'      => false, // /wp-json/wp/v2/post-type-singluar
            'rest_base'         => false, // e.g. /wp-json/wp/v2/post-type-plural
            'description'       => '',
            'menu_icon'         => '',
            'supports'          => [],
            'labels'            => $this->labels($postType)
        );

        return wp_parse_args($postType['arguments'], $default_arguments);
    }

    private function labels($postType)
    {
        $default_labels = array(
            'name' => $postType['plural'],
            'singular_name' => $postType['singular'],
            'add_new_item' => sprintf(__('New %s', 'bc-text'), $postType['singular']),
            'edit_item' => sprintf(__('Edit %s', 'bc-text'), $postType['singular']),
            'view_item' => sprintf(__('View %s', 'bc-text'), $postType['singular']),
            'view_items' => sprintf(__('View %s', 'bc-text'), $postType['plural']),
            'search_items' => sprintf(__('Search %s', 'bc-text'), $postType['plural']),
            'not_found' => sprintf(__('No %s found', 'bc-text'), $postType['plural']),
            'not_found_in_trash' => sprintf(__('No %s found in trash', 'bc-text'), $postType['plural']),
            'parent_item_colon' => sprintf(__('Parent %s', 'bc-text'), $postType['singular']),
            'all_items' => sprintf(__('All %s', 'bc-text'), $postType['plural']),
            'archives' => sprintf(__('%s Archives', 'bc-text'), $postType['singular']),
            'attributes' => sprintf(__('%s Attributes', 'bc-text'), $postType['singular']),
            'insert_into_item' => sprintf(__('Insert into %s', 'bc-text'), $postType['singular']),
            'uploaded_to_this_item' => sprintf(__('Uploaded to %s', 'bc-text'), $postType['singular']),
        );

        return wp_parse_args($postType['labels'], $default_labels);
    }
}
