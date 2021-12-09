<?php

namespace Nghh\Lib\Wordpress\Models;

use Nghh\Lib\Wordpress\Utils\Image;

class Singular
{

    private $_post;

    protected function __construct($wp_post)
    {
        $this->_post = get_post($wp_post);
    }

    public function getTitle($truncate = false): string
    {
        $title = apply_filters('the_title', $this->_post->post_title, $this->_post->ID);

        if ($truncate) {
            return wp_trim_words($title, $truncate);
        }

        return $title;
    }

    public function getSlug(): string
    {
        return $this->_post->post_name;
    }

    public function getContent(): string
    {
        // Must run through the_content filter so dynamic blocks will be rendered correctly
        return apply_filters('the_content', $this->_post->post_content);
    }

    public function geteExcerpt($length = 10, $strip_tags = false): string
    {
        $excerpt = ($this->_post->post_excerpt) ? $this->_post->post_excerpt : $this->_post->post_content;
        $excerpt = wp_trim_words($excerpt, $length, '…');
        $excerpt = ($strip_tags) ? strip_tags($excerpt) : $excerpt;

        return apply_filters('nghh/lib/excerpt', $excerpt);
    }

    /**
     * Weather Post has Featured Image or not
     *
     * @return boolean
     */
    public function hasFeaturedImage(): bool
    {
        return has_post_thumbnail($this->_post);
    }

    public function getFeaturedImage(): Image
    {
        $image_id = (int) get_post_thumbnail_id($this->_post);

        return new Image($image_id);
    }

    public function getPermalink(): string
    {
        return get_the_permalink($this->_post);
    }
}
