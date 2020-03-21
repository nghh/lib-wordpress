<?php

namespace Nghh\Lib\Wordpress\Models\Archive;

class Model
{
    protected $term;

    public function __construct(\WP_Term $term)
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

    // TODO: Default Excerpt length to config file
    public function excerpt($length = 10)
    {
        $excerpt = $this->term->description;
        $excerpt = wp_trim_words($excerpt, $length, '…');

        return $excerpt;
    }

    public function permalink()
    {
        return get_term_link($this->term);
    }
}
