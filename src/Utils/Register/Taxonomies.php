<?php

namespace Nghh\Lib\Wordpress\Utils\Register;

class Taxonomies
{
    private $postType;
    private $taxonomy;
    private $taxonomy_defaults = [
        'id'            => '',
        'singular'      => '',
        'plural'        => '',
        'rewrite'       => [],
        'arguments'     => [],
        'labels'        => [],
        'rewrite'       => [],
        'object_type'   => [], // (array|string) (Required) Object type or array of object types with which the taxonomy should be associated.
        'remove_from_post_edit' => false
    ];

    public function __construct(string $postType, array $taxonomy)
    {
        // Set Properties
        $this->taxonomy = wp_parse_args($taxonomy, $this->taxonomy_defaults);
        $this->postType = (!empty($this->taxonomy['object_type'])) ? $this->taxonomy['object_type'] : $postType;

        if (
            !$this->taxonomy['id'] ||
            !$this->taxonomy['singular'] ||
            !$this->taxonomy['plural'] ||
            !$this->taxonomy['rewrite']
        ) return;

        // Register Taxonomy
        $this->registerTaxonomy();
    }

    private function registerTaxonomy()
    {
        register_taxonomy(
            $this->taxonomy['id'],
            $this->postType,
            $this->arguments()
        );
    }

    private function arguments()
    {
        $default_arguments = array(
            'hierarchical' => true,
            'labels' => $this->labels(),
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => $this->taxonomy['rewrite']),
        );

        return wp_parse_args($this->taxonomy['arguments'], $default_arguments);
    }

    private function labels()
    {
        $default_labels = array(
            'name' => sprintf(_x('%s', 'taxonomy general name', 'bc-text'), $this->taxonomy['plural']),
            'singular_name' => sprintf(_x('%s', 'taxonomy singular name', 'bc-text'), $this->taxonomy['singular']),
            'search_items' => sprintf(__('Search %s', 'bc-text'), $this->taxonomy['plural']),
            'all_items' => sprintf(__('All %s', 'bc-text'), $this->taxonomy['plural']),
            'parent_item' => sprintf(__('Parent %s', 'bc-text'), $this->taxonomy['singular']),
            'parent_item_colon' => sprintf(__('Parent %s:', 'bc-text'), $this->taxonomy['singular']),
            'edit_item' => sprintf(__('Edit %s', 'bc-text'), $this->taxonomy['singular']),
            'update_item' => sprintf(__('Update %s', 'bc-text'), $this->taxonomy['singular']),
            'add_new_item' => sprintf(__('Add New %s', 'bc-text'), $this->taxonomy['singular']),
            'new_item_name' => sprintf(__('New %s Name', 'bc-text'), $this->taxonomy['singular']),
            'menu_name' => sprintf(__('%s', 'bc-text'), $this->taxonomy['plural']),
            'not_found' => sprintf(__('No %s found', 'bc-text'), $this->taxonomy['plural']),
        );

        return wp_parse_args($this->taxonomy['labels'], $default_labels);
    }
}
