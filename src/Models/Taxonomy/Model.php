<?php

namespace Nghh\Lib\Wordpress\Models\Taxonomy;

class Model
{
    protected $term;
    protected $term_meta;

    protected function __construct($term)
    {
        $this->term = $term;
    }

    public function title()
    {
        return $this->term->name;
    }

    public function slug()
    {
        return $this->term->slug;
    }

    public function description()
    {
        return $this->term->description;
    }

    public function excerpt($length = 10)
    {
        $excerpt = $this->term->description;
        $excerpt = wp_trim_words($excerpt, $length, '…');

        return apply_filters('ng_term_excerpt', $excerpt);
    }
}
