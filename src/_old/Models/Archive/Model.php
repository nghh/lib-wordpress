<?php

namespace Nghh\Lib\Wordpress\Models\Archive;

use function Nghh\Lib\Helper\func\parse_args;

class Model
{
    protected $term;

    public function __construct(\WP_Term $term)
    {
        $this->term = $term;
    }

    public function id()
    {
        return $this->term->term_id;
    }

    public function title()
    {
        return $this->term->name;
    }

    public function slug()
    {
        return $this->term->slug;
    }

    // TODO: Default Excerpt length to config file
    public function excerpt($length = 10, $by_words = true, $more = '&hellip;')
    {
        if (!$excerpt = $this->term->description) {
            return '';
        }

        if ($by_words) {
            return wp_trim_words($excerpt, $length, $more);
        } else {
            // By Characters
            return substr($excerpt, 0, strpos($excerpt, ' ', $length)) . $more;
        }
    }

    public function parent_id()
    {
        return $this->term->parent;
    }

    public function permalink()
    {
        return get_term_link($this->term);
    }

    public function getSubcategories(array $args = [])
    {

        // Get Parent Name as Kicker if Kicker is not set in Backend
        $parent = $this->term->parent;
        $exclude = $this->id();

        if ($this->term->parent === 0) {
            $parent = $this->id();
            $exclude = false;
        }

        $default_args = [
            'parent' => $parent,
            'orderby' => 'name',
            'order'   => 'ASC',
            'taxonomy' => $this->term->taxonomy,
            'exclude' => $exclude,
            'hide_empty' => true
        ];
        // Parse Args
        $args = parse_args($args, $default_args);
        $categories = get_terms($args);

        return $categories;
    }
    public function getSiblingcategories(array $args = [])
    {

        // Get Parent Name as Kicker if Kicker is not set in Backend
        $parent = $this->term->parent;
        $exclude = $this->id();

        $default_args = [
            // 'parent' => $parent,
            'orderby' => 'name',
            'order'   => 'ASC',
            'taxonomy' => $this->term->taxonomy,
            'exclude' => $exclude,
            'hide_empty' => true
        ];
        // Parse Args
        $args = parse_args($args, $default_args);
        $categories = get_terms($args);

        return $categories;
    }
}
